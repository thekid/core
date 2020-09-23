<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalStateException;
use unittest\{Expect, Test, TestCase};

/**
 * TestCase for reflection on a non-generic
 */
class NotGenericTest extends TestCase {
  
  #[Test]
  public function thisIsNotAGeneric() {
    $this->assertFalse(typeof($this)->isGeneric());
  }

  #[Test]
  public function thisIsNotAGenericDefinition() {
    $this->assertFalse(typeof($this)->isGenericDefinition());
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotCreateGenericTypeFromThis() {
    typeof($this)->newGenericType([]);
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotGetGenericArgumentsForThis() {
    typeof($this)->genericArguments();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotGetGenericComponentsForThis() {
    typeof($this)->genericComponents();
  }
}