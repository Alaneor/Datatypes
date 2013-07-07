<?php

namespace Datatype\Traits;

trait MethodMapperTrait
{
	abstract protected static function _function_prefix();

	abstract protected function &_get_object_data();


	public function __call( $method, $args )
	{
		$prefix = static::_function_prefix();
		$function = $prefix . $method;

		if ( function_exists( $function ) )
		{
			$data = $this->_get_object_data();
			$args = array_merge( [&$data], $args );

			return call_user_func_array( $function, $args );
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
				'Call to undefined function ' . $function .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR );

			return null;
		}
	}
}
