<?php

namespace jsonschema;

use Exception;

class Validator
{
	private $validators;
	private $commonValidators;
	private $schema;
	private static $validator;

	public function __construct($schema=null)
	{
		if($schema)
		{
			$this->schema = $schema;
		}

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
				'maxProperties' => function($input, $max){ return count(get_object_vars($input)) <= $max; },
				'minProperties' => function($input, $min){ return count(get_object_vars($input)) >= $min; },
				'required' => function($input, $required){
					return !array_diff($required, array_keys(get_object_vars($input)));
				},
				'additionalProperties' => function($input, $additionalProperties, $schema){
					if($additionalProperties) return true;
					$properties = array_keys(get_object_vars($input));
					if(isset($schema->properties)){
						$properties = array_diff($properties, array_keys(get_object_vars($schema->properties)));
					}
					if(isset($schema->patternProperties)){
						$properties = array_filter($properties, function($property) use ($schema){
							foreach($schema->patternProperties as $pattern=> $_){
								if(preg_match("/$pattern/", $property)) return false;
							}
							return true;
						});
					}
					return 0 == count($properties);
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
				foreach($schemas as $schema) if($this->_validate($input, $schema)) return false;
				return true;
			},
			'anyOf' => function($input, $schemas){
				foreach($schemas as $schema) if(!$this->_validate($input, $schema)) return true;
			},
			'oneOf' => function($input, $schemas){
				return 1 == count(array_filter(array_map(function($schema) use ($input){
					return $this->_validate($input, $schema);
				}, $schemas), function($errors){
					return 0 == count($errors);
				}));
			},
			'not' => function($input, $schema){
				return count($this->_validate($input, $schema)) > 0;
			},
		];
	}

	public function validate($input, $schema=null, $validateSchema=false)
	{
		if($schema)
		{
			$this->schema = new Schema($schema);
		}
		$schema = $this->schema->schema;
		if($validateSchema)
		{
			if(!isset(self::$validator))
			{
				self::$validator = new Validator(new Schema(json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'schema.json'))));
			}
			if($errors = self::$validator->validate($schema))
			{
				throw new Exception('schema invalide');
			}
		}
		return $this->_validate($input, $schema);
	}

	private function _validate($input, $schema)
	{
		if(isset($schema->{'$ref'})) $schema = $this->schema->dereference($schema->{'$ref'});
		$errors = [];

		if(is_null($schema)) var_dump($input);

		$type = isset($schema->type) ? $schema->type : null;

		if($error = $this->validateNode($type, $schema, $input))
		{
			list($constrain, $value) = $error;
			$errors[] = [$constrain, $value, null];
		}

		if($errors) return $errors;

		if('object' === $type)
		{
			$errors += $this->validateObject($input, $schema);
		}
		elseif('array' === $type)
		{
			$errors += $this->validateArray($input, $schema);
		}

		return $errors;
	}

	private function validateObject($input, $schema)
	{
		$errors = [];
		foreach($input as $key => $value)
		{
			$subErrors = [];
			if(isset($schema->properties->$key))
			{
				$subErrors = $this->_validate($value, $schema->properties->$key);
			}
			elseif(isset($schema->patternProperties))
			{
				foreach($schema->patternProperties as $pattern => $subSchema)
				{
					if($subErrors = $this->_validate($value, $subSchema)) break;
				}
			}
			foreach($subErrors as $error)
			{
				list($constrain, $value, $k) = $error;
				$errors[] = [$constrain, $value, $k ? "$key.$k" : $key];
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
			elseif(is_array($schema->items) && isset($schema->additionalItems) && false === $schema->additionalItems)
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
