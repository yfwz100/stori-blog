<?php require 'vendor/autoload.php';

session_start();

$config = require 'admin.config.php';

$app = new \Slim\App(new \Slim\Container([
  'settings' => [
    'displayErrorDetails' => true,
  ],
  'view' => function ($c) {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/templates/admin');
  }
]));

// auth & portal.

$app->get('/', function ($req, $res) {
  if (isset($_SESSION['admin'])) {
    $params = $req->getQueryParams();
    if ($params['action'] === 'logout') {
      session_unset();
    } else {
      return $this->view->render($res, '/index.phtml', [
        'stories' => \stori\StoryAPI::listEntries(),
        'noteTopics' => \stori\MarkAPI::listNoteTopics()
      ]);
    }
  }
  return $this->view->render($res, '/login.phtml');
});

$app->post('/', function ($req, $res) {
  $body = $req->getParsedBody();
  if ($body['password'] == $config['password']) {
    if (isset($_SESSION['admin'])) {
      session_unset();
    } else {
      $_SESSION['admin'] = 'yfwz100';
    }
  }
  return $res->withStatus(302)->withHeader('Location', '/admin.php');
});

function auth($req, $res, $next) {
  if (isset($_SESSION['admin'])) {
    return $next($req, $res);
  } else {
    return $res->withStatus(302)->withHeader('Location', '/admin.php');
  }
}

// modules.

$app->group('/blog', function () {
  $this->post('/new', function ($req, $res) {
    $params = $req->getParsedBody();
    $time = strip_tags($params['time']);
    $title = strip_tags($params['title']);
    $author = strip_tags($params['author']);
    $content = \Michelf\Markdown::defaultTransform(strip_tags($params['content']));
    $ip = $_SERVER["HTTP_CLIENT_IP"];
    $state = 0;

    if (empty($title) || empty($content) || empty($author)) {
      return $res->withJson(['status' => -2], 400);
    }

    if (empty($time)) {
      $ret = \stori\StoryAPI::postStory($title, $author, $content, $ip);
    } else {
      $ret = \stori\StoryAPI::updateStory($time, $title, $author, $content);
    }
    
    return $res->withJson(['status' => $ret]);
  });
  
  $this->get('/del/{time}', function ($req, $res, $arg) {
    $ret = \stori\StoryAPI::delStory($time);
    if ($ret) {
      return $res->withJson(['status' => $ret]);
    } else {
      return $res->withJson(['status' => -1], 400);
    }
  });
})->add(auth);

$app->group('/mark', function () {
  $this->post('/new', function ($req, $res) {
    $params = $req->getParsedBody();
    $url = strip_tags($params['url']);
    $topic = strip_tags($params['topic']);
    $comment = strip_tags($params['comment']);

    if (empty($url) || empty($topic) || empty($comment)) {
      die('{"status": -2}');
    }

    $ret = \stori\MarkAPI::postNoteFromUrl($url, $topic, $comment);

    $res = $res->withStatus(302)->withHeader('Location', '/admin.php')->withHeader('Content-Type', 'application/json');
    $res->getBody()->write($ret);
    return $res;
  });
})->add(auth);

$app->group('/profile', function () {
  $this->post('/header/desc', function ($req, $res) {
    $body = $req->getParsedBody();
    $headerDesc = $body['header-desc'];
    if (!empty($headerDesc)) {
      \stori\ProfileAPI::setHeaderDesc($headerDesc);
      return $res->withStatus(302)->withHeader('Location', '/admin.php');
    } else {
      return $res->withStatus(403);
    }
  });
  $this->post('/profile/about', function ($req, $res) {
    return $res->withStatus(405);
  });
})->add(auth);

$app->run();
