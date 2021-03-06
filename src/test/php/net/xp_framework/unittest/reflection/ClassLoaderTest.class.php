<?php namespace net\xp_framework\unittest\reflection;

use lang\archive\{Archive, ArchiveClassLoader};
use lang\reflect\Package;
use lang\{
  ClassCastException,
  ClassDependencyException,
  ClassFormatException,
  ClassLoader,
  ClassNotFoundException,
  IllegalStateException,
  XPClass
};
use unittest\{Expect, Test, Values};

/**
 * TestCase for classloading
 *
 * Makes use of the following classes in the package
 * net.xp_framework.unittest.reflection.classes:
 *
 * - ClassOne, ClassTwo - exist in the same directory as this class
 * - ClassThree, ClassFour - exist in "lib/three-and-four.xar"
 * - ClassFive - exists in "contained.xar" within "lib/three-and-four.xar"
 *
 * @see   xp://lang.ClassLoader
 * @see   xp://lang.XPClass#getClassLoader
 * @see   https://github.com/xp-framework/xp-framework/pull/235
 */
class ClassLoaderTest extends \unittest\TestCase {
  protected
    $libraryLoader   = null,
    $brokenLoader    = null,
    $containedLoader = null;

  /**
   * Register XAR
   *
   * @param  io.File $file
   * @return lang.IClassLoader
   */
  protected function registerXar($file) {
    return ClassLoader::registerLoader(new ArchiveClassLoader(new Archive($file)));
  }
    
  /**
   * Setup this test. Registeres class loaders deleates for the 
   * afforementioned XARs
   */
  public function setUp() {
    $lib= typeof($this)->getPackage()->getPackage('lib');
    $this->libraryLoader= $this->registerXar($lib->getResourceAsStream('three-and-four.xar'));
    $this->brokenLoader= $this->registerXar($lib->getResourceAsStream('broken.xar'));
    $this->containedLoader= $this->registerXar($this->libraryLoader->getResourceAsStream('contained.xar'));
  }
  
  /**
   * Tear down this test. Removes classloader delegates registered 
   * during setUp()
   */
  public function tearDown() {
    ClassLoader::removeLoader($this->libraryLoader);
    ClassLoader::removeLoader($this->containedLoader);
    ClassLoader::removeLoader($this->brokenLoader);
  }

  #[Test, Values(['net.xp_framework.unittest.reflection.classes.ClassOne', 'net.xp_framework.unittest.reflection.classes.InterfaceOne', 'net.xp_framework.unittest.reflection.classes.TraitOne'])]
  public function classloader_for_types_alongside_this_class($type) {
    $this->assertEquals(
      typeof($this)->getClassLoader(),
      XPClass::forName($type)->getClassLoader()
    );
  }

  #[Test]
  public function twoClassesFromSamePlace() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne')->getClassLoader(),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassTwo')->getClassLoader()
    );
  }

  #[Test]
  public function archiveClassLoader() {
    $this->assertInstanceOf(
      ArchiveClassLoader::class,
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree')->getClassLoader()
    );
  }

  #[Test]
  public function containedArchiveClassLoader() {
    $this->assertInstanceOf(
      ArchiveClassLoader::class,
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassFive')->getClassLoader()
    );
  }

  #[Test]
  public function twoClassesFromArchive() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree')->getClassLoader(),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassFour')->getClassLoader()
    );
  }

  #[Test]
  public function loadClass() {
    $this->assertEquals(XPClass::forName('lang.Value'), ClassLoader::getDefault()->loadClass('lang.Value'));
  }

  #[Test]
  public function findThisClass() {
    $this->assertEquals(
      typeof($this)->getClassLoader(),
      ClassLoader::getDefault()->findClass(nameof($this))
    );
  }

  #[Test]
  public function findNullClass() {
    $this->assertNull(ClassLoader::getDefault()->findClass(null));
  }

  #[Test]
  public function initializerCalled() {
    $name= 'net.xp_framework.unittest.reflection.LoaderTestClass';
    if (class_exists(literal($name), false)) {
      return $this->fail('Class "'.$name.'" may not exist!');
    }

    $this->assertTrue(ClassLoader::getDefault()
      ->loadClass($name)
      ->getMethod('initializerCalled')
      ->invoke(null)
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadNonExistantClass() {
    ClassLoader::getDefault()->loadClass('@@NON-EXISTANT@@');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/No types declared in .+/'])]
  public function loadClassFileWithoutDeclaration() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.NoClass');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/File does not declare type `.+FalseClass`, but `.+TrueClass`/'])]
  public function loadClassFileWithIncorrectDeclaration() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.FalseClass');
  }

  #[Test, Expect(ClassDependencyException::class)]
  public function loadClassWithBrokenDependency() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.BrokenDependencyClass');
  }

  #[Test]
  public function loadClassFileWithRecusionInStaticBlock() {
    with ($p= Package::forName('net.xp_framework.unittest.reflection.classes')); {
      $two= $p->loadClass('StaticRecursionTwo');
      $one= $p->loadClass('StaticRecursionOne');
      $this->assertEquals($two, $one->getField('two')->get(null));
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function newInstance() {
    (new XPClass('DoesNotExist'))->reflect();
  }

  #[Test, Expect(ClassCastException::class)]
  public function newInstance__PHP_Incomplete_Class() {
    new XPClass(unserialize('O:12:"DoesNotExist":0:{}'));
  }
  
  #[Test]
  public function packageContents() {
    $this->assertEquals(
      ['net/', 'META-INF/', 'contained.xar'],
      $this->libraryLoader->packageContents('')
    );
  }

  #[Test]
  public function providesPackage() {
    $this->assertTrue($this->libraryLoader->providesPackage('net.xp_framework'));
  }
  
  #[Test]
  public function doesNotProvideAPackage() {
    $this->assertFalse($this->libraryLoader->providesPackage('net.xp_frame'));
  }

  #[Test]
  public function doesNotProvideClassone() {
    $this->assertFalse(ClassLoader::getDefault()
      ->providesClass('net.xp_framework.unittest.reflection.classes.Classone')
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadingClassoneFails() {
    ClassLoader::getDefault()
      ->loadClass('net.xp_framework.unittest.reflection.classes.Classone')
    ;
  }

  #[Test]
  public function providesExistantUri() {
    $this->assertTrue(
      ClassLoader::getDefault()->providesUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[Test]
  public function doesNotProvideNonExistantUri() {
    $this->assertFalse(
      ClassLoader::getDefault()->providesUri('non/existant/Class.class.php')
    );
  }

  #[Test]
  public function findExistantUri() {
    $cl= ClassLoader::getDefault();
    $this->assertEquals(
      $cl->findClass('net.xp_framework.unittest.reflection.classes.ClassOne'),
      $cl->findUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[Test]
  public function cannotFindNontExistantUri() {
    $this->assertNull(ClassLoader::getDefault()->findUri('non/existant/Class.class.php'));
  }

  #[Test]
  public function loadUri() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne'),
      ClassLoader::getDefault()->loadUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadNonExistantUri() {
    ClassLoader::getDefault()->loadUri('non/existant/Class.class.php');
  }
}