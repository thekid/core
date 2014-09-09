<?php namespace net\xp_framework\unittest\core;

use lang\Runnable;
use lang\Runtime;
use lang\reflect\Package;
use lang\ClassLoader;
use unittest\actions\VerifyThat;

/**
 * TestCase for newinstance() functionality. Some tests are skipped if
 * process execution has been disabled.
 */
class NewInstanceTest extends \unittest\TestCase {

  /** @return bool */
  protected function processExecutionEnabled() {
    return !\lang\Process::$DISABLED;
  }

  /**
   * Issues a uses() command inside a new runtime for every class given
   * and returns a line indicating success or failure for each of them.
   *
   * @param   string[] uses
   * @param   string src
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runInNewRuntime($uses, $src) {
    with ($out= $err= '', $p= Runtime::getInstance()->newInstance(null, 'class', 'xp.runtime.Evaluate', [])); {
      $uses && $p->in->write('uses("'.implode('", "', $uses).'");');
      $p->in->write($src);
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
    }
    return [$exitv, $out, $err];
  }
  
  #[@test]
  public function new_class_with_empty_body() {
    $o= newinstance('lang.Object', []);
    $this->assertInstanceOf('lang.Object', $o);
  }

  #[@test]
  public function new_class_with_empty_body_as_string() {
    $o= newinstance('lang.Object', [], '{}');
    $this->assertInstanceOf('lang.Object', $o);
  }

  #[@test]
  public function new_class_with_empty_body_as_closuremap() {
    $o= newinstance('lang.Object', [], []);
    $this->assertInstanceOf('lang.Object', $o);
  }

  #[@test]
  public function new_class_with_member_as_string() {
    $o= newinstance('lang.Object', [], '{
      public $test= "Test";
    }');
    $this->assertEquals('Test', $o->test);
  }

  #[@test]
  public function new_class_with_member_as_closuremap() {
    $o= newinstance('lang.Object', [], [
      'test' => 'Test'
    ]);
    $this->assertEquals('Test', $o->test);
  }

  #[@test]
  public function new_class_with_annotations() {
    $o= newinstance('#[@test] lang.Object', []);
    $this->assertTrue($o->getClass()->hasAnnotation('test'));
  }

  #[@test]
  public function new_class_with_field_annotations() {
    $o= newinstance('lang.Object', [], [
      '#[@test] fixture' => null
    ]);
    $this->assertTrue($o->getClass()->getField('fixture')->hasAnnotation('test'));
  }

  #[@test]
  public function new_class_with_method_annotations() {
    $o= newinstance('lang.Object', [], [
      '#[@test] fixture' => function() { }
    ]);
    $this->assertTrue($o->getClass()->getMethod('fixture')->hasAnnotation('test'));
  }

  #[@test]
  public function new_interface_with_body_as_string() {
    $o= newinstance('lang.Runnable', [], '{ public function run() { } }');
    $this->assertInstanceOf('lang.Runnable', $o);
  }

  #[@test]
  public function new_interface_with_body_as_closuremap() {
    $o= newinstance('lang.Runnable', [], [
      'run' => function() { }
    ]);
    $this->assertInstanceOf('lang.Runnable', $o);
  }

  #[@test]
  public function new_interface_with_annotations() {
    $o= newinstance('#[@test] lang.Runnable', [], [
      'run' => function() { }
    ]);
    $this->assertTrue($o->getClass()->hasAnnotation('test'));
  }

  #[@test]
  public function arguments_are_passed_to_constructor() {
    $instance= newinstance('lang.Object', [$this], '{
      public $test= null;
      public function __construct($test) {
        $this->test= $test;
      }
    }');
    $this->assertEquals($this, $instance->test);
  }

  #[@test]
  public function arguments_are_passed_to_constructor_in_closuremap() {
    $instance= newinstance('lang.Object', [$this], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    $this->assertEquals($this, $instance->test);
  }

  #[@test]
  public function arguments_are_passed_to_base_constructor_in_closuremap() {
    $base= ClassLoader::defineClass($this->getClassName().'_BaseFixture', 'lang.Object', [], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    $this->assertEquals($this, newinstance($base->getName(), [$this], [])->test);
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function missingMethodImplementationFatals() {
    $r= $this->runInNewRuntime(['lang.Runnable'], '
      newinstance("lang.Runnable", [], "{}");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Fatal error'),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function syntaxErrorFatals() {
    $r= $this->runInNewRuntime(['lang.Runnable'], '
      newinstance("lang.Runnable", [], "{ @__SYNTAX ERROR__@ }");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Parse error'),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function missingClassFatals() {
    $r= $this->runInNewRuntime([], '
      newinstance("lang.NonExistantClass", [], "{}");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Class "lang.NonExistantClass" could not be found'),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function notPreviouslyDefinedClassIsLoaded() {
    $r= $this->runInNewRuntime([], '
      if (isset(xp::$cl["lang.Runnable"])) {
        xp::error("Class lang.Runnable may not have been previously loaded");
      }
      $r= newinstance("lang.Runnable", [], "{ public function run() { echo \"Hi\"; } }");
      $r->run();
    ');
    $this->assertEquals(0, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Hi'),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test]
  public function packageOfNewInstancedClass() {
    $i= newinstance('lang.Object', [], '{}');
    $this->assertEquals(
      Package::forName('lang'),
      $i->getClass()->getPackage()
    );
  }

  #[@test]
  public function packageOfNewInstancedFullyQualifiedClass() {
    $i= newinstance('net.xp_framework.unittest.core.PackagedClass', [], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      $i->getClass()->getPackage()
    );
  }

  #[@test]
  public function packageOfNewInstancedNamespacedClass() {
    $i= newinstance('net.xp_framework.unittest.core.NamespacedClass', [], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      $i->getClass()->getPackage()
    );
  }

  #[@test]
  public function packageOfNewInstancedNamespacedInterface() {
    $i= newinstance('net.xp_framework.unittest.core.NamespacedInterface', [], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      $i->getClass()->getPackage()
    );
  }

  #[@test]
  public function className() {
    $instance= newinstance('Object', [], '{ }');
    $n= $instance->getClassName();
    $this->assertEquals(
      'lang.Object',
      substr($n, 0, strrpos($n, '�')),
      $n
    );
  }

  #[@test]
  public function classNameWithFullyQualifiedClassName() {
    $instance= newinstance('lang.Object', [], '{ }');
    $n= $instance->getClassName();
    $this->assertEquals(
      'lang.Object',
      substr($n, 0, strrpos($n, '�')),
      $n
    );
  }

  #[@test]
  public function anonymousClassWithoutConstructor() {
    newinstance('util.log.Traceable', [], '{
      public function setTrace($cat) {}
    }');
  }

  #[@test]
  public function anonymousClassWithoutConstructorIgnoresConstructArgs() {
    newinstance('util.log.Traceable', ['arg1'], '{
      public function setTrace($cat) {}
    }');
  }

  #[@test]
  public function anonymousClassWithConstructor() {
    newinstance('util.log.Traceable', ['arg1'], '{
      public function __construct($arg) {
        if ($arg != "arg1") {
          throw new \\unittest\\AssertionFailedError("equals", $arg, "arg1");
        }
      }
      public function setTrace($cat) {}
    }');
  }

  #[@test]
  public function this_can_be_accessed() {
    $instance= newinstance('lang.Object', [], [
      'test'    => null,
      'setTest' => function($test) {
        $this->test= $test;
      },
      'getTest' => function() {
        return $this->test;
      }
    ]);

    $instance->setTest('Test');
    $this->assertEquals('Test', $instance->getTest());
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_array_typehint() {
    $r= $this->runInNewRuntime([], '
      abstract class Base extends \lang\Object {
        public abstract function fixture(array $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(array $args) { return "Hello"; }]);
      echo $instance->fixture([]);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_callable_typehint() {
    $r= $this->runInNewRuntime([], '
      abstract class Base extends \lang\Object {
        public abstract function fixture(callable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(callable $args) { return "Hello"; }]);
      echo $instance->fixture(function() { });
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_class_typehint() {
    $r= $this->runInNewRuntime([], '
      abstract class Base extends \lang\Object {
        public abstract function fixture(\lang\Generic $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(\lang\Generic $args) { return "Hello"; }]);
      echo $instance->fixture(new \lang\Object());
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_self_typehint() {
    $r= $this->runInNewRuntime([], '
      abstract class Base extends \lang\Object {
        public abstract function fixture(self $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(\Base $args) { return "Hello"; }]);
      echo $instance->fixture($instance);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }
}
