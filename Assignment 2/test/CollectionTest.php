<?php

use \Toolkit\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->coll = new Collection();
    }

    /**
     * @test
     */
    public function AddItemWithoutKey()
    {
        $this->coll->addItem("Collection item 1");
        $this->assertEquals("Collection item 1", $this->coll[0]);
    }

    public function testAddItemForLengthEqualsOne() {
        $this->coll->addItem('ItemValue', 'itemKey');
        $this->assertEquals(1, $this->coll->length());
    }

    public function testAddItemNoKeyLengthEqualsOne() {
        $this->coll->addItem('ItemValue');
        $this->assertEquals(1, $this->coll->length());
    }

    public function testLengthForTwoItems() {
        $this->coll->addItem('ItemValue1');
        $this->coll->addItem('ItemValue2');
        $this->assertEquals(2, $this->coll->length());
    }

    public function testExceptionIfKeyExists() {
        $this->setExpectedException('Exception', 'Key already exists.');
        $this->coll->addItem('ItemValue1', 'itemKey');
        $this->coll->addItem('ItemValue2', 'itemKey');
    }

    public function testAddRemoveItemIndex() {
        $this->coll->addItem('ItemValue');
        $this->coll->removeItem(0);
        $this->assertEquals(0, $this->coll->length());
    }

    public function testAddRemoveItemKey() {
        $this->coll->addItem('ItemValue', 'itemKey');
        $this->coll->removeItem('itemKey');
        $this->assertEquals(0, $this->coll->length());
    }

    public function testRemoveItemDoesntExist() {
        $this->setExpectedException('Exception', 'Key does not exist.');
        $this->coll->removeItem(0);
    }

    public function testGetItemUsingKey() {
        $this->coll->addItem('ItemValue', 'itemKey');
        $this->assertEquals('ItemValue', $this->coll->getItem('itemKey'));
    }

    public function testGetItemUsingIndex() {
        $this->coll->addItem('ItemValue');
        $this->assertEquals('ItemValue', $this->coll->getItem(0));
    }

    public function testGetItemDoesntExist() {
        $this->setExpectedException('Exception', 'Key does not exist.');
        $this->coll->getItem(0);
    }

    public function testGetArrayOfKeys() {
        $this->coll->addItem('ItemValue1');
        $this->coll->addItem('ItemValue2');
        $this->assertEquals(array(0, 1), $this->coll->keys());
    }

    public function testGetEmptyArrayOfkeys() {
        $this->assertEquals(array(), $this->coll->keys());
    }

    public function testIteration() {
        $coll = new Collection();
        $coll->addItem('HelloWorld');

        $count = 0;

        foreach($coll as $item) {
            $count++;
        }
        $this->assertEquals(1, $count);
    }

    public function testAssociationIteration() {
        $coll = new Collection();
        $coll->addItem('HelloWorld', 'msg1');

        $count = 0;
        $actual = '';
        foreach($coll as $key=>$item) {
            $actual = $key;
        }
        $this->assertEquals('msg1', $actual);
    }
}
