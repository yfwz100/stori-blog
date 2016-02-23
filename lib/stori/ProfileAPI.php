<?php namespace stori;

/**
 * The Weather of the site.
 */
class Profile {

  private $pdo;
  private $profile;

  public function __construct() {
    if (defined('SAE_MYSQL_HOST_M')) {
      $host = SAE_MYSQL_HOST_M;
      $db = SAE_MYSQL_DB;
      $port = SAE_MYSQL_PORT;
      $user = SAE_MYSQL_USER;
      $pass = SAE_MYSQL_PASS;
      try {
        $this->pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
        $this->profile = [];
        foreach($this->pdo->query('select * from profile') as $item) {
          $this->profile[$item['key']] = $item['value'];
        }
      } catch (\PDOException $e) {
      }
    }
  }

  function setHeaderDesc($headerDesc) {
    if ($this->pdo) {
      $stat = $this->pdo->prepare('update profile set value=:value where `key`=:key');
      return $stat->execute([
        ':key' => 'header-desc',
        ':value' => $headerDesc
      ]);
    } else {
      return false;
    }
  }

  /**
   * Fetch the latest header description.
   */
  function getHeaderDesc() {
    return $this->profile['header-desc'];
  }
}

class ProfileAPI {
  private static $profile;

  static function __callStatic($method, $args) {
    if (!static::$profile) {
      static::$profile = new Profile();
    }
    return call_user_func_array(array(static::$profile, $method), $args);
  }
}
