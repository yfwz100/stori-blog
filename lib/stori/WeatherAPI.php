<?php namespace stori;

/**
 * The Weather of the site.
 */
class Weather {

  private $mmc;

  public function __construct()
  {
    $mmc = new \Memcache;
    $this->mmc = $mmc->connect(); ;
  }

  /**
   * Fetch the latest Weather.
   */
  function get() {
    if ($this->mmc) {
      $weatherStr = $this->mmc->get('weather.shenyang');
      if ($weatherStr) {
        return json_decode($weatherStr);
      }
    }
    $ret = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=Shenyang,cn&appid=53ea2c2e855f424e989b42ba4ba2c0c5');
    if ($this->mmc) {
      $this->mmc->set('weather.shenyang', $ret);
    }
    return json_decode($ret);
  }
}

class WeatherAPI {
  private static $weather;

  static function __callStatic($method, $args) {
    if (!static::$weather) {
      static::$weather = new Weather();
    }
    return call_user_func_array(array(static::$weather, $method), $args);
  }
}
