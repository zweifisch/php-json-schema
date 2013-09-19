<?php

use \jsonschema\Validator;

class CommenTest extends PHPUnit_Framework_TestCase
{
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

		$schema = (object)[
			'type' => 'string',
			'enum' => ['97','#ac'],
		];

		$input = 'foo';
		$this->assertSame([['enum', $schema->enum, null]], $this->validator->validate($input, $schema));

		$input = '97';
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testType()
	{
		$schema = (object)[
			'type' => 'null',
		];

		$input = false;
		$this->assertSame([['type', 'null', null]], $this->validator->validate($input, $schema));
	}

	public function testAllOf()
	{
		$schema = (object)[
			'type' => 'string',
			'allOf' => [
				(object)[
					'type'=> 'string',
					'minLength'=> 3,
				],
				(object)[
					'type'=> 'string',
					'maxLength'=> 3,
				],
			],
		];

		$input = '?';
		$this->assertSame([['allOf', $schema->allOf, null]], $this->validator->validate($input, $schema));

		$input = '????';
		$this->assertSame([['allOf', $schema->allOf, null]], $this->validator->validate($input, $schema));

		$input = '???';
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testAnyOf()
	{
		$schema = (object)[
			'type' => 'string',
			'anyOf' => [
				(object)[
					'type'=> 'string',
					'minLength'=> 3,
				],
				(object)[
					'type'=> 'string',
					'pattern'=> '/^\d+$/',
				],
			],
		];

		$input = '1';
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = '?';
		$this->assertSame([['anyOf', $schema->anyOf, null]], $this->validator->validate($input, $schema));

		$input = '???';
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testNo()
	{
		$schema = (object)[
			'type' => 'string',
			'not' => (object)[
				'type'=> 'string',
				'minLength'=> 3,
			],
		];
		$input = '???';
		$this->assertSame([['not', $schema->not, null]], $this->validator->validate($input, $schema));

		$input = '??';
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}
