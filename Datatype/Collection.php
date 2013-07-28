<?php

namespace Datatype;

use Datatype\Traits\PropertyMapperTrait;
use Datatype\Traits\FunctionMapperTrait;


/**
 * Collection datatype
 *
 * @method		self		unique( int $sort_flags = SORT_STRING )	Get all unique elements of the Collection<br>{@link http://www.php.net/manual/en/function.array-unique.php PHP - array_unique}
 * @method		self		keys()									Get all keys in the Collection<br>{@link http://www.php.net/manual/en/function.array-keys.php PHP - array_keys}
 * @method		self		values()								Get all values in the Collection<br>{@link http://www.php.net/manual/en/function.array-values.php PHP - array_values}
 * @method		self		map( callable $callback = "" )			Apply the callback to all elements in the Collection and return the result as new Collection<br>{@link http://www.php.net/manual/en/function.array-map.php PHP - array_map}
 * @method		bool		has( mixed $key = null )				Check whether the given key is set in the Collection<br>{@link http://www.php.net/manual/en/function.array-key-exists.php PHP - array_key_exists}
 * @method		bool		exists( mixed $key = null )				Alias for the 'has' method
 * @method		bool		contains( mixed $value = null )			Check whether the given value exists in the Collection<br>{@link http://www.php.net/manual/en/function.in-array.php PHP - in_array}
 * @method		self		filter( callable $callback = "" )		{@link http://www.php.net/manual/en/function.array-filter.php PHP - array_filter}
 * @method		self		flip()									Get a new Collection where keys in current Collection are values and values are keys<br>{@link http://www.php.net/manual/en/function.array-flip.php PHP - array_flip}
 * @method		self		replace( array $array1, ... )			{@link http://www.php.net/manual/en/function.array-replace.php PHP - array_replace}
 * @method		self		reverse( bool $preserve_keys = false )	Get a mew Collection with elements in reverse order<br>{@link http://www.php.net/manual/en/function.array-reverse.php PHP - array_reverse}
 * @method		self		search( mixed $needle, bool $strict = false )	Search the Collection for a given value and return the corresponding key if successful<br>{@link http://www.php.net/manual/en/function.array-search.php PHP - array_search}
 * @method		self		slice( int $offset, int $length = null, bool $preserve_keys = false )	{@link http://www.php.net/manual/en/function.array-slice.php PHP - array_slice}
 * @method		int			sum()									Calculate the sum of values in the Collection<br>{@link http://www.php.net/manual/en/function.array-sum.php PHP - array_sum}
 *
 * @property-read		integer		count		Number of elements in Collection
 * @property-read		integer		length		Alias for count
 * @property-read		integer		size		Alias for count
 * @property-read		self		keys		All keys in the current Collection
 * @property-read		self		values		All values in the current Collection
 * @property-read		mixed		first		The first item in the Collection
 * @property-read		mixed		last		The last item in the Collection
 */
class Collection extends Object implements
	\ArrayAccess,
	\SeekableIterator,
	\Countable,
	\Serializable
{
	use	PropertyMapperTrait,
		FunctionMapperTrait
		{
			FunctionMapperTrait::___process_return_value as ___t_process_return_value;
		}


	/**
	 * The collection's data is stored here
	 *
	 * @var		array
	 */
	protected $data = [];

	/**
	 * Snapshot of all the keys in the Collection when the loop started
	 *
	 * @var		array
	 */
	private $iterator_keys = [];

	/**
	 * The current key the iterator is on
	 *
	 * @var		integer|string
	 */
	private $iterator_position = 0;

	/**
	 * PropertyMapper mappings
	 *
	 * @var		array
	 */
	static private $property_map = [
		'length'		=> 'count',
		'size'			=> 'count',
		'count'			=> 'count',

		'keys'			=> 'keys',
		'values'		=> 'values',

		'first'			=> 'first',
		'last'			=> 'last',
	];

	/**
	 * FunctionMapper mappings
	 *
	 * @var		array
	 */
	static private $function_map = [
	//	Called method:		Maps to:				$data position:			Args default values:
		'unique'	=>		[ 'array_unique' ],
		'keys'		=>		[ 'array_keys' ],
		'values'	=>		[ 'array_values' ],
		'map'		=>		[ 'array_map',			2 ],
		'has'		=>		[ 'array_key_exists',	2 ],
		'exists'	=>		[ 'array_key_exists',	2 ],
		'contains'	=>		[ 'in_array',			2 ],
		'filter'	=>		[ 'array_filter' ],
		'flip'		=>		[ 'array_flip' ],
		'replace'	=>		[ 'array_replace' ],
		'reverse'	=>		[ 'array_reverse' ],
		'search'	=>		[ 'array_search',		2 ],
		'slice'		=>		[ 'array_slice' ],
		'sum'		=>		[ 'array_sum' ],
	];


	/**
	 * Convert non-array elements to arrays and arrays to Collections
	 *
	 * @param		mixed		Any input value
	 *
	 * @return		array		An array. If this array originally contained nested arrays, they become instances of Collection
	 */
	private function to_c( $items )
	{
		$data = [];

		// Ensure we are dealing with arrays all the time
		$items = (array)$items;

		foreach ( $items as $key => &$value )
		{
			if ( is_array( $value ) ) $items[$key] = new Collection( $value );
		}

		return $items;
	}

	/**
	 * Get the Collection's keys
	 *
	 * @return		self		A Collection containing all the keys
	 */
	protected function keys()
	{
		return $this->___call_function_from_map( 'keys', $this->data );
	}

	/**
	 * Get the Collection's values
	 *
	 * @return		self		A Collection containing all the values
	 */
	protected function values()
	{
		return $this->___call_function_from_map( 'values', $this->data );
	}

	/**
	 * Get the first item in the Collection
	 *
	 * @return		mixed		The first item in the Collection
	 */
	protected function first()
	{
		return reset( $this->data );
	}

	/**
	 * Get the last item in the Collection
	 *
	 * @return		mixed		The last item in the Collection
	 */
	protected function last()
	{
		return end( $this->data );
	}


	/**
	 * Create a new instance of the class
	 *
	 * @param		array		The data this instance should hold
	 */
	public function __construct( $array = [] )
	{
		parent::__construct();

		// If the provided array contains nested arrays, they should become Collections, too
		$this->data = $this->to_c( $array );
	}

	/**
	 * Return an array representation of the Collection instance
	 *
	 * @return		array		The array representation of the Collection
	 */
	public function to_a()
	{
		$data = [];

		foreach ( $this->data as $key => &$value )
		{
			$data[$key] = $value instanceof static ? $value->to_a() : $value;
		}

		return $data;
	}


	/**
	 * Returns the data at a given key
	 *
	 * @param		mixed		The key to for which data should be returned
	 *
	 * @return		mixed		The data, if it exists, or null otherwise
	 */
	public function get( $key )
	{
		return isset( $this->data[$key] ) ? $this->data[$key] : null;
	}

	/**
	 * Sets the value for a given key
	 *
	 * @param		string|int		The key to hold the data
	 * @param		mixed			The data to be held by the key
	 *
	 * @return		self
	 */
	public function set( $key, $value )
	{
		if ( is_array( $value ) ) $value = new Collection( $value );

		if ( is_null( $key ) )
		{
			$this->data[] = $value;
		}
		else $this->data[$key] = $value;

		return $this;
	}

	/**
	 * Add one element to the end of the Collection
	 *
	 * @param		mixed		The data to be added to the Collection
	 */
	public function add( $value )
	{
		return $this->set( null, $value );
	}

	/**
	 * Removes a given key from the Collection
	 *
	 * @param		string|int		The key to be removed from the Collection
	 *
	 * @return		self
	 */
	public function remove( $key )
	{
		unset( $this->data[$key] );

		return $this;
	}

	/**
	 * Pop one element off the end of Collection and return it
	 *
	 * @see			<a href="http://www.php.net/manual/en/function.array-pop.php">PHP - array_pop()</a>
	 *
	 * @return		mixed		The last value of Collection. If Collection is empty NULL will be returned
	 */
	public function pop()
	{
		$value = $this->last();

		end( $this->data );						// Move array pointer to last item
		$this->remove( key( $this->data ) );	// Remove the item by the key at the end of $data

		return $value;
	}

	/**
	 * Push one or more elements onto the end of Collection
	 *
	 * This function behaves identically to PHP's array_push() except that
	 * it returns self instead of the new number of elements in the Collection.
	 *
	 * @see			<a href="http://www.php.net/manual/en/function.array-push.php">PHP - array_push()</a>
	 *
	 * @return		self
	 */
	public function push()
	{
		$args = func_get_args();

		foreach( $args as $arg ) $this->add( $arg );

		return $this;
	}

	/**
	 * Apply a callback to each element in the Collection
	 *
	 * The callback will receive two parameters: The current key and a reference to
	 * the current value in the Collection.
	 *
	 * <p class='alert alert-danger'>You cannot modify $key - it is provided to you in the callback
	 * only for your reference should you need it.<br>
	 * *( You CAN change the variable, but this will not be
	 * reflected in the Collection )*</p>
	 * <br>
	 * <p class='alert alert-danger'>The $value is passed to your callback as a reference to the actual value
	 * in the Collection - in order to actually modify the value, you must declare your callback's $value
	 * parameter to accept references - you do this by prefixing the $value with '&' ( ampersand ). See
	 * example code below.</p>
	 *
	 * <h3>Example:</h3>
	 * <code>
	 * $col = new Datatype\Collection( [1,2,3] );
	 * $col->each( function( $key, &$value )	// Notice the ampersand before $value
	 * {
	 * 	$value++;
	 * });
	 * print_r( $col->to_a() );		// [2,3,4]
	 * </code>
	 *
	 * @param		callable		The callback to be applied to each element in the Collection
	 * @param		\Object			An optional instance of an object that should be available via $this inside the callback
	 *
	 * @return		self
	 */
	public function each( $callback, $newthis = null )
	{
		if ( ! is_callable( $callback ) )
		{
			$trace = debug_backtrace();
			trigger_error(
				'Invalid callback provided' .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR );
		}

		$newthis && $callback instanceof \Closure && $callback->bindTo( $newthis );

		foreach ( $this->data as $key => &$value ) $callback( $key, $value );

		return $this;
	}


	protected function ___process_return_value( $function, $value )
	{
		is_array( $value ) && $value = new self( $value );

		return $this->___t_process_return_value( $function, $value );
	}

	public function __call( $function, $args )
	{
		// Use the FunctionMapperTrait to attempt to resolve
		// the method call into a function call
		try
		{
			$result = $this->___call_function_from_map( $function, $this->data, $args );
		}
		catch ( \Exception $e )
		{
			// This means that the trait was unable to resolve the method call into a mapped function
			if ( $e->getCode() === 1 )
			{
				$trace = debug_backtrace();
				trigger_error(
					'Call to undefined function ' . $function .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_ERROR );
			}
		}

		return $result;
	}

	/**
	 * Implement deep copying of the Collection
	 *
	 * @return		void
	 */
	public function __clone()
	{
		$this->__construct( $this->to_a() );
	}


	// Serializable interface implementation

	/**
	 * Serialises the data the Collection holds
	 *
	 * This will only serialise the data the instance holds;
	 * you will not get an instance of Collection after unserialising
	 * data you obtained by this method. To achieve that, you should use
	 * the standard `serialize( $object )` function.
	 *
	 * @return		string		The serialised representation of the data
	 */
	public function serialize()
	{
		return serialize( $this->to_a() );
	}

	/**
	 * Unserialises a serialised string and stores it in the current instance
	 *
	 * You may use this method on an existing instance to restore already serialised data.
	 * Any data previously stored in the instance will be replaced by the unserialised data.
	 *
	 * @param		string		The serialised data
	 *
	 * @return		void
	 */
	public function unserialize( $data )
	{
		$this->__construct( unserialize( $data ) );
	}


	// Countable interface implementation

	/**
	 * Returns the number of items in the Collection
	 *
	 * @internal
	 *
	 * @return		int		The number of items in the Collection
	 */
	public final function count()
	{
		return count( $this->data );
	}


	// ArrayAccess interface implementation

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function offsetSet( $offset, $value )
	{
		return $this->set( $offset, $value );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function offsetExists( $key )
	{
		return $this->exists( $key );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function offsetUnset( $offset )
	{
		return $this->remove( $offset );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function offsetGet( $offset )
	{
		return $this->get( $offset );
	}


	// SeekableIterator implementation

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function seek( $position )
	{
		if ( ! isset( $this->iterator_keys[$position] ) ) throw new Exception( "Invalid seek position: $position" );

		$this->iterator_position = $position;
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function rewind()
	{
		$this->iterator_position = 0;
		$this->iterator_keys = array_keys( $this->data );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function current()
	{
		$key = $this->iterator_keys[$this->iterator_position];
		return $this->data[$key];
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function key()
	{
		return $this->iterator_keys[$this->iterator_position];
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function next()
	{
		$this->iterator_position++;
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public final function valid()
	{
		return isset( $this->iterator_keys[$this->iterator_position] );
	}
}
