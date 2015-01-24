<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\FunctionType;
use lang\MapType;
use lang\XPClass;
use util\collections\Vector;
use util\collections\HashTable;

/**
 * TestCase
 *
 * @see      xp://lang.Type
 */
class TypeTest extends TestCase {

  #[@test]
  public function stringType() {
    $this->assertEquals(Primitive::$STRING, Type::forName('string'));
  }

  #[@test]
  public function intType() {
    $this->assertEquals(Primitive::$INT, Type::forName('int'));
  }

  #[@test]
  public function doubleType() {
    $this->assertEquals(Primitive::$DOUBLE, Type::forName('double'));
  }

  #[@test]
  public function boolType() {
    $this->assertEquals(Primitive::$BOOL, Type::forName('bool'));
  }

  #[@test]
  public function voidType() {
    $this->assertEquals(Type::$VOID, Type::forName('void'));
  }

  #[@test]
  public function varType() {
    $this->assertEquals(Type::$VAR, Type::forName('var'));
  }

  #[@test]
  public function arrayOfString() {
    $this->assertEquals(ArrayType::forName('string[]'), Type::forName('string[]'));
  }

  #[@test]
  public function mapOfString() {
    $this->assertEquals(MapType::forName('[:string]'), Type::forName('[:string]'));
  }

  #[@test]
  public function objectType() {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName('lang.Object'));
  }

  #[@test]
  public function objectTypeShortClass() {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName('Object'));
  }

  #[@test]
  public function objectTypeLiteral() {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName('lang\\Object'));
  }

  #[@test]
  public function objectTypeLiteralLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByLiteralLoadedOnDemand';

    Type::forName($literal);
    $this->assertTrue(class_exists($literal, false));
  }

  #[@test]
  public function objectTypeLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByNameLoadedOnDemand';
    $name= 'net.xp_framework.unittest.reflection.TypeRefByNameLoadedOnDemand';

    Type::forName($name);
    $this->assertTrue(class_exists($literal, false));
  }

  #[@test]
  public function closureType() {
    $this->assertEquals(new XPClass('Closure'), Type::forName('Closure'));
  }

  #[@test]
  public function generic() {
    $this->assertEquals(
      XPClass::forName('util.collections.Vector')->newGenericType([Primitive::$STRING]),
      Type::forName('util.collections.Vector<string>')
    );
  }

  #[@test]
  public function genericOfGeneneric() {
    $t= XPClass::forName('util.collections.Vector');
    $this->assertEquals(
      $t->newGenericType([$t->newGenericType([Primitive::$INT])]), 
      Type::forName('util.collections.Vector<util.collections.Vector<int>>')
    );
  }

  #[@test]
  public function genericObjectType() {
    with ($t= Type::forName('util.collections.HashTable<String, Object>')); {
      $this->assertInstanceOf('lang.XPClass', $t);
      $this->assertTrue($t->isGeneric());
      $this->assertEquals(XPClass::forName('util.collections.HashTable'), $t->genericDefinition());
      $this->assertEquals(
        [XPClass::forName('lang.types.String'), XPClass::forName('lang.Object')],
        $t->genericArguments()
      );
    }
  }

  #[@test]
  public function deprecated_arrayKeyword() {
    $this->assertEquals(ArrayType::forName('var[]'), Type::forName('array'));
  }

  #[@test]
  public function deprecated_stringTypeVariant() {
    $this->assertEquals(Primitive::$STRING, Type::forName('char'));
  }

  #[@test]
  public function deprecated_intTypeVariant() {
    $this->assertEquals(Primitive::$INT, Type::forName('integer'));
  }

  #[@test]
  public function deprecated_mapOfStringDeprecatedSyntax() {
    $this->assertEquals(MapType::forName('[:string]'), Type::forName('array<string, string>'));
  }

  #[@test]
  public function deprecated_stringArrayDeprecatedSyntax() {
    $this->assertEquals(ArrayType::forName('string[]'), Type::forName('array<string>'));
  }

  #[@test]
  public function deprecated_doubleTypeVariant() {
    $this->assertEquals(Primitive::$DOUBLE, Type::forName('float'));
  }

  #[@test]
  public function deprecated_booleanTypeVariant() {
    $this->assertEquals(Primitive::$BOOL, Type::forName('boolean'));
  }

  #[@test, @values(['mixed', '*'])]
  public function deprecated_varTypeVariant($name) {
    $this->assertEquals(Type::$VAR, Type::forName($name));
  }

  #[@test]
  public function resourceType() {
    $this->assertEquals(Type::$VAR, Type::forName('resource'));
  }

  #[@test, @expect('lang.IllegalStateException'), @values([null, ''])]
  public function forNameAndEmptyString($value) {
    Type::forName($value);
  }

  /** @return var[] */
  protected function instances() {
    return [$this, null, false, true, '', 0, -1, 0.0, [[]], [['one' => 'two']], $this];
  }

  #[@test, @values('instances')]
  public function anythingIsAnInstanceOfVar($value) {
    $this->assertTrue(Type::$VAR->isInstance($value));
  }

  #[@test, @values('instances')]
  public function nothingIsAnInstanceOfVoid($value) {
    $this->assertFalse(Type::$VOID->isInstance($value));
  }

  /** @return var[] */
  protected function types() {
    return [
      $this->getClass(),
      Type::$VAR,
      Primitive::$BOOL, Primitive::$STRING, Primitive::$INT, Primitive::$DOUBLE,
      new ArrayType('var'),
      new MapType('var')
    ];
  }

  #[@test, @values('types')]
  public function varIsAssignableFromAnything($type) {
    $this->assertTrue(Type::$VAR->isAssignableFrom($type));
  }

  #[@test]
  public function varIsNotAssignableFromVoid() {
    $this->assertFalse(Type::$VAR->isAssignableFrom(Type::$VOID));
  }

  #[@test, @values('types')]
  public function voidIsAssignableFromNothing($type) {
    $this->assertFalse(Type::$VOID->isAssignableFrom($type));
  }

  #[@test]
  public function voidIsAlsoNotAssignableFromVoid() {
    $this->assertFalse(Type::$VOID->isAssignableFrom(Type::$VOID));
  }

  #[@test, @values('instances')]
  public function newInstance_of_var($value) {
    $this->assertEquals($value, Type::$VAR->newInstance($value));
  }

  #[@test, @expect('lang.IllegalAccessException'), @values('instances')]
  public function newInstance_of_void($value) {
    Type::$VOID->newInstance($value);
  }

  #[@test, @values('instances')]
  public function cast_to_var($value) {
    $this->assertEquals($value, Type::$VAR->cast($value));
  }

  #[@test, @expect('lang.ClassCastException'), @values('instances')]
  public function cast_to_void($value) {
    Type::$VOID->cast($value);
  }

  #[@test]
  public function string_type_default() {
    $this->assertEquals('', Primitive::$STRING->default);
  }

  #[@test]
  public function int_type_default() {
    $this->assertEquals(0, Primitive::$INT->default);
  }

  #[@test]
  public function double_type_default() {
    $this->assertEquals(0.0, Primitive::$DOUBLE->default);
  }

  #[@test]
  public function bool_type_default() {
    $this->assertEquals(false, Primitive::$BOOL->default);
  }

  #[@test]
  public function array_type_default() {
    $this->assertEquals([], (new ArrayType('var'))->default);
  }

  #[@test]
  public function map_type_default() {
    $this->assertEquals([], (new MapType('var'))->default);
  }

  #[@test]
  public function class_type_default() {
    $this->assertEquals(null, XPClass::forName('lang.Object')->default);
  }

  #[@test]
  public function var_type_default() {
    $this->assertEquals(null, Type::$VAR->default);
  }

  #[@test]
  public function void_type_default() {
    $this->assertEquals(null, Type::$VOID->default);
  }

  #[@test]
  public function native_array_default() {
    $this->assertEquals([], Type::$ARRAY->default);
  }

  #[@test]
  public function native_callable_default() {
    $this->assertEquals(null, Type::$CALLABLE->default);
  }

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_isInstance($value) {
    $this->assertTrue(Type::$ARRAY->isInstance($value));
  }

  #[@test, @values([
  #  [[]],
  #  [1], [1.5], [true], ['Test'],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_newInstance_from_array($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[@test]
  public function array_type_union_newInstance_without_args() {
    $this->assertEquals([], Type::$ARRAY->newInstance());
  }

  #[@test, @values([Type::$ARRAY, new ArrayType('var'), new MapType('var')])]
  public function array_type_union_isAssignableFrom_arrays($type) {
    $this->assertTrue(Type::$ARRAY->isAssignableFrom($type));
  }

  #[@test, @values([Primitive::$INT, Type::$VOID, new FunctionType([], Type::$VAR)])]
  public function array_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom($type));
  }

  #[@test]
  public function array_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom($this->getClass()));
  }

  #[@test, @values([
  #  [1], [1.5], [true], ['Test'],
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_cast($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[@test]
  public function array_type_union_cast_null() {
    $this->assertEquals(null, Type::$ARRAY->cast(null));
  }

  #[@test, @values([
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_isInstance($value) {
    $this->assertTrue(Type::$CALLABLE->isInstance($value));
  }

  #[@test, @values([
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_newInstance($value) {
    $this->assertEquals($value, Type::$CALLABLE->newInstance($value));
  }

  #[@test, @values([
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_cast($value) {
    $this->assertEquals($value, Type::$CALLABLE->cast($value));
  }

  #[@test]
  public function callable_type_union_cast_null() {
    $this->assertEquals(null, Type::$CALLABLE->cast(null));
  }

  #[@test, @values([Type::$CALLABLE, new FunctionType([], Type::$VAR)])]
  public function callable_type_union_isAssignableFrom_functions($type) {
    $this->assertTrue(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[@test, @values([Primitive::$INT, Type::$VOID, new ArrayType('var'), new MapType('var')])]
  public function callable_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[@test]
  public function callable_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom($this->getClass()));
  }
}
