<?php

namespace Datatype;

use Datatype\Traits\PropertyMapper;

/**
 * Collection datatype
 */
class Collection extends Object implements \ArrayAccess, \SeekableIterator, \Countable
{
	use PropertyMapper;

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
	private $property_map = [
		'length'		=> 'count',
		'size'			=> 'count',
		'count'			=> 'count',
	];


	public function __construct( $array = [] )
	{
		// Ensure we are dealing with arrays all the time
		if ( ! is_array( $array ) ) $array = [$array];

		// If the provided array contains nested arrays, they should become Collections, too
		foreach ( $array as $key => $value )
		{
			$this->data[$key] = is_array( $value ) ? new Collection( $value ) : $value;
		}
	}

	/**
	 * Return an array representation of the Collection object
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

	public function exists( $key )
	{
		return isset( $this->data[$key] );
	}

	public function keys()
	{
		return new Collection( array_keys( $this->data ) );
	}

	public function get( $key )
	{
		return isset( $this->data[$key] ) ? $this->data[$key] : null;
	}

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

	public function add( $value )
	{

		return $this->set( null, $value );
	}

	public function remove( $index )
	{
		unset( $this->data[$index] );

		return $this;
	}

	public function first()
	{
		return reset( $this->data );
	}

	public function last()
	{
		return end( $this->data );
	}


	// Countable interface implementation

	public function count()
	{
		return count( $this->data );
	}


	// ArrayAccess interface implementation

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function offsetSet( $offset, $value )
	{
		return $this->set( $offset, $value );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function offsetExists( $key )
	{
		return $this->exists( $key );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function offsetUnset( $offset )
	{
		return $this->remove( $offset );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function offsetGet( $offset )
	{
		return $this->get( $offset );
	}


	// SeekableIterator implementation

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function seek( $position )
	{
		if ( ! isset( $this->iterator_keys[$position] ) ) throw new Exception( "Invalid seek position: $position" );

		$this->iterator_position = $position;
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function rewind()
	{
		$this->iterator_position = 0;
		$this->iterator_keys = array_keys( $this->data );
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function current()
	{
		$key = $this->iterator_keys[$this->iterator_position];
		return $this->data[$key];
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function key()
	{
		return $this->iterator_keys[$this->iterator_position];
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function next()
	{
		$this->iterator_position++;
	}

	/**
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function valid()
	{
		return isset( $this->iterator_keys[$this->iterator_position] );
	}
}
