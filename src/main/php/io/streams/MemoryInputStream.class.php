<?php namespace io\streams;

use lang\Value;
use util\Comparison;

/**
 * InputStream that reads from a given string.
 *
 * @test  net.xp_framework.unittest.io.streams.MemoryInputStreamTest
 */
class MemoryInputStream implements InputStream, Seekable, Value {
  use Comparison;

  protected $pos= 0;
  protected $bytes;

  /**
   * Constructor
   *
   * @param  string $bytes
   */
  public function __construct($bytes) {
    $this->bytes= (string)$bytes;
  }

  /**
   * Read a string
   *
   * @param  int $limit default 8192
   * @return string
   */
  public function read($limit= 8192) {
    $chunk= substr($this->bytes, $this->pos, $limit);
    $this->pos+= strlen($chunk);
    return $chunk;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   * @return int
   */
  public function available() {
    return strlen($this->bytes) - $this->pos;
  }

  /**
   * Close this output stream.
   *
   * @return void
   */
  public function close() { }
  
  /**
   * Seek to a given offset
   *
   * @param  int $offset
   * @param  int $whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws io.IOException in case of error
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $this->pos= $offset; break;
      case SEEK_CUR: $this->pos+= $offset; break;
      case SEEK_END: $this->pos= strlen($this->bytes) + $offset; break;
    }
  }

  /**
   * Return current offset
   *
   * @return int
   */
  public function tell() { return $this->pos; }

  /**
   * Return size
   *
   * @return int
   */
  public function size() { return strlen($this->bytes); }

  /** @return string */
  public function toString() {
    return nameof($this).'(@'.$this->pos.' of '.strlen($this->bytes).' bytes)';
  }
}
