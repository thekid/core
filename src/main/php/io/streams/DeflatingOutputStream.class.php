<?php namespace io\streams;

use lang\Value;
use util\Comparison;

/**
 * OuputStream that deflates 
 *
 * @ext   zlib
 * @test  net.xp_framework.unittest.io.streams.DeflatingOutputStreamTest
 */
class DeflatingOutputStream implements OutputStream, Value {
  use Comparison;

  protected $out= null;
  
  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   * @param   int level default 6
   * @throws  lang.IllegalArgumentException if the level is not between 0 and 9
   */
  public function __construct(OutputStream $out, $level= 6) {
    if ($level < 0 || $level > 9) {
      throw new \lang\IllegalArgumentException('Level '.$level.' out of range [0..9]');
    }
    $this->out= Streams::writeableFd($out);
    if (!stream_filter_append($this->out, 'zlib.deflate', STREAM_FILTER_WRITE, $level)) {
      fclose($this->out);
      $this->out= null;
      throw new \io\IOException('Could not append stream filter');
    }
  }
  
  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) {
    fwrite($this->out, $arg);
  }

  /**
   * Flush this buffer
   *
   */
  public function flush() {
    fflush($this->out);
  }

  /**
   * Close this buffer. Flushes this buffer and then calls the close()
   * method on the underlying OuputStream.
   *
   */
  public function close() {
    if (!$this->out) return;
    fclose($this->out);
    $this->out= null;
  }

  /**
   * Destructor. Ensures output stream is closed.
   *
   */
  public function __destruct() {
    $this->close();
  }


  /** @return string */
  public function toString() {
    return nameof($this).'(->'.$this->out.')';
  }
}
