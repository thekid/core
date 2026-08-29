<?php namespace lang\unittest;

use lang\{Module, ClassLoader, ElementNotFoundException, XPClass};
use test\{After, Assert, Expect, Test};

class ModuleLoadingTest {
  private $registered= [];

  /**
   * Register a loader with the CL
   *
   * @param  lang.IClassLoader $l
   * @return lang.IClassLoader
   */
  private function register($l) {
    $this->registered[]= ClassLoader::registerLoader($l);
    return $l;
  }

  /**
   * Removes all registered loaders
   *
   * @return void
   */
  public function remove() {
    foreach ($this->registered as $l) {
      ClassLoader::removeLoader($l);
    }
  }

  #[Test]
  public function simple_module() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/simple { }']));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function leading_php_tag_is_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagstart { }']));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function leading_and_trailing_php_tags_are_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagboth { } ?>']));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function trailing_php_tag_is_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/tagend { } ?>']));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function module_in_namespace() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php namespace lang\unittest;
      module xp-framework/namespaced { 

      }']));
    } finally {
      $this->remove();
    }
  }

  #[Test, Expect(class: ElementNotFoundException::class, message: '/Missing or malformed module-info/')]
  public function empty_module_file() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '']));
    } finally {
      $this->remove();
    }
  }

  #[Test, Expect(class: ElementNotFoundException::class, message: '/Missing or malformed module-info/')]
  public function module_without_name() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module { }']));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function loaded_module() {
    try {
      $cl= $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/loaded { }']));
      Assert::equals(new Module('xp-framework/loaded', $cl), Module::forName('xp-framework/loaded'));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function modules_initializer_is_invoked() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/initialized {
        public $initialized= false;

        public function initialize() {
          $this->initialized= true;
        }
      }']));
      Assert::equals(true, Module::forName('xp-framework/initialized')->initialized);
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function modules_initializer_is_invoked_once_when_registered_multiple_times() {
    try {
      $tracksInit= new LoaderProviding(['module.xp' => 'module xp-framework/tracks-init {
        public static $initialized= 0;

        public function initialize() {
          self::$initialized++;
        }

        public function initialized() {
          return self::$initialized;
        }
      }']);
      $this->register($tracksInit);
      $this->register($tracksInit);
      Assert::equals(1, Module::forName('xp-framework/tracks-init')->initialized());
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function module_inheritance() {
    try {
      $cl= ClassLoader::defineClass('lang.unittest.BaseModule', Module::class, []);
      $this->register(new LoaderProviding([
        'module.xp' => '<?php module xp-framework/child extends lang\unittest\BaseModule { }'
      ]));
      Assert::equals($cl, new XPClass(typeof(Module::forName('xp-framework/child'))->reflect()->getParentclass()));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function module_implementation() {
    try {
      $cl= ClassLoader::defineInterface('lang.unittest.IModule', []);
      $this->register(new LoaderProviding([
        'module.xp' => '<?php module xp-framework/impl implements lang\unittest\IModule { }'
      ]));
      Assert::true(typeof(Module::forName('xp-framework/impl'))->reflect()->isSubclassOf($cl->reflect()));
    } finally {
      $this->remove();
    }
  }

  #[Test]
  public function modules_initializer_can_register_itself_upfront_without_causing_endless_recursion() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/self-upfront {
        public function initialize() {
          \lang\ClassLoader::registerLoader($this->classLoader(), true);
        }
      }']));
    } finally {
      $this->remove();
    }
  }
}