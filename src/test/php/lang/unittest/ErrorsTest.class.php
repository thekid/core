<?php namespace lang\unittest;

use lang\{
  ClassCastException,
  Error,
  IllegalArgumentException,
  IndexOutOfBoundsException,
  NullPointerException,
  Value,
  Throwable
};
use test\{Action, Assert, Before, Expect, Test};

class ErrorsTest {

  #[Before]
  public function setUp() {
    Assert::equals(E_ALL, error_reporting(), 'Error reporting level not E_ALL');
    \xp::$errors= [];
  }

  #[Test]
  public function errors_get_appended_to_registry() {
    trigger_error('Test error');
    Assert::equals(1, sizeof(\xp::$errors));
    \xp::gc();
  }

  #[Test]
  public function errors_appear_in_stacktrace() {
    trigger_error('Test error');
      
    try {
      throw new Throwable('');
    } catch (Throwable $e) {
      $element= $e->getStackTrace()[0];
      Assert::equals(
        ['file' => __FILE__, 'message' => 'Test error'],
        ['file' => $element->file, 'message' => $element->message]
      );
      \xp::gc();
    }
  }

  #[Test]
  public function errorAt_given_a_file_without_error() {
    Assert::false(isset(\xp::$errors[__FILE__]));
  }

  #[Test]
  public function errorAt_given_a_file_with_error() {
    trigger_error('Test error');
    Assert::true(isset(\xp::$errors[__FILE__]));
    \xp::gc();
  }

  #[Test]
  public function errorAt_given_a_file_and_line_without_error() {
    Assert::false(isset(\xp::$errors[__FILE__][__LINE__ - 1]));
  }

  #[Test]
  public function errorAt_given_a_file_and_line_with_error() {
    trigger_error('Test error');
    Assert::true(isset(\xp::$errors[__FILE__][__LINE__ - 1]));
    \xp::gc();
  }

  #[Test, Expect(NullPointerException::class)]
  public function undefined_variable_yields_npe() {
    $a++;
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function undefined_array_offset_yields_ioobe() {
    $a= [];
    $a[0];
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function undefined_map_key_yields_ioobe() {
    $a= [];
    $a['test'];
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function undefined_string_offset_yields_ioobe() {
    $a= '';
    $a[0];
  }

  #[Test, Expect(Error::class)]
  public function call_to_member_on_non_object_yields_error() {
    $a= null;
    $a->method();
  }

  #[Test, Expect(Error::class)]
  public function argument_mismatch_yield_type_exception() {
    $f= function(Value $arg) { };
    $f('Primitive');
  }

  #[Test, Expect(Error::class)]
  public function missing_argument_mismatch_yield_error() {
    $f= function($arg) { };
    $f();
  }

  #[Test, Expect(Error::class)]
  public function cannot_convert_object_to_string_yields_error() {
    $object= new class() { };
    $object.'String';
  }

  #[Test, Expect(ClassCastException::class)]
  public function cannot_convert_array_to_string_yields_cce() {
    $array= [];
    $array.'String';
  }

  #[Test, Expect(ClassCastException::class)]
  public function __toString_not_returning_a_string_yields_cce() {
    $object= new class() {
      public function __toString() { return null; }
    };
    (string)$object;
  }
}