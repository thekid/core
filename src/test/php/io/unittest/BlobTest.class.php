<?php namespace io\unittest;

use ArrayObject;
use io\Blob;
use io\streams\MemoryInputStream;
use lang\{IllegalArgumentException, Error};
use test\{Assert, Expect, Test, Values};
use util\Bytes;

class BlobTest {

  /** @return iterable */
  private function cases() {
    yield [new Blob(), []];
    yield [new Blob('Test'), ['Test']];
    yield [new Blob(['Über']), ['Über']];
    yield [new Blob([new Blob(['Test']), 'ed']), ['Test', 'ed']];
    yield [new Blob(['Test', 'ed']), ['Test', 'ed']];
    yield [new Blob((function() { yield 'Test'; yield 'ed'; })()), ['Test', 'ed']];
    yield [new Blob(new ArrayObject(['Test', 'ed'])), ['Test', 'ed']];
    yield [new Blob(new Bytes('Test')), ['Test']];
    yield [new Blob(new MemoryInputStream('Test')), ['Test']];
  }

  #[Test]
  public function can_create() {
    new Blob();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function not_from_null() {
    new Blob(null);
  }

  #[Test, Values(from: 'cases')]
  public function iteration($fixture, $expected) {
    Assert::equals($expected, iterator_to_array($fixture));
  }

  #[Test, Values(from: 'cases')]
  public function bytes($fixture, $expected) {
    Assert::equals(new Bytes($expected), $fixture->bytes());
  }

  #[Test, Values(from: 'cases')]
  public function stream($fixture, $expected) {
    $stream= $fixture->stream();
    $data= [];
    while ($stream->available()) {
      $data[]= $stream->read();
    }
    Assert::equals($expected, $data);
  }

  #[Test, Values(from: 'cases')]
  public function string_cast($fixture, $expected) {
    Assert::equals(implode('', $expected), (string)$fixture);
  }

  #[Test, Values([[1, ['T', 'e', 's', 't']], [2, ['Te', 'st']], [3, ['Tes', 't']], [4, ['Test']]])]
  public function slices($size, $expected) {
    Assert::equals($expected, iterator_to_array((new Blob('Test'))->slices($size)));
  }

  #[Test]
  public function fill_slice() {
    Assert::equals(['Test'], iterator_to_array((new Blob(['Te', 'st']))->slices()));
  }

  #[Test]
  public function cannot_fetch_slices_twice() {
    $fixture= new Blob('Test');
    iterator_to_array($fixture->slices());

    Assert::throws(Error::class, fn() => iterator_to_array($fixture->slices()));
  }
}