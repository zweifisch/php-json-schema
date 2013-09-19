<?php

use \jsonschema\Validator;

class StringTest extends PHPUnit_Framework_TestCase
{

	public function testType()
	{
		$schema = (object)[
			'type'=> 'string',
		];

		$input = null;
		$this->assertSame([['type', 'string', null]], $this->validator->validate($input, $schema));

		$input = 0;
		$this->assertSame([['type', 'string', null]], $this->validator->validate($input, $schema));

		$input = [];
		$this->assertSame([['type', 'string', null]], $this->validator->validate($input, $schema));

		$input = false;
		$this->assertSame([['type', 'string', null]], $this->validator->validate($input, $schema));

		$input = '';
		$this->assertSame([], $this->validator->validate($input, $schema));
	}

	public function testLength()
	{
		$schema = (object)[
			'type'=> 'string',
			'minLength'=> 2,
			'maxLength'=> 5,
		];

		$input = '';
		$this->assertSame([['minLength', 2, null]], $this->validator->validate($input, $schema));

		$input = '1';
		$this->assertSame([['minLength', 2, null]], $this->validator->validate($input, $schema));

		$input = '12';
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = '12345';
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = '123456';
		$this->assertSame([['maxLength', 5, null]], $this->validator->validate($input, $schema));
	}

	public function testPattern()
	{
		$schema = (object)[
			'type'=> 'string',
			'pattern'=> '/^\d+$/',
		];

		$input = '1324';
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = '13-24';
		$this->assertSame([['pattern', '/^\d+$/', null]], $this->validator->validate($input, $schema));
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

