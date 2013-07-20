<?php

namespace Datatype;

use Datatype\Traits\PropertyMapperTrait;
use Datatype\Traits\MethodMapperTrait;


/**
 * Collection datatype
 */
class Collection extends Object implements
	\ArrayAccess,
	\SeekableIterator,
	\Countable,
	\Serializable
{
	use PropertyMapperTrait,
	MethodMapperTrait
	{
		MethodMapperTrait::__call as ___call;
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
	private static $property_map = [
		'length'		=> 'count',
		'size'			=> 'count',
		'count'			=> 'count',
	];


	/**
	 * Required by MethodMapperTrait
	 *
	 * @return		string		The function prefix to be appended to function call
	 * @internal
	 */
	private static function _function_prefix()
	{
		return "array_";
	}


	/**
	 * Convert non-array elements to arrays and arrays to Collections
	 *
	 * @param		mixed		Any input value
	 * @return		array		An array. If this array originally contained nested arrays, they become instances of Collection
	 */
	private function to_c( $items )
	{
		$data = [];

		// Ensure we are dealing with arrays all the time
		$items = (array)$items;

		foreach ( $items as $key => $value )
		{
			$data[$key] = is_array( $value ) ? new Collection( $value ) : $value;
		}

		return $data;
	}


	/**
	 * Create a new instance of the class
	 *
	 * @param		array		The data this instance should hold
	 */
	public function __construct( $array = [] )
	{
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

		foreach ( $this->data as $key => $value )
		{
			$data[$key] = $value instanceof static ? $value->to_a() : $value;
		}

		return $data;
	}

	/**
	 * Does a specific key exist in the Collection?
	 *
	 * @param		mixed		The key to be searched for
	 *
	 * @return		bool		True of a key exists in the Collection, false otherwise
	 */
	public function exists( $key )
	{
		return isset( $this->data[$key] );
	}

	/**
	 * Returns all keys in the Collection
	 *
	 * @return		Collection		A Collection holding all the keys
	 */
	public function keys()
	{
		return new Collection( array_keys( $this->data ) );
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
	 * You should use the standard array notation '[]' to add
	 * items to the Collection for better performace.
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
	 * Get the first item in the Collection
	 *
	 * @return		mixed		The first item in the Collection
	 */
	public function first()
	{
		return reset( $this->data );
	}

	/**
	 * Get the last item in the Collection
	 *
	 * @return		mixed		The last item in the Collection
	 */
	public function last()
	{
		return end( $this->data );
	}

	/**
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
	 * Intercept the MethodMapperTrait behaviour and convert
	 * its return value to class-specific, if possible
	 *
	 * If the function call returned null, $this will be returned
	 * If the function call returned array, {@link Collection} will be returned
	 * For any other return value, the value will be returned untouched
	 *
	 * @param		string		The method that was called
	 * @param		array		The arguments passed to the method call
	 *
	 * @return		self|mixed	If applicable, convert the result to instance of Collection; otherwise pass the result unmodified
	 */
	public function __call( $method, $args )
	{
		$result = $this->___call( $method, $args );

		switch ( $result )
		{
			case is_null( $result ):

				return $this;

			case is_array( $result ):

				return new static( $result );

			default:

				return $result;
		}
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
	public final function serialize()
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
	public final function unserialize( $data )
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

	/**
	 * Method required by the MethodMapperTrait
	 *
	 * @return		array		An array representation of the Collection
	 */
	protected function _get_object_data()
	{
		return $this->to_a();
	}
}
