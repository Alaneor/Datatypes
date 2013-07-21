<?php

namespace Datatype\Traits;

/**
 * Map a magic property to a method call
 */
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
		$parent = current( class_parents( get_class() ) );

		if (
			isset( self::$property_map ) &&
			isset( self::$property_map[$property] ) &&
			method_exists( $this, self::$property_map[$property] )
			)
		{
			$method = self::$property_map[$property];
		}
		elseif ( in_array( 'Datatype\Traits\PropertyMapperTrait', class_uses( $parent ) ) )
		{
			return parent::__get( $property );
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
				'Undefined property: ' . $property .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE );

			return null;
		}

		return $this->$method();
	}
}
