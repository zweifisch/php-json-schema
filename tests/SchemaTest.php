<?php

use \jsonschema\Schema;

class SchemaTest extends PHPUnit_Framework_TestCase
{

	public function testObject()
	{
		$schema = new Schema(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'post.json'));

		$result = $schema->validate((object)[
			'title'=>'foo',
			'content'=>'text',
		]);
		$this->assertSame([], $result);

		$result = $schema->validate([
			'title'=>'foo',
			'content'=>'text',
		]);
		$this->assertSame([], $result);

		$result = $schema->validate((object)[
			'title'=>'foo',
		]);
		$this->assertSame([['required', ['title', 'content'], null]], $result);

		$result = $schema->validate([
			'title'=>'foo',
		]);
		$this->assertSame([['required', ['title', 'content'], null]], $result);

	}

	public function testArray(){
		$schema = new Schema(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'post.json'));

		$result = $schema->validate([
			'title'=>'foo',
			'content'=>'text',
			'tags' => []
		]);
		$this->assertSame([], $result);

		$result = $schema->validate([
			'title'=>'foo',
			'content'=>'text',
			'tags' => [1=>null]
		]);
		$this->assertSame([], $result);


	}

}
