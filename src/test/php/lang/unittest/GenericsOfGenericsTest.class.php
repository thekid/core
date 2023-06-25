<?php namespace lang\unittest;

use test\{Assert, Test};

class GenericsOfGenericsTest {
  
  #[Test]
  public function listOfListOfStringsReflection() {
    $l= create('new lang.unittest.ListOf<lang.unittest.ListOf<string>>');
    
    with ($class= typeof($l)); {
      Assert::true($class->isGeneric());
      $arguments= $class->genericArguments();
      Assert::equals(1, sizeof($arguments));
      
      with ($cclass= $arguments[0]); {
        Assert::true($cclass->isGeneric());
        $arguments= $cclass->genericArguments();
        Assert::equals(1, sizeof($arguments));
        Assert::equals(\lang\Primitive::$STRING, $arguments[0]);
      }
    }
  }

  #[Test]
  public function lookupOfListOfStringsReflection() {
    $l= create('new lang.unittest.Lookup<string, lang.unittest.ListOf<string>>');
    
    with ($class= typeof($l)); {
      Assert::true($class->isGeneric());
      $arguments= $class->genericArguments();
      Assert::equals(2, sizeof($arguments));
      
      Assert::equals(\lang\Primitive::$STRING, $arguments[0]);
      with ($vclass= $arguments[1]); {
        Assert::true($vclass->isGeneric());
        $arguments= $vclass->genericArguments();
        Assert::equals(1, sizeof($arguments));
        Assert::equals(\lang\Primitive::$STRING, $arguments[0]);
      }
    }
  }

  #[Test]
  public function lookupOfLookupOfStringsReflection() {
    $l= create('new lang.unittest.Lookup<string, lang.unittest.Lookup<string, lang.Value>>');
    
    with ($class= typeof($l)); {
      Assert::true($class->isGeneric());
      $arguments= $class->genericArguments();
      Assert::equals(2, sizeof($arguments));
      
      Assert::equals(\lang\Primitive::$STRING, $arguments[0]);
      with ($vclass= $arguments[1]); {
        Assert::true($vclass->isGeneric());
        $arguments= $vclass->genericArguments();
        Assert::equals(2, sizeof($arguments));
        Assert::equals(\lang\Primitive::$STRING, $arguments[0]);
        Assert::equals(\lang\XPClass::forName('lang.Value'), $arguments[1]);
      }
    }
  }
}