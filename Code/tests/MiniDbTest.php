<?php

namespace Collector;

class MiniDbTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = new MiniDb();
    }

    /**
     * @covers Collector\MiniDb::__construct
     * @covers Collector\MiniDb::export
     * @covers Collector\MiniDb::formatArray
     */
    public function testConstructorAndExportArray()
    {
        $obj = new MiniDb(array('a' => 1, 'B' => 2));
        $this->assertEquals(array('a' => 1, 'b' => 2), $obj->export());
    }

    /**
     * @covers Collector\MiniDb::add
     * @covers Collector\MiniDb::export
     */
    public function testAdd()
    {
        $this->assertTrue($this->obj->add('A'));
        $this->assertTrue($this->obj->add('b', 2));
        $this->assertFalse($this->obj->add('A'));
        $this->assertFalse($this->obj->add('a'));
        $this->assertEquals(array('a' => null, 'b' => 2), $this->obj->export());
    }

    /**
     * @covers Collector\MiniDb::get
     */
    public function testGet()
    {
        $this->obj->add('a');
        $this->obj->add('B', 2);
        $this->assertNull($this->obj->get('a'));
        $this->assertEquals(2, $this->obj->get('B'));
        $this->assertEquals(2, $this->obj->get('b'));
    }

    /**
     * @covers Collector\MiniDb::update
     */
    public function testUpdate()
    {
        $this->assertFalse($this->obj->update('a', 1));
        $this->assertEquals(1, $this->obj->get('a'));
        $this->assertTrue($this->obj->update('A', 2));
        $this->assertEquals(2, $this->obj->get('a'));
    }

    /**
     * @covers Collector\MiniDb::export
     * @covers Collector\MiniDb::formatArray
     */
    public function testExportJson()
    {
        $array = array('a' => 1, 'b' => 2, "c" => array('d' => 4));
        $this->obj = new MiniDb($array);

        $expected = "{\n"
                  . "    \"a\": 1,\n"
                  . "    \"b\": 2,\n"
                  . "    \"c\": {\n"
                  . "        \"d\": 4\n"
                  . "    }\n"
                  . "}";
        $actual = $this->obj->export('json');
        $this->assertEquals($expected, $actual);
    }
}
