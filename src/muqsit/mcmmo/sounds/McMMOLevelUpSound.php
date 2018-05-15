<?php
namespace muqsit\mcmmo\sounds;

use pocketmine\level\sound\Sound;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class McMMOLevelUpSound extends Sound{

	public function encode() : PlaySoundPacket{
		$pk = new PlaySoundPacket();
		$pk->soundName = "random.levelup";
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->volume = 320;
		$pk->pitch = 0.5;
		return $pk;
	}
}