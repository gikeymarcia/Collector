<?php

namespace Collector;

class MainTrialTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $expt = new Experiment();
        $this->obj = $expt->addTrialAbsolute(array("trial 1" => 1))
                          ->addPostTrial(array("post trial 1" => 2))
                          ->addPostTrial(array("post trial 2" => 3));

        $this->exported = array(
            'main' => array('trial 1' => 1, 'response' => array()),
            'post 1' => array('post trial 1' => 2, 'response' => array()),
            'post 2' => array('post trial 2' => 3, 'response' => array()),
        );
    }

    /**
     * @covers Collector\MainTrial::count
     */
    public function testCount()
    {
        $this->assertCount(3, $this->obj);
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
        $validator = new Validator(__DIR__ . '/validators/passCheck.php');
        $this->obj->getExperiment()->addValidator('a', $validator);
        $this->obj->update('trial type', 'a');

        $this->assertNotEmpty($this->obj->validate()); // key1 not in Main

        $this->obj->update('key1', 1);
        $this->assertEmpty($this->obj->validate()); // key1 in Main

        $this->obj->advance();
        $this->obj->update('trial type', 'a');
        // should pass-fail-pass: last has no trial type
        $results = $this->obj->validate();
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

        $result = $this->obj->export();
        $this->assertEquals(1, $result['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddStrict()
    {
        $this->assertTrue($this->obj->add('new information', 1, true));
        $this->assertFalse($this->obj->add('new information', 1, true));

        $result = $this->obj->export();
        $this->assertEquals(1, $result['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddWhenInPost()
    {
        $this->obj->advance();
        $this->assertTrue($this->obj->add('new information', 1));
        $this->assertFalse($this->obj->add('new information', 1));

        $result = $this->obj->export();
        $this->assertEquals(1, $result['post 1']['new information']);
    }

    /**
     * @covers Collector\MainTrial::add
     */
    public function testAddWhenInPostStrict()
    {
        $this->obj->advance();
        $this->assertTrue($this->obj->add('new information', 1, true));
        $this->assertFalse($this->obj->add('new information', 1, true));

        $result = $this->obj->export();
        $this->assertEquals(1, $result['main']['new information']);
    }

    /**
     * @covers Collector\MainTrial::get
     * @covers Collector\Trial::record
     */
    public function testGetLoose()
    {
        $this->obj->record(array('input'=> 3));

        $this->assertEquals(1, $this->obj->get('trial 1', false));
        $this->assertNull($this->obj->get('post trial 1', false));
        $this->assertEquals(3, $this->obj->get('input', false));
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
    public function testGetWhenInPostLoose()
    {
        $this->obj->advance();
        $this->obj->record(array('input'=> 3));

        $this->assertEquals(1, $this->obj->get('trial 1', false));
        $this->assertEquals(2, $this->obj->get('post trial 1', false));
        $this->assertEquals(3, $this->obj->get('input', false));
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

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     */
    public function testDeletePostTrialAbsoluteRemovesTrial()
    {
        $precount = count($this->obj);
        $this->obj->deletePostTrialAbsolute(1);
        $this->assertCount($precount - 1, $this->obj);
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     */
    public function testDeletePostTrialAbsoluteRenumbersTrials()
    {
        $this->obj->deletePostTrialAbsolute(1);
        $this->assertEquals(1, $this->obj->getPostTrial(1)->position);
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     */
    public function testDeletePostTrialAbsoluteRemovesCorrectTrial()
    {
        $deleted = $this->obj->getPostTrialAbsolute(1);
        $this->assertTrue($this->obj->deletePostTrialAbsolute(1));
        $this->assertNotSame($deleted, $this->obj->getPostTrialAbsolute(1));
    }

    /**
     * @covers Collector\MainTrial::deletePostTrial
     */
    public function testDeletePostTrialFuture()
    {
        $deleted = $this->obj->getPostTrial(1);
        $this->obj->deletePostTrial(1);
        $this->assertNotSame($deleted, $this->obj->getPostTrial(1));
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     * @covers Collector\MainTrial::deletePostTrial
     */
    public function testDeletePostTrialPreviousFails()
    {
        $notDeleted = $this->obj->getPostTrialAbsolute(1);
        $this->obj->advance();
        $this->obj->advance();
        $this->assertFalse($this->obj->deletePostTrial(-1));
        $this->assertSame($notDeleted, $this->obj->getPostTrial(-1));
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     * @covers Collector\MainTrial::deletePostTrial
     */
    public function testDeletePostTrialMainFails()
    {
        $notDeleted = $this->obj;
        $this->assertFalse($this->obj->deletePostTrial(0));
        $this->assertSame($notDeleted, $this->obj->getPostTrial());
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialsAbsolute
     * @covers Collector\MainTrial::deletePostTrials
     */
    public function testDeletePostTrialsAbsolute()
    {
        $this->obj->addPostTrial(array("post trial 3" => 4));
        $trialThree = $this->obj->getPostTrialAbsolute(3);
        $precount = count($this->obj);
        $this->obj->deletePostTrialsAbsolute('1::2');
        $this->assertCount($precount - 2, $this->obj);
        $this->assertSame($trialThree, $this->obj->getPostTrial(1));
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialsAbsolute
     * @covers Collector\MainTrial::deletePostTrials
     */
    public function testDeletePostTrials()
    {
        $this->obj->addPostTrial(array("post trial 3" => 4));
        $trialThree = $this->obj->getPostTrialAbsolute(3);
        $precount = count($this->obj);
        $this->obj->deletePostTrials('1::2');
        $this->assertCount($precount - 2, $this->obj);
        $this->assertSame($trialThree, $this->obj->getPostTrial(1));
    }

    /**
     * @covers Collector\MainTrial::deletePostTrialAbsolute
     * @covers Collector\MainTrial::deletePostTrial
     * @covers Collector\MainTrial::deletePostTrialsAbsolute
     * @covers Collector\MainTrial::deletePostTrials
     */
    public function testDeleteFuturePostTrialUpdatesIsComplete()
    {
        $this->obj->deletePostTrials("1::2");
        $this->assertTrue($this->obj->isComplete());
    }
}
