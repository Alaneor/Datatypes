<?php

namespace Datatype;


use Datatype\Traits\PropertyMapperTrait;


/**
 * Object datatype
 *
 * @property-read		bool			is_mutable		Is this class mutable / can the class be modified after instantiation?
 * @property-read		string			parent			The full namespaced name of the parent class or null if the class does not have a parent
 * @property-read		string			instance_of		The full namespaced name of the current class
 * @property-read		Collection		implements		List of all interfaces this object or it's parents implement
 * @property-read		Collection		uses			List of all traits this object uses ( does not include traits used by parent classes )
 */
abstract class Object
{
	use PropertyMapperTrait;


	private static $property_map = [
		'is_mutable'	=> '___is_mutable',
		'parent'		=> '___parent',
		'instance_of'	=> '___instance_of',
		'implements'	=> '___implements',
		'uses'			=> '___uses',
	];

	// Cached values for dynamic class properties
	static protected $___is_mutable		= [];
	static protected $___implements		= [];
	static protected $___uses			= [];


	static public function class_implements( $interface = null )
	{
		$interface	= (string)$interface;
		$class		= get_called_class();

		! isset( static::$___implements[$class] ) && static::$___implements[$class] = new UnmutableCollection( class_implements( $class ) );
		$implements	= static::$___implements[$class];

		return $interface ? $implements->contains( $interface ) : $implements;
	}

	static public function class_uses( $trait = null )
	{
		$trait	= (string)$trait;
		$class	= get_called_class();

		! isset( static::$___uses[$class] ) && static::$___uses[$class] = new UnmutableCollection( class_uses( $class ) );
		$uses	= static::$___uses[$class];

		return $trait ? $uses->contains( $trait ) : $uses;
	}


	protected function ___is_mutable()
	{
		// A class is considered to be mutable when it does not use UnmutableClassTrait
		return ! $this->class_uses( 'Datatype\Traits\UnmutableClassTrait' );
	}

	protected function ___parent()
	{
		$parents = class_parents( $this );

		return current( $parents ) ?: null;
	}

	protected function ___instance_of()
	{
		return get_class( $this );
	}

	protected function ___implements()
	{
		return static::class_implements();
	}

	protected function ___uses()
	{
		return static::class_uses();
	}


	/**
	 * Standard constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Make a shallow copy of the object
	 *
	 * @return		mixed		A new instance of the copied object
	 */
	public function copy()
	{
		return clone $this;
	}

	/**
	 * Does the current instance equal to a given value?
	 *
	 * @param		mixed		The value or object to compare against
	 *
	 * @return		bool		A boolean result of equality ( '==' ) comparison
	 */
	public function equals( $to )
	{
		return $this == $to;
	}

	/**
	 * Check if the object is of this class or has this class as one of its parents
	 *
	 * @param		string		The class name to compare against
	 *
	 * @return		bool		Returns true if the object is of this class or has this class as one of its parents, false otherwise
	 */
	public function is_a( $class )
	{
		return is_a( $this, $class );
	}

	/**
	 * Check if the object is instance of the given class
	 *
	 * This method is different from {@link self::is_a()} method that
	 * it does not take parents into consideration.
	 *
	 * @param		string|Object	A class name or instance of any object to compare against
	 *
	 * @return		bool			Returns true if this object is an instance of the given class
	 */
	public function instance_of( $class )
	{
		// Make sure we are dealing with a string during comparison
		is_object( $class ) && $class = get_class( $class );
		if ( ! is_string( $class ) )
		{
			$trace = debug_backtrace();
			trigger_error(
				'Trying to compare class name against non-string value' .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_WARNING );
		}

		return get_class( $this ) == $class;
	}

	/**
	 * Check if the given method can be called on the current object
	 *
	 * This method only lists / checks against public methods.
	 *
	 * @todo		Consider also magic public methods defined via FunctionMapperTrait
	 *
	 * @param		string				The method that should be checked
	 *
	 * @return		Collection|bool		Either a {@link Collection} of all public methods or a boolean value
	 */
	public function responds_to( $method = null )
	{
		$method			= (string)$method;

		$refl			= new \ReflectionClass( $this );
		$methods		= $refl->getMethods( \ReflectionMethod::IS_PUBLIC );

		foreach ( $methods as $key => &$value ) $methods[$key] = $value->name;

		return $method ? in_array( $method, $methods ) : new Collection( $methods );
	}

	/**
	 * Is the current instance identical to a given value?
	 *
	 * This method performs strict ( '===' ) comparison against the given
	 * value, in contrary to {@link self::equals()}.
	 *
	 * @param		mixed		The value or object to compare against
	 *
	 * @return		bool		A boolean result of identicality ( '===' ) comparison
	 */
	public function same_as( $value )
	{
		return $this === $value;
	}
}
