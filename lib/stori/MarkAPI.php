<?php namespace stori;

/**
 * The Mark of the site.
 */
class Mark {

  private $pdo;

  public function __construct()
  {
    if (defined('SAE_MYSQL_HOST_M')) {
      $host = SAE_MYSQL_HOST_M;
      $db = SAE_MYSQL_DB;
      $port = SAE_MYSQL_PORT;
      $user = SAE_MYSQL_USER;
      $pass = SAE_MYSQL_PASS;
      try {
        $this->pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
      } catch (\PDOException $e) {
      }
    }
  }

  /**
   * List the notes.
   *
   * @param $topicId the id of topic.
   * @return generator or iterable
   */
  function listNotes($topicId) {
    if ($this->pdo) {
      $stat = $this->pdo->prepare('select * from `note` where topic=:topic');
      if ($stat->execute([':topic' => $topicId])) {
        return $stat->fetchAll();
      }
    }
    return [];
  }

  /**
   * Post note.
   */
  function postNote($title, $abstract, $url, $topic, $comment='') {
    $pdo = $this->pdo;
    if ($pdo) {
      try {
        $stat = $pdo->prepare('INSERT INTO `note`(`title`,`abstract`,`url`,`topic`,`comment`) VALUES(:title,:abstract,:url,:topic,:comment)');
        return $stat->execute([
          ':title' => $title,
          ':abstract' => $abstract,
          ':url' => $url,
          ':topic' => $topic,
          ':comment' => $comment
        ]);
      } catch (\PDOException $e) {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Post note from URL.
   */
  function postNoteFromURL($url, $topic, $comment='') {
    $abstract = Abstractify::fromURL($url);
    $title = $abstract['title'];
    $content = $abstract['content'];
    return $this->postNote($title, $content, $url, $topic, $comment);
  }

  function getNoteTopic($id) {
    if ($this->pdo) {
      $stat = $this->pdo->prepare('select * from `note_topic` where id=:id');
      if ($stat->execute([':id' => $id])) {
        return $stat->fetch();
      }
    }
    return ['name' => 'Unknown'];
  }

  /**
   * List the note topics.
   *
   * @return generator or iterable
   */
  function listNoteTopics() {
    if ($this->pdo) {
      $data = [];
      $stat = $this->pdo->query('select * from `note_topic`');
      foreach($stat as $row) {
        $data[] = $row;
      }
      return $data;
    }
    return [];
  }

  /**
   * Post note topic.
   */
  function postNoteTopic($name, $description) {
    $pdo = $this->pdo;
    if ($pdo) {
      try {
        $stat = $pdo->prepare('INSERT INTO `note_topic`(`name`,`description`) VALUES(:name,:description)');
        return $stat->execute([
          ':name' => $name,
          ':description' => $description,
        ]);
      } catch (\PDOException $e) {
        return false;
      }
    } else {
      return false;
    }
  }
}

class MarkAPI {
  private static $Mark;

  static function __callStatic($method, $args) {
    if (!static::$Mark) {
      static::$Mark = new Mark();
    }
    return call_user_func_array(array(static::$Mark, $method), $args);
  }
}
