<?php

use Codeception\Util\Stub;
use Datatype\UnmutableCollection;


class UnmutableCollectionTest extends \Codeception\TestCase\Test
{
   /**
	* @var \CodeGuy
	*/
	protected $codeGuy;

	protected function _before()
	{
		$this->col = new UnmutableCollection( ['a', 'b'] );
	}

	protected function _after()
	{
	}


	public function testModifyingContentsOfUnmutableCollectionShouldTriggerError()
	{
		$expected   = 4;
		$caught     = 0;

		try
		{
			$this->col->add( 'c' );
		}
		catch ( ErrorException $e )
		{
			$caught++;
		}

		try
		{
			$this->col[] = 'c';
		}
		catch ( ErrorException $e )
		{
			$caught++;
		}

		try
		{
			$this->col->set( 10, 'c' );
		}
		catch ( ErrorException $e )
		{
			$caught++;
		}

		try
		{
			$this->col->remove( 'b' );
		}
		catch ( ErrorException $e )
		{
			$caught++;
		}

		return $expected == $caught ? 0 : $this->fail( 'Not all setters triggered error' );
	}
}
