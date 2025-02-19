<?php namespace util\unittest;

use lang\{IllegalArgumentException, IllegalStateException};
use test\{After, Assert, Before, Expect, Ignore, Test, Values};
use util\{Date, TimeZone};

class DateTest {
  private $nowTime, $nowDate, $refDate, $tz;

  /** @return iterable  */
  private function timezones() {
    yield 'Europe/Berlin';
    yield new TimeZone('Europe/Berlin');
  }

  #[Before]
  public function setUp() {

    // Force timezone to GMT
    Date::__static();
    $this->tz= date_default_timezone_get();
    date_default_timezone_set('GMT');

    $this->nowTime= time();
    $this->nowDate= new Date($this->nowTime);
    $this->refDate= new Date('1977-12-14 11:55');
  }

  #[After]
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
    Assert::equals( 
      $expected,
      date_format($date->getHandle(), 'Y-m-d\TH:i:sP'),
      $error
    );
  }
  
  #[Test]
  public function constructorParseWithoutTz() {
    Assert::equals(true, new Date('2007-01-01 01:00:00 Europe/Berlin') instanceof Date);
  }
  
  #[Test]
  public function constructorUnixtimestampWithoutTz() {
    $this->assertDateEquals('2007-08-23T12:35:47+00:00', new Date(1187872547));
  }
  
  #[Test, Values(from: 'timezones')]
  public function constructorUnixtimestampWithTz($tz) {
    $this->assertDateEquals('2007-08-23T14:35:47+02:00', new Date(1187872547, $tz));
  }

  #[Test]
  public function constructorParseTz() {
    $date= new Date('2007-01-01 01:00:00 Europe/Berlin');
    Assert::equals('Europe/Berlin', $date->getTimeZone()->name());
    $this->assertDateEquals('2007-01-01T01:00:00+01:00', $date);
    
    $date= new Date('2007-01-01 01:00:00 Europe/Berlin', new TimeZone('Europe/Athens'));
    Assert::equals('Europe/Berlin', $date->getTimeZone()->name());
    $this->assertDateEquals('2007-01-01T01:00:00+01:00', $date);

    $date= new Date('2007-01-01 01:00:00', new TimeZone('Europe/Athens'));
    Assert::equals('Europe/Athens', $date->getTimeZone()->name());
    $this->assertDateEquals('2007-01-01T01:00:00+02:00', $date);
  }
  
  #[Test]
  public function noDiscreteTimeZone() {
    $date= new Date('2007-11-04 14:32:00+1000');
    Assert::equals('+1000', $date->getOffset());
    Assert::equals(36000, $date->getOffsetInSeconds());
  }
  
  #[Test]
  public function constructorParseNoTz() {
    $date= new Date('2007-01-01 01:00:00', new TimeZone('Europe/Athens'));
    Assert::equals('Europe/Athens', $date->getTimeZone()->name());
    
    $date= new Date('2007-01-01 01:00:00');
    Assert::equals('GMT', $date->getTimeZone()->name());
  }
  
  #[Test]
  public function testDate() {
    Assert::equals($this->nowDate->getTime(), $this->nowTime);
    Assert::equals($this->nowDate->toString('r'), date('r', $this->nowTime));
    Assert::true($this->nowDate->isAfter(new Date('yesterday')));
    Assert::true($this->nowDate->isBefore(new Date('tomorrow')));
  }
  
  #[Test]
  public function preUnixEpoch() {
    $this->assertDateEquals('1969-12-31T00:00:00+00:00', new Date('31.12.1969 00:00 GMT'));
  }

  /**
   * Test dates before the year 1582 are 11 days off.
   *
   * Quoting Wikipedia:
   * The last day of the Julian calendar was Thursday October 4, 1582 and 
   * this was followed by the first day of the Gregorian calendar, Friday 
   * October 15, 1582 (the cycle of weekdays was not affected).
   *
   * @see   http://en.wikipedia.org/wiki/Gregorian_calendar
   */
  #[Test, Ignore('PHP date functions do not support dates before 1753')]
  public function pre1582() {
    $this->assertDateEquals('1499-12-21T00:00:00+00:00', new Date('01.01.1500 00:00 GMT'));
  }

  /**
   * Test dates before the year 1752 are 11 days off.
   *
   * Quoting Wikipedia:
   * The Kingdom of Great Britain and thereby the rest of the British 
   * Empire (including the eastern part of what is now the United States) 
   * adopted the Gregorian calendar in 1752 under the provisions of 
   * the Calendar Act 1750; by which time it was necessary to correct 
   * by eleven days (Wednesday, September 2, 1752 being followed by 
   * Thursday, September 14, 1752) to account for February 29, 1700 
   * (Julian). 
   *
   * @see   http://en.wikipedia.org/wiki/Gregorian_calendar
   */
  #[Test, Ignore('PHP date functions do not support dates before 1753')]
  public function calendarAct1750() {
    $this->assertDateEquals('1753-01-01T00:00:00+00:00', new Date('01.01.1753 00:00 GMT'));
    $this->assertDateEquals('1751-12-21T00:00:00+00:00', new Date('01.01.1752 00:00 GMT'));
  }

  #[Test]
  public function anteAndPostMeridiem() {
    Assert::equals(1, (new Date('May 28 1980 1:00AM'))->getHours(), '1:00AM != 1h');
    Assert::equals(0, (new Date('May 28 1980 12:00AM'))->getHours(), '12:00AM != 0h');
    Assert::equals(13, (new Date('May 28 1980 1:00PM'))->getHours(), '1:00PM != 13h');
    Assert::equals(12, (new Date('May 28 1980 12:00PM'))->getHours(), '12:00PM != 12h');
  }
  
  #[Test]
  public function anteAndPostMeridiemInMidage() {
    Assert::equals(1, (new Date('May 28 1580 1:00AM'))->getHours(), '1:00AM != 1h');
    Assert::equals(0, (new Date('May 28 1580 12:00AM'))->getHours(), '12:00AM != 0h');
    Assert::equals(13, (new Date('May 28 1580 1:00PM'))->getHours(), '1:00PM != 13h');
    Assert::equals(12, (new Date('May 28 1580 12:00PM'))->getHours(), '12:00PM != 12h');
  }
  
  #[Test]
  public function dateCreate() {
    
    // Test with a date before 1971
    Assert::equals(-44668800, Date::create(1968, 8, 2, 0, 0, 0)->getTime());
  }

  #[Test, Values(from: 'timezones')]
  public function create_date_with_timezone($tz) {
    Assert::equals(-44672400, Date::create(1968, 8, 2, 0, 0, 0, $tz)->getTime());
  }

  #[Test]
  public function pre1970() {
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('01.02.1969'));
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('1969-02-01'));
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('1969-02-01 12:00AM'));
  }
  
  #[Test]
  public function serialization() {
    $original= new Date('2007-07-18T09:42:08 Europe/Athens');
    $copy= unserialize(serialize($original));
    Assert::equals($original, $copy);
  }
  
  #[Test]
  public function timeZoneSerialization() {
    date_default_timezone_set('Europe/Athens');
    $date= new Date('2007-11-20 21:45:33 Europe/Berlin');
    Assert::equals('Europe/Berlin', $date->getTimeZone()->name());
    Assert::equals('+0100', $date->getOffset());
    
    $copy= unserialize(serialize($date));
    Assert::equals('+0100', $copy->getOffset());
  }
  
  #[Test]
  public function handlingOfTimezone() {
    $date= new Date('2007-07-18T09:42:08 Europe/Athens');

    Assert::equals('Europe/Athens', $date->getTimeZone()->name());
    Assert::equals(3 * 3600, $date->getTimeZone()->offset($date));
  }

  /**
   * Provides values for supportedFormatTokens() test
   *
   * @return var[]
   */
  public function formatTokens() {
    return [
      //    input   , expect
      ['%Y'    , '1977'],
      ['%D %T' , '12/14/1977 11:55:00'],
      ['%C'    , '77'],
      ['%e'    , '14'],
      ['%G'    , '1977'],
      ['%H'    , '11'],
      ['%I'    , '11'],
      ['%j'    , '347'],
      ['%m'    , '12'],
      ['%M'    , '55'],
      ['%n'    , "\n"],
      ['%r'    , '11:55:00am'],
      ['%R'    , '11:55:00'],
      ['%S'    , '00'],
      ['%t'    , "\t"],
      ['%u'    , '3'],
      ['%V'    , '50'],
      ['%W'    , '50'],
      ['%w'    , '3'],
      ['%y'    , '77'],
      ['%Z'    , '+0000'],
      ['%z'    , '+0000'],
      ['%%'    , '%']
    ];
  }

  #[Test, Values(from: 'formatTokens')]
  public function supportedFormatTokens($input, $expect) {
    Assert::equals($expect, $this->refDate->format($input));
  }
  
  #[Test]
  public function unsupportedFormatToken() {
    Assert::equals('%b', $this->refDate->format('%b'));
  }
  
  #[Test]
  public function testTimestamp() {
    date_default_timezone_set('Europe/Berlin');
    try {
      $d1= new Date('1980-05-28 06:30:00 Europe/Berlin');
      $d2= new Date(328336200);
      
      Assert::equals($d1, $d2);
      Assert::equals($d2, new Date($d2->toString()));
    } finally {
      date_default_timezone_set('GMT');
    }
  }
  
  #[Test]
  public function testTimestampWithTZ() {
    $d= new Date(328336200, new TimeZone('Australia/Sydney'));
    Assert::equals('Australia/Sydney', $d->getTimeZone()->name());
  }
  
  /**
   * Test PHP Bug #42910 - timezone should not fallback to default
   * timezone if it actually is unknown.
   */
  #[Test, Ignore, Expect(IllegalStateException::class)]
  public function emptyTimeZoneNameIfUnknown() {
  
    // Specific timezone id unknown, can be Europe/Paris, Europe/Berlin, ...
    $date= new Date('1980-05-28 06:30:00+0200');
    Assert::notEquals('GMT', $date->getTimeZone()->name());
  }
  
  #[Test]
  public function string_representation() {
    Assert::equals(
      '2007-11-10 20:15:00+0100',
      (new Date('2007-11-10 20:15+0100'))->toString(Date::DEFAULT_FORMAT)
    );
  }
  
  #[Test, Values(from: 'timezones')]
  public function string_representation_with_timezone($timezone) {
    Assert::equals(
      '2007-11-10 20:15:00+0100',
      (new Date('2007-11-10 19:15+0000'))->toString(Date::DEFAULT_FORMAT, $timezone)
    );
  }

  #[Test]
  public function timezone_preserved_during_serialization() {
    $date= unserialize(serialize(new Date('2007-11-10 20:15+0100')));
    Assert::equals('2007-11-10 20:15:00+0100', $date->toString());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function malformed_input_string() {
    new Date('@@not-a-date@@');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function monthExceeded() {
    new Date('30.99.2010');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function dayExceeded() {
    new Date('99.30.2010');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unknownTimeZoneNameInString() {
    new Date('14.12.2010 11:55:00 Europe/Karlsruhe');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unknownTimeZoneOffsetInString() {
    new Date('14.12.2010 11:55:00+9999');
  }

  #[Test]
  public function constructorBrokenAfterException() {
    Date::now();
    Assert::throws(IllegalArgumentException::class, fn() => new Date('bogus'));
    Date::now();
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function dateCreateWithAllInvalidArguments() {
    Date::create('', '', '', '', '', '');
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function dateCreateWithInvalidArgumentsExceptTimeZone() {
    Date::create('', '', '', '', '', '', new TimeZone('UTC'));
  }
  
  #[Test]
  public function createDateFromStaticNowFunctionWithoutParam() {
    Assert::equals(true, Date::now() instanceof Date);
  }
  
  #[Test]
  public function createDateFromStaticNowFunctionWithZimeZone() {
    $d= Date::now(new TimeZone('Australia/Sydney'));
    Assert::equals('Australia/Sydney', $d->getTimeZone()->name());
  }

  #[Test]
  public function createDateFromTime() {
    $date= new Date('19.19');
    Assert::equals(strtotime('19.19'), $date->getTime());
  }

  #[Test, Values([[0, '1970-01-01T00:00:00+00:00'], [1, '1970-01-01T00:00:01+00:00'], [-1, '1969-12-31T23:59:59+00:00'], [-1000000, '1969-12-20T10:13:20+00:00'], [1000000, '1970-01-12T13:46:40+00:00']])]
  public function testValidUnixTimestamp($timestamp, $expected) {
    $this->assertDateEquals($expected, new Date($timestamp));
    $this->assertDateEquals($expected, new Date((string)$timestamp));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function testInvalidUnixTimestamp() {
    new Date('+1000000');
  }

  #[Test]
  public function microseconds() {
    Assert::equals(393313, (new Date('2019-07-03 15:18:10.393313'))->getMicroSeconds());
  }

  #[Test]
  public function float_timestamp() {
    Assert::equals(393000, (new Date(1723896922.393))->getMicroSeconds());
  }
}