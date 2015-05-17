<?php namespace text\format;

/**
 * Printf formatter
 *
 * @deprecated
 * @see      php://number_format
 * @see      php://localeconv
 * @see      xp://text.format.IFormat
 */
class NumberFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.NumberFormat
   */
  public function getInstance() {
    return parent::getInstance('NumberFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   * @throws  lang.FormatException
   */
  public function apply($fmt, $argument) {
    if (!is_numeric($argument)) {
      throw new \lang\FormatException('Argument '.$argument.' of type "'.gettype($argument).'" is not a number');
    }
    
    list($decimals, $dec_point, $thousands_sep)= explode('#', $fmt);
    return number_format(
      floatval($argument), 
      $decimals, 
      $dec_point,
      $thousands_sep
    );
  }
}
