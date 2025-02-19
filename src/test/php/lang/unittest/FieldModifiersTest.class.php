<?php namespace lang\unittest;

use test\verify\Runtime;
use test\{Action, Assert, Test};

class FieldModifiersTest extends FieldsTest {

  #[Test]
  public function public_modifier() {
    Assert::equals(MODIFIER_PUBLIC, $this->field('public $fixture;')->getModifiers());
  }

  #[Test]
  public function private_modifier() {
    Assert::equals(MODIFIER_PRIVATE, $this->field('private $fixture;')->getModifiers());
  }

  #[Test]
  public function protected_modifier() {
    Assert::equals(MODIFIER_PROTECTED, $this->field('protected $fixture;')->getModifiers());
  }

  #[Test]
  public function static_modifier() {
    Assert::equals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->field('public static $fixture;')->getModifiers());
  }

  #[Test, Runtime(php: '>=8.1')]
  public function readonly_modifier() {

    // Remove implicit protected(set) returned by PHP 8.4
    $modifiers= $this->field('public readonly int $fixture;')->getModifiers() & ~0x800;
    Assert::equals(MODIFIER_READONLY | MODIFIER_PUBLIC, $modifiers);
  }
}