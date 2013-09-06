<?php

use \jsonschema\Validator;

class ArrayTest extends PHPUnit_Framework_TestCase
{

	public function testMin()
	{
		$schema = (object)[
			'type'=> 'array',
			'minItems'=> 2,
		];
		$input = [1];

		$this->assertSame([['minItems', 2, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'array',
			'minItems'=> 2,
		];
		$input = [1,2];

		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testMax()
	{
		$schema = (object)[
			'type'=> 'array',
			'maxItems'=> 2,
		];
		$input = [1,2,3];

		$this->assertSame([['maxItems', 2, null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'array',
			'maxItems'=> 3,
		];
		$input = [1,2,3];

		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testUnique()
	{
		$schema = (object)[
			'type'=> 'array',
			'uniqueItems' => true,
		];
		$input = [1,2,2];

		$this->assertSame([['uniqueItems',true,null]], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'array',
			'uniqueItems' => false,
		];
		$input = [1,2,2];

		$this->assertSame([], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'array',
			'uniqueItems' => true,
			'minItems' => 2,
			'maxItems' => 3,
		];
		$input = [1,2,3];

		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testEnum()
	{
		$schema = (object)[
			'type' => 'array',
			'enum' => [[1, 2], [2, 3]],
		];
		$input = [1,3];
		$this->assertSame([['enum', [[1,2], [2,3]], null]], $this->validator->validate($input, $schema));

		$input = [2,3];
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function setup(){
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

