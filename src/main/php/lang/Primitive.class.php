<?php namespace lang;

use lang\types\String;
use lang\types\Double;
use lang\types\Integer;
use lang\types\Boolean;
use lang\types\Number;
use lang\types\ArrayList;

/**
 * Represents primitive types:
 * 
 * - string
 * - int
 * - double
 * - bool
 *
 * @test  xp://net.xp_framework.unittest.reflection.PrimitiveTest 
 * @see   xp://lang.Type
 */
class Primitive extends Type {
  public static
    $STRING  = null,
    $INT     = null,
    $DOUBLE  = null,
    $BOOL    = null,
    $ARRAY   = null;
  
  static function __static() {
    self::$STRING= new self('string', '');
    self::$INT= new self('int', 0);
    self::$DOUBLE= new self('double', 0.0);
    self::$BOOL= new self('bool', false);
    self::$ARRAY= new self('array', \xp::null());
  }
  
  /**
   * Returns the wrapper class for this primitive
   *
   * @see     http://en.wikipedia.org/wiki/Wrapper_class
   * @return  lang.XPClass
   */
  public function wrapperClass() {
    switch ($this) {
      case self::$STRING: return XPClass::forName('lang.types.String');
      case self::$INT: return XPClass::forName('lang.types.Integer');
      case self::$DOUBLE: return XPClass::forName('lang.types.Double');
      case self::$BOOL: return XPClass::forName('lang.types.Boolean');
    }
  }
  
  /**
   * Boxes a type - that is, turns Generics into primitives
   *
   * @param   var in
   * @return  var the primitive if not already primitive
   * @throws  lang.IllegalArgumentException in case in cannot be unboxed.
   */
  public static function unboxed($in) {
    if ($in instanceof String) return $in->toString();
    if ($in instanceof Double) return $in->doubleValue();
    if ($in instanceof Integer) return $in->intValue();
    if ($in instanceof Boolean) return $in->value;
    if ($in instanceof ArrayList) return $in->values;   // deprecated
    if ($in instanceof Generic) {
      throw new \IllegalArgumentException('Cannot unbox '.\xp::typeOf($in));
    }
    return $in; // Already primitive
  }

  /**
   * Boxes a type - that is, turns primitives into Generics
   *
   * @param   var in
   * @return  lang.Generic the Generic if not already generic
   * @throws  lang.IllegalArgumentException in case in cannot be boxed.
   */
  public static function boxed($in) {
    if (null === $in || $in instanceof Generic) return $in;
    $t= gettype($in);
    if ('string' === $t) return new String($in);
    if ('integer' === $t) return new Integer($in);
    if ('double' === $t) return new Double($in);
    if ('boolean' === $t) return new Boolean($in);
    if ('array' === $t) return ArrayList::newInstance($in);   // deprecated
    throw new \IllegalArgumentException('Cannot box '.\xp::typeOf($in));
  }
  
  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.Type
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a primitive
   */
  public static function forName($name) {
    switch ($name) {
      case 'string': return self::$STRING;
      case 'int': return self::$INT;
      case 'double': return self::$DOUBLE;
      case 'bool': return self::$BOOL;
      default: throw new \IllegalArgumentException('Not a primitive: '.$name);
    }
  }

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return '�'.$this->name;
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj) {
    return $obj === null || $obj instanceof \Generic 
      ? false 
      : $this === Type::forName(gettype($obj))
    ;
  }

  /**
   * Helper for cast() and newInstance()
   *
   * @param  var $value
   * @param  var $default A function
   * @return var
   */
  protected function coerce($value, $default) {
    if (!is_array($value)) switch ($this) {
      case self::$STRING:
        if ($value instanceof String) return $value->toString();
        if ($value instanceof Number) return (string)$value->value;
        if ($value instanceof Boolean) return (string)$value->value;
        if ($value instanceof Generic) return $value->toString();
        return (string)$value;

      case self::$INT:
        if ($value instanceof String) return (int)$value->toString();
        if ($value instanceof Number) return $value->intValue();
        if ($value instanceof Boolean) return (int)$value->value;
        if ($value instanceof Generic) return (int)$value->toString();
        return (int)$value;

      case self::$DOUBLE:
        if ($value instanceof String) return (double)$value->toString();
        if ($value instanceof Number) return $value->doubleValue();
        if ($value instanceof Boolean) return (double)$value->value;
        if ($value instanceof Generic) return (double)$value->toString();
        return (double)$value;

      case self::$BOOL:
        if ($value instanceof String) return (bool)$value->toString();
        if ($value instanceof Number) return (bool)$value->value;
        if ($value instanceof Boolean) return $value->value;
        if ($value instanceof Generic) return (bool)$value->toString();
        return (bool)$value;
    }

    return $default($value);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    return $this->coerce($value, function($value) {
      raise('lang.IllegalArgumentException', 'Cannot create instances of '.$this->getName().' from '.\xp::typeOf($value));
    });
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    return null === $value ? null : $this->coerce($value, function($value) {
      raise('lang.ClassCastException', 'Cannot cast to '.$this->getName().' from '.\xp::typeOf($value));
    });
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    return $this === ($type instanceof Type ? $type : Type::forName($type));
  }
}
