<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{GzDecompressingInputStream, InputStream, MemoryInputStream};
use unittest\{Test, Values};

/**
 * TestCase
 *
 * @ext   zlib
 * @see   xp://io.streams.GzDecompressingInputStream
 */
class GzDecompressingInputStreamTest extends AbstractDecompressingInputStreamTest {

  /** @return string */
  protected function filter() { return 'zlib.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.InputStream $wrapped
   * @return  int level
   * @return  io.streams.InputStream
   */
  protected function newStream(InputStream $wrapped) {
    return new GzDecompressingInputStream($wrapped);
  }

  /**
   * Compress data
   *
   * @param   string $in
   * @return  int $level
   * @return  string
   */
  protected function compress($in, $level) {
    return gzencode($in, $level);
  }

  /** @return var[][] */
  private function dataWithFileName() {
    return ["\x1F\x8B\x08\x08\x82\x86\xE0T\x00\x03test.txt\x00\xF3H\xCD\xC9\xC9\x07\x00\x82\x89\xD1\xF7\x05\x00\x00\x00"];
  }

  #[Test, Values('dataWithFileName')]
  public function data_with_original_filename($data) {
    $decompressor= $this->newStream(new MemoryInputStream($data));
    $chunk= $decompressor->read();
    $decompressor->close();
    $this->assertEquals('Hello', $chunk);
  }

  #[Test, Values('dataWithFileName')]
  public function header_with_original_filename($data) {
    $decompressor= $this->newStream(new MemoryInputStream($data));
    $decompressor->close();
    $this->assertEquals('test.txt', $decompressor->header()['filename']);
  }
}