<?php

namespace jsonschema;

use Exception;

class Validator
{
	private $validators;
	private $commonValidators;

	public function __construct()
	{
		$this->validators = [
			'string'=> [
				'type' => function($input){ return is_string($input); },
				'maxLength' => function($input, $max){ return strlen($input) <= $max; },
				'minLength' => function($input, $min){ return strlen($input) >= $min; },
				'pattern' => function($input, $pattern){ return prep_match($pattern, $input); },
			],
			'number' => [
				'type' => function($input){ return is_int($input) || is_float($input); },
				'multipleOf' => function($input, $num){ return ctype_digit((string)($input / $num)); },
				'maximum' => function($input, $max){ return $input <= $max; },
				'minimum' => function($input, $min){ return $input >= $min; },
				'exclusiveMaximum' => function($input, $max){ return $input < $max; },
				'exclusiveMinimum' => function($input, $min){ return $input > $min; },
			],
			'integer' => [
				'type' => function($input){ return is_int($input); },
				'multipleOf' => function($input, $num){ return ctype_digit((string)($input / $num)); },
				'maximum' => function($input, $max){ return $input <= $max; },
				'minimum' => function($input, $min){ return $input >= $min; },
				'exclusiveMaximum' => function($input, $max){ return $input < $max; },
				'exclusiveMinimum' => function($input, $min){ return $input > $min; },
			],
			'array' => [
				'type' => function($input){ return is_array($input); },
				'maxItems' => function($input, $max){ return count($input) <= $max; },
				'minItems' => function($input, $min){ return count($input) >= $min; },
				'uniqueItems' => function($input, $unique){
					return !$unique || count(array_unique($input)) == count($input);
				},
				'additionalItems' => function($input, $additionalItems, $schema) {
					return !$additionalItems || count($input) <= count($schema->items);
				},
			],
			'object' => [
				'type' => function($input){ return is_object($input); },
				'maxProperties' => function($input, $max){ return count($input) <= $max; },
				'minProperties' => function($input, $min){ return count($input) >= $min; },
				'required' => function($input, $required){
					return !array_diff($required, array_keys((array)($input)));
				},
			],
			'null' => [
				'type' => function($input){ return is_null($input); },
			],
			'boolean' => [
				'type' => function($input){ return is_bool($input); },
			],
		];

		$this->commonValidators = [
			'enum' => function($input, $values){ return in_array($input, $values, true); },
			// 'allOf' => function($input){},
			// 'anyOf' => function($input){},
			// 'oneOf' => function($input){},
			// 'not' => function($input){},
			// 'definations' => function($input){},
		];
	}

	private function _validate($type, $schema, $input)
	{
		foreach($schema as $constrain => $value)
		{
			$validator = isset($this->validators[$type][$constrain])
				? $this->validators[$type][$constrain]
				: (isset($this->commonValidators[$constrain]) ? $this->commonValidators[$constrain] : null);
			if($validator)
			{
				if(!$validator($input, $value, $schema))
				{
					return [$constrain, $value];
				}
			}
		}
	}

	public function validate($input, $schema)
	{
		$errors = [];
		if(is_string($schema)) $schema = json_decode($schema);

		if($error = $this->_validate($schema->type, $schema, $input))
		{
			list($constrain, $value) = $error;
			$errors[] = [$constrain, $value, null];
		}
		if ($errors) return $errors;

		if('object' === $schema->type)
		{
			foreach($schema->properties as $key=>$subSchema)
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

		if('array' === $schema->type)
		{
			if(isset($schema->items) && is_object($schema->items))
			{
				foreach($input as $idx => $element)
				{
					if($subErrors = $this->validate($element, $schema->items))
					{
						foreach($subErrors as $error)
						{
							list($constrain, $value, $k) = $error;
							$errors[] = [$constrain, $value, $k ? "$idx.$k" : $idx];
						}
					}
				}
			}
		}

		return $errors;
	}
}
