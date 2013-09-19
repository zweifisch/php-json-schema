<?php

namespace jsonschema;

use Exception;

class Validator
{
	private $validators;
	private $commonValidators;
	private $schemaOfSchema;

	private $definations;

	public function __construct()
	{
		$this->schemaOfSchema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'schema.json'));

		$this->validators = [
			'string'=> [
				'type' => function($input){ return is_string($input); },
				'maxLength' => function($input, $max){ return strlen($input) <= $max; },
				'minLength' => function($input, $min){ return strlen($input) >= $min; },
				'pattern' => function($input, $pattern){ return preg_match($pattern, $input); },
			],
			'number' => [
				'type' => function($input){ return is_int($input) || is_float($input); },
				'multipleOf' => function($input, $num){ return ctype_digit((string)abs($input / $num)); },
				'maximum' => function($input, $max, $schema){ return empty($schema->exclusiveMaximum) ? $input <= $max : $input < $max; },
				'minimum' => function($input, $min, $schema){ return empty($schema->exclusiveMinimum) ? $input >= $min : $input > $min; },
			],
			'integer' => [
				'type' => function($input){ return is_int($input); },
				'multipleOf' => function($input, $num){ return ctype_digit((string)abs($input / $num)); },
				'maximum' => function($input, $max, $schema){ return empty($schema->exclusiveMaximum) ? $input <= $max : $input < $max; },
				'minimum' => function($input, $min, $schema){ return empty($schema->exclusiveMinimum) ? $input >= $min : $input > $min; },
			],
			'array' => [
				'type' => function($input){ return is_array($input); },
				'maxItems' => function($input, $max){ return count($input) <= $max; },
				'minItems' => function($input, $min){ return count($input) >= $min; },
				'uniqueItems' => function($input, $unique){
					return !$unique || count(array_unique($input)) == count($input);
				},
				'items' => function($input, $items, $schema){
					return (isset($schema->additionalItems) && false === $schema->additionalItems)
						? count($input) <= count($items)
						: true;
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
			'allOf' => function($input, $schemas){
				foreach($schemas as $schema) if($this->validate($input, $schema)) return false;
				return true;
			},
			'anyOf' => function($input, $schemas){
				foreach($schemas as $schema) if(!$this->validate($input, $schema)) return true;
			},
			'oneOf' => function($input, $schemas){
				return 1 == count(array_filter(array_map(function($schema) use ($input){
					return $this->validate($input, $schema);
				}, $schemas), function($errors){
					return empty($error);
				}));
			},
			'not' => function($input, $schema){
				return count($this->validate($input, $schema)) > 0;
			},
		];
	}

	public function validate($input, $schema, $validateSchema=false)
	{
		if(is_string($schema)) $schema = json_decode($schema);
		$schema = new Schema($schema);
		$schema->dereference();
		$schema = $schema->schema;
		if($validateSchema)
		{
			if($errors = $this->validate($schema, $this->schemaOfSchema))
			{
				throw new Exception('schema invalide');
			}
		}
		return $this->_validate($input, $schema);
	}

	private function _validate($input, $schema)
	{
		$errors = [];

		if(empty($schema->type)) return [];

		if($error = $this->validateNode($schema->type, $schema, $input))
		{
			list($constrain, $value) = $error;
			$errors[] = [$constrain, $value, null];
		}
		if ($errors) return $errors;

		if('object' === $schema->type)
		{
			$errors += $this->validateObject($input, $schema);
		}
		elseif('array' === $schema->type)
		{
			$errors += $this->validateArray($input, $schema);
		}

		return $errors;
	}

	private function validateObject($input, $schema)
	{
		$errors = [];
		if(isset($schema->properties))
		{
			foreach($schema->properties as $key=>$subSchema)
			{
				if(isset($input->$key) && $subErrors = $this->_validate($input->$key, $subSchema))
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

	private function validateArray($input, $schema)
	{
		$errors = [];
		if(isset($schema->items))
		{
			if(is_object($schema->items))
			{
				foreach($input as $idx => $element)
				{
					if($subErrors = $this->_validate($element, $schema->items))
					{
						foreach($subErrors as $error)
						{
							list($constrain, $value, $k) = $error;
							$errors[] = [$constrain, $value, $k ? "$idx.$k" : $idx];
						}
					}
				}
			}
			elseif(is_array($schema->items))
			{
				if(isset($schema->additionalItems))
				{
					if(false === $schema->additionalItems)
					{
						foreach($input as $idx => $element)
						{
							if($subErrors = $this->_validate($element, $schema->items[$idx]))
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
			}
		}
		return $errors;
	}

	private function validateNode($type, $schema, $input)
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

}
