<?php

namespace Datatype;


use Datatype\Traits\PropertyMapperTrait;


/**
 * Object datatype
 */
class Object
{
	use PropertyMapperTrait;


	private static $property_map = [
		'parent'		=> '___parent',
	];


	protected function ___parent()
	{
		$parents = class_parents( $this );

		return current( $parents ) ?: null;
	}
}
