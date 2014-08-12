<?php namespace lang\reflect;

/**
 * Represents a class method
 *
 * @see   xp://lang.XPClass
 * @see   xp://lang.reflect.Routine
 * @test  xp://net.xp_framework.unittest.reflection.MethodsTest
 * @test  xp://net.xp_framework.unittest.reflection.ReflectionTest
 */
class Method extends Routine {

  /**
   * Retrieve whether this method is generic
   *
   * @return  bool
   */
  public function isGeneric() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass()->getName(), $this->_reflect->getName());
    return isset($details[DETAIL_ANNOTATIONS]['generic']['self']);
  }

  /**
   * Retrieve how many parameters this method declares (including optional 
   * ones)
   *
   * @return  int
   */
  public function numParameters() {
    return parent::numParameters() - $this->isGeneric();
  }

  /**
   * Returns this method's parameters
   *
   * @return  lang.reflect.Parameter[]
   */
  public function getParameters() {
    return array_slice(parent::getParameters(), $this->isGeneric());
  }

  /**
   * Retrieve one of this method's parameters by its offset
   *
   * @param   int $offset
   * @return  lang.reflect.Parameter or NULL if it does not exist
   */
  public function getParameter($offset) {
    return parent::getParameter($offset + $this->isGeneric());
  }

  /**
   * Invokes the underlying method represented by this Method object, 
   * on the specified object with the specified parameters.
   *
   * Example:
   * <code>
   *   $method= XPClass::forName('lang.Object')->getMethod('toString');
   *
   *   var_dump($method->invoke(new Object()));
   * </code>
   *
   * Example (passing arguments)
   * <code>
   *   $method= XPClass::forName('lang.types.String')->getMethod('concat');
   *
   *   var_dump($method->invoke(new String('Hello'), ['World']));
   * </code>
   *
   * Example (static invokation):
   * <code>
   *   $method= XPClass::forName('util.log.Logger')->getMethod('getInstance');
   *
   *   var_dump($method->invoke(NULL));
   * </code>
   *
   * @param   lang.Object obj
   * @param   var[] args default []
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case the method is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked method
   */
  public function invoke($obj, $args= []) {
    if (null !== $obj && !($obj instanceof $this->_class)) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        \xp::nameOf($this->_class),
        \xp::typeOf($obj)
      ));
    }
    
    // Check modifiers. If caller is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    if ($m & MODIFIER_ABSTRACT) {
      throw new \lang\IllegalAccessException(sprintf(
        'Cannot invoke abstract %s::%s',
        $this->_class,
        $this->_reflect->getName()
      ));
    }
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(0, 2);
      $decl= $this->_reflect->getDeclaringClass()->getName();
      if ($m & MODIFIER_PROTECTED) {
        $allow= $t[1]['class'] === $decl || is_subclass_of($t[1]['class'], $decl);
      } else {
        $allow= $t[1]['class'] === $decl;
      }
      if (!$allow) {
        throw new \lang\IllegalAccessException(sprintf(
          'Cannot invoke %s %s::%s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          $this->_class,
          $this->_reflect->getName(),
          $t[1]['class']
        ));
      }
    }

    // For non-public methods: Use setAccessible() / invokeArgs() combination 
    // if possible, resort to __call() workaround.
    try {
      if (!$public) {
        $this->_reflect->setAccessible(true);
      }
      return $this->_reflect->invokeArgs($obj, (array)$args);
    } catch (\lang\SystemExit $e) {
      throw $e;
    } catch (\lang\Throwable $e) {
      throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName(), $e);
    } catch (\Exception $e) {
      throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName(), new \lang\XPException($e->getMessage()));
    }
  }
}
