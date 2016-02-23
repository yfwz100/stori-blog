<?php namespace stori;

/**
 * The Story of the site.
 */
class Story {

  private $data;
  private $pdo;

  public function __construct() {
    if (defined('SAE_MYSQL_HOST_M')) {
      $host = SAE_MYSQL_HOST_M;
      $db = SAE_MYSQL_DB;
      $port = SAE_MYSQL_PORT;
      $user = SAE_MYSQL_USER;
      $pass = SAE_MYSQL_PASS;
      try {
        $this->pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
        $this->data = [];
        $stat = $this->pdo->query('select * from `article` where state=1 order by time desc');
        foreach($stat as $row) {
          $this->data[] = $row;
        }
      } catch (\PDOException $e) {
        $this->data = null;
      }
    }

  }

  /**
   * Fetch the latest story.
   */
  function fetchLatestStory() {
    if ($this->data) {
      return $this->data[0];
    } else {
      return null;
    }
  }

  /**
   * List the stories.
   *
   * @return generator or iterable
   */
  function listEntries() {
    if ($this->data) {
      return $this->data;
    } else {
      return array();
    }
  }

  /**
   * Post story.
   */
  function postStory($title, $author, $content, $ip) {
    $pdo = $this->pdo;
    try {
      $stat = $pdo->prepare('INSERT INTO `article`(`title`,`author`,`content`, `ip`, `state`) VALUES(:title,:author,:content,:ip, 1)');
      return $stat->execute([
        ':title' => $title,
        ':author' => $author,
        ':content' => $content,
        ':ip' => $ip
      ]);
    } catch (\PDOException $e) {
      return false;
    }
  }

  function updateStory($time, $title, $author, $content) {
    $pdo = $this->pdo;
    try {
      $stat = $pdo->prepare('UPDATE `article` SET title=:title,author=:author,content=:content WHERE time=:time');
      return $stat->execute([
        ':title' => $title,
        ':author' => $author,
        ':content' => $content,
        ':time' => $time
      ]);
    } catch (\PDOException $e) {
      return false;
    }
  }

  function delStory($time) {
    try {
      $stat = $this->pdo->prepare('DELETE FROM `article` WHERE time=:time');
      return $stat->execute([
        ':time' => $time
      ]);
    } catch (\PDOException $e) {
      return false;
    }
  }
}

class StoryAPI {
  private static $story;

  static function __callStatic($method, $args) {
    if (!static::$story) {
      static::$story = new Story();
    }
    return call_user_func_array(array(static::$story, $method), $args);
  }
}
