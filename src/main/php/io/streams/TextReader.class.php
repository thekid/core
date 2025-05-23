<?php namespace io\streams;

use lang\FormatException;

/**
 * Reads text from an underlying input stream, converting it from the
 * given character set to our internal encoding.
 *
 * @ext   iconv
 * @test  io.unittest.TextReaderTest
 */
class TextReader extends Reader {
  private $charset;
  private $cl= 1, $of= 0;

  /**
   * Constructor. Creates a new TextReader on an underlying input
   * stream with a given charset.
   *
   * @param   io.streams.InputStream|io.Channel|string $arg The input source
   * @param   string $charset the charset the stream is encoded in or NULL to trigger autodetection by BOM
   * @throws  lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= null) {
    parent::__construct($arg);
    switch ($this->charset= strtolower($charset ?: $this->detectCharset())) {
      case 'utf-16le': $this->cl= 2; $this->of= 0; break;
      case 'utf-16be': $this->cl= 2; $this->of= 1; break;
      case 'utf-32le': $this->cl= 4; $this->of= 0; break;
      case 'utf-32be': $this->cl= 4; $this->of= 3; break;
    }
  }

  /**
   * Returns the character set used
   *
   * @return string
   */
  public function charset() { return $this->charset; }

  /**
   * Detect charset of stream
   *
   * @see     http://de.wikipedia.org/wiki/Byte_Order_Mark
   * @see     http://unicode.org/faq/utf_bom.html
   * @return  string
   */
  protected function detectCharset() {
    $c= $this->stream->read(2);

    // Check for UTF-16 (BE)
    if ("\376\377" === $c) {
      $this->start= 2;
      return 'utf-16be';
    }

    // Check for UTF-16 (LE)
    if ("\377\376" === $c) {
      $this->start= 2;
      return 'utf-16le';
    }

    // Check for UTF-8 BOM
    if ("\357\273" === $c && "\357\273\277" === ($c.= $this->stream->read(1))) {
      $this->start= 3;
      return 'utf-8';
    }

    // Fall back to ISO-8859-1
    $this->buf= (string)$c;
    $this->start= 0;
    return 'iso-8859-1';
  }

  /**
   * Read a number of characters
   *
   * @param   int size default 8192
   * @return  string NULL when end of data is reached
   */
  public function read($size= 8192) {
    if (0 === $size) return '';
    $this->beginning= false;

    // fread() will always work with bytes, so reading may actually read part of
    // an incomplete multi-byte sequence. In this case, iconv_strlen() will raise
    // a warning, and return FALSE (or 0, for the "less than" operator), causing
    // the loop to read more. Maybe there's a more elegant way to do this?
    $l= 0;
    $bytes= '';
    do {
      if ('' === ($chunk= $this->read0($size - $l))) break;
      $bytes.= $chunk;
    } while (($l= iconv_strlen($bytes, $this->charset)) < $size);

    if (false === $l) {
      $message= key(@\xp::$errors[__FILE__][__LINE__ - 3]);
      \xp::gc(__FILE__);
      throw new FormatException($message);
    }

    \xp::gc(__FILE__);
    if ('' === $bytes) {
      $this->buf= null;
      return null;
    } else {
      return iconv($this->charset, \xp::ENCODING, $bytes);
    }
  }

  /**
   * Read an entire line
   *
   * @return  string NULL when end of data is reached
   */
  public function readLine() {
    if (null === $this->buf) return null;

    $this->beginning= false;
    do {
      $p= strcspn($this->buf, "\r\n");
      $l= strlen($this->buf);
      if ($p >= $l - $this->cl) {
        $chunk= $this->stream->read();
        if ('' === $chunk) {
          if ('' === $this->buf) return null;
          $bytes= $p === $l ? $this->buf : substr($this->buf, 0, $p - $this->of);
          $this->buf= null;
          break;
        }
        $this->buf.= $chunk;
        continue;
      }

      $o= ("\r" === $this->buf[$p] && "\n" === $this->buf[$p + $this->cl]) ? $this->cl * 2 : $this->cl;
      $p-= $this->of;
      $bytes= substr($this->buf, 0, $p);
      $this->buf= substr($this->buf, $p + $o);
      break;
    } while (true);

    // echo "<<< '", addcslashes($bytes, "\0..\17!\177..\377"), "'\n";

    $line= iconv($this->charset, \xp::ENCODING, $bytes);
    if (false === $line) {
      $message= key(@\xp::$errors[__FILE__][__LINE__ - 2]);
      \xp::gc(__FILE__);
      throw new FormatException($message);
    }
    return $line;
  }
}
