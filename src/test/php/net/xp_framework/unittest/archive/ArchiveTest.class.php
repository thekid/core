<?php namespace net\xp_framework\unittest\archive;

use unittest\TestCase;
use lang\archive\Archive;
use net\xp_framework\unittest\io\Buffer;
use io\File;
use io\streams\MemoryInputStream;
use io\streams\Streams;

/**
 * Base class for archive file tests
 *
 * @see  xp://net.xp_framework.unittest.archive.ArchiveV1Test
 * @see  xp://net.xp_framework.unittest.archive.ArchiveV2Test
 * @see   xp://lang.archive.Archive
 */
abstract class ArchiveTest extends TestCase {
  
  /**
   * Returns the xar version to test
   *
   * @return  int
   */
  protected abstract function version();

  /**
   * Asserts on entries in an archive
   *
   * @param   lang.archive.Archive a
   * @param   [:string] entries
   * @throws  unittest.AssertionFailedError
   */
  protected function assertEntries(Archive $a, array $entries) {
    $a->open(ARCHIVE_READ);
    $actual= [];
    while ($key= $a->getEntry()) {
      $actual[$key]= $a->extract($key);
    }
    $a->close();
    $this->assertEquals($entries, $actual);
  }
  
  /**
   * Returns an empty XAR archive as a buffer
   *
   * @return net.xp_framework.unittest.io.Buffer
   */
  protected function buffer($version) {
    static $header= [
      0 => "not.an.archive",
      1 => "CCA\1\0\0\0\0",
      2 => "CCA\2\0\0\0\0",
    ];

    return new File(Streams::readableFd(new MemoryInputStream($header[$version].str_repeat("\0", 248))));
  }

  #[@test, @expect('lang.FormatException')]
  public function open_non_archive() {
    $a= new Archive($this->buffer(0));
    $a->open(ARCHIVE_READ);
  }

  #[@test]
  public function version_equals_stream_version() {
    $a= new Archive($this->buffer($this->version()));
    $a->open(ARCHIVE_READ);
    $this->assertEquals($this->version(), $a->version);
  }

  #[@test]
  public function version_equals_resource_version() {
    $a= new Archive($this->getClass()->getPackage()->getResourceAsStream('v'.$this->version().'.xar'));
    $a->open(ARCHIVE_READ);
    $this->assertEquals($this->version(), $a->version);
  }

  #[@test]
  public function contains_non_existant() {
    $a= new Archive($this->buffer($this->version()));
    $a->open(ARCHIVE_READ);
    $this->assertFalse($a->contains('DOES-NOT-EXIST'));
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function extract_non_existant() {
    $a= new Archive($this->buffer($this->version()));
    $a->open(ARCHIVE_READ);
    $a->extract('DOES-NOT-EXIST');
  }

  #[@test]
  public function entries_for_empty_archive_are_an_empty_array() {
    $a= new Archive($this->buffer($this->version()));
    $a->open(ARCHIVE_READ);
    $this->assertEntries($a, []);
  }

  #[@test]
  public function contains_existant() {
    $a= new Archive($this->getClass()->getPackage()->getResourceAsStream('v'.$this->version().'.xar'));
    $a->open(ARCHIVE_READ);
    $this->assertTrue($a->contains('contained.txt'));
  }

  #[@test]
  public function entries_contain_file() {
    $a= new Archive($this->getClass()->getPackage()->getResourceAsStream('v'.$this->version().'.xar'));
    $a->open(ARCHIVE_READ);
    $this->assertEntries($a, ['contained.txt' => "This file is contained in an archive!\n"]);
  }

  #[@test]
  public function creating_empty_archive() {
    $a= new Archive(new Buffer());
    $a->open(ARCHIVE_CREATE);
    $a->create();
    
    $this->assertEntries($a, []);
  }

  #[@test]
  public function creating_archive() {
    $contents= array(
      'lang/Object.class.php'    => '<?php class Object { }',
      'lang/Type.class.php'      => '<?php class Type extends Object { }'
    );
    
    $a= new Archive(new Buffer());
    $a->open(ARCHIVE_CREATE);
    foreach ($contents as $filename => $bytes) {
      $a->addBytes($filename, $bytes);
    }
    $a->create();
    
    $this->assertEntries($a, $contents);
  }
}
