<?php namespace net\xp_framework\unittest\core;

use lang\FunctionType;
use lang\Primitive;
use lang\XPClass;
use lang\Type;
use lang\ArrayType;
use lang\MapType;

/**
 * TestCase
 *
 * @see      xp://lang.FunctionType
 */
class FunctionTypeTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new FunctionType([Primitive::$STRING], Primitive::$STRING);
  }

  #[@test]
  public function returns() {
    $this->assertEquals(Type::$VOID, (new FunctionType([Primitive::$STRING], Type::$VOID))->returns());
  }

  #[@test]
  public function signature() {
    $this->assertEquals([Primitive::$STRING], (new FunctionType([Primitive::$STRING], Type::$VOID))->signature());
  }

  #[@test]
  public function a_function_accepting_one_string_arg_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string): string')
    );
  }

  #[@test]
  public function a_function_accepting_two_string_args_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING, Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string, string): string')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_bool() {
    $this->assertEquals(
      new FunctionType([], Primitive::$BOOL),
      FunctionType::forName('function(): bool')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_a_function_type() {
    $this->assertEquals(
      new FunctionType([], new FunctionType([Primitive::$STRING], Primitive::$INT)),
      FunctionType::forName('function(): function(string): int')
    );
  }

  #[@test]
  public function a_function_which_accepts_a_function_type() {
    $this->assertEquals(
      new FunctionType([new FunctionType([Primitive::$STRING], Primitive::$INT)], Type::$VAR),
      FunctionType::forName('function(function(string): int): var')
    );
  }

  #[@test]
  public function a_function_accepting_an_array_of_generic_objects_and_not_returning_anything() {
    $this->assertEquals(
      new FunctionType([new ArrayType(XPClass::forName('lang.Generic'))], Type::$VOID),
      FunctionType::forName('function(lang.Generic[]): void')
    );
  }

  #[@test]
  public function function_with_zero_args_is_instance_of_zero_arg_function_type() {
    $this->assertTrue((new FunctionType([], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_instance_of_two_arg_function_type() {
    $this->assertTrue((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_type_hinted_arg_is_instance_of_function_type_with_class_signature() {
    $this->assertTrue((new FunctionType([XPClass::forName('lang.XPClass')], Type::$VAR))->isInstance(
      function(XPClass $c) { }
    ));
  }

  #[@test]
  public function function_with_array_hinted_arg_is_instance_of_function_type_with_array_signature() {
    $this->assertTrue((new FunctionType([Primitive::$ARRAY], Type::$VAR))->isInstance(
      function(array $a) { }
    ));
  }

  #[@test]
  public function function_with_callable_hinted_arg_is_instance_of_function_type_with_function_signature() {
    $this->assertTrue((new FunctionType([new FunctionType(null, Type::$VAR)], Type::$VAR))->isInstance(
      function(callable $a) { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_not_instance_of_zero_arg_function_type() {
    $this->assertFalse((new FunctionType([], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_zero_args_is_not_instance_of_two_arg_function_type() {
    $this->assertFalse((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test, @values([
  #  [function() { }],
  #  [function($a) { }],
  #  [function($a, $b) { }],
  #  [function(array $a, callable $b, \lang\Object $c, $d, $e= false) { }]
  #])]
  public function function_with_no_args_is_instance_of_null_signature_function_type($value) {
    $this->assertTrue((new FunctionType(null, Type::$VAR))->isInstance($value));
  }

  #[@test]
  public function builtin_strlen_isInstance() {
    $this->assertTrue((new FunctionType([Primitive::$STRING], Primitive::$INT))->isInstance('strlen'));
  }

  #[@test]
  public function parameter_types_not_verified_for_builtin_strlen() {
    $this->assertTrue((new FunctionType([Type::$VAR], Primitive::$INT))->isInstance('strlen'));
  }

  #[@test]
  public function return_type_not_verified_for_builtin_strlen() {
    $this->assertTrue((new FunctionType([Primitive::$STRING], Type::$VAR))->isInstance('strlen'));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [['lang\XPClass', 'forName']]
  #])]
  public function array_referencing_static_class_method_is_instance($value) {
    $type= new FunctionType([Primitive::$STRING, XPClass::forName('lang.IClassLoader')], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [['lang\XPClass', 'forName']]
  #])]
  public function array_referencing_static_class_method_is_instance_without_optional_parameter($value) {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [['lang\XPClass', 'forName']]
  #])]
  public function return_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([Primitive::$STRING], Type::$VOID);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [['lang\XPClass', 'forName']]
  #])]
  public function parameter_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([XPClass::forName('lang.Object')], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.Object', 'new']], ['lang.Object::new'],
  #  [['lang\Object', 'new']]
  #])]
  public function array_referencing_constructor_is_instance($value) {
    $type= new FunctionType([], XPClass::forName('lang.Object'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([[['util.collections.Vector<int>', 'new']], ['util.collections.Vector<int>::new']])]
  public function array_referencing_generic_constructor_is_instance($value) {
    $type= new FunctionType([], Type::forName('util.collections.Vector<int>'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test]
  public function array_referencing_non_existant_static_class_method_is_instance() {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance(['lang.XPClass', 'non-existant']));
  }

  #[@test]
  public function array_referencing_instance_method_is_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertTrue($type->isInstance([$this, 'getName']));
  }

  #[@test]
  public function return_type_verified_for_instance_methods() {
    $type= new FunctionType([], Primitive::$INT);
    $this->assertFalse($type->isInstance([$this, 'getName']));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function array_referencing_instance_method_via_string_is_not_instance($value) {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test]
  public function array_referencing_non_existant_instance_method_is_not_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isInstance([$this, 'non-existant']));
  }

  #[@test]
  public function lang_Type_forName_parsed_function_type() {
    $this->assertEquals(
      new FunctionType([Type::$VAR], Primitive::$BOOL),
      Type::forName('function(var): bool')
    );
  }

  #[@test]
  public function lang_Type_forName_parsed_wildcard_function_type() {
    $this->assertEquals(
      new FunctionType(null, Primitive::$BOOL),
      Type::forName('function(?): bool')
    );
  }

  #[@test]
  public function cast() {
    $value= function($a) { };
    $this->assertEquals($value, (new FunctionType([Type::$VAR], Type::$VAR))->cast($value));
  }

  #[@test]
  public function cast_null() {
    $this->assertNull((new FunctionType([Type::$VAR], Type::$VAR))->cast(null));
  }

  #[@test, @expect('lang.ClassCastException'), @values([
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']],
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function cannot_cast_this($value) {
    (new FunctionType([Type::$VAR], Type::$VAR))->cast($value);
  }

  #[@test, @expect('lang.ClassCastException')]
  public function return_type_verified_for_instance_methods_when_casting() {
    (new FunctionType([], Primitive::$VOID))->cast([$this, 'getName']);
  }

  #[@test, @expect('lang.ClassCastException')]
  public function number_of_required_parameters_is_verified_when_casting() {
    (new FunctionType([], Type::$VAR))->cast('strlen');
  }

  #[@test, @expect('lang.ClassCastException')]
  public function excess_parameters_are_verified_when_casting() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->cast('strlen');
  }

  #[@test]
  public function create_instances_from_function() {
    $value= (new FunctionType([], Type::$VAR))->newInstance(function() { return 'Test'; });
    $this->assertEquals('Test', $value());
  }

  #[@test]
  public function create_instances_from_string_referencing_builtin() {
    $value= (new FunctionType([Primitive::$STRING], Type::$VAR))->newInstance('strlen');
    $this->assertEquals(4, $value('Test'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function number_of_required_parameters_is_verified_when_creating_instances() {
    (new FunctionType([], Type::$VAR))->newInstance('strlen');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function excess_parameters_are_verified_when_creating_instances() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->newInstance('strlen');
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [['lang\XPClass', 'forName']]
  #])]
  public function create_instances_from_array_referencing_static_class_method($value) {
    $value= (new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass')))->newInstance($value);
    $this->assertEquals(XPClass::forName('lang.Object'), $value('lang.Object'));
  }

  #[@test, @values([
  #  [['lang.Object', 'new']], ['lang.Object::new'],
  #  [['lang\Object', 'new']]
  #])]
  public function create_instances_from_array_referencing_constructor($value) {
    $new= (new FunctionType([], XPClass::forName('lang.Object')))->newInstance($value);
    $this->assertInstanceOf('lang.Object', $new());
  }


  #[@test, @values([
  #  [['unittest.TestCase', 'new']], ['unittest.TestCase::new'],
  #  [['unittest\TestCase', 'new']]
  #])]
  public function create_instances_from_array_referencing_declared_constructor($value) {
    $new= (new FunctionType([Type::$VAR], XPClass::forName('unittest.TestCase')))->newInstance($value);
    $this->assertEquals($this, $new($this->getName()));
  }

  #[@test, @values([[['util.collections.Vector<int>', 'new']], ['util.collections.Vector<int>::new']])]
  public function create_instances_from_array_referencing_generic_constructor($value) {
    $new= (new FunctionType([Type::$VAR], Type::forName('util.collections.Vector<int>')))->newInstance($value);
    $this->assertInstanceOf('util.collections.Vector<int>', $new());
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([[['lang.Generic', 'new']], ['lang.Generic::new']])]
  public function cannot_create_instances_from_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('lang.Generic')))->newInstance($value);
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([[['util.collections.IList<int>', 'new']], ['util.collections.IList<int>::new']])]
  public function cannot_create_instances_from_generic_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('util.collections.IList<int>')))->newInstance($value);
  }

  #[@test]
  public function create_instances_from_array_referencing_instance_method() {
    $value= (new FunctionType([], Primitive::$STRING))->newInstance([$this, 'getName']);
    $this->assertEquals($this->getName(), $value());
  }

  #[@test]
  public function create_instances_from_array_referencing_generic_instance_method() {
    $vector= create('new util.collections.Vector<int>', [1, 2, 3]);
    $value= (new FunctionType([], new ArrayType('int')))->newInstance([$vector, 'elements']);
    $this->assertEquals([1, 2, 3], $value());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function generic_argument_parameter_types_are_verified_when_creating_instances() {
    $vector= create('new util.collections.Vector<int>');
    (new FunctionType([Primitive::$STRING], Primitive::$INT))->newInstance([$vector, 'add']);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function generic_argument_return_type_is_verified_when_creating_instances() {
    $vector= create('new util.collections.Vector<int>');
    (new FunctionType([Primitive::$INT], Primitive::$STRING))->newInstance([$vector, 'add']);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function return_type_verified_for_instance_methods_when_creating_instances() {
    (new FunctionType([], Primitive::$VOID))->newInstance([$this, 'getName']);
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  null,
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']],
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function cannot_create_instances_from($value) {
    (new FunctionType([], Type::$VAR))->newInstance($value);
  }

  #[@test]
  public function can_assign_to_itself() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom($type));
  }

  #[@test, @values([
  #  [[]],
  #  [[Type::$VAR]],
  #  [[Type::$VAR, Type::$VAR]]
  #])]
  public function can_assign_to_wildcard_function($signature) {
    $type= new FunctionType(null, Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType($signature, Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_smaller() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_larger() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_return_type_not_assignable() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[@test]
  public function signature_matching() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType([Primitive::$STRING], Type::$VAR)));
  }

  #[@test]
  public function invoke() {
    $f= function(Type $in) { return $in->getName(); };
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    $this->assertEquals('string', $t->invoke($f, [Primitive::$STRING]));
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  null,
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']],
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName'],
  #  function() { }
  #])]
  public function invoke_not_instance($value) {
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    $t->invoke($value);
  }

  #[@test, @expect('lang.reflect.TargetInvocationException')]
  public function invoke_wraps_exceptions_in_TargetInvocationExceptions() {
    $t= new FunctionType([], Primitive::$VOID);
    $t->invoke(function() { throw new \lang\IllegalArgumentException('Test'); }, []);
  }

  #[@test]
  public function invoke_does_not_wrap_SystemExit() {
    $t= new FunctionType([], Primitive::$VOID);
    try {
      $t->invoke(function() { throw new \lang\SystemExit(0); }, []);
      $this->fail('No exception thrown', null, 'lang.SystemExit');
    } catch (\lang\SystemExit $expected) {
      // OK
    }
  }

  #[@test]
  public function cast_loads_class_if_necessary_with_new() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('net.xp_framework.unittest.core.FunctionTypeFixture::new');
  }

  #[@test]
  public function cast_loads_class_if_necessary_with_method() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('net.xp_framework.unittest.core.FunctionTypeMethodFixture::method');
  }
}
