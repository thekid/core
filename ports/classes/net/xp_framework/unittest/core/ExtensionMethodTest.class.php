<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'lang.types.ArrayList',
    'util.collections.Vector',
    'net.xp_framework.unittest.core.ArrayListExtensions',
    'net.xp_framework.unittest.core.IListExtensions'
  );

  /**
   * Tests extension methods
   *
   * @see   xp://net.xp_framework.unittest.core.ArrayListExtensions
   * @see   xp://net.xp_framework.unittest.core.ClassExtensions
   */
  class ExtensionMethodTest extends TestCase {

    /**
     * Test ArrayListExtensions::find() method for an instance of ArrayList
     *
     */
    #[@test]
    public function findInArrayList() {
      $this->assertEquals(
        5,
        create(new ArrayList(1, 2, 3, 4, 5, 6))->find(create_function('$e', 'return $e % 5 == 0;'))
      );
    }
  
    /**
     * Test ArrayListExtensions::findAll() method for an instance of ArrayList
     *
     */
    #[@test]
    public function findAllInArrayList() {
      $this->assertEquals(
        new ArrayList(2, 4, 6),
        create(new ArrayList(1, 2, 3, 4, 5, 6))->findAll(create_function('$e', 'return $e % 2 == 0;'))
      );
    }

    /**
     * Test ArrayListExtensions::find() method for an instance of an 
     * ArrayList subclass
     *
     */
    #[@test]
    public function findInArrayListSubclass() {
      $this->assertEquals(
        1,
        newinstance('lang.types.ArrayList', array(1, 2, 3), '{}')->find(create_function('$e', 'return $e % 2 == 1;'))
      );
    }

    /**
     * Test call to non-existant method in ArrayList class
     *
     */
    #[@test, @expect(class= 'lang.Error', withMessage= 'Call to undefined method ArrayList::nonExistant')]
    public function callNonExistantArrayListMethod() {
      ArrayList::newInstance(0)->nonExistant();
    }

    /**
     * Test IListExtensions::find() method for an instance of Vector
     * (which implements IList)
     *
     */
    #[@test]
    public function findInVector() {
      $v= create('new Vector<String>', array(new String('Hello'), new String('World!')));
      $this->assertEquals(
        new String('World!'),
        $v->find(create_function('$e', 'return $e->length() > 5;'))
      );
    }
  
    /**
     * Test IListExtensions::findAll() method for an instance of Vector
     * (which implements IList)
     *
     */
    #[@test]
    public function findAllInVector() {
      $v= create('new Vector<String>', array(new String('Hi'), new String('World'), new String('!')));
      $this->assertEquals(
        create('new Vector<String>', array(new String('Hi'), new String('!'))),
        $v->findAll(create_function('$e', 'return $e->length() < 5;'))
      );
    }
  }
?>
