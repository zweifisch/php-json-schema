<?php

use \jsonschema\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{

	public function testObject()
	{
		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
		], $this->post);
		$this->assertSame([], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
		], $this->post);
		$this->assertSame([['required', ['title', 'content'], null]], $result);

	}

	public function testArray()
	{
		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
			'tags' => []
		], $this->post);
		$this->assertSame([], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
			'tags' => 1,
		], $this->post);
		$this->assertSame([['type', 'array', 'tags']], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
			'tags' => ['nil','nil'],
		], $this->post);
		$this->assertSame([['uniqueItems', true, 'tags']], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
			'tags' => [null],
		], $this->post);
		$this->assertSame([['type', 'string', 'tags']], $result);

		$result = $this->validator->validate((object)[
			'title'=>'foo',
			'content'=>'text',
			'tags' => ['foo'],
		], $this->post);
		$this->assertSame([], $result);

	}

	public function testArrayWithSchema(){
		$post = json_decode(json_encode([
			'title' => 'foo',
			'content' => 'text',
			'comments' => [
				['title'=>'untitled'],
			],
		]));
		$result = $this->validator->validate($post, $this->post);
		$this->assertSame([['required', ['title', 'content'], 'comments']], $result);

		$post = json_decode(json_encode([
			'title' => 'foo',
			'content' => 'text',
			'comments' => [
				['title'=>'title', 'content'=>'content']
			],
		]));
		$result = $this->validator->validate($post, $this->post);
		$this->assertSame([], $result);

		$post = json_decode(json_encode([
			'title' => 'foo',
			'content' => 'text',
			'comments' => [
				['title'=>'title', 'content'=>null]
			],
		]));
		$result = $this->validator->validate($post, $this->post);
		$this->assertSame([], $result);
	}

	public function setup(){
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
			$this->post = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'post.json'));
		}
	}

}
