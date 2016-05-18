<?php

namespace Collector;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = new Response();
    }

    /**
     * @covers Collector\Response::__construct
     * @covers Collector\Response::export
     */
    public function testConstructor()
    {
        $this->assertEmpty($this->obj->export());
    }

    /**
     * @covers Collector\Response::isSealed
     */
    public function testIsSealed()
    {
        $this->assertFalse($this->obj->isSealed());
        $this->obj->seal();
        $this->assertTrue($this->obj->isSealed());
    }

    /**
     * @covers Collector\Response::add
     */
    public function testAdd()
    {
        $this->assertTrue($this->obj->add('A', 1));
        $this->assertEquals(1, $this->obj->get('a'));
        $this->assertFalse($this->obj->add('a', 2));
    }

    /**
     * @covers Collector\Response::update
     */
    public function testUpdate()
    {
        $this->assertFalse($this->obj->update('a', 1));
        $this->assertEquals(1, $this->obj->get('a'));
        $this->assertTrue($this->obj->update('a', 2));
        $this->assertEquals(2, $this->obj->get('a'));
    }

    /**
     * @covers Collector\Response::add
     * @covers Collector\Response::update
     * @covers Collector\Response::seal
     */
    public function testSeal()
    {
        $this->obj->seal();
        $this->assertFalse($this->obj->add('A'));
        $this->assertNull($this->obj->update('A', 2));
        $this->assertEmpty($this->obj->export());
    }
}
