<?php namespace net\xp_framework\unittest\util;
 
use unittest\Test;
use util\{Calendar, Date, TimeZone};

/**
 * Tests Calendar class
 */
class CalendarTest extends \unittest\TestCase {
  public
    $nowTime  = 0,
    $nowDate  = null,
    $refDate  = null;
  
  protected $tz= null;
  
  /**
   * Set up this test
   *
   */
  public function setUp() {
    $this->tz= date_default_timezone_get();
    date_default_timezone_set('GMT');
    
    $this->nowTime= time();
    $this->nowDate= new Date($this->nowTime);
    $this->refDate= new Date('1977-12-14 11:55');
  }

  /**
   * Tear down test.
   *
   */
  public function tearDown() {
    date_default_timezone_set($this->tz);
  }
  
  /**
   * Helper method
   *
   * @param   string expected
   * @param   util.Date date
   * @param   string error default 'datenotequal'
   * @return  bool
   */
  public function assertDateEquals($expected, $date, $error= 'datenotequal') {
    return $this->assertEquals(new Date($expected), $date, $error);
  }
  
  /**
   * Test calendar class
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarBasic() {
    $this->assertDateEquals('1977-12-14T00:00:00+00:00', Calendar::midnight($this->refDate), 'midnight');
    $this->assertDateEquals('1977-12-01T00:00:00+00:00', Calendar::monthBegin($this->refDate), 'monthbegin');
    $this->assertDateEquals('1977-12-31T23:59:59+00:00', Calendar::monthEnd($this->refDate), 'monthend');
    $this->assertEquals(50, Calendar::week($this->refDate), 'week');
  }
  
  /**
   * Test calendar class (easter day calculation)
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarEaster() {
    $this->assertDateEquals('2003-04-20T00:00:00+00:00', Calendar::easter(2003), 'easter');
  }
  
  /**
   * Test calendar class (first of advent calculation)
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarAdvent() {
    $this->assertDateEquals('2003-11-30T00:00:00+00:00', Calendar::advent(2003), 'advent');
  }
  
  /**
   * Test calendar class (DST / daylight savings times)
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarDSTBegin() {
    $this->assertDateEquals('2003-03-30T01:00:00+00:00', Calendar::dstBegin(2003), 'dstbegin');
  }
  
  /**
   * Test calendar class (DST / daylight savings times), make sure $method argument is obsolete
   * when passing a Date object with timezone information
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarDSTBeginByDate() {
    $this->assertDateEquals('2003-03-30T01:00:00+00:00', Calendar::dstBegin(new Date('2003-11-11 22:22:22 Europe/Berlin'), CAL_DST_EU), 'dstbegin');
    $this->assertDateEquals('2003-03-30T01:00:00+00:00', Calendar::dstBegin(new Date('2003-11-11 22:22:22 Europe/Berlin'), CAL_DST_US), 'dstbegin');
  }

  /**
   * Test calendar class (DST / daylight savings times)
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarDSTBeginUS() {
    $this->assertDateEquals('2003-04-06T07:00:00+00:00', Calendar::dstBegin(2003, CAL_DST_US), 'dstbegin');
  }

  /**
   * Test calendar class (DST / daylight savings times)
   *
   * @see     xp://util.Calendar
   */
  #[Test]
  public function testCalendarDSTEnd() {
    $this->assertDateEquals('2003-10-26T01:00:00+00:00', Calendar::dstEnd(2003), 'dstend');
  }
  
  /**
   * Test
   *
   */
  #[Test]
  public function inDst() {
    $this->assertEquals(true, Calendar::inDST(new Date('2007-08-24', new TimeZone('Europe/Berlin'))));
  }
  
  /**
   * Test
   *
   */
  #[Test]
  public function notInDst() {
    $this->assertEquals(false, Calendar::inDST(new Date('2007-01-24', new TimeZone('Europe/Berlin'))));
  }

  /**
   * Test Asia/Singapore does not have DST
   *
   */
  #[Test]
  public function haveNoDst() {
    $this->assertEquals(false, Calendar::inDST(new Date('2007-01-24', new TimeZone('Asia/Singapore'))));
    $this->assertEquals(false, Calendar::inDST(new Date('2007-08-24', new TimeZone('Asia/Singapore'))));
  }
}