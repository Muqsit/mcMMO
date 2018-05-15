<?php
namespace muqsit\mcmmo\database;

class YAMLDatabase extends FlatFileDatabase{

	const TYPE = FlatFileDatabase::TYPE_YAML;

	protected function serialize(array $loaded) : string{
		return yaml_emit($loaded);
	}
}