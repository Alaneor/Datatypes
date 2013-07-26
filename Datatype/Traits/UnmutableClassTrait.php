<?php

namespace Datatype\Traits;

/**
 * Use this trait to indicate that your class is unmutable ( cannot be modified after instantiation )
 *
 * Remember that you still have to override your setters that perform modifications of your
 * class to actually prohibit the operation. Using this trait will only cause the ->is_mutable
 * to indicate false and will provide you with the ___raise_error method that you can call
 * to trigger standard E_USER_ERROR with proper error message.
 */
trait UnmutableClassTrait
{

	/**
	 * Use this method to trigger standard Unmutable class error while overriding your setters
	 *
	 * @return		void
	 */
	protected function ___raise_error()
	{
		$trace = debug_backtrace();
		trigger_error(
			'Cannot modify instances of an unmutable class ' . $this->instance_of .
			' in ' . $trace[2]['file'] .
			' on line ' . $trace[2]['line'],
			E_USER_ERROR );
	}
}
