<?php namespace io\unittest;

use io\streams\{FilterOutputStream, MemoryOutputStream};
use test\verify\Runtime;
use test\{Assert, Test, Values};

class FilterOutputStreamTest {

  /** Test helper */
  private function write($input, $filter) {
    $out= new MemoryOutputStream();

    $fixture= new FilterOutputStream($out, $filter);
    $fixture->write($input);
    $fixture->close();

    return $out->bytes();
  }

  #[Test, Values([['str_rot13', 'string.rot13'], ['base64_encode', 'convert.base64-encode'], ['quoted_printable_encode', 'convert.quoted-printable-encode']])]
  public function builtin($encode, $filter) {
    Assert::equals($encode('Test'), $this->write('Test', $filter));
  }

  #[Test]
  public function chained() {
    Assert::equals(base64_encode(str_rot13('Test')), $this->write('Test', [
      'string.rot13',
      'convert.base64-encode',
    ]));
  }

  #[Test]
  public function lambda() {
    Assert::equals('test', $this->write('TEST', fn($chunk) => strtolower($chunk)));
  }

  #[Test]
  public function user_filter() {
    Assert::equals('test', $this->write('TEST', function($in, $out, &$consumed, $closing) {
      while ($bucket= stream_bucket_make_writeable($in)) {
        $consumed+= $bucket->datalen;
        $bucket->data= strtolower($bucket->data);
        stream_bucket_append($out, $bucket);
      }
      return PSFS_PASS_ON;
    }));
  }

  #[Test]
  public function append() {
    $out= new MemoryOutputStream();
    $fixture= new FilterOutputStream($out);

    $toupper= $fixture->append('string.toupper');
    $rot13= $fixture->append('string.rot13');
    $fixture->write('test');

    $fixture->remove($rot13);
    $fixture->write('e');

    $fixture->remove($toupper);
    $fixture->write('d');

    $fixture->close();

    Assert::equals(str_rot13(strtoupper('test')).strtoupper('e').'d', $out->bytes());
  }

  #[Test]
  public function base64_line_length() {
    Assert::equals(
      "VGhpcyBp\r\ncyBhIHRl\r\nc3QuCg==",
      $this->write("This is a test.\n", ['convert.base64-encode' => ['line-length' => 8]])
    );
  }

  #[Test, Runtime(extensions: ['zlib'])]
  public function deflate_level() {
    Assert::equals(
      gzdeflate('This is a test. This also.', 6),
      $this->write('This is a test. This also.', ['zlib.deflate' => ['level' => 6]])
    );
  }
}