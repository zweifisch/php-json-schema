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

	public function testDereference()
	{
		$schema = new Schema(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'schema.json'));
		$this->assertSame($schema->schema->definitions->schemaArray, $schema->dereference('#/definitions/schemaArray'));
	}
}
