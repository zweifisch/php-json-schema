
a partially implemented json schema validator

```php
$validator = new \jsonschema\Validator;

if($errors = $validator->validate($input, $schema))
{
	foreach($errors as $error)
	{
		list($constrain, $detail, $key) = $error;
	}
}
else
{
	// $input is valid
}
```

also validate schema

```php
try
{
	$errors = $validator->validate($input, $schema, true);
}
catch(Exception $e)
{
	// $schema is invalid
}
```

## TBD

* array as `type`
* type `any`
* `dependencies`
* canonical dereferencing(inline dereferencing is supported)
* `id`
* ...
