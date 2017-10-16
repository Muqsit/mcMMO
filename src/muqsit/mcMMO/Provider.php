<?php
namespace muqsit\mcMMO;

use muqsit\mcMMO\skills\SkillManager;

class Provider{

    /** @var string */
    private $filepath;

    /** @var array */
    private $userdata = [];

    public function __construct(string $filepath){
        $this->filepath = $filepath;
        $this->load();
    }

    private function load(){
        if(is_file($this->filepath)){
            $contents = file_get_contents($this->filepath);
            foreach(json_decode($contents, true) as $uuid => $data){//TODO: do NOT abuse alloc. memory.
                $this->userdata[$uuid] = new SkillManager($data);
            }
        }
    }

    public function getUserData(string $uuid, bool $create = false) : ?SkillManager{
        return $this->userdata[$uuid] ?? ($create ? $this->userdata[$uuid] = new SkillManager() : null);
    }

    public function save(){
        $data = [];
        foreach($this->userdata as $uuid => $manager){
            $data[$uuid] = $manager->getData();
        }
        file_put_contents($this->filepath, json_encode($data));
    }
}