<?php

use \jsonschema\Validator;

class NumberTest extends PHPUnit_Framework_TestCase
{

	public function testType()
	{
		$schema = (object)[
			'type'=> 'number',
		];

		$input = null;
		$this->assertSame([['type', 'number', null]], $this->validator->validate($input, $schema));

		$input = false;
		$this->assertSame([['type', 'number', null]], $this->validator->validate($input, $schema));

		$input = '0';
		$this->assertSame([['type', 'number', null]], $this->validator->validate($input, $schema));

		$input = -1;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 1.1;
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testMaximum()
	{
		$schema = (object)[
			'type'=> 'number',
			'maximum'=> 10,
		];

		$input = 10;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 11;
		$this->assertSame([['maximum', 10, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'number',
			'maximum'=> 10,
			'exclusiveMaximum'=> false,
		];

		$input = 10;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 11;
		$this->assertSame([['maximum', 10, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'number',
			'maximum'=> 10,
			'exclusiveMaximum'=> true,
		];

		$input = 10;
		$this->assertSame([['maximum', 10, null]], $this->validator->validate($input, $schema));

		$input = 11;
		$this->assertSame([['maximum', 10, null]], $this->validator->validate($input, $schema));

		$input = 9.9;
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testMinimum()
	{
		$schema = (object)[
			'type'=> 'number',
			'minimum'=> 10,
		];

		$input = 10;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 9;
		$this->assertSame([['minimum', 10, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'number',
			'minimum'=> 10,
			'exclusiveMaximum'=> false,
		];

		$input = 10;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 9;
		$this->assertSame([['minimum', 10, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'number',
			'minimum'=> 10,
			'exclusiveMinimum'=> true,
		];

		$input = 10;
		$this->assertSame([['minimum', 10, null]], $this->validator->validate($input, $schema));

		$input = 9;
		$this->assertSame([['minimum', 10, null]], $this->validator->validate($input, $schema));

		$input = 10.1;
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testMultipleOf()
	{
		$schema = (object)[
			'type'=> 'number',
			'multipleOf'=> 0.1,
		];

		$input = 10;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 0.1;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = -0.9;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 0.15;
		$this->assertSame([['multipleOf', 0.1, null]], $this->validator->validate($input, $schema));
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

