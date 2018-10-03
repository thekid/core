<?php namespace xp\runtime;

use lang\{XPClass, Environment};
use util\cmd\Console;

/**
 * Evaluates sourcecode. Used by `xp eval` subcommand.
 */
class Evaluate {
  
  /**
   * Main
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {

    // Read sourcecode from STDIN if no further argument is given
    if (empty($args)) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if (is_file($args[0])) {
      $code= new Code(file_get_contents($args[0]));
    } else {
      $code= new Code($args[0]);
    }

    try {
      return $code->run($code->fragment(), [XPClass::nameOf(self::class)] + $args);
    } catch (ModuleNotFound $e) {
      Console::$err->writeLine("\033[41;1;37mError: ", $e->getMessage(), "\033[0m");
      Console::$err->writeLinef(
        "Try installing it via `\033[36mmkdir -p '%1\$s' && composer require -d '%1\$s' %2\$s\033[0m`",
        Environment::configDir('xp').$code->namespace(),
        $e->module()
      );
      return 127;
    }
  }
}
