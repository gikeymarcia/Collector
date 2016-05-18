<?php

namespace Collector;

class MainTrialTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = (new Experiment())
            ->addTrialAbsolute(array("trial 1" => 1))
            ->addPostTrial(array("post trial 1" => 2))
            ->addPostTrial(array("post trial 2" => 3));

        $this->exported = array(
            'main' => array('trial 1' => 1, 'response' => array()),
            'post 1' => array('post trial 1' => 2, 'response' => array()),
            'post 2' => array('post trial 2' => 3, 'response' => array()),
        );
    }

    /**
     * @covers Collector\MainTrial::export
     * @covers Collector\PostTrial::export
     */
    public function testExport()
    {
        $this->assertEquals($this->exported, $this->obj->export());
    }

    /**
     * @covers Collector\MainTrial::markComplete
     */
    public function testMarkComplete()
    {
        $this->obj->markComplete();
        $this->assertTrue($this->obj->isComplete());
        $this->assertFalse($this->obj->record(array('a' => 1)));
    }
    
    /**
     * @covers Collector\MainTrial::validate
     */
    public function testValidate()
    {
        $validator = new Validator(function (Trial $trial) {
            return ($trial->get('valkey', true) === 'any') ? true : "Failed!";
        });
        $this->obj->getExperiment()->addValidator('a', $validator);

        $this->obj->update('trial type', 'a');
        $this->assertNotEmpty($this->obj->validate()); // valkey not in Main

        $this->obj->update('valkey', 'any');
        $this->assertEmpty($this->obj->validate()); // valkey in Main

        $this->obj->advance();
        $this->obj->update('trial type', 'a');
        $results = $this->obj->validate(); // should pass-fail-pass: 1 error
        $this->assertCount(1, $results);
    }

    /**
     * @covers Collector\MainTrial::getPostTrialAbsolute
     */
    public function testGetPostTrialAbsolute()
    {
        $this->assertEquals($this->obj, $this->obj->getPostTrialAbsolute(0));
        $this->assertEquals(1, $this->obj->getPostTrialAbsolute(1)->position);
        $this->assertInstanceOf('Collector\PostTrial', $this->obj->getPostTrialAbsolute(1));
    }

    /**
     * @covers Collector\MainTrial::getPostTrial
     */
    public function testGetPostTrialForward()
    {
        $this->assertEquals($this->obj, $this->obj->getPostTrial(0));
        $this->assertEquals(
            $this->obj->getPostTrialAbsolute(1),
            $this->obj->getPostTrial(1)
        );
        $this->assertNull($this->obj->getPostTrial(3));
    }

    /**
     * @covers Collector\MainTrial::advance
     * @covers Collector\MainTrial::getPostTrial
     */
    public function testGetPostTrialBackward()
    {
        $this->obj->advance();
        $this->obj->advance();
        $this->assertEquals($this->obj, $this->obj->getPostTrial(-2));
        $this->assertEquals(
            $this->obj->getPostTrialAbsolute(1),
            $this->obj->getPostTrial(-1)
        );
    }

    /**
     * @covers Collector\MainTrial::advance
     * @covers Collector\MainTrial::getPostTrial
     */
    public function testAdvanceToEnd()
    {
        $this->obj->advance();
        $this->obj->advance();
        $this->obj->advance();
        $this->assertTrue($this->obj->isComplete());
        $this->assertNull($this->obj->getPostTrial());
    }

    /**
     * @covers Collector\MainTrial::advance
     * @covers Collector\MainTrial::getCurrent
     */
    public function testAdvanceAndGetCurrent()
    {
        $this->obj->advance();
        $this->assertEquals(
            $this->obj->getCurrent(),
            $this->obj->getPostTrialAbsolute(1)
        );

        $this->obj->advance();
        $this->assertEquals(
            $this->obj->getCurrent(),
            $this->obj->getPostTrialAbsolute(2)
        );

        $this->obj->advance();
        $this->assertNull($this->obj->getCurrent());
    }

    /**
     * @covers Collector\Trial::getResponse
     */
    public function testGetResponse()
    {
        $this->obj->record(array('a' => 1));
        $this->assertEquals(1, $this->obj->getResponse('a'));
        $this->assertNull($this->obj->getResponse('b'));
        $this->assertInstanceOf('Collector\Response', $this->obj->getResponse());
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAdd()
    {
        $this->assertTrue($this->obj->add('new information', 1));
        $this->assertFalse($this->obj->add('new information', 1));
        $this->assertEquals(1, $this->obj->export()['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddStrict()
    {
        $this->assertTrue($this->obj->add('new information', 1, true));
        $this->assertFalse($this->obj->add('new information', 1, true));
        $this->assertEquals(1, $this->obj->export()['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddWhenInPost()
    {
        $this->obj->advance();
        $this->assertTrue($this->obj->add('new information', 1));
        $this->assertFalse($this->obj->add('new information', 1));
        $this->assertEquals(1, $this->obj->export()['post 1']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddWhenInPostStrict()
    {
        $this->obj->advance();
        $this->assertTrue($this->obj->add('new information', 1, true));
        $this->assertFalse($this->obj->add('new information', 1, true));
        $this->assertEquals(1, $this->obj->export()['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::get
     * @covers Collector\Trial::record
     */
    public function testGet()
    {
        $this->obj->record(array('input'=> 3));

        $this->assertEquals(1, $this->obj->get('trial 1'));
        $this->assertNull($this->obj->get('post trial 1'));
        $this->assertEquals(3, $this->obj->get('input'));
    }

    /**
     * @covers Collector\MainTrial::get
     */
    public function testGetStrict()
    {
        $this->obj->record(array('input'=> 3));
        $this->obj->add('input', 4);

        $this->assertEquals(1, $this->obj->get('trial 1', true));
        $this->assertNull($this->obj->get('post trial 1', true));
        $this->assertEquals(4, $this->obj->get('input', true));
    }

    /**
     * @covers Collector\MainTrial::get
     * @covers Collector\PostTrial::get
     */
    public function testGetWhenInPost()
    {
        $this->obj->advance();
        $this->obj->record(array('input'=> 3));

        $this->assertEquals(1, $this->obj->get('trial 1'));
        $this->assertEquals(2, $this->obj->get('post trial 1'));
        $this->assertEquals(3, $this->obj->get('input'));
    }

    /**
     * @covers Collector\MainTrial::get
     */
    public function testGetWhenInPostStrict()
    {
        $this->obj->record(array('input'=> 3));
        $this->obj->advance();
        $this->obj->record(array('input'=> 4));

        $this->assertEquals(1, $this->obj->get('trial 1', true));
        $this->assertNull($this->obj->get('post trial 1', true));
        $this->assertNull($this->obj->get('input', true));
    }

    /**
     * @covers Collector\MainTrial::update
     */
    public function testUpdate()
    {
        $this->assertTrue($this->obj->update('trial 1', 'new'));
        $this->assertFalse($this->obj->update('new info', 'new'));
        $this->assertTrue($this->obj->update('new info', 'new2'));
    }

    /**
     * @covers Collector\MainTrial::update
     * @covers Collector\PostTrial::update
     */
    public function testUpdateWhenInPost()
    {
        $this->obj->advance();
        $this->assertTrue($this->obj->update('post trial 1', 'new'));
        $this->assertFalse($this->obj->update('new info', 'new'));
        $this->assertTrue($this->obj->update('new info', 'new2'));
    }

    /**
     * @covers Collector\MainTrial::addPostTrial
     */
    public function testAddPostTrial()
    {
        $this->obj->addPostTrial();
        $this->assertCount(4, $this->obj->export());
        $this->assertInstanceOf('Collector\PostTrial', $this->obj->getPostTrial(3));
    }
    
    /**
     * @covers Collector\MainTrial::apply
     */
    public function testApply()
    {
        $function = function($trial, $key, $value) {
            $trial->add($key, $value);
        };
        $this->obj->apply($function, array('brand new key', 10));
        
        for ($i = 0; $i < 3; ++$i) {
            $this->assertEquals(10, $this->obj->get('brand new key', true));
            $this->obj->advance();
        }
    }

    /**
     * @covers Collector\MainTrial::copy
     * @covers Collector\MainTrial::__clone
     */
    public function testCopyAndClone()
    {
        $clone = clone $this->obj;
        $this->assertEquals($clone, $this->obj->copy());
        $this->assertFalse($clone->isComplete());
        $this->assertNull($clone->position);
        $this->assertEquals($clone, $clone->getPostTrial());
        $this->assertEquals($clone->getResponse(), new Response());
    }
}
