<?php

class MockClass {
    public $testParam;
    
    public function __construct($testParam = 0) 
    {
        $this->testParam = $testParam;
    }
}