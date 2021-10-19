<?php namespace net\xp_framework\unittest\core\generics;

use lang\{GenericTypes, Primitive, XPClass};
use unittest\{BeforeClass, Test, TestCase};

/**
 * TestCase for lang.GenericTypes
 */
class GenericTypesTest extends TestCase {
  private static $filter;

  #[BeforeClass]
  public static function defineBase() {
    self::$filter= XPClass::forName('net.xp_framework.unittest.core.generics.ArrayFilter');
  }
  
  #[Test]
  public function newType0_returns_literal() {
    $this->assertEquals(
      "net\\xp_framework\\unittest\\core\\generics\\ArrayFilter\xb7\xb7\xfeint",
      (new GenericTypes())->newType0(self::$filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_returns_XPClass_instance() {
    $this->assertInstanceOf(
      XPClass::class,
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_creates_generic_class() {
    $this->assertTrue(
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->isGeneric()
    );
  }

  #[Test]
  public function newType_sets_generic_arguments() {
    $this->assertEquals(
      [Primitive::$INT],
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->genericArguments()
    );
  }
}