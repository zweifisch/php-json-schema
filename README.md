
```php
$validator = new \jsonschema\Validator;

if($errors = $validator->validate($input, $schema))
{
	foreach($errors as $error)
	{
		list($constrain, $value, $key) = $error;
	}
}
else
{
	$expectedInput = $validator->extract($input, $schema);
}
```
