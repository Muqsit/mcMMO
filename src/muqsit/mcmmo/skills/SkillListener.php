<?php
namespace muqsit\mcmmo\skills;

use muqsit\mcmmo\Loader;

use pocketmine\event\Listener;

abstract class SkillListener implements Listener, SkillIds{

	/** @var Loader */
	protected $plugin;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
		$this->init();
	}

	protected function init() : void{
	}
}