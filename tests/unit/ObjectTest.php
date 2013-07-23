<?php

use Codeception\Util\Stub;
use Datatype\Object;
use Datatype\Collection;


class ObjectTest extends \Codeception\TestCase\Test
{
	/**
	 * @var \CodeGuy
	 */
	protected $codeGuy;


	protected function _before()
	{
		$this->obj = new Object;
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
		$this->assertEquals( 'Datatype\Object', $this->obj->instance_of );
	}

	public function testInstance_ofMethodChecksOnlyCurrentClass()
	{

		$this->assertTrue( $this->obj->instance_of( 'Datatype\Object' ) );
		$this->assertFalse( $this->col->instance_of( 'Datatype\Object' ) );
	}

	public function testIsaMethodIncludesParentsInClassChecking()
	{
		$this->assertTrue( $this->col->is_a( 'Datatype\Collection' ) );
		$this->assertTrue( $this->col->is_a( 'Datatype\Object' ) );
	}

	public function testSame_asMethodShouldReturnTrueOnlyForReferencesToIdenticalObject()
	{
		$ref = $this->obj;
		$new = new Object;
		$dup = clone $this->obj;

		$this->assertTrue( $this->obj->same_as( $ref ) );
		$this->assertFalse( $this->obj->same_as( $new ) );
		$this->assertFalse( $this->obj->same_as( $dup ) );
	}

	public function testEqualsMethodShouldNotBeSoStrict()
	{
		$ref = $this->obj;
		$new = new Object;
		$dup = clone $this->obj;

		$this->assertTrue( $this->obj->equals( $ref ) );
		$this->assertTrue( $this->obj->equals( $new ) );
		$this->assertTrue( $this->obj->equals( $dup ) );
	}

}