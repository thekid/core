<?php namespace lang;

/**
 * Represents function types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.FunctionTypeTest
 */
class FunctionType extends Type {
  protected $signature;
  protected $returns;

  static function __static() { }

  /**
   * Creates a new array type instance
   *
   * @param  lang.Type[] $signature
   * @param  lang.Type $returns
   */
  public function __construct(array $signature= null, $returns) {
    $this->signature= $signature;
    $this->returns= $returns;
    parent::__construct(sprintf(
      'function(%s): %s',
      null === $signature ? '?' : implode(',', array_map(function($e) { return $e->getName(); }, $signature)),
      $this->returns->getName()
    ), null);
  }

  /** @return lang.Type[] */
  public function signature() {
    return $this->signature;
  }

  /** @return lang.Type */
  public function returns() {
    return $this->returns;
  }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  self
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a function type
   */
  public static function forName($name) {
    if (0 !== strncmp($name, 'function(', 9)) {
      throw new IllegalArgumentException('Not a function type: '.$name);
    }

    if (')' === $name{9}) {
      $args= substr($name, 10);
      $o= strpos($args, ':');
      $signature= [];
    } else if ('?' === $name{9}) {
      $args= substr($name, 11);
      $o= strpos($args, ':');
      $signature= null;
    } else for ($args= substr($name, 8), $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
      if (':' === $args{$i} && 0 === $brackets) {
        $signature[]= parent::forName(substr($args, $o + 1, $i- $o- 2));
        $o= $i+ 1;
        break;
      } else if (',' === $args{$i} && 1 === $brackets) {
        $signature[]= parent::forName(substr($args, $o + 1, $i- $o- 1));
        $o= $i+ 1;
      } else if ('(' === $args{$i}) {
        $brackets++;
      } else if (')' === $args{$i}) {
        $brackets--;
      }
    }

    return new self($signature, Type::forName(ltrim(substr($args, $o+ 1), ' ')));
  }

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    throw new IllegalStateException('Function types cannot be used in type literals');
  }

  /**
   * Verifies a reflection function or method
   *
   * @param  php.ReflectionFunctionAbstract $value
   * @param  [lang.Type] $signature
   * @param  function(string): var $value A function to invoke when verification fails
   * @param  php.ReflectionClass $class Class to get details from, optionally
   * @return var
   */
  protected function verify($r, $signature, $false, $class= null) {
    if (null !== $signature && sizeof($signature) < $r->getNumberOfRequiredParameters()) {
      return $false('Required signature length mismatch, expecting '.sizeof($signature).', have '.$r->getNumberOfParameters());
    }

    $details= $class ? XPClass::detailsForMethod($class->getName(), $r->getName()) : null;
    if (isset($details[DETAIL_RETURNS])) {
      $returns= Type::forName($details[DETAIL_RETURNS]);
      if (!$this->returns->equals($returns) && !$this->returns->isAssignableFrom($returns)) {
        return $false('Return type mismatch, expecting '.$this->returns->getName().', have '.$returns->getName()); 
      }
    }

    if (null === $signature) return true;
    $params= $r->getParameters();
    foreach ($signature as $i => $type) {
      if (!isset($params[$i])) return $false('No parameter #'.($i + 1));
      if (isset($details[DETAIL_ARGUMENTS][$i])) {
        $param= Type::forName($details[DETAIL_ARGUMENTS][$i]);
        if (!$type->isAssignableFrom($param)) {
          return $false('Parameter #'.($i + 1).' not a '.$param->getName().' type: '.$type->getName());
        }
      } else {
        $param= $params[$i];
        if ($param->isArray()) {
          if (!$type->equals(Primitive::$ARRAY) && !$type instanceof ArrayType && !$type instanceof MapType) {
            return $false('Parameter #'.($i + 1).' not an array type: '.$type->getName());
          }
        } else if ($param->isCallable()) {
          if (!$type instanceof FunctionType) {
            return $false('Parameter #'.($i + 1).' not a function type: '.$type->getName());
          }
        } else if (null !== ($class= $param->getClass())) {
          if (!$type->isAssignableFrom(new XPClass($class))) {
            return $false('Parameter #'.($i + 1).' not a '.$class->getName().': '.$type->getName());
          }
        }
      }
    }
    return true;
  }

  /**
   * Returns a verified function instance for a given value. Supports the following:
   *
   * - A closure
   * - A string referencing a function, e.g. 'strlen' or 'typeof'
   * - A string referencing an instance creation expression: 'lang.Object::new'
   * - A string referencing a static method: 'lang.XPClass::forName'
   * - An array of two strings referencing a static method: ['lang.XPClass', 'forName']
   * - An array of an instance and a string referencing an instance method: [$this, 'getName']
   *
   * @param  var $arg
   * @param  function(string): var $false A function to return when verification fails
   * @param  bool $return Whether to return the closure, or TRUE
   * @return php.Closure
   */
  protected function verified($arg, $false, $return= true) {
    if ($arg instanceof \Closure) {
      if ($this->verify(new \ReflectionFunction($arg), $this->signature, $false)) {
        return $return ? $arg : true;
      }
    } else if (is_string($arg)) {
      $r= sscanf($arg, '%[^:]::%s', $class, $method);
      if (2 === $r) {
        return $this->verifiedMethod($class, $method, $false, $return);
      } else if (function_exists($arg)) {
        $r= new \ReflectionFunction($arg);
        if ($this->verify($r, $this->signature, $false)) {
          return $return ? $r->getClosure() : true;
        }
      } else {
        return $false('Function "'.$arg.'" does not exist');
      }
    } else if (is_array($arg) && 2 === sizeof($arg)) {
      return $this->verifiedMethod($arg[0], $arg[1], $false, $return);
    } else if (method_exists($arg, '__invoke')) {
      $inv= new \ReflectionMethod($arg, '__invoke');
      if ($this->verify($inv, $this->signature, $false)) {
        return $return ? $inv->getClosure($arg) : true;
      }
    } else {
      return $false('Unsupported type');
    }

    return $false('Verification failed');
  }

  /**
   * Returns a verified function instance for a given arg and method.
   *
   * @param  var $arg Either a string referencing a class or an object
   * @param  string $method
   * @param  function(string): var $false A function to return when verification fails
   * @param  bool $return Whether to return the closure, or TRUE
   * @return php.Closure
   */
  protected function verifiedMethod($arg, $method, $false, $return) {
    if ('new' === $method) {
      $class= literal($arg);
      if (method_exists($class, '__construct')) {
        $r= new \ReflectionMethod($class, '__construct');
        if (!$this->verify($r, $this->signature, $false, $r->getDeclaringClass())) return false;
      } else {
        if (!$this->returns->isAssignableFrom(new XPClass($class))) return $false('Class type mismatch');
      }
      $c= new \ReflectionClass($class);
      if (!$c->isInstantiable()) return $false(\xp::nameOf($class).' cannot be instantiated');
      return $return ? function() use($c) { return $c->newInstanceArgs(func_get_args()); } : true;
    } else if (is_string($arg) && is_string($method)) {
      $class= literal($arg);
      if (!method_exists($class, $method)) return $false('Method '.\xp::nameOf($class).'::'.$method.' does not exist');
      $r= new \ReflectionMethod($class, $method);
      if ($r->isStatic()) {
        if ($this->verify($r, $this->signature, $false, $r->getDeclaringClass())) {
          return $return ? $r->getClosure(null) : true;
        }
      } else {
        if (null === $this->signature) {
          $verify= null;
        } else {
          if (empty($this->signature) || !$this->signature[0]->isAssignableFrom(new XPClass($class))) {
            return $false('Method '.\xp::nameOf($class).'::'.$method.' requires instance of class as first parameter');
          }
          $verify= array_slice($this->signature, 1);
        }
        if ($this->verify($r, $verify, $false, $r->getDeclaringClass())) {
          return $return ? function() use($r) {
            $args= func_get_args();
            $self= array_shift($args);
            try {
              return $r->invokeArgs($self, $args);
            } catch (\ReflectionException $e) {
              throw new IllegalArgumentException($e->getMessage());
            }
          } : true;
        }
      }
    } else if (is_object($arg) && is_string($method)) {
      if (!method_exists($arg, $method)) return $false('Method '.\xp::nameOf(get_class($arg)).'::'.$method.' does not exist');
      $r= new \ReflectionMethod($arg, $method);
      if ($this->verify($r, $this->signature, $false, $r->getDeclaringClass())) {
        return $return ? $r->getClosure($arg) : true;
      }
    } else {
      return $false('Array argument must either be [string, string] or an [object, string]');
    }

    return $false('Verifying method failed');
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var $obj
   * @return  bool
   */
  public function isInstance($obj) {
    return $this->verified($obj, function($m) { return false; }, false);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    return $this->verified($value, function($m) use($value) { throw new IllegalArgumentException(sprintf(
      'Cannot create instances of the %s type from %s: %s',
      $this->getName(),
      \xp::typeOf($value),
      $m
    )); });
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    return null === $value ? null : $this->verified($value, function($m) use($value) { throw new ClassCastException(sprintf(
      'Cannot cast %s to the %s type: %s',
      \xp::typeOf($value),
      $this->getName(),
      $m
    )); });
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var $type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    $t= $type instanceof Type ? $type : Type::forName($type);
    if (!($t instanceof self) || !$this->returns->isAssignableFrom($t->returns)) return false;
    if (null === $this->signature) return true;
    if (sizeof($t->signature) !== sizeof($this->signature)) return false;
    foreach ($this->signature as $i => $type) {
      if (!$type->isAssignableFrom($t->signature[$i])) return false;
    }
    return true;
  }

  /**
   * Invokes a given function with the given arguments.
   *
   * @param   var $func
   * @param   var[] $args
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed function is not an instance of this type
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked function
   */
  public function invoke($func, $args= []) {
    $closure= $this->verified($func, function($m) use($func) { raise('lang.IllegalArgumentException', sprintf(
      'Passed argument is not of a %s type (%s): %s',
      $this->getName(),
      \xp::typeOf($func),
      $m
    )); });
    try {
      return call_user_func_array($closure, $args);
    } catch (SystemExit $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new \lang\reflect\TargetInvocationException($this->getName(), $e);
    }
  }
}
