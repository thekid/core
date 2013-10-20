<?php namespace net\xp_framework\unittest\rdbms;
 
use unittest\TestCase;
use rdbms\query\SelectQuery;
use rdbms\query\UpdateQuery;
use rdbms\query\DeleteQuery;
use rdbms\query\SetOperation;
use rdbms\Criteria;
use net\xp_framework\unittest\rdbms\dataset\Job;
use net\xp_framework\unittest\rdbms\dataset\Person;

/**
 * Test query class
 *
 * @see      xp://rdbms.Query
 * @purpose  Unit Test
 */
class QueryTest extends TestCase {
  const
    MOCK_CONNECTION_CLASS = 'net.xp_framework.unittest.rdbms.mock.MockConnection';

  private
    $qa= null,
    $qb= null,
    $qas= 'select  job_id, title from JOBS.job  where job_id = 5',
    $qbs= 'select  job_id, name from JOBS.Person ',
    $qu= null;
    
  /**
   * Mock connection registration
   *
   */  
  #[@beforeClass]
  public static function registerMockConnection() {
    \rdbms\DriverManager::register('mock', \lang\XPClass::forName(self::MOCK_CONNECTION_CLASS));
  }
  
  /**
   * Setup method
   *
   */
  public function setUp() {
    with ($conn= \rdbms\DriverManager::getConnection('mock://mock/JOBS?autoconnect=1')); {
      Job::getPeer()->setConnection($conn);
      Person::getPeer()->setConnection($conn);
    }

    $this->qa= new SelectQuery();
    $this->qa->setPeer(Job::getPeer());
    $this->qa->setCriteria(
      create(new Criteria(Job::column('job_id')->equal(5)))->setProjection(
        \rdbms\criterion\Projections::ProjectionList()
        ->add(Job::column('job_id'))
        ->add(Job::column('title'))
      )
    );

    $this->qb= new SelectQuery();
    $this->qb->setPeer(Person::getPeer());
    $this->qb->setCriteria(
      create(new Criteria())->setProjection(
        \rdbms\criterion\Projections::ProjectionList()
        ->add(Person::column('job_id'))
        ->add(Person::column('name'))
      )
    );

  }
  
  /**
   * Test existance
   *
   */
  #[@test]
  public function newQuery() {
    $this->assertTrue(class_exists('SelectQuery'));
  }
  
  /**
   * set and store criteria
   *
   */
  #[@test]
  public function setCriteria() {
    $q= new SelectQuery();
    $c= new Criteria();
    $q->setCriteria($c);
    $this->assertEquals($c, $q->getCriteria());
  }
  
  /**
   * set and store Dataset by name
   *
   */
  #[@test]
  public function setPeer() {
    $q= new SelectQuery();
    $q->setPeer(Job::getPeer());
    $this->assertEquals(Job::getPeer(), $q->getPeer());
  }
  
  /**
   * get Connection
   *
   */
  #[@test]
  public function getConnection() {
    $q= new SelectQuery();
    $this->assertNull($q->getConnection());
    $q->setPeer(Job::getPeer());
    $this->assertClass($q->getConnection(), 'net.xp_framework.unittest.rdbms.mock.MockConnection');
  }
  
  /**
   * withRestriction test
   *
   */
  #[@test]
  public function executeWithRestriction() {
    $this->assertClass(create(new SelectQuery())->withRestriction(Job::column('job_id')->equal(5)), 'rdbms.query.SelectQuery');
  }
  
  /**
   * test query string without set operation
   *
   */
  #[@test]
  public function getSingleQueryString() {
    $this->assertEquals($this->qas, $this->qa->getQueryString());
    $this->assertEquals($this->qbs, $this->qb->getQueryString());
  }
  
  /**
   * test query string with set operation
   *
   */
  #[@test]
  public function getQueryString() {
    $so= new SetOperation(SetOperation::UNION, $this->qa, $this->qb);
    $this->assertEquals(
      $this->qas.' union '.$this->qbs,
      $so->getQueryString()
    );
  }
  
  /**
   * test query string with set operation
   *
   */
  #[@test]
  public function factory() {
    $so= SetOperation::union($this->qa, $this->qb);
    $this->assertEquals(
      $this->qas.' union '.$this->qbs,
      $so->getQueryString()
    );
    $so= SetOperation::except($this->qa, $this->qb);
    $this->assertEquals(
      $this->qas.' except '.$this->qbs,
      $so->getQueryString()
    );
    $so= SetOperation::intercept($this->qa, $this->qb);
    $this->assertEquals(
      $this->qas.' intercept '.$this->qbs,
      $so->getQueryString()
    );
  }
  
  /**
   * test query string with set operation
   *
   */
  #[@test]
  public function all() {
    $so= SetOperation::union($this->qa, $this->qb, true);
    $this->assertEquals(
      $this->qas.' union all '.$this->qbs,
      $so->getQueryString()
    );
  }
  
  /**
   * test query string with set operation
   *
   */
  #[@test]
  public function nesting() {
    $so= SetOperation::union(SetOperation::union($this->qb, $this->qa), SetOperation::union($this->qb, $this->qa));
    $this->assertEquals(
      $this->qbs.' union '.$this->qas.' union '.$this->qbs.' union '.$this->qas,
      $so->getQueryString()
    );
  }
  
}
