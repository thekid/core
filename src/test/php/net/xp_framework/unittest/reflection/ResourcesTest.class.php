<?php namespace net\xp_framework\unittest\reflection;

use io\File;
use lang\archive\{Archive, ArchiveClassLoader};
use lang\{ClassLoader, ElementNotFoundException};
use unittest\Assert;
use unittest\{Expect, Test};

class ResourcesTest {
  private $cl;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->cl= ClassLoader::registerLoader(new ArchiveClassLoader(new Archive(typeof($this)
      ->getPackage()
      ->getPackage('lib')
      ->getResourceAsStream('three-and-four.xar'))
    ));
  }

  /** @return void */
  #[After]
  public function tearDown() {
    ClassLoader::removeLoader($this->cl);
  }

  /**
   * Helper method for getResource() and getResourceAsStream()
   *
   * @param  string $contents
   * @throws unittest.AssertionFailedError
   */
  private function assertManifestFile($contents) {
    Assert::equals(
      "[runnable]\nmain-class=\"remote.server.impl.ApplicationServer\"",
      trim($contents)
    );
  }
  
  #[Test]
  public function findResource() {
    Assert::instance(
      ArchiveClassLoader::class,
      ClassLoader::getDefault()->findResource('META-INF/manifest.ini')
    );
  }

  #[Test]
  public function getResource() {
    $this->assertManifestFile(ClassLoader::getDefault()->getResource('META-INF/manifest.ini'));
  }

  #[Test]
  public function getResourceAsStream() {
    $stream= ClassLoader::getDefault()->getResourceAsStream('META-INF/manifest.ini');
    Assert::instance(File::class, $stream);
    $stream->open(File::READ);
    $this->assertManifestFile($stream->read($stream->size()));
    $stream->close();
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function nonExistantResource() {
    ClassLoader::getDefault()->getResource('::DOES-NOT-EXIST::');
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function nonExistantResourceStream() {
    ClassLoader::getDefault()->getResourceAsStream('::DOES-NOT-EXIST::');
  }
}