<?php namespace net\xp_framework\unittest\core;

use Countable;
use lang\{Type, Primitive, ArrayType, MapType, XPClass, Nullable, ClassNotFoundException, TypeUnion};
use net\xp_framework\unittest\core\generics\Lookup;
use unittest\{Test, Values, TestCase};

class TypeResolveTest extends TestCase {
  private $context;

  /** @return void */
  public function setUp() {
    $this->context= [
      'self'   => function() { return new XPClass(self::class); },
      'parent' => function() { return new XPClass(parent::class); },
      '*'      => function($type) {
        switch ($type) {
          case 'TypeResolveTest': return new XPClass(self::class);
          case 'Lookup': return XPClass::forName(Lookup::class);
          default: throw new ClassNotFoundException($type);
        }
      }
    ];
  }

  #[Test]
  public function resolve_primitive() {
    $this->assertEquals(Primitive::$STRING, Type::named('string', $this->context));
  }

  #[Test]
  public function resolve_nullable() {
    $this->assertEquals(new Nullable(Primitive::$STRING), Type::named('?string', $this->context));
  }

  #[Test]
  public function resolve_self() {
    $this->assertEquals(new XPClass(self::class), Type::named('self', $this->context));
  }

  #[Test, Values(['self[]', 'array<self>'])]
  public function resolve_array_of_self($type) {
    $this->assertEquals(new ArrayType(new XPClass(self::class)), Type::named($type, $this->context));
  }

  #[Test, Values(['[:self]', 'array<string, self>'])]
  public function resolve_map_of_self($type) {
    $this->assertEquals(new MapType(new XPClass(self::class)), Type::named($type, $this->context));
  }

  #[Test]
  public function resolve_parent() {
    $this->assertEquals(new XPClass(parent::class), Type::named('parent', $this->context));
  }

  #[Test]
  public function resolve_literal() {
    $this->assertEquals(new XPClass(self::class), Type::named(self::class, $this->context));
  }

  #[Test]
  public function resolve_name() {
    $this->assertEquals(new XPClass(self::class), Type::named(nameof($this), $this->context));
  }

  #[Test]
  public function resolve_without_namespace() {
    $this->assertEquals(new XPClass(self::class), Type::named('TypeResolveTest', $this->context));
  }

  #[Test]
  public function resolve_absolute_name() {
    $this->assertEquals(new XPClass(Countable::class), Type::named('\\Countable', $this->context));
  }

  #[Test, Expect(class: ClassNotFoundException::class, withMessage: '/NonExistant/')]
  public function resolve_non_existant() {
    Type::named('NonExistant', $this->context);
  }

  #[Test]
  public function resolve_generic() {
    $this->assertEquals(
      Type::forName('net.xp_framework.unittest.core.generics.Lookup<string, string>'),
      Type::named('Lookup<string, string>', $this->context)
    );
  }

  #[Test]
  public function resolve_wildcard() {
    $this->assertEquals(
      Type::forName('net.xp_framework.unittest.core.generics.Lookup<string, ?>'),
      Type::named('Lookup<string, ?>', $this->context)
    );
  }

  #[Test]
  public function resolve_union() {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      Type::named('int|string', $this->context)
    );
  }

  #[Test]
  public function resolve_nullable_union() {
    $this->assertEquals(
      new Nullable(new TypeUnion([Primitive::$STRING, Primitive::$INT])),
      Type::named('int|string|null', $this->context)
    );
  }
}