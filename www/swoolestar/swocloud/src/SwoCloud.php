<?php
namespace SwoCloud;

class SwoCloud
{
    public function run()
    {
        (new \SwoCloud\Server\Route)->start();
    }
}
