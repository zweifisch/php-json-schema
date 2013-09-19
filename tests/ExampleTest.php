<?php

use \jsonschema\Validator;

class ExampleTest extends PHPUnit_Framework_TestCase
{

	public function testProduct()
	{
		$schema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'product.json'));
		$validator = new Validator;

		$input = '
		{
			 "id": 1,
			 "name": "A green door",
			 "price": 12.50,
			 "tags": ["home", "green"]
		}';
		$result = $validator->validate(json_decode($input), $schema, true);
		$this->assertSame([], $result);

		$input = '
		{
			 "id": 1,
			 "name": "A green door",
			 "price": 12.50,
			 "tags": ["home", "green"]
		}';
		$result = $validator->validate(json_decode($input), $schema);
		$this->assertSame([], $result);

		$input = '
		{
			 "id": 1,
			 "name": "A green door",
			 "price": 12.50,
			 "tags": ["home", 0]
		}';
		$result = $validator->validate(json_decode($input), $schema);
		$this->assertSame([['type', 'string', 'tags.1']], $result);

		$input = '
		{
			 "id": 1,
			 "name": "A green door",
			 "tags": ["home", "green"]
		}';
		$result = $validator->validate(json_decode($input), $schema);
		$this->assertSame([['required', ['id','name','price'], null]], $result);

	}

	public function testProducts()
	{
		$validator = new Validator;
		$schema = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'products.json'));
		$input = '
		[
			 {
				  "id": 2,
				  "name": "An ice sculpture",
				  "price": 12.50,
				  "tags": ["cold", "ice"],
				  "dimensions": {
						"length": 7.0,
						"width": 12.0,
						"height": 9.5
				  },
				  "warehouseLocation": {
						"latitude": -78.75,
						"longitude": 20.4
				  }
			 },
			 {
				  "id": 3,
				  "name": "A blue mouse",
				  "price": 25.50,
						"dimensions": {
						"length": 3.1,
						"width": 1.0,
						"height": 1.0
				  },
				  "warehouseLocation": {
						"latitude": 54.4,
						"longitude": -32.7
				  }
			 }
		]
		';
		$result = $validator->validate(json_decode($input), $schema, true);
		$this->assertSame([], $result);
	}
}
