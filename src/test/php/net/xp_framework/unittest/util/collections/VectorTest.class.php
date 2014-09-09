<?php namespace net\xp_framework\unittest\util\collections;

use unittest\TestCase;
use lang\types\String;
use lang\types\ArrayList;
use util\collections\Vector;

/**
 * TestCase for vector class
 *
 * @see  xp://util.collections.Vector
 */
class VectorTest extends TestCase {

  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue((new Vector())->isEmpty());
  }

  #[@test]
  public function sizeOfEmptyVector() {
    $this->assertEquals(0, (new Vector())->size());
  }
  
  #[@test]
  public function nonEmptyVector() {
    $v= new Vector([new \lang\Object()]);
    $this->assertEquals(1, $v->size());
    $this->assertFalse($v->isEmpty());
  }

  #[@test]
  public function adding() {
    $v= new Vector();
    $v->add(new \lang\Object());
    $this->assertEquals(1, $v->size());
  }

  #[@test]
  public function addAllArray() {
    $v= new Vector();
    $this->assertTrue($v->addAll([new \lang\Object(), new \lang\Object()]));
    $this->assertEquals(2, $v->size());
  }

  #[@test]
  public function addAllVector() {
    $v1= new Vector();
    $v2= new Vector();
    $v2->add(new \lang\Object());
    $v2->add(new \lang\Object());
    $this->assertTrue($v1->addAll($v2));
    $this->assertEquals(2, $v1->size());
  }

  #[@test]
  public function addAllArrayList() {
    $v= new Vector();
    $this->assertTrue($v->addAll(new ArrayList(new \lang\Object(), new \lang\Object())));
    $this->assertEquals(2, $v->size());
  }

  #[@test]
  public function addAllEmptyArray() {
    $this->assertFalse((new Vector())->addAll([]));
  }

  #[@test]
  public function addAllEmptyVector() {
    $this->assertFalse((new Vector())->addAll(new Vector()));
  }

  #[@test]
  public function addAllEmptyArrayList() {
    $this->assertFalse((new Vector())->addAll(new ArrayList()));
  }

  #[@test]
  public function unchangedAfterNullInAddAll() {
    $v= create('new util.collections.Vector<Object>()');
    try {
      $v->addAll([new \lang\Object(), null]);
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  #[@test]
  public function unchangedAfterIntInAddAll() {
    $v= create('new util.collections.Vector<string>()');
    try {
      $v->addAll(['hello', 5]);
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function addingNull() {
    create('new util.collections.Vector<Object>()')->add(null);
  }

  #[@test]
  public function replacing() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->set(0, new String('two'));
    $this->assertEquals(1, $v->size());
    $this->assertEquals($o, $r);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function replacingWithNull() {
    create('new util.collections.Vector<Object>', [new \lang\Object()])->set(0, null);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function settingPastEnd() {
    (new Vector())->set(0, new \lang\Object());
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function settingNegative() {
    (new Vector())->set(-1, new \lang\Object());
  }

  #[@test]
  public function reading() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->get(0);
    $this->assertEquals($o, $r);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function readingPastEnd() {
    (new Vector())->get(0);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function readingNegative() {
    (new Vector())->get(-1);
  }

  #[@test]
  public function removing() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $r= $v->remove(0);
    $this->assertEquals(0, $v->size());
    $this->assertEquals($o, $r);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function removingPastEnd() {
    (new Vector())->get(0);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function removingNegative() {
    (new Vector())->get(-1);
  }

  #[@test]
  public function clearing() {
    $v= new Vector([new String('Goodbye cruel world')]);
    $this->assertFalse($v->isEmpty());
    $v->clear();
    $this->assertTrue($v->isEmpty());
  }

  #[@test]
  public function elementsOfEmptyVector() {
    $this->assertEquals([], (new Vector())->elements());
  }

  #[@test]
  public function elementsOf() {
    $el= [new String('a'), new \lang\Object()];
    $this->assertEquals($el, (new Vector($el))->elements());
  }

  #[@test]
  public function addedStringIsContained() {
    $v= new Vector();
    $o= new String('one');
    $v->add($o);
    $this->assertTrue($v->contains($o));
  }

  #[@test]
  public function emptyVectorDoesNotContainString() {
    $this->assertFalse((new Vector())->contains(new \lang\Object()));
  }

  #[@test]
  public function indexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->indexOf(new \lang\Object()));
  }

  #[@test]
  public function indexOf() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector([$a]))->indexOf($a));
  }

  #[@test]
  public function indexOfElementContainedTwice() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector([$a, new \lang\Object(), $a]))->indexOf($a));
  }

  #[@test]
  public function lastIndexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->lastIndexOf(new \lang\Object()));
  }

  #[@test]
  public function lastIndexOf() {
    $a= new String('A');
    $this->assertEquals(0, (new Vector([$a]))->lastIndexOf($a));
  }

  #[@test]
  public function lastIndexOfElementContainedTwice() {
    $a= new String('A');
    $this->assertEquals(2, (new Vector([$a, new \lang\Object(), $a]))->lastIndexOf($a));
  }

  #[@test]
  public function stringOfEmptyVector() {
    $this->assertEquals(
      "util.collections.Vector[0]@{\n}",
      (new Vector())->toString()
    );
  }

  #[@test]
  public function stringOf() {
    $this->assertEquals(
      "util.collections.Vector[2]@{\n  0: One\n  1: Two\n}",
      (new Vector([new String('One'), new String('Two')]))->toString()
    );
  }

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

  #[@test]
  public function twoEmptyVectorsAreEqual() {
    $this->assertTrue((new Vector())->equals(new Vector()));
  }

  #[@test]
  public function sameVectorsAreEqual() {
    $a= new Vector([new String('One'), new String('Two')]);
    $this->assertTrue($a->equals($a));
  }

  #[@test]
  public function vectorsWithSameContentsAreEqual() {
    $a= new Vector([new String('One'), new String('Two')]);
    $b= new Vector([new String('One'), new String('Two')]);
    $this->assertTrue($a->equals($b));
  }

  #[@test]
  public function aVectorIsNotEqualToNull() {
    $this->assertFalse((new Vector())->equals(null));
  }

  #[@test]
  public function twoVectorsOfDifferentSizeAreNotEqual() {
    $this->assertFalse((new Vector([new \lang\Object()]))->equals(new Vector()));
  }

  #[@test]
  public function orderMattersForEquality() {
    $a= [new String('a'), new String('b')];
    $b= [new String('b'), new String('a')];
    $this->assertFalse((new Vector($a))->equals(new Vector($b)));
  }
}
