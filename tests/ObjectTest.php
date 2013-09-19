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
		$schema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'post.json'));

		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
		], $schema, true);
		$this->assertSame([], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
		], $schema);
		$this->assertSame([['required', ['title', 'content'], null]], $result);
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

