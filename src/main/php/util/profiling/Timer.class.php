<?php namespace util\profiling;
 
/**
 * The Timer class provides a simple timer
 *
 * <code>
 *   $p= new Timer();
 *   $p->start();
 *   // ... code you want profiled
 *   $p->stop();
 *   var_dump($p->elapsedTime());
 * </code>
 *
 * @test     xp://net.xp_framework.unittest.util.TimerTest
 * @purpose  Provide a simple profiling timer
 */
class Timer extends \lang\Object {
  protected $start= null;
  protected $stop= null;
    
  /**
   * Start the timer
   *
   * @return  self
   */
  public function start() {
    $this->start= microtime(true);
    return $this;
  }
  
  /**
   * Stop the timer
   *
   * @return  self
   */
  public function stop() {
    $this->stop= microtime(true);
    return $this;
  }

  /**
   * Measure a closure
   *
   * @see    http://php.net/manual/en/language.types.callable.php
   * @param  var block a callable
   * @return self
   * @throws lang.IllegalArgumentException when block is not callable
   */
  public static function measure($block) {
    if (!is_callable($block)) {
      throw new \lang\IllegalArgumentException('Cannot call '.\xp::stringOf($block));
    }

    $self= new self();
    $self->start= microtime(true);
    $block();
    $self->stop= microtime(true);
    return $self;
  }

  /**
   * Retrieve elapsed time
   *
   * @return  float seconds elapsed
   */
  public function elapsedTime() {
    if (null === $this->start) {
      return 0.0;
    } else if (null === $this->stop) {
      return microtime(true) - $this->start;
    } else {
      return $this->stop - $this->start;
    }
  }
}
