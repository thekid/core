<?php namespace util\profiling;
 
/**
 * The Timer class provides a simple timer
 *
 * ```php
 * $p= new Timer();
 * $p->start();
 *
 * // ... code you want profiled
 *
 * $p->stop();
 * printf("Took %.3f seconds\n", $p->elapsedTime());
 * ```
 *
 * @test  util.unittest.TimerTest
 */
class Timer {
  private $start, $stop;
    
  /** Start the timer */
  public function start(): self {
    $this->start= microtime(true);
    return $this;
  }
  
  /** Stop the timer */
  public function stop(): self {
    $this->stop= microtime(true);
    return $this;
  }

  /**
   * Measure a closure
   *
   * @see    http://php.net/manual/en/language.types.callable.php
   * @param  function(): void $block a callable
   * @return self
   * @throws lang.IllegalArgumentException when block is not callable
   */
  public static function measure(callable $block): self {
    $self= new self();
    $self->start= microtime(true);
    $block();
    $self->stop= microtime(true);
    return $self;
  }

  /** Retrieve elapsed time */
  public function elapsedTime(): float {
    if (null === $this->start) {
      return 0.0;
    } else if (null === $this->stop) {
      return microtime(true) - $this->start;
    } else {
      return $this->stop - $this->start;
    }
  }
}
