<?php namespace io\streams;

use Iterator, Closure, Traversable;
use lang\IllegalArgumentException;

/** @test io.unittest.IterableInputStreamTest */
class IterableInputStream implements InputStream {
  private $iterator;
  private $buffer= null;

  /** @param iterable|function(): Iterator $input */
  public function __construct($input) {
    if ($input instanceof Iterator) {
      $this->iterator= $input;
    } else if ($input instanceof Closure) {
      $this->iterator= cast($input(), Iterator::class);
    } else if (is_iterable($input)) {
      $this->iterator= (function() use($input) { yield from $input; })();
    } else {
      throw new IllegalArgumentException('Expected iterable|function(): Iterator, have '.typeof($input));
    }
  }

  /** @return int */
  public function available() {
    if (null !== $this->buffer) {
      return strlen($this->buffer);
    } else if ($this->iterator->valid()) {
      $this->buffer= $this->iterator->current();
      $this->iterator->next();
      return strlen($this->buffer);
    } else {
      return 0;
    }
  }

  /**
   * Reads up to a given limit
   *
   * @param  int $limit
   * @return string
   */
  public function read($limit= 8192) {
    if (null !== $this->buffer) {
      // Continue draining the buffer
    } else if ($this->iterator->valid()) {
      $this->buffer= $this->iterator->current();
      $this->iterator->next();
    } else {
      return '';
    }

    $chunk= substr($this->buffer, 0, $limit);
    $this->buffer= $limit >= strlen($this->buffer) ? null : substr($this->buffer, $limit);
    return $chunk;
  }

  /** @return void */
  public function close() {
    // NOOP
  }
}