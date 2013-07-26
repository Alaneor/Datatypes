<?php

namespace Datatype;

use Datatype\Traits\UnmutableClassTrait;


/**
 * An unmutable ( unmodifiable ) version of the {@link Collection} class
 */
class UnmutableCollection extends Collection
{
	use UnmutableClassTrait;


	public function set( $key, $value )
	{
		$this->___raise_error();
	}

	public function remove( $key )
	{
		$this->___raise_error();
	}
}
