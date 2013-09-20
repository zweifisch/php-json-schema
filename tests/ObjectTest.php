<?php

use \jsonschema\Validator;

class ObjectTest extends PHPUnit_Framework_TestCase
{

	public function testType()
	{
		$schema = (object)[
			'type'=> 'object',
		];

		$input = null;
		$this->assertSame([['type', 'object', null]], $this->validator->validate($input, $schema));

		$input = false;
		$this->assertSame([['type', 'object', null]], $this->validator->validate($input, $schema));

		$input = '0';
		$this->assertSame([['type', 'object', null]], $this->validator->validate($input, $schema));

		$input = -1;
		$this->assertSame([['type', 'object', null]], $this->validator->validate($input, $schema));

		$input = 1.1;
		$this->assertSame([['type', 'object', null]], $this->validator->validate($input, $schema));
	}

	public function testRequired()
	{
		$schema = (object)[
			'type'=> 'object',
			'properties'=> (object)[
				'title'=> (object)[
					'type'=> 'string',
				],
				'content'=> (object)[
					'type'=> 'string',
				],
				'tags' => (object)[
					'type'=> 'array',
				],
			],
			'required'=> ['title', 'content'],
		];

		$input= (object)[
			'title'=>'foo',
			'content'=>'text',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'title'=>'foo',
		];
		$this->assertSame([['required', ['title', 'content'], null]], $this->validator->validate($input, $schema));
	}

	public function testMaxProperties()
	{
		$schema = (object)[
			'type'=> 'object',
			'maxProperties'=> 2,
		];

		$input= (object)[
			'title'=> 'foo',
			'content'=> '',
			'tags'=> [],
		];
		$this->assertSame([['maxProperties', 2, null]], $this->validator->validate($input, $schema));

		$input= (object)[
			'title'=> 'foo',
			'content'=> '',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testMinProperties()
	{
		$schema = (object)[
			'type'=> 'object',
			'minProperties'=> 2,
		];

		$input= (object)[
			'title'=> 'foo',
			'content'=> '',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'title'=> 'foo',
		];
		$this->assertSame([['minProperties', 2, null]], $this->validator->validate($input, $schema));
	}

	public function testProperties()
	{
		$schema = (object)[
			'type'=> 'object',
			'properties'=> (object)[
				'p1'=> (object)['type'=> 'string'],
			],
			'additionalProperties'=> false,
			'patternProperties'=> [
				'p'=> (object)['type'=> 'string'],
				'[0-3]'=> (object)['type'=> 'string'],
			],
		];

		$input= (object)[
			'p1'=> 'foo',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'p1'=> 'foo',
			'p2'=> '',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'pp'=> 'foo',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'a'=> 'foo',
		];
		$this->assertSame([['additionalProperties', false, null]], $this->validator->validate($input, $schema));

		$input= (object)[
			'a1'=> 'foo',
		];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input= (object)[
			'a1'=> null,
		];
		$this->assertSame([['type','string','a1']], $this->validator->validate($input, $schema));
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}
