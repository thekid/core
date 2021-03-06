<?php namespace io\streams;

/**
 * Defines a stream as being seekable
 *
 * @see   php://fseek
 */
interface Seekable {

  /**
   * Seek to a given offset
   *
   * @param   int offset
   * @param   int whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws  io.IOException in case of error
   */
  public function seek($offset, $whence= SEEK_SET);

  /**
   * Return current offset
   *
   * @return  int offset
   */
  public function tell();

}
