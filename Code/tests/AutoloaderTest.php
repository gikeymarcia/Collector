<?php

namespace Collector;

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;

    protected function setUp()
    {
        $this->obj = new Autoloader;
    }

    protected function getProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @covers Collector\Autoloader::register
     */
    public function testRegister()
    {
        $this->obj->register();
        $actual = spl_autoload_functions()[0][0];
        $this->assertInstanceOf('Collector\Autoloader', $actual);
    }

    /**
     * @covers Collector\Autoloader::add
     */
    public function testAdd()
    {
        $this->obj->add('Foo\Bar', './ns/FooBar');
        $actual = $this->getProperty($this->obj, 'namespaces');
        $this->assertEquals('./ns/FooBar/', $actual['Foo\Bar\\']);
    }

    /**
     * @depends testAdd
     * @covers Collector\Autoloader::load
     * @covers Collector\Autoloader::requireFile
     */
    public function testLoad()
    {
        $this->obj->add('Foo\Bar', __DIR__.'/ns/FooBar');
        $this->assertTrue($this->obj->load('Foo\Bar\Baz'));
        $this->assertFalse($this->obj->load('Foo\Bar\Zaz'));
        $this->assertFalse($this->obj->load('Foo\Baz\Zaz'));
    }
}
