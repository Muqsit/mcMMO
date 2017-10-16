<?php
namespace muqsit\mcMMO\handlers;

class HandlerManager{

    /** @var ItemHandler */
    private $itemHandler;

    public function __construct(){
        $this->itemHandler = new ItemHandler();
    }

    public function getItemHandler() : ItemHandler{
        return $this->itemHandler;
    }
}