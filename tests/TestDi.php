<?php

require_once('bootstrap.php');

class TestDi extends PHPUnit_Framework_TestCase
{
    /** 
    * @dataProvider providerInvalidSettings
    */
    public function testSetInvalidSettings($settings, $errorMsg)
    {
        $alias = 'test';
        
        $errorMsg = strtr($errorMsg, array(
            '{%ALIAS%}' => $alias,
            '{%PATH%}' => $settings['path'] . '.php',
            '{%CLASS_NAME%}' => $settings['className']
        ));
        
        $this->setExpectedException('DIException', $errorMsg);
        
        DI::set('test', $settings);
        DI::get('test');
    }

    public function providerInvalidSettings()
    {
        $pathRequired = "path is required (Alias: '{%ALIAS%}')";
        $classNameRequired = "className is required (Alias: '{%ALIAS%}')";
        $fileNotFound = "File '{%PATH%}' not found. (Alias: '{%ALIAS%}')";
        $classNotFound = "Class '{%CLASS_NAME%}' not found. (Alias: '{%ALIAS%}', path: '{%PATH%}')";
        
        return array(
            array( array(), $classNameRequired ),
            array( array(
                    'className'
                ), $classNameRequired 
            ),
            array( array(
                    'path' => BASE_PATH . '/tests/MockClass',
                ), $classNameRequired,
            ),
            array( array(
                    'className' => 'MockClass',
                ), $pathRequired,
            ),
            array( array(
                    'className' => 'MockClass',
                    'path'
                ), $pathRequired,
            ),
            array( array(
                    'path' => BASE_PATH . '/tests/MockClass.', 
                    'className' => 'MockClass'
                ), $fileNotFound
            ),
            array( array(
                    'path' => BASE_PATH . '/tests/MockClass', 
                    'className' => 'MockClass.'
                ), $classNotFound
            )
        );
    }
    
    /**
     * @dataProvider providerValidSettings
     */
    public function testSet($settings, $alias)
    {
        $this->assertTrue(DI::set($alias, $settings));
        
        $protectedSettings = getPrivate('DI', 'settings');
        
        $this->assertarrayhasKey($alias, $protectedSettings->getValue());
    }

    /**
     * @depends testSet
     * @dataProvider providerValidSettings
     */
    public function testGet($settings, $alias)
    {
        $instance = DI::get($alias);
        
        $this->assertInstanceOf($settings['className'], $instance);
    }
    
    /**
     * @depends testSet
     */
    public function testWithArgumentsn()
    {
        $instance = DI::get('withArgs');
        $this->assertTrue($instance->testParam === 'sucess');
        
        $instance = DI::get('normal', array(
            'alsoSuccess'
        ));
        $this->assertTrue($instance->testParam === 'alsoSuccess');
    }
    
    /**
     * @depends testSet
     */
    public function testSingleton() 
    {
        $instance1 = DI::get('singletone');
        $instance2 = DI::get('singletone');
        $notSingletone = DI::get('normal');
        
        $this->assertSame($instance1, $instance2);
        $this->assertNotSame($instance1, $notSingletone);
        $this->assertNotSame($instance2, $notSingletone);
    }
    
    /**
     * @dataProvider providerValidSettings
     */
    public function testProcessSettings($settings, $alias)
    {
        $property = getPrivate('DI', 'processSettings');
        
        $processed = $property->invokeArgs(null, array($settings, $alias));
        
        $this->assertArrayHasKey('singletone', $processed);
        $this->assertArrayHasKey('args', $processed);
        $this->assertTrue(is_array($processed['args']));
        $this->assertStringEndsWith('.php', $processed['path']);
    }
    
    public function providerValidSettings()
    {
        return array(
            array( array(
                'className' => 'MockClass',
                'path' => BASE_PATH . '/tests/MockClass'
            ), 'normal'),
            array( array(
                'className' => 'MockClass',
                'path' => BASE_PATH . '/tests/MockClass.php'
            ), 'withExt'),
            array( array(
                'className' => 'MockClass',
                'path' => BASE_PATH . '/tests/MockClass',
                'singletone' => true
            ), 'singletone'),
            array( array(
                'className' => 'MockClass',
                'path' => BASE_PATH . '/tests/MockClass',
                'args' => array(
                    'sucess'
                )
            ), 'withArgs')
        );
    }
}