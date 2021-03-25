<?php

namespace Test\Vcs;

class ClassC
{
    public function testFunction(): string
    {
        return 'ClassC';
    }

    protected function testFunctionProtected(): string
    {
        return 'ClassC';
    }
    
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function __call(string $name, array $arguments)
    {

    }

    public static function __callStatic(string $name, array $arguments)
    {

    }

    public function __get(string $name)
    {
        return null;
    }

    public function __set(string $name,  $value) : void
    {

    }

    public function __isset(string $name ) : bool
    {
        return false;
    }

    public function __unset(string $name ) : void
    {

    }

    public function __sleep() : array
    {
        return [];
    }

    public function __wakeup() : void
    {

    }

    public function __serialize() : array
    {
        return [];
    }

    public function __unserialize(array $data ) : void
    {

    }

    public function __toString() : string
    {
        return '';
    }

    public function __invoke( ...$values)
    {

    }

    public function __set_state(array $properties ) : object
    {
        return new \stdClass();
    }

    public function __clone()
    {
        return;
    }

    public function __debugInfo() : array
    {
        return [];
    }
}
