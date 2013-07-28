<?php

use Codeception\Util\Stub;
use Datatype\Collection;


class CollectionTest extends \Codeception\TestCase\Test
{
   /**
	* @var \CodeGuy
	*/
	protected $codeGuy;

	protected function _before()
	{
		$this->collection = new Collection( ['a' => 'b', 'c' => ['d' => 'nested'], 0 => 'e'] );
	}

	protected function _after()
	{
	}

	public function testCollectionShouldBeDefinedAsMutable()
	{
		$this->assertTrue( $this->collection->is_mutable, 'The Collection class must be defined as being mutable' );
	}

	public function testCollectionMimicksArrays()
	{
		$implements = class_implements( $this->collection );

		$this->assertContains( 'Countable', $implements, 'Collection must implement Countable interface' );
		$this->assertContains( 'ArrayAccess', $implements, 'Collection must implement ArrayAccess interface' );
		$this->assertContains( 'SeekableIterator', $implements, 'Collection must implement SeekableIterator interface' );
	}

	public function testCollectionHasDynamicallyCalculatedProperties()
	{
		$this->assertEquals( 3, $this->collection->length, 'The length property is not the same as the actual length' );
		$this->assertEquals( ['a', 'c', 0], $this->collection->keys->to_a(), 'The keys property does not contain all keys' );
		$this->assertEquals( ['b', ['d' => 'nested'], 'e'], $this->collection->values->to_a(), 'The values property does not contain all values' );
		$this->assertEquals( 'Datatype\Object', $this->collection->parent );
	}

	public function testCollectionHasSameStructureAsOriginalArray()
	{
		// Collection must not modify the array it was initialised with without explicit instructions to do so
		$this->assertSame( ['a' => 'b', 'c' => ['d' => 'nested'], 0 => 'e'], $this->collection->to_a() );
	}

	public function testLastPropertyReturnsLastElement()
	{
		$this->assertEquals( 'e', $this->collection->last, "The 'last' property must return last element in Collection" );
	}

	public function testTheFirstPropertyReturnsFirstElement()
	{
		$this->assertEquals( 'b', $this->collection->first, "The 'first' property must return first element in Collection" );
	}

	public function testICanCheckIfKeyExists()
	{
		$this->assertTrue( $this->collection->exists( 'c' ) );
		$this->assertFalse( $this->collection->exists( 'y' ) );
	}

	public function testICanAddItemsToCollection()
	{
		$this->collection[] = 'added item';
		$this->assertEquals( 'added item', end( $this->collection->to_a() ), 'Could not add item via array notation []' );

		$this->collection['custom key'] = 'new item';
		$this->assertEquals( 'new item', $this->collection['custom key'] );

		$this->collection->add( 'another item' );
		$this->assertEquals( 'another item', end( $this->collection->to_a() ), "Could not add item via 'add()' method" );
	}

	public function testPushMoreItemsAtOnceToCollection()
	{
		$this->collection->push( 'pushed', 'pushed again' );
		$this->assertEquals( 'pushed again', $this->collection->last );
	}

	public function testICanRemoveItemsFromCollection()
	{
		$this->collection->remove( 'c' );
		$this->assertFalse( array_key_exists( 'c', $this->collection->to_a() ) );
	}

	public function testICanPopItemsOutOfCollection()
	{
		$this->assertEquals( 'e', $this->collection->pop() );
		$this->assertEquals( ['d' => 'nested'], $this->collection->pop()->to_a() );
	}

	public function testPoppingFromEmptyCollectionReturnsNull()
	{
		$col = new Collection;
		$this->assertEquals( null, $col->pop() );
	}

	public function testAddingAnArrayToCollectionWillTurnItToCollection()
	{
		$nested_array = ['added' => 'later'];

		$this->collection[] = $nested_array;

		$this->assertTrue( $this->collection->last instanceof Collection, 'Arrays added later should be also converted to collections' );
	}

	public function testCollectionHasMembersAccessibleByIndexes()
	{
		// I can access members of the array using indexes
		$this->assertEquals( 'b', $this->collection['a'] );
		$this->assertEquals('e', $this->collection[0] );
	}

	public function testNestedArraysAreAlsoCollections()
	{
		$this->assertTrue( $this->collection['c'] instanceof Collection, 'Nested arrays should be converted to Collections' );
	}

	public function testICanIterateOverTheCollectionWithLoop()
	{
		foreach ( $this->collection as $key => $item )
		{
			$this->assertSame( $this->collection[$key], $item );

			// Make sure that if I add an element during the foreach loop, I will not
			// see it in this loop
			$this->collection[] = 'should not see this item';
			$this->assertFalse( $item == 'should not see this item', 'Items added during foreach loop should not show up in the same loop' );
		}
	}

	public function testICanCallDefinedArrayFunctionAsMethod()
	{
		$testArray = new Collection( ['a' => 'b', 'c' => ['d' => 'nested'], 0 => 'e'] );

		$this->assertEquals( $testArray, $this->collection->unique( SORT_REGULAR ) );
		$this->assertEquals( $testArray->to_a(), $this->collection->to_a(), 'The original object must not be modified by previous assertion' );
	}

	public function testMappedFunctionCallsYieldInstancesOfCollection()
	{
		$this->assertTrue($this->collection->unique( SORT_REGULAR ) instanceof Collection, 'Mapped function calls yield instances of Collection');
	}

	public function testCloningCreatesDeepCopy()
	{
		$clone = clone $this->collection;

		$this->assertFalse( $clone['c'] === $this->collection['c'], 'Nested objects should be of different instance' );
	}

	public function testSerialisingAndUnserialisingCollectionDoesNotCorruptData()
	{
		$ser = serialize( $this->collection );

		$this->assertEquals( $this->collection, unserialize( $ser ) );
	}
}
