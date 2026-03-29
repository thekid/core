<?php namespace io\streams;

use lang\IllegalArgumentException;
use php_user_filter;

/** @see https://www.php.net/manual/en/filters.php */
abstract class FilterStream {
  public static $filters= [];
  private static $id= 0;
  protected $fd;
  private $remove= [];

  static function __static() {

    // Suppress "Declaration should be compatible" in PHP 7.4
    stream_filter_register('iostrf.*', get_class(@new class() extends php_user_filter {
      public function filter($in, $out, &$consumed, bool $closing): int {
        return FilterStream::$filters[$this->filtername]($in, $out, $consumed, $closing);
      }
    }));
  }

  /**
   * Appends a filter and returns its handle
   *
   * @param  string|callable $filter
   * @param  array $parameters
   * @return mixed
   * @throws lang.IllegalArgumentException
   */
  public function append($filter, $parameters= []) {
    if (is_string($filter)) {
      $name= $filter;
    } else {
      $this->remove[]= $name= 'iostrf.'.(++self::$id);
      self::$filters[$name]= $filter;
    }

    if (!($handle= stream_filter_append($this->fd, $name, static::MODE, $parameters))) {
      throw new IllegalArgumentException('Could not append stream filter '.$name);
    }
    return $handle;
  }

  /**
   * Removes a stream filter
   *
   * @param  mixed $handle
   * @return void
   * @throws lang.IllegalArgumentException
   */
  public function remove($handle) {
    if (!stream_filter_remove($handle)) {
      throw new IllegalArgumentException('Could not remove stream filter '.$name);
    }
  }

  /**
   * Close stream
   *
   * @return void
   */
  public function close() {
    fclose($this->fd);
    $this->fd= null;
  }

  /** Ensures stream is closed */
  public function __destruct() {
    foreach ($this->remove as $filter) {
      unset(self::$filters[$filter]);
    }
    $this->fd && $this->close();
  }
}