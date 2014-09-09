<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;
use lang\reflect\Module;

/**
 * TestCase for modules
 *
 * @see   xp://lang.ClassLoader
 */
class ModuleLoadingTest extends \unittest\TestCase {
  protected $registered= [];

  /**
   * Register a loader with the CL
   *
   * @param  lang.IClassLoader $l
   */
  protected function register($l) {
    $this->registered[]= ClassLoader::registerLoader($l);
  }

  /**
   * Tears down test, removing all loaders registered with the CL.
   */
  public function tearDown() {
    foreach ($this->registered as $l) {
      ClassLoader::removeLoader($l);
    }
  }

  #[@test]
  public function simple_module() {
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/simple { }']));
  }

  #[@test]
  public function leading_php_tag_is_stripped() {
    $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagstart { }']));
  }

  #[@test]
  public function leading_and_trailing_php_tags_are_stripped() {
    $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagboth { } ?>']));
  }

  #[@test]
  public function trailing_php_tag_is_stripped() {
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/tagend { } ?>']));
  }

  #[@test]
  public function module_in_namespace() {
    $this->register(new LoaderProviding(['module.xp' => '<?php namespace net\xp_framework\unittest\reflection;
    module xp-framework/namespaced { 

    }']));
  }

  #[@test, @expect(class= 'lang.ElementNotFoundException', withMessage= '/Missing or malformed module-info/')]
  public function empty_module_file() {
    $this->register(new LoaderProviding(['module.xp' => '']));
  }

  #[@test, @expect(class= 'lang.ElementNotFoundException', withMessage= '/Missing or malformed module-info/')]
  public function module_without_name() {
    $this->register(new LoaderProviding(['module.xp' => 'module { }']));
  }

  #[@test]
  public function loaded_module() {
    $cl= new LoaderProviding(['module.xp' => 'module xp-framework/loaded { }']);
    $this->register($cl);
    $this->assertEquals(new Module('xp-framework/loaded', $cl), Module::forName('xp-framework/loaded'));
  }

  #[@test]
  public function modules_initializer_is_invoked() {
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/initialized {
      public $initialized= 0;

      public function initialize() {
        $this->initialized++;
      }
    }']));
    $this->assertEquals(1, Module::forName('xp-framework/initialized')->initialized);
  }

  #[@test]
  public function module_inheritance() {
    $cl= ClassLoader::defineClass('net.xp_framework.unittest.reflection.BaseModule', 'lang.reflect.Module', []);
    $this->register(new LoaderProviding([
      'module.xp' => '<?php module xp-framework/child extends net\xp_framework\unittest\reflection\BaseModule { }'
    ]));
    $this->assertEquals($cl, Module::forName('xp-framework/child')->getClass()->getParentclass());
  }

  #[@test]
  public function module_implementation() {
    $cl= ClassLoader::defineInterface('net.xp_framework.unittest.reflection.IModule', []);
    $this->register(new LoaderProviding([
      'module.xp' => '<?php module xp-framework/impl implements net\xp_framework\unittest\reflection\IModule { }'
    ]));
    $this->assertTrue(in_array($cl, Module::forName('xp-framework/impl')->getClass()->getInterfaces()));
  }
}
