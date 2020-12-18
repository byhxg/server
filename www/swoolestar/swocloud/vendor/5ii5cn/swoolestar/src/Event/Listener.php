<?php
namespace SwooleStar\Event;

use SwooleStar\Foundation\Application;

abstract class Listener
{
    protected $name = 'listener';
    protected $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public abstract function handler();

    public function getName()
    {
        return $this->name;
    }
}
