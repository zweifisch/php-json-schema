<?php

use \jsonschema\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function testSchema()
	{
		$schema = (object)[
			'maxLength' => null,
		];
		$errors = $this->validator->validate($schema, $this->schema);
		$this->assertSame([['type','integer','maxLength']], $errors);

		$schema = (object)[
			'type' => null,
		];
		$errors = $this->validator->validate($schema, $this->schema);
		$this->assertSame([['anyOf', $this->schema->properties->type->anyOf,'type']], $errors);

		$schema = (object)[
			'enum' => null,
		];
		$errors = $this->validator->validate($schema, $this->schema);
		$this->assertSame([['type','array','enum']], $errors);

		$schema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'products.json'));
		$errors = $this->validator->validate($schema, $this->schema);
		$this->assertSame([], $errors);
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
			$this->schema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'schema.json'));
		}
	}

}
