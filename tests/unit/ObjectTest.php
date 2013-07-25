<?php

use Codeception\Util\Stub;
use Datatype\Collection;


class ObjectTest extends \Codeception\TestCase\Test
{
	/**
	 * @var \CodeGuy
	 */
	protected $codeGuy;

	// Cannot test directly with Object as it is an abstract class, so let's use Collection
	protected function _before()
	{
		$this->obj = new Collection;
		$this->col = new Collection( ['a', 'b', 'c', 2] );
	}

	protected function _after()
	{
	}

	public function testParentPropertyHoldsNameOfParentClass()
	{
		$this->assertEquals( 'Datatype\Object', $this->col->parent );
	}

	public function testInstance_ofPropertyHoldsClassName()
	{
		$this->assertEquals( 'Datatype\Collection', $this->col->instance_of );
	}

	public function testStaticMethodClass_implementsBehaviour()
	{
		$this->assertContains( 'ArrayAccess', Collection::class_implements(), 'class_implements should return a Collection with all implemented interfaces' );
		$this->assertEquals( true, Collection::class_implements( 'SeekableIterator' ) );
		$this->assertEquals( false, Collection::class_implements( 'NonExistentInterface' ) );
	}

	public function testStaticMethodClass_usesBehaviour()
	{
		$this->assertContains( 'Datatype\Traits\PropertyMapperTrait', Collection::class_uses(), 'class_uses should return a Collection with all used traits in given class' );
		$this->assertEquals( true, Collection::class_uses( 'Datatype\Traits\PropertyMapperTrait' ) );
		$this->assertEquals( false, Collection::class_uses( 'NonExistentTrait' ) );
	}

	public function testInstance_ofMethodChecksOnlyCurrentClass()
	{

		$this->assertTrue( $this->obj->instance_of( 'Datatype\Collection' ) );
		$this->assertFalse( $this->col->instance_of( 'Datatype\Object' ) );
	}

	public function testInstance_ofAllowsAnInstaneAsInput()
	{
		$this->assertTrue( $this->col->instance_of( new Collection ), 'instance_of method must allow an object instance as input' );
	}

	public function testIsaMethodIncludesParentsInClassChecking()
	{
		$this->assertTrue( $this->col->is_a( 'Datatype\Collection' ) );
		$this->assertTrue( $this->col->is_a( 'Datatype\Object' ) );
	}

	public function testSame_asMethodShouldReturnTrueOnlyForReferencesToIdenticalObject()
	{
		$ref = $this->obj;
		$new = new Collection;
		$dup = clone $this->obj;

		$this->assertTrue( $this->obj->same_as( $ref ) );
		$this->assertFalse( $this->obj->same_as( $new ) );
		$this->assertFalse( $this->obj->same_as( $dup ) );
	}

	public function testEqualsMethodShouldNotBeSoStrict()
	{
		$ref = $this->obj;
		$new = new Collection;
		$dup = clone $this->obj;

		$this->assertTrue( $this->obj->equals( $ref ) );
		$this->assertTrue( $this->obj->equals( $new ) );
		$this->assertTrue( $this->obj->equals( $dup ) );
	}

	public function testResponds_toOnlyRespondsToPublicMethods()
	{
		$this->assertFalse( $this->obj->responds_to( '___parent' ) );
		$this->assertTrue( $this->obj->responds_to( 'responds_to' ) );
	}

}
