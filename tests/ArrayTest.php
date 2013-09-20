<?php

use \jsonschema\Validator;

class ArrayTest extends PHPUnit_Framework_TestCase
{

	public function testType()
	{
		$schema = (object)[
			'type'=> 'array',
		];
			
		$this->assertSame([], $this->validator->validate([], $schema));
		$this->assertSame([['type','array',null]], $this->validator->validate(null, $schema));
		$this->assertSame([['type','array',null]], $this->validator->validate(new stdClass, $schema));
	}

	public function testMinItems()
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

	public function testMaxItems()
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

	public function testUniqueItems()
	{
		$schema = (object)[
			'type'=> 'array',
			'uniqueItems' => true,
		];

		$input = [1,2,2];
		$this->assertSame([['uniqueItems',true,null]], $this->validator->validate($input, $schema));

		$input = [1,2,3];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type'=> 'array',
			'uniqueItems' => false,
		];
		$input = [1,2,2];

		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testItems()
	{
		$schema = (object)[
			'type' => 'array',
			'items' => [new stdClass, new stdClass],
		];

		$input = [1, 3];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = [1, 3, 4];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$schema = (object)[
			'type' => 'array',
			'items' => [new stdClass, new stdClass],
			'additionalItems' => false,
		];

		$input = [1, 3];
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = [1, 3, 4];
		$this->assertEquals([['items', [new stdClass, new stdClass], null]], $this->validator->validate($input, $schema));
	}

	public function testSchema()
	{
		$schema = (object)[
			'type' => 'array',
			'items' => (object)[
				'type' => 'object',
				'properties' => (object)[
					'title' => (object)[
						'type' => 'string'
					],
					'content' => (object)[
						'type' => 'string'
					],
				],
				'required' => ['title', 'content'],
			],
		];

		$post = json_decode(json_encode([['title'=>'untitled']]));
		$result = $this->validator->validate($post, $schema, true);
		$this->assertSame([['required', ['title', 'content'], 0]], $result);

		$post = json_decode(json_encode([
			[
				'title'=>'title',
				'content'=>'content',
			]
		]));
		$result = $this->validator->validate($post, $schema);
		$this->assertSame([], $result);

		$post = json_decode(json_encode([
			[
				'title'=>'title',
				'content'=>'',
			],
			[
				'title'=>'title',
				'content'=>'',
			]
		]));
		$result = $this->validator->validate($post, $schema);
		$this->assertSame([], $result);
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

