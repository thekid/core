<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\ClassLoader;

/**
 * Base class for runtime type definitions
 *
 * @see   xp://lang.ClassLoader
 * @see   https://github.com/xp-framework/xp-framework/issues/94
 */
abstract class RuntimeTypeDefinitionTest extends TestCase {

  /**
   * Wraps around a function which defines types, giving it unique names and
   * verifying the type has not been defined before.
   *
   * @param  string $annotations
   * @param  var $define A function
   * @return lang.XPClass
   * @throws unittest.AssertionFailedError
   */
  protected function defineType($annotations, $define) {
    $t= $this->getClassName().'__'.$this->name;
    $spec= trim($annotations.' '.$t);
    if (interface_exists(\xp::reflect($t), false) || class_exists(\xp::reflect($t), false)) {
      $this->fail('Type may not exist!', $t, null);
    }
    return $define($spec);
  }

  /**
   * Define a type
   *
   * @return  lang.XPClass
   */
  protected abstract function define();

  #[@test]
  public function returns_XPClass_instances() {
    $this->assertInstanceOf('lang.XPClass', $this->define());
  }

  #[@test]
  public function classloader_of_defined_type_is_DynamicClassLoader() {
    $this->assertInstanceOf('lang.DynamicClassLoader', $this->define()->getClassLoader());
  }

  #[@test]
  public function package_name() {
    $this->assertEquals('net.xp_framework.unittest.reflection', $this->define()->getPackage()->getName());
  }

  #[@test]
  public function default_classloader_provides_defined_type() {
    $this->assertTrue(ClassLoader::getDefault()->providesClass($this->define()->getName()));
  }

  #[@test]
  public function default_classloader_provides_packaged_of_defined_type() {
    $this->assertTrue(ClassLoader::getDefault()->providesPackage($this->define()->getPackage()->getName()));
  }

  #[@test]
  public function declares_passed_annotation() {
    $this->assertTrue($this->define('#[@test]')->hasAnnotation('test'));
  }

  #[@test]
  public function declares_passed_annotation_with_value() {
    $this->assertEquals('/rest', $this->define('#[@webservice(path= "/rest")]')->getAnnotation('webservice', 'path'));
  }
}
