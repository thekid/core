<?php namespace io;

use IteratorAggregate, Traversable;
use io\streams\{InputStream, IterableInputStream};
use lang\{Value, IllegalArgumentException};
use util\{Bytes, Objects};

/** @test io.unittest.BlobTest */
class Blob implements IteratorAggregate, Value {
  private $parts;
  private $iterator= null;

  /**
   * Creates a new blob from parts
   *
   * @param  iterable|string|util.Bytes|io.streams.InputStream $parts
   * @throws lang.IllegalArgumentException
   */
  public function __construct($parts= []) {
    if ($parts instanceof InputStream) {
      $this->iterator= (function() {
        while ($this->parts->available()) {
          yield $this->parts->read();
        }
        $this->parts->close();
      })();
    } else if ($parts instanceof Bytes || is_string($parts)) {
      $this->iterator= (function() { yield (string)$this->parts; })();
    } else if (is_iterable($parts)) {
      $this->iterator= (function() {
        foreach ($this->parts as $part) {
          yield (string)$part;
        }
      })();
    } else {
      throw new IllegalArgumentException(sprintf(
        'Expected iterable|string|util.Bytes|io.streams.InputStream, have %s',
        typeof($parts)
      );
    }

    $this->parts= $parts;
  }

  /** @return iterable */
  public function getIterator(): Traversable { return $this->iterator; }

  /** @return util.Bytes */
  public function bytes() { return new Bytes(...$this->iterator); }

  /** @return io.streams.InputStream */
  public function stream() {
    return $this->parts instanceof InputStream
      ? $this->parts
      : new IterableInputStream($this->iterator)
    ;
  }

  /** @return string */
  public function hashCode() { return 'B'.Objects::hashOf($this->parts); }

  /** @return string */
  public function toString() { return nameof($this).'('.Objects::stringOf($this->parts).')'; }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self
      ? Objects::compare($this->parts, $value->parts)
      : 1
    ;
  }

  /** @return string */
  public function __toString() {
    $bytes= '';
    foreach ($this->iterator as $chunk) {
      $bytes.= $chunk;
    }
    return $bytes;
  }
}