<?php require 'vendor/autoload.php';

session_start();

$app = new \Slim\App(new \Slim\Container(require('config.php')));

$app->get('/', function ($req, $res) {
  $article = \stori\StoryAPI::fetchLatestStory();
  $date = date_parse($article['time']);
  return $this->view->render($res, '/index.phtml', [
    'year' => $date['year'],
    'month' => $date['month'],
    'day' => $date['day'],
    'title' => $article['title'],
    'content' => $article['content']
  ]);
});

$app->get('/weather', function ($req, $res) {
  if ($req->isXhr()) {
    $ret = $res->withHeader('Content-type', 'application/json');
    $body = $ret->getBody();
    $body->write(file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=Shenyang,cn&appid=53ea2c2e855f424e989b42ba4ba2c0c5&c&units=metric&lang=zh_cn '));
    return $ret;
  } else {
    return $res->withStatus(403);
  }
});

$app->get('/blog', function ($req, $res) {
  return $this->view->render($res, '/blog.phtml', [
    'stories' => \stori\StoryAPI::listEntries()
  ]);
});

$app->get('/marked', function ($req, $res) {
  return $this->view->render($res, '/marked.phtml', [
    'noteTopics' => \stori\MarkAPI::listNoteTopics()
  ]);
});

$app->get('/marked/{id}', function ($req, $res, $arg) {
  return $this->view->render($res, '/marked-note.phtml', [
    'topic' => \stori\MarkAPI::getNoteTopic($arg['id']),
    'notes' => \stori\MarkAPI::listNotes($arg['id'])
  ]);
});

$app->get('/contact', function ($req, $res) {
  return $this->view->render($res, 'contact.phtml');
});

$app->get('/about', function ($req, $res) {
  return $this->view->render($res, '/about.phtml');
});

$app->run();
