<?php

namespace Datatype\Traits;

/**
 * Map a method call to a function call
 */
trait FunctionMapperTrait
{
//	private static $function_map = [
//	//	Called method:		Maps to:				$data position:		Args default values:
//		'values' =>			[ 'array_values',		3,					[1 => null, 2 => -1] ],
//	];


	/**
	 * Process the return value of a mapped function call
	 *
	 * This allows the implementing method to intercept the return
	 * value from a mapped method call and process it in any way.
	 *
	 * Implementing classes should either override this method
	 * or alias it, implement their own version and then call the alias
	 * to let it pass through parents, if this functionality is required.
	 *
	 * @param		string		The name of the function that returned this value
	 * @param		mixed		The return value
	 *
	 * @return		mixed		The processed return value
	 */
	protected function ___process_return_value( $function, $value )
	{
		$parent = current( class_parents( get_class() ) );

		if ( in_array( 'Datatype\Traits\FunctionMapperTrait', class_uses( $parent ) ) )
		{
			return parent::___process_return_value( $function, $value );
		}
		else return $value;
	}

	/**
	 * Call a function defined in $function_map
	 *
	 * Use this function when you want to call a function defined in
	 * a function map. The most likely place you'd want to use this
	 * method is inside a __call() magic method.
	 *
	 * Call this method with the function name ( the function that the
	 * user called ), the data that the function should work **on** and
	 * the arguments the function was called with.
	 *
	 * @param		string		The function that was called
	 * @param		mixed		The data the function should work with
	 * @param		array|null	The arguments to be passed to the function being called
	 *
	 * @return		mixed		Whatever the called function returned
	 *
	 * @throws		\Exception	Thrown when the function could not be found in $function_map in current nor parent classes
	 */
	protected function ___call_function_from_map( $function, $data, $args = [] )
	{
		if ( ! isset( self::$function_map[$function] ) )
		{
			// This method is not present in $function_map of this class, delegate it to parent
			$parent = current( class_parents( get_class() ) );

			if ( in_array( 'Datatype\Traits\FunctionMapperTrait', class_uses( $parent ) ) )
			{
				return parent::___call_function_from_map( $function, $data, $args );
			}
			// Parent does not implement this trait - end of possible delegation scenarios
			else throw new \Exception( "Unable to dynamically call $function", 1 );
		}

		$args		= (array)$args;										// Make sure $args is array
		$definition	= self::$function_map[$function];					// Pull the function definition
		$function	= $definition[0];									// Get the actual function to be called
		$position	= isset( $definition[1] ) ? $definition[1] : 1;		// At which argument position should we put $data?
		$defaults	= isset( $definition[2] ) ? $definition[2] : null;	// Are any default values defined?

		// Do we have any overrides for default param values?
		if ( $defaults )
		{
			// Set default values for params that are not yet set
			// or that are set to null
			foreach ( $defaults as $key => $value )
			{
				$args[$key] = ! isset( $args[$key] ) ? $value : $args[$key];
			}
		}

		// Insert the $data to designated position in the $args array
		$reordered		= array_slice( $args, 0, $position - 1 );
		$reordered[]	= $data;
		$args			= array_merge( $reordered, array_slice( $args, $position - 1 ) );

		$result = call_user_func_array( $function, $args );				// Call the function

		return $this->___process_return_value( $function, $result );	// Allow the implementing class to process the return value
	}
}
