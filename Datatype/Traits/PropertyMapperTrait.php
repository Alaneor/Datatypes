<?php

namespace Datatype\Traits;

trait PropertyMapperTrait
{
	/**
	 * Map a property accessor to a method call
	 *
	 * The benefit here is that I can have read-only properties
	 * that get calculated on-demand via method call -> no worries
	 * recalculating some internal counts whenever the count changes.
	 *
	 * @param		string		The property being accessed
	 * @return		mixed		Return whatever the called method ( if any ) returns
	 */
	public function __get( $property )
	{
		$method = '';

		if (
			isset( static::$property_map ) &&
			isset( static::$property_map[$property] ) &&
			is_callable( [$this, static::$property_map[$property]] )
			)
		{
			$method = static::$property_map[$property];
		}
		elseif ( is_callable( [$this, $property] ) )
		{
			$method = $property;
		}
		elseif ( is_callable( [$this, "___$property"] ) )
		{
			$method = "___$property";
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
				'Undefined read-only property: ' . $property .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE );

			return null;
		}

		return $this->$method();
	}
}
