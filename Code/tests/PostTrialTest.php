<?php

namespace Collector;

class PostTrialTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = (new Experiment())
            ->addTrialAbsolute(array("trial 1" => 1))
            ->addPostTrial(array("post trial 1" => 2));
        $this->obj = $this->obj->getPostTrial(1);
    }

    /**
     * @covers Collector\PostTrial::__construct
     * @covers Collector\PostTrial::getMainTrial
     * @covers Collector\PostTrial::get
     */
    public function testConstructor()
    {
        $this->obj = (new Experiment())
            ->addTrialAbsolute(array("trial 1" => 1))
            ->addPostTrial(array("post trial 1" => 2));
        $this->obj = $this->obj->getPostTrial(1);
        $this->assertInstanceOf('Collector\MainTrial', $this->obj->getMainTrial());
        $this->assertEquals(2, $this->obj->get('post trial 1'));
    }
    
    /**
     * @covers Collector\PostTrial::markComplete
     */
    public function testMarkComplete()
    {
        $this->obj->markComplete();
        $this->assertTrue($this->obj->isComplete());
        $this->assertFalse($this->obj->record(array('something new' => 1)));
    }
    
    /**
     * @covers Collector\PostTrial::validate
     */    
    public function testValidate()
    {
        $validator = new Validator(__DIR__ . '/validators/passCheck.php');
        $this->obj->getExperiment()->addValidator('a', $validator);

        $this->obj->update('trial type', 'a');
        $this->assertNotEmpty($this->obj->validate());
        
        $this->obj->update('key1', 'any');
        $this->assertEmpty($this->obj->validate());
    }

    /**
     * @covers Collector\PostTrial::record
     */
    public function testRecordSealed()
    {
        $this->obj->getResponse()->seal();
        $this->assertFalse($this->obj->record(array('b' => true)));
        $this->assertNull($this->obj->getResponse('b'));
    }

    /**
     * @covers Collector\PostTrial::get
     */
    public function testGetLoose()
    {
        $this->obj->record(array('response'=> 3));
        $this->assertEquals(1, $this->obj->get('trial 1', false));
        $this->assertEquals(2, $this->obj->get('post trial 1', false));
        $this->assertEquals(3, $this->obj->get('response', false));
        $this->assertNull($this->obj->get('not here', false));
    }

    /**
     * @covers Collector\PostTrial::get
     */
    public function testGetStrict()
    {
        $this->obj->record(array('response'=> 3));
        $this->assertNull($this->obj->get('trial 1', true));
        $this->assertEquals(2, $this->obj->get('post trial 1', true));
        $this->assertNull($this->obj->get('response', true));
        $this->assertNull($this->obj->get('not here', true));
    }

    /**
     * @covers Collector\PostTrial::export
     */
    public function testExportArray()
    {
        $expected = array('post trial 1' => 2, 'response' => array());
        $this->assertEquals($expected, $this->obj->export());
        $this->assertEquals($expected, $this->obj->export('array'));
    }
}
