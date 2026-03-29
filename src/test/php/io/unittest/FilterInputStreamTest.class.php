<?php namespace io\unittest;

use io\streams\{FilterInputStream, MemoryInputStream, Streams};
use lang\IllegalStateException;
use test\{Assert, Expect, Test, Values};

class FilterInputStreamTest {

  /** Test helper */
  private function read($input, $filter) {
    return Streams::readAll(new FilterInputStream(new MemoryInputStream($input), $filter));
  }

  #[Test, Values([['str_rot13', 'string.rot13'], ['base64_encode', 'convert.base64-decode'], ['quoted_printable_encode', 'convert.quoted-printable-decode']])]
  public function builtin($encode, $filter) {
    Assert::equals('Test', $this->read($encode('Test'), $filter));
  }

  #[Test]
  public function chained() {
    Assert::equals('Test', $this->read(base64_encode(str_rot13('Test')), [
      'convert.base64-decode',
      'string.rot13',
    ]));
  }

  #[Test]
  public function lambda() {
    Assert::equals('test', $this->read('TEST', fn($chunk) => strtolower($chunk)));
  }

  #[Test, Expect(IllegalStateException::class)]
  public function lambda_raising_error() {
    $this->read('TEST', function($chunk) {
      throw new IllegalStateException('Test');
    });
  }

  #[Test]
  public function user_filter() {
    Assert::equals('test', $this->read('TEST', function($in, $out, &$consumed, $closing) {
      while ($bucket= stream_bucket_make_writeable($in)) {
        $consumed+= $bucket->datalen;
        $bucket->data= strtolower($bucket->data);
        stream_bucket_append($out, $bucket);
      }
      return PSFS_PASS_ON;
    }));
  }
}