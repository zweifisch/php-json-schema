<?php

use \jsonschema\Schema;

class SchemaTest extends PHPUnit_Framework_TestCase
{

	public function testJoinPath()
	{
		$schema = new Schema('{}');
		$this->assertEquals('http://foo.com#bar', $schema->joinpath('http://foo.com#', '#bar'));

		$this->assertEquals('http://foo.com/zoo#', $schema->joinpath('http://foo.com/#', 'zoo'));
	}
}
