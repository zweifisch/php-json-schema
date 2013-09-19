<?php

use \jsonschema\Validator;

class IntegerTest extends PHPUnit_Framework_TestCase
{

	public function testType()
	{
		$schema = (object)[
			'type'=> 'integer',
		];

		$input = null;
		$this->assertSame([['type', 'integer', null]], $this->validator->validate($input, $schema));

		$input = false;
		$this->assertSame([['type', 'integer', null]], $this->validator->validate($input, $schema));

		$input = '0';
		$this->assertSame([['type', 'integer', null]], $this->validator->validate($input, $schema));

		$input = -1;
		$this->assertSame([], $this->validator->validate($input, $schema));

		$input = 1.1;
		$this->assertSame([['type', 'integer', null]], $this->validator->validate($input, $schema));
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}
}

