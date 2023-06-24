<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;
use unittest\Assert;
use unittest\{Expect, Test, TestCase};

/**
 * TestCase for generic construction behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ListOf
 */
class VarArgsTest {

  #[Test]
  public function withArguments() {
    Assert::equals(
      ['Hello', 'World'],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 'World')->elements()
    );
  }

  #[Test]
  public function withoutArguments() {
    Assert::equals(
      [],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')->elements()
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function withIncorrectArguments() {
    create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 1);
  }

  #[Test]
  public function withAllOf() {
    Assert::equals(
      ['Hello', 'World'],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')->withAll('Hello', 'World')->elements()
    );
  }
}