<?php

namespace jsonschema;

class Schema
{
	public $schema;
	public $dereferenced;

	public function __construct($schema)
	{
		$this->schema = is_string($schema) ? json_decode($schema) : $schema;
		$this->dereferenced = [];
	}

	public function dereference($ref)
	{
		if('#' == $ref) return $this->schema;

		if(isset($this->dereferenced[$ref])) return $this->dereferenced[$ref];

		if(!strncmp('#/', $ref, 2))
		{
			$pathes = explode('/', substr($ref, 2));
			$ret = $this->schema;
			foreach($pathes as $p)
			{
				$ret = $ret->$p;
			}
			return $tihs->dereferenced[$ref] = $ret;
		}
	}

	public function joinpath($scope, $uri)
	{
		if(!strncmp('#', $uri, 1))
		{
			return $scope . substr($uri, 1);
		}
		$tokens = explode('/', $scope);
		array_pop($tokens);
		return implode('/', $tokens) . '/' . $uri . '#';
	}
}
