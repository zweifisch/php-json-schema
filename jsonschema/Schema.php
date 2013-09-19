<?php

namespace jsonschema;

class Schema
{
	public $schema;
	private $definitions;

	public function __construct($schema)
	{
		$this->schema = is_string($schema) ? json_decode($schema) : $schema;
		$this->definitions = [];
	}

	public function dereference()
	{
		return $this->dereferenceNode($this->schema, isset($this->schema->id) ? $this->schema->id : '#');
	}

	public function getSubschema($path)
	{
		if('#' == $path)
		{
			return $this->schema;
		}
		$pathes = explode('/', substr($path, 2));
		$ret = $this->schema;
		foreach($pathes as $p)
		{
			$ret = $ret->$p;
		}
		return $ret;
	}

	private function dereferenceNode($schema, $scope)
	{
		if(isset($schema->definitions))
		{
			foreach($schema->definitions as $name=>$subschema)
			{
				$this->subschemas['#/definitions/'.$name] = $subschema;
				if(isset($subschema->id))
				{
					$scope = $this->join($scope, $subschema->id);
					$this->definitions[$scope] = $subschema;
				}
			}

			foreach($schema->definitions as $name=>$subschema)
			{
				if(isset($subschema->{'$ref'}))
				{
					$ref = $subschema->{'$ref'};
					if(!strncmp('#/', $ref, 2) || '#' == $ref)
					{
						$schema->definitions->$name = $this->getSubschema($ref);
					}
					elseif(strcmp('#', $ref, 1))
					{
						// TODO
					}
					else
					{
						// TODO
					}
				}
			}
		}

		if(empty($schema->type)) return;

		if('object' == $schema->type)
		{
			if(isset($schema->properties))
			{
				foreach($schema->properties as $name=>$subschema)
				{
					$this->dereferenceNode($subschema, $scope);
				}
			}
			if(isset($schema->additionalProperties))
			{
				if(is_object($schema->additionalProperties))
				$this->dereferenceNode($schema->additionalProperties, $scope);
			}
		}

		if('array' == $schema->type && !empty($schema->items))
		{
			if(is_array($schema->items))
			{
				foreach($schema->items as $subschema)
				{
					if(is_object($subschema))
					{
						$this->dereferenceNode($subschema, $scope);
					}
				}
			}
			elseif(is_object($schema->items))
			{
				$this->dereferenceNode($schema->items, $scope);
			}
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
