<?php namespace io\streams;

/**
 * Input stream that runs input through a given filter
 *
 * @test  io.unittest.FilterInputStreamTest
 */
class FilterInputStream extends FilterStream implements InputStream {
  const MODE= STREAM_FILTER_READ;

  static function __static() { }

  /**
   * Creates a new instance
   * 
   * @param io.streams.InputStream $in
   * @param string|callable|string[]|callable[]|[:string] $filters
   */
  public function __construct(InputStream $in, $filters) {
    $this->fd= Streams::readableFd($in);
    foreach (is_array($filters) ? $filters : [$filters] as $key => $filter) {
      is_array($filter) ? $this->append($key, $filter) : $this->append($filter);
    }
  }

  /**
   * Read bytes
   *
   * @param  int $limit default 8192
   * @return string
   */
  public function read($limit= 8192) {
    return fread($this->fd, $limit);
  }

  /**
   * Availably bytes
   *
   * @return int
   */
  public function available() {
    return feof($this->fd) ? 0 : 1;
  }
}