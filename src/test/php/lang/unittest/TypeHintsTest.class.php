<?php namespace lang\unittest;

use lang\{Error, IllegalArgumentException, Value};
use test\verify\Runtime;
use test\{Assert, Expect, Test};

class TypeHintsTest {

  /**
   * Pass an object
   * 
   * @param  lang.Value $o
   * @return lang.Value
   */
  protected function pass(Value $o) { return $o; }

  /**
   * Pass a nullable object
   * 
   * @param  lang.Value $o
   * @return lang.Value
   */
  protected function nullable(Value $o= null) { return $o; }

  #[Test]
  public function pass_an_object() {
    $o= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 'Test'; }
      public function compareTo($value) { return $this <=> $value; }
    };
    Assert::equals($o, $this->pass($o));
  }

  #[Test, Expect(Error::class)]
  public function pass_a_primitive() {
    $this->pass(1);
  }

  #[Test, Expect(Error::class)]
  public function pass_null() {
    $this->pass(null);
  }

  #[Test]
  public function pass_object_to_nullable() {
    $o= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 'Test'; }
      public function compareTo($value) { return $this <=> $value; }
    };
    Assert::equals($o, $this->nullable($o));
  }

  #[Test, Expect(Error::class)]
  public function pass_a_primitive_to_nullable() {
    $this->nullable(1);
  }


  #[Test]
  public function pass_null_to_nullable() {
    Assert::equals(null, $this->nullable(null));
  }
}