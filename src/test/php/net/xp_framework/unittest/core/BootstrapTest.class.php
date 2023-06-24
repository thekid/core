<?php namespace net\xp_framework\unittest\core;

use lang\{Process, Runtime, RuntimeOptions};
use unittest\{Assert, Before, PrerequisitesNotMetError, Test};
use util\Objects;

class BootstrapTest {

  #[Before]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', null, ['enabled']);
    }
    if (strstr(php_uname('v'), 'Windows Server 2016')) {
      throw new PrerequisitesNotMetError('Process execution bug on Windows Server 2016', null, ['enabled']);
    }
  }

  /**
   * Run given code in a new runtime
   *
   * @param  lang.RuntimeOptions $options
   * @param  string $code
   * @return var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runWith(RuntimeOptions $options, $code= 'return 1;') {
    with ($out= $err= '', $p= Runtime::getInstance()->newInstance($options, 'class', 'xp.runtime.Evaluate', [$code])); {
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
    }
    return [$exitv, $out, $err];
  }

  #[Test, Values(['Europe/Berlin', 'UTC'])]
  public function valid_timezone($tz) {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withSetting('date.timezone', $tz));
    Assert::equals([1, '', ''], $r);
  }

  #[Test]
  public function leading_colon_stripped_from_timezone() {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withSetting('date.timezone', ':UTC'), 'echo "TZ=", date_default_timezone_get();');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'TZ=UTC'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
    Assert::equals(0, $r[0], 'exitcode');
  }

  #[Test, Values(['', 'Foo/Bar'])]
  public function invalid_timezone($tz) {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withSetting('date.timezone', $tz), 'new \util\Date();');
    Assert::true(
      (bool)strstr($r[1].$r[2], '[xp::core] date.timezone not configured properly.'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
    Assert::equals(255, $r[0], 'exitcode');
  }

  #[Test]
  public function fatals_for_non_existant_class_path() {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withClassPath('/does-not-exist'));
    Assert::equals(255, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], '[bootstrap] Classpath element [/does-not-exist] not found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test]
  public function fatals_for_non_existant_xar() {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withClassPath('/does-not-exist.xar'));
    Assert::equals(255, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], '[bootstrap] Classpath element [/does-not-exist.xar] not found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }
}