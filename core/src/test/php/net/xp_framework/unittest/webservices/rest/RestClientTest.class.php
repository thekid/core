<?php namespace net\xp_framework\unittest\webservices\rest;

use unittest\TestCase;
use webservices\rest\RestClient;


/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestClient
 */
class RestClientTest extends TestCase {
  const BASE_URL = 'http://example.com';

  /**
   * Creates a new RestClient fixture with a given base
   *
   * @param   var base
   * @return  webservices.rest.RestClient
   */
  protected function newFixture($base= null) {
    return new RestClient($base);
  }
  
  /**
   * Test getBase()
   *
   */
  #[@test]
  public function stringBase() {
    $this->assertEquals(
      new \peer\URL(self::BASE_URL),
      $this->newFixture(self::BASE_URL)->getBase()
    );
  }

  /**
   * Test getBase()
   *
   */
  #[@test]
  public function nullBase() {
    $this->assertNull($this->newFixture()->getBase());
  }

  /**
   * Test getBase()
   *
   */
  #[@test]
  public function urlBase() {
    $this->assertEquals(
      new \peer\URL(self::BASE_URL),
      $this->newFixture(new \peer\URL(self::BASE_URL))->getBase()
    );
  }

  /**
   * Test setBase()
   *
   */
  #[@test]
  public function setBase() {
    $fixture= $this->newFixture();
    $fixture->setBase(self::BASE_URL);
    $this->assertEquals(new \peer\URL(self::BASE_URL), $fixture->getBase());
  }

  /**
   * Test withBase()
   *
   */
  #[@test]
  public function withBase() {
    $fixture= $this->newFixture();
    $this->assertEquals($fixture, $fixture->withBase(self::BASE_URL));
    $this->assertEquals(new \peer\URL(self::BASE_URL), $fixture->getBase());
  }

  /**
   * Test setConnection()
   *
   */
  #[@test]
  public function setConnection() {
    $fixture= $this->newFixture();
    $fixture->setConnection(new \peer\http\HttpConnection(self::BASE_URL));
    $this->assertEquals(new \peer\URL(self::BASE_URL), $fixture->getBase());
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function singleArgumentExecuteNull() {
    $this->newFixture()->execute(null);
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function singleArgumentExecuteThis() {
    $this->newFixture()->execute($this);
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function executeNullTypeNullRequest() {
    $this->newFixture()->execute(null, null);
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function executeNullType() {
    $this->newFixture()->execute(null, new \webservices\rest\RestRequest());
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function executeNullRequest() {
    $this->newFixture()->execute(\lang\Type::$VAR, null);
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function executeThisRequest() {
    $this->newFixture()->execute(\lang\Type::$VAR, $this);
  }

  /**
   * Test execute()
   *
   */
  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'No connection set')]
  public function executeWithoutBase() {
    $this->newFixture()->execute(\lang\Type::$VAR, new \webservices\rest\RestRequest());
  }

  /**
   * Test "text/xml" is supported
   *
   */
  #[@test]
  public function textXmlDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/xml')
    );
  }

  /**
   * Test "application/xml" is supported
   *
   */
  #[@test]
  public function applicationXmlDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('application/xml')
    );
  }

  /**
   * Test "text/json" is supported
   *
   */
  #[@test]
  public function textJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/json')
    );
  }

  /**
   * Test "text/x-json" is supported
   *
   */
  #[@test]
  public function textXJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/x-json')
    );
  }

  /**
   * Test "text/javascript" is supported
   *
   */
  #[@test]
  public function textJavascriptDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/javascript')
    );
  }

  /**
   * Test "application/json" is supported
   *
   */
  #[@test]
  public function applicationJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('application/json')
    );
  }

  /**
   * Test "text/html" is not supported
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function unknownDeserializer() {
    $this->assertNull($this->newFixture()->deserializerFor('text/html'));
  }

  /**
   * Test "text/xml" is supported
   *
   */
  #[@test]
  public function textXmlSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/xml')
    );
  }

  /**
   * Test "application/xml" is supported
   *
   */
  #[@test]
  public function applicationXmlSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('application/xml')
    );
  }

  /**
   * Test "text/json" is supported
   *
   */
  #[@test]
  public function textJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/json')
    );
  }

  /**
   * Test "text/x-json" is supported
   *
   */
  #[@test]
  public function textXJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/x-json')
    );
  }

  /**
   * Test "text/javascript" is supported
   *
   */
  #[@test]
  public function textJavascriptSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/javascript')
    );
  }

  /**
   * Test "application/json" is supported
   *
   */
  #[@test]
  public function applicationJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('application/json')
    );
  }

  /**
   * Test "text/html" is not supported
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function unknownSerializer() {
    $this->assertNull($this->newFixture()->serializerFor('text/html'));
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function stringRepresentation() {
    $this->assertEquals(
      "webservices.rest.RestClient(->null)",
      $this->newFixture()->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function stringRepresentationWithBase() {
    $this->assertEquals(
      "webservices.rest.RestClient(->peer.http.HttpConnection(->URL{http://api.example.com/ via peer.http.SocketHttpTransport}, timeout: [read= 60.00, connect= 2.00]))",
      $this->newFixture('http://api.example.com/')->toString()
    );
  }

  /**
   * Setting connect timeouts w/o connection object yields
   * an IllegalStateException
   *
   */
  #[@test, @expect('lang.IllegalStateException')]
  public function setConnectTimeoutWithNoConnectionFails() {
    $this->newFixture()->setConnectTimeout(31337);
  }

  /**
   * Test set connect timeout values can be read
   * later.
   *
   */
  #[@test]
  public function setConnectTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');
    $fixture->setConnectTimeout(31337);

    $this->assertEquals(31337, $fixture->getConnectTimeout());
  }

  /**
   * Setting timeouts w/o connection object yields
   * an IllegalStateException
   *
   */
  #[@test, @expect('lang.IllegalStateException')]
  public function setTimeoutWithoutConnectionFails() {
    $this->newFixture()->setTimeout(31337);
  }

  /**
   * Test set timeout values can be read
   * later.
   *
   */
  #[@test]
  public function setTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');
    $fixture->setTimeout(31337);

    $this->assertEquals(31337, $fixture->getTimeout());
  }

  /**
   * Test connect timeouts are inherited from HttpConnection
   *
   */
  #[@test]
  public function inheritsAConnectionsDefaultConnectTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');

    $this->assertEquals(2.0, $fixture->getConnectTimeout());
  }

  /**
   * Test timeouts are inherited from HttpConnection
   *
   */
  #[@test]
  public function inheritsAConnectionsDefaultTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');

    $this->assertEquals(60, $fixture->getTimeout());
  }
}
