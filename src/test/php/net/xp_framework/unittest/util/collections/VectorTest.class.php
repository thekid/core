<?php namespace net\xp_framework\unittest\util\collections;

use unittest\TestCase;
use lang\types\String;
use lang\types\ArrayList;
use util\collections\Vector;


/**
 * TestCase for vector class
 *
 * @see      xp://util.collections.Vector
 * @purpose  Unittest
 */
class VectorTest extends TestCase {

  /**
   * Test a newly created vector is empty
   *
   */
  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue((new Vector())->isEmpty());
  }

  /**
   * Test a newly created vector is empty
   *
   */
  #[@test]
  public function sizeOfEmptyVector() {
    $this->assertEquals(0, (new Vector())->size());
  }
  
  /**
   * Test a newly created vector is empty
   *
   */
  #[@test]
  public function nonEmptyVector() {
    $v= new Vector(array(new \lang\Object()));
    $this->assertEquals(1, $v->size());
    $this->assertFalse($v->isEmpty());
  }

  /**
   * Test adding elements
   *
   */
  #[@test]
  public function adding() {
    $v= new Vector();
    $v->add(new \lang\Object());
    $this->assertEquals(1, $v->size());
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllArray() {
    $v= new Vector();
    $this->assertTrue($v->addAll(array(new \lang\Object(), new \lang\Object())));
    $this->assertEquals(2, $v->size());
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllVector() {
    $v1= new Vector();
    $v2= new Vector();
    $v2->add(new \lang\Object());
    $v2->add(new \lang\Object());
    $this->assertTrue($v1->addAll($v2));
    $this->assertEquals(2, $v1->size());
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllArrayList() {
    $v= new Vector();
    $this->assertTrue($v->addAll(new ArrayList(new \lang\Object(), new \lang\Object())));
    $this->assertEquals(2, $v->size());
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllEmptyArray() {
    $this->assertFalse((new Vector())->addAll(array()));
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllEmptyVector() {
    $this->assertFalse((new Vector())->addAll(new Vector()));
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function addAllEmptyArrayList() {
    $this->assertFalse((new Vector())->addAll(new ArrayList()));
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function unchangedAfterNullInAddAll() {
    $v= create('new util.collections.Vector<Object>()');
    try {
      $v->addAll(array(new \lang\Object(), null));
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  /**
   * Test adding elements via addAll
   *
   */
  #[@test]
  public function unchangedAfterIntInAddAll() {
    $v= create('new util.collections.Vector<string>()');
    try {
      $v->addAll(array('hello', 5));
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  /**
   * Test adding NULL does not work
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function addingNull() {
    create('new util.collections.Vector<Object>()')->add(null);
  }

  /**
   * Test replacing elements
   *
   */
  #[@test]
  public function replacing() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->set(0, new String('two'));
    $this->assertEquals(1, $v->size());
    $this->assertEquals($o, $r);
  }

  /**
   * Test replacing elements with NULL does not work
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function replacingWithNull() {
    create('new util.collections.Vector<Object>', array(new \lang\Object()))->set(0, null);
  }

  /**
   * Test replacing elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function settingPastEnd() {
    (new Vector())->set(0, new \lang\Object());
  }

  /**
   * Test replacing elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function settingNegative() {
    (new Vector())->set(-1, new \lang\Object());
  }

  /**
   * Test reading elements
   *
   */
  #[@test]
  public function reading() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->get(0);
    $this->assertEquals($o, $r);
  }

  /**
   * Test reading elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function readingPastEnd() {
    (new Vector())->get(0);
  }

  /**
   * Test reading elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function readingNegative() {
    (new Vector())->get(-1);
  }

  /**
   * Test removing elements
   *
   */
  #[@test]
  public function removing() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->remove(0);
    $this->assertEquals(0, $v->size());
    $this->assertEquals($o, $r);
  }

  /**
   * Test removing elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function removingPastEnd() {
    (new Vector())->get(0);
  }

  /**
   * Test removing elements
   *
   */
  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function removingNegative() {
    (new Vector())->get(-1);
  }

  /**
   * Test clearing the vector
   *
   */
  #[@test]
  public function clearing() {
    $v= new Vector(array(new String('Goodbye cruel world')));
    $this->assertFalse($v->isEmpty());
    $v->clear();
    $this->assertTrue($v->isEmpty());
  }

  /**
   * Test elements()
   *
   */
  #[@test]
  public function elementsOfEmptyVector() {
    $this->assertEquals(array(), (new Vector())->elements());
  }

  /**
   * Test elements()
   *
   */
  #[@test]
  public function elementsOf() {
    $el= array(new String('a'), new \lang\Object());
    $this->assertEquals($el, (new Vector($el))->elements());
  }

  /**
   * Test contains()
   *
   */
  #[@test]
  public function addedStringIsContained() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $this->assertTrue($v->contains($o));
  }

  /**
   * Test contains()
   *
   */
  #[@test]
  public function emptyVectorDoesNotContainString() {
    $this->assertFalse((new Vector())->contains(new \lang\Object()));
  }

  /**
   * Test indexOf()
   *
   */
  #[@test]
  public function indexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->indexOf(new \lang\Object()));
  }

  /**
   * Test indexOf()
   *
   */
  #[@test]
  public function indexOf() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector(array($a)))->indexOf($a));
  }

  /**
   * Test indexOf()
   *
   */
  #[@test]
  public function indexOfElementContainedTwice() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector(array($a, new \lang\Object(), $a)))->indexOf($a));
  }

  /**
   * Test lastIndexOf()
   *
   */
  #[@test]
  public function lastIndexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->lastIndexOf(new \lang\Object()));
  }

  /**
   * Test lastIndexOf()
   *
   */
  #[@test]
  public function lastIndexOf() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector(array($a)))->lastIndexOf($a));
  }

  /**
   * Test lastIndexOf()
   *
   */
  #[@test]
  public function lastIndexOfElementContainedTwice() {
    $a= new String('A');
    $this->assertEquals(2, (new Vector(array($a, new \lang\Object(), $a)))->lastIndexOf($a));
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function stringOfEmptyVector() {
    $this->assertEquals(
      "util.collections.Vector[0]@{\n}",
      (new Vector())->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function stringOf() {
    $this->assertEquals(
      "util.collections.Vector[2]@{\n  0: One\n  1: Two\n}",
      (new Vector(array(new String('One'), new String('Two'))))->toString()
    );
  }

  /**
   * Test iteration
   *
   */
  #[@test]
  public function iteration() {
    $v= new Vector();
    for ($i= 0; $i < 5; $i++) {
      $v->add(new String('#'.$i));
    }
    
    $i= 0;
    foreach ($v as $offset => $string) {
      $this->assertEquals($offset, $i);
      $this->assertEquals(new String('#'.$i), $string);
      $i++;
    }
  }

  /**
   * Test equals()
   *
   */
  #[@test]
  public function twoEmptyVectorsAreEqual() {
    $this->assertTrue((new Vector())->equals(new Vector()));
  }

  /**
   * Test equals()
   *
   */
  #[@test]
  public function sameVectorsAreEqual() {
    $a= new Vector(array(new String('One'), new String('Two')));
    $this->assertTrue($a->equals($a));
  }

  /**
   * Test equals()
   *
   */
  #[@test]
  public function vectorsWithSameContentsAreEqual() {
    $a= new Vector(array(new String('One'), new String('Two')));
    $b= new Vector(array(new String('One'), new String('Two')));
    $this->assertTrue($a->equals($b));
  }

  /**
   * Test equals() does not choke on NULL values
   *
   */
  #[@test]
  public function aVectorIsNotEqualToNull() {
    $this->assertFalse((new Vector())->equals(null));
  }

  /**
   * Test equals()
   *
   */
  #[@test]
  public function twoVectorsOfDifferentSizeAreNotEqual() {
    $this->assertFalse((new Vector(array(new \lang\Object())))->equals(new Vector()));
  }

  /**
   * Test equals()
   *
   */
  #[@test]
  public function orderMattersForEquality() {
    $a= array(new String('a'), new String('b'));
    $b= array(new String('b'), new String('a'));
    $this->assertFalse((new Vector($a))->equals(new Vector($b)));
  }
}
