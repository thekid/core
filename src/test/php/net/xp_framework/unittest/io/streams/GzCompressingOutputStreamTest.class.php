<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\GzCompressingOutputStream;
use unittest\Assert;

/**
 * TestCase
 *
 * @ext      zlib
 * @see      xp://io.streams.GzCompressingOutputStream
 */
class GzCompressingOutputStreamTest extends AbstractCompressingOutputStreamTest {

  /** @return string */
  protected function filter() { return 'zlib.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.OutputStream wrapped
   * @return  int level
   * @return  io.streams.OutputStream
   */
  protected function newStream(\io\streams\OutputStream $wrapped, $level) {
    return new GzCompressingOutputStream($wrapped, $level);
  }

  /**
   * Compress data
   *
   * @param   string in
   * @return  int level
   * @return  string
   */
  protected function compress($in, $level) {
    return gzencode($in, $level);
  }

  /**
   * Asserts GZ-encoded data equals. Ignores the first 10 bytes - the
   * GZIP header, which will change every time due to current Un*x 
   * timestamp being embedded therein.
   *
   * @param   string expected
   * @param   string actual
   * @throws  unittest.AssertionFailedError
   */
  protected function assertCompressedDataEquals($expected, $actual) {
    parent::assertCompressedDataEquals(substr($expected, 10), substr($actual, 10));
  }
}