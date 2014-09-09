<?php namespace net\xp_framework\unittest\core\generics;

use lang\types\String;
use lang\types\Integer;
use lang\Primitive;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ArrayFilter
 */
class AnonymousInstanceTest extends \unittest\TestCase {

  #[@test]
  public function anonymous_generic_is_generic() {
    $filter= newinstance('util.collections.Vector<string>', [], []);
    $this->assertTrue($filter->getClass()->isGeneric());
  }

  #[@test]
  public function anonymous_generics_arguments() {
    $filter= newinstance('util.collections.Vector<string>', [], []);
    $this->assertEquals([Primitive::$STRING], $filter->getClass()->genericArguments());
  }

  #[@test]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[@anon] util.collections.Vector<string>', [], []);
    $this->assertTrue($filter->getClass()->hasAnnotation('anon'));
  }

  #[@test]
  public function class_name_contains_argument() {
    $name= newinstance('util.collections.Vector<Object>', [])->getClassName();
    $this->assertEquals('util.collections.Vector��lang�Object', substr($name, 0, strrpos($name, '�')), $name);
  }

  #[@test]
  public function class_name_of_generic_package_class() {
    $instance= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<Object>', [], '{
      protected function accept($e) { return true; }
    }');
    $n= $instance->getClassName();
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.ArrayFilter��lang�Object',
      substr($n, 0, strrpos($n, '�')),
      $n
    );
  }

  #[@test]
  public function invocation() {
    $methods= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<Method>', [], [
      'accept' => function($method) { return 'invocation' === $method->getName(); }
    ]);
    $this->assertEquals(
      [$this->getClass()->getMethod('invocation')],
      $methods->filter($this->getClass()->getMethods())
    );
  }
}
