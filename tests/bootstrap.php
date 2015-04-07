<?php

require_once('../DI.php');

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

function getPrivate( $className, $propertyName ) {
    $reflector = new ReflectionClass( $className );
    
    try {
        $property = $reflector->getProperty( $propertyName );
    } catch(ReflectionException $ex) {
        $property = $reflector->getMethod( $propertyName );
    }
    
    $property->setAccessible( true );

    return $property;
}