<?php

namespace jsonschema;

class Schema
{
	private $schema;
	private $types = ['array', 'boolean', 'integer', 'number', 'null', 'object', 'string'];
	private $validators;

	public function __construct($schema)
	{

		$this->validators = [
			'string'=> [
				'maxLength' => function($input, $max){ return strlen($input) <= $max; },
				'minLength' => function($input, $min){ return strlen($input) >= $min; },
				'pattern' => function($input, $pattern){ return prep_match($pattern, $input); },
			],
			'number' => [
			],
			'array' => [
				'maxItems' => function($input, $max){ return count($input) <= $max; },
				'minItems' => function($input, $min){ return count($input) <= $min; },
				'uniqueItems' => function($input, $uniq){
					return !$unique || count(array_unique($input)) == count($input);
				},
			],
			'object' => [
				'maxProperties' => function($input, $max){ return count($input) <= $max; },
				'minProperties' => function($input, $min){ return count($input) >= $min; },
				'required' => function($input, $required){ return !array_diff($required, array_keys((array)($input))); },
			],
			'null' => [],
			'integer' => [],
			'boolean' => [],
		];

		$this->typeValidators = [
			'string' => function($input){ return is_string($input); },
			'integer' => function($input){ return is_int($input); },
			'array' => function($input){ return is_array($input) && array_keys($input) === range(0, count($input) - 1); },
	
			'object' => function($input){ return is_object($input) || is_array($input) && (bool)count(array_filter(array_keys($input), 'is_string')); },
			'boolean' => function($input){ return is_bool($input); },
			'number' => function($input){ return is_int($input) || is_float($input); },
			'null' => function($input){ return is_null($input); },
		];

		$this->schema = is_string($schema) ? json_decode($schema, true) : $schema;
	}

	private function _validate($type, $schema, $input)
	{
		foreach($schema as $constrain => $value)
		{
			if(isset($this->validators[$type][$constrain]))
			{
				if(!$this->validators[$type][$constrain]($input, $value))
				{
					return [$constrain, $value];
				}
			}
		}
	}

	public function validate($input, $schema=null, $name=null)
	{
		$errors = [];
		$schema = is_null($schema) ? $this->schema : $schema;

		if(empty($schema['type']))
		{
			throw new \Exception('');
		}

		if(!$this->typeValidators[$schema['type']]($input)) return [['type', $schema['type'], $name]];

		if($error = $this->_validate($schema['type'], $schema, $input))
		{
			list($constrain, $value) = $error;
			$errors[] = [$constrain, $value, $name];
		}
		if ($errors) return $errors;

		if('object' === $schema['type'])
		{
			foreach($schema['properties'] as $key=>$subSchema)
			{
				if(isset($input->$key) && $subErrors = $this->validate($input->$key, $subSchema))
				{
					foreach($subErrors as $error)
					{
						list($constrain, $value, $k) = $error;
						$errors[] = [$constrain, $value, $k ? "$key.$k" : $key];
					}
				}
			}
		}

		return $errors;
	}
}
