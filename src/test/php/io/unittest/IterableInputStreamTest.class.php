<?php namespace io\unittest;

use ArrayIterator, ArrayObject;
use io\streams\IterableInputStream;
use lang\IllegalArgumentException;
use test\{Assert, Expect, Test};

class IterableInputStreamTest {

  /** Creates a stream from the given input and reads all available chunks */
  private function read($input, $limit= 8192) {
    $stream= new IterableInputStream($input);
    $data= [];
    while ($stream->available()) {
      $data[]= $stream->read($limit);
    }
    return $data;
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function not_from_null() {
    new IterableInputStream(null);
  }

  #[Test]
  public function read_empty() {
    Assert::equals([], $this->read([]));
  }

  #[Test]
  public function read_array() {
    Assert::equals(['Test', 'ed'], $this->read(['Test', 'ed']));
  }

  #[Test]
  public function read_iterator() {
    Assert::equals(['Test', 'ed'], $this->read(new ArrayIterator(['Test', 'ed'])));
  }

  #[Test]
  public function read_iterator_aggregate() {
    Assert::equals(['Test', 'ed'], $this->read(new ArrayObject(['Test', 'ed'])));
  }

  #[Test]
  public function read_closure() {
    Assert::equals(['Test', 'ed'], $this->read(function() {
      yield 'Test';
      yield 'ed';
    }));
  }

  #[Test]
  public function one_chunk_under_limit() {
    Assert::equals(
      [str_repeat('*', 9)],
      $this->read([str_repeat('*', 9)], 10)
    );
  }

  #[Test]
  public function one_chunk_when_limit_reached() {
    Assert::equals(
      [str_repeat('*', 10)],
      $this->read([str_repeat('*', 10)], 10)
    );
  }

  #[Test]
  public function new_chunk_when_limit_exceeded() {
    Assert::equals(
      [str_repeat('*', 10), '*'],
      $this->read([str_repeat('*', 11)], 10)
    );
  }

  #[Test]
  public function chunks_when_limit_exceeded() {
    Assert::equals(
      [str_repeat('*', 10), str_repeat('*', 10), '*'],
      $this->read([str_repeat('*', 21)], 10)
    );
  }
}