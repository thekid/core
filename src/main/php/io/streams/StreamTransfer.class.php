<?php namespace io\streams;

use io\IOException;
use lang\Closeable;

/**
 * A stream transfer copies from an input stream to an output stream
 *
 * Example (downloading a file):
 * ```php
 * $t= new StreamTransfer(
 *   (new HttpConnection('http://example.com'))->get('/')->getInputStream(),
 *   new FileOutputStream(new File('index.html'))
 * );
 * $t->transferAll();
 * $t->close();
 * ```
 *
 * @test  net.xp_framework.unittest.io.streams.StreamTransferTest
 */
class StreamTransfer implements Closeable {
  protected $in, $out;
  
  /**
   * Creates a new stream transfer
   *
   * @param  io.streams.InputStream $in
   * @param  io.streams.OutputStream $out
   */
  public function __construct(InputStream $in, OutputStream $out) {
    $this->in= $in;
    $this->out= $out;
  }

  /**
   * Copy all available input from in
   *
   * @return int number of bytes copied
   * @throws io.IOException
   */
  public function transferAll() {
    $r= 0;
    while ($this->in->available()) {
      $r+= $this->out->write($this->in->read());
    }
    return $r;
  }

  /**
   * Transmit all available input from in, yielding control after each chunk.
   *
   * @return iterable
   * @throws io.IOException
   */
  public function transmit() {
    while ($this->in->available()) {
      $this->out->write($this->in->read());
      yield;
    }
  }

  /**
   * Close input and output streams. Guarantees to try to close both 
   * streams even if one of the close() calls yields an exception.
   *
   * @return void
   * @throws io.IOException
   */
  public function close() {
    $errors= '';
    try {
      $this->in->close();
    } catch (IOException $e) {
      $errors.= 'Could not close input stream: '.$e->getMessage().', ';
    }
    try {
      $this->out->close();
    } catch (IOException $e) {
      $errors.= 'Could not close output stream: '.$e->getMessage().', ';
    }
    if ($errors) {
      throw new IOException(rtrim($errors, ', '));
    }
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->in->toString().' -> '.$this->out->toString().')';
  }
}
