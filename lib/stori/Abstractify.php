<?php namespace stori;

libxml_use_internal_errors(true);

class Abstractify {

  private $body;
  private $alpha;
  private $tagBlackList;

  function __construct($body) {
    $this->body = $body;
    $this->alpha = 1.5;
    $this->tagBlackList = ['script', 'textarea', 'style'];
  }

  protected static function getScore($node) {
    $pnode = $node->parentNode;
    if (!empty($pnode)) {
      $pnodeLen = strlen(trim($pnode->textContent));
      if ($pnodeLen > 0) {
        return strlen(trim($node->textContent)) / $pnodeLen;
      } else {
        return 0;
      }
    } else {
      return 0;
    }
  }

  protected function extractComps($body) {
    if ($body->hasChildNodes()) {
      // extract nodes and its score.
      $childNodes = $body->childNodes;
      $data = [];
      $sscore = 0;
      foreach ($childNodes as $node) {
        $score = self::getScore($node);
        if ($score > 0) {
          $data[] = [$node, $score];
          $sscore -= $score * log($score);
        }
      }
      if ($sscore >= 0 && $sscore < $this->alpha) {
        $ret = [];
        foreach ($data as $item) {
          $node = $item[0];
          if (array_search($node->tagName, $this->tagBlackList) === FALSE) {
            $comps = $this->extractComps($node);
            $ret = array_merge($ret, $comps);
          }
        }
        return $ret;
      }
    }
    // fall back to merged state.
    $score = $this->getScore($body);
    return [[trim($body->textContent), $score]];
  }

  function getAbstract() {
    $comps = $this->extractComps($this->body);
    $abstract = $comps[0][0];
    foreach($comps as $comp) {
      if (strlen($comp[0]) > strlen($abstract)) {
        $abstract = $comp[0];
      }
    }
    return $abstract;
  }

  static function fromURL($url) {
    $html = file_get_contents($url);
    $doc = new \DOMDocument();
    $doc->loadHTML($html);
    $abst = new Abstractify($doc);
    return [
      'title' => $doc->getElementsByTagName('title')->item(0)->textContent,
      'content' => $abst->getAbstract()
    ];
  }
}

//$url = 'http://grouplens.org/datasets/movielens/';
//$url = 'https://www.zhihu.com/question/32164316/answer/55036580';
//$url = 'http://www.jianshu.com/p/9f3cbdb1c51f';
//$url = 'http://neunews.neu.edu.cn/campus/media/2016-02-05/43251.html';
//$url = 'http://www.neu.edu.cn';
//$url = 'http://view.news.qq.com/original/intouchtoday/n3131.html';
//file_put_contents('test.json', json_encode(Abstractify::fromURL($url)));
//file_put_contents('test.json', Abstractify::fromURL($url));
