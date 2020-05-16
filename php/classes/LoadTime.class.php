<?php

class LoadTime
{
    public $start = 0;
    public $stamps = [];
    public $stampsCount = [];
    public $stop = 0;

    public function __construct()
    {
        $this->setStart();
    }

    private function setStart()
    {
        $this->start = get_microtime();
    }

    public function setStamp($name)
    {
        if ( isset($this->stamps[$name]) ) {
            $this->stampsCount[$name] = $this->stampsCount[$name] + 1;
            $name = $name . '-' . $this->stampsCount[$name];

        }
        $this->stampsCount[$name] = 1;
        $this->stamps[$name] = round(get_microtime() - $this->start, 4);
    }

    public function setStop()
    {
        $this->stop = round(get_microtime() - $this->start, 4);
    }
}
