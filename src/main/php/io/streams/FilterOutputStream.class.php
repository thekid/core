<?php namespace io\streams;

/**
 * Output stream that runs input through a given filter
 *
 * @test  io.unittest.FilterOutputStreamTest
 */
class FilterOutputStream extends FilterStream implements OutputStream {
  const MODE= STREAM_FILTER_WRITE;

  static function __static() { }

  /**
   * Creates a new instance
   * 
   * @param io.streams.OutputStream $out
   * @param string|callable|string[]|callable[]|[:string] $filters
   */
  public function __construct(OutputStream $out, $filters= []) {
    $this->fd= Streams::writeableFd($out);
    foreach (is_array($filters) ? $filters : [$filters] as $key => $filter) {
      is_array($filter) ? $this->append($key, $filter) : $this->append($filter);
    }
  }

  /**
   * Write bytes
   *
   * @param  string $bytes
   * @return void
   */
  public function write($bytes) {
    fwrite($this->fd, $bytes);
  }

  /**
   * Flush
   *
   * @return void
   */
  public function flush() {
    fflush($this->fd);
  }
}