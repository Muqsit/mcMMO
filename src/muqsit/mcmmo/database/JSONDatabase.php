<?php
namespace muqsit\mcmmo\database;

class JSONDatabase extends FlatFileDatabase{

	const TYPE = FlatFileDatabase::TYPE_JSON;

	protected function serialize(array $loaded) : string{
		return json_encode($loaded);
	}
}