<?php
namespace muqsit\mcmmo\database;

class IgBinaryDatabase extends FlatFileDatabase{

	const TYPE = FlatFileDatabase::TYPE_IGBINARY;

	protected function serialize(array $loaded) : string{
		return igbinary_serialize($loaded);
	}
}