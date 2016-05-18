<?php

namespace Collector;

class ExperimentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = new Experiment(array(), array('a', 'b', 'c'));
        $this->testTrial = new MainTrial(array('trial 0' => 0), null);
        $this->testTrial->addPostTrial(array("post trial 0-1" => 0))
                        ->addPostTrial(array("post trial 0-2" => 0));
        foreach(range(0, 3) as $i) {
            $this->obj->addTrialAbsolute()->addPostTrial()->addPostTrial();
        }
    }
    
    protected function getProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @covers Collector\Experiment::count
     */
    public function testCount()
    {
        $this->assertCount(4, $this->obj);
        $this->obj->addTrialAbsolute(array('new' => 1));
        $this->assertCount(5, $this->obj);
    }

    /**
     * @covers Collector\Experiment::getTrialAbsolute
     */
    public function testGetTrialAbsolute()
    {
        $trialZero = $this->obj->getTrialAbsolute(0);
        $trialTwo = $this->obj->getTrialAbsolute(2);
        $trialDNE = $this->obj->getTrialAbsolute(10);

        $this->assertInstanceOf('Collector\MainTrial', $trialZero);
        $this->assertInstanceOf('Collector\MainTrial', $trialTwo);
        $this->assertNull($trialDNE);

        $this->assertEquals(0, $trialZero->position);
        $this->assertEquals(2, $trialTwo->position);
    }

    /**
     * @covers Collector\Experiment::getTrial
     * @covers Collector\Experiment::getTrialAbsolute
     */
    public function testGetTrialForward()
    {
        $trialZero = $this->obj->getTrial(0);
        $trialOne = $this->obj->getTrial(1);
        $trialTwo = $this->obj->getTrial(2);
        $trialDNE = $this->obj->getTrial(10);

        $this->assertInstanceOf('Collector\MainTrial', $trialZero);
        $this->assertInstanceOf('Collector\MainTrial', $trialOne);
        $this->assertInstanceOf('Collector\MainTrial', $trialTwo);
        $this->assertNull($trialDNE);

        $this->assertEquals(0, $trialZero->position);
        $this->assertEquals(1, $trialOne->position);
        $this->assertEquals(2, $trialTwo->position);
    }

    /**
     * @covers Collector\Experiment::getTrial
     * @covers Collector\Experiment::getTrialAbsolute
     */
    public function testGetTrialForwardFromPost()
    {
        $this->obj->advance();

        $trialZero = $this->obj->getTrial(0);
        $trialOne = $this->obj->getTrial(1);
        $trialTwo = $this->obj->getTrial(2);
        $trialDNE = $this->obj->getTrial(10);

        $this->assertInstanceOf('Collector\MainTrial', $trialZero);
        $this->assertInstanceOf('Collector\MainTrial', $trialOne);
        $this->assertInstanceOf('Collector\MainTrial', $trialTwo);
        $this->assertNull($trialDNE);

        $this->assertEquals(0, $trialZero->position);
        $this->assertEquals(1, $trialOne->position);
        $this->assertEquals(2, $trialTwo->position);
    }

    /**
     * @covers Collector\Experiment::getTrial
     * @covers Collector\Experiment::getTrialAbsolute
     */
    public function testGetTrialReverse()
    {
        $this->obj->skip();
        $this->obj->skip();

        $trialZero = $this->obj->getTrial(-2);
        $trialOne = $this->obj->getTrial(-1);
        $trialTwo = $this->obj->getTrial(0);
        $trialDNE = $this->obj->getTrialAbsolute(-4);

        $this->assertInstanceOf('Collector\MainTrial', $trialZero);
        $this->assertInstanceOf('Collector\MainTrial', $trialOne);
        $this->assertInstanceOf('Collector\MainTrial', $trialTwo);
        $this->assertNull($trialDNE);

        $this->assertEquals(0, $trialZero->position);
        $this->assertEquals(1, $trialOne->position);
        $this->assertEquals(2, $trialTwo->position);
    }

    /**
     * @covers Collector\Experiment::getTrial
     * @covers Collector\Experiment::getTrialAbsolute
     */
    public function testGetTrialReverseFromPost()
    {
        $this->obj->skip();
        $this->obj->skip();
        $this->obj->advance();

        $trialZero = $this->obj->getTrial(-2);
        $trialOne = $this->obj->getTrial(-1);
        $trialTwo = $this->obj->getTrial(0);
        $trialDNE = $this->obj->getTrialAbsolute(-4);

        $this->assertInstanceOf('Collector\MainTrial', $trialZero);
        $this->assertInstanceOf('Collector\MainTrial', $trialOne);
        $this->assertInstanceOf('Collector\MainTrial', $trialTwo);
        $this->assertNull($trialDNE);

        $this->assertEquals(0, $trialZero->position);
        $this->assertEquals(1, $trialOne->position);
        $this->assertEquals(2, $trialTwo->position);
    }
    
    /**
     * @covers Collector\Experiment::getTrialsAbsolute
     */
    public function testGetTrialsAbsoluteArray()
    {
        $trials = $this->obj->getTrialsAbsolute(array(1, 2));
        $this->assertEquals($this->obj->getTrialAbsolute(1), $trials[1]);
        $this->assertEquals($this->obj->getTrialAbsolute(2), $trials[2]);
    }
    
    /**
     * @covers Collector\Experiment::getTrialsAbsolute
     */
    public function testGetTrialsAbsoluteString()
    {
        $trials = $this->obj->getTrialsAbsolute('1::2');
        $this->assertEquals($this->obj->getTrialAbsolute(1), $trials[1]);
        $this->assertEquals($this->obj->getTrialAbsolute(2), $trials[2]);
    }
    
    /**
     * @covers Collector\Experiment::getTrialsAbsolute
     */
    public function testGetTrialsAbsoluteAll()
    {
        $trials = $this->obj->getTrialsAbsolute('all');
        $this->assertCount(count($this->obj), $trials);
    }
    
    /**
     * @covers Collector\Experiment::getTrials
     */
    public function testGetTrialsArray()
    {
        $trials = $this->obj->getTrials(array(1, 2));
        $this->assertEquals($this->obj->getTrial(1), $trials[1]);
        $this->assertEquals($this->obj->getTrial(2), $trials[2]);
    }
    
    /**
     * @covers Collector\Experiment::getTrials
     */
    public function testGetTrialsString()
    {
        $trials = $this->obj->getTrials("1::2");
        $this->assertEquals($this->obj->getTrial(1), $trials[1]);
        $this->assertEquals($this->obj->getTrial(2), $trials[2]);
    }
    
    /**
     * @covers Collector\Experiment::getTrials
     */
    public function testGetTrialsAll()
    {
        $trials = $this->obj->getTrials('all');
        $this->assertCount(count($this->obj), $trials);
    }

    /**
     * @covers Collector\Experiment::advance
     * @covers Collector\Experiment::getCurrent
     */
    public function testAdvanceAndGetCurrent()
    {
        $this->obj->advance();
        $this->assertEquals(0, $this->obj->position);
        $this->assertInstanceOf('Collector\PostTrial', $this->obj->getCurrent());

        $this->obj->advance();
        $this->assertEquals(0, $this->obj->position);
        $this->assertInstanceOf('Collector\PostTrial', $this->obj->getCurrent());

        $this->obj->advance();
        $this->assertEquals(1, $this->obj->position);
        $this->assertInstanceOf('Collector\MainTrial', $this->obj->getCurrent());
    }

    /**
     * @covers Collector\Experiment::skip
     */
    public function testSkip()
    {
        $this->obj->skip();
        $this->assertEquals(1, $this->obj->position);
        $this->assertInstanceOf('Collector\MainTrial', $this->obj->getCurrent());
        $this->assertTrue($this->obj->getTrial(-1)->isComplete());
    }
    
    /**
     * @covers Collector\Experiment::skip
     */
    public function testSkipFromPost()
    {
        $this->obj->advance();
        $this->obj->skip();
        $this->assertEquals(1, $this->obj->position);
        $this->assertInstanceOf('Collector\MainTrial', $this->obj->getCurrent());
        $this->assertTrue($this->obj->getTrial(-1)->isComplete());
    }
    
    /**
     * @covers Collector\Experiment::skip
     */
    public function testSkipMultiple()
    {
        $pos = $this->obj->position;
        $this->obj->skip(2);
        $this->assertEquals($pos + 2, $this->obj->position);
    }
    
    /**
     * @covers Collector\Experiment::skip
     */
    public function testSkipTooFarStopsAtEnd()
    {
        $this->obj->skip(16);
        $this->assertEquals(count($this->obj) - 1, $this->obj->position);
    }
    
    /**
     * @covers Collector\Experiment::isComplete
     */
    public function testIsComplete()
    {
        $this->assertFalse($this->obj->isComplete());
        $this->obj->skip(10);
        $this->assertTrue($this->obj->isComplete());
    }

    /**
     * @covers Collector\Experiment::export
     */
    public function testExport()
    {
        $this->assertEmpty($this->obj->export()['Condition']);
        $this->assertEquals(array('a', 'b', 'c'), $this->obj->export()['Stimuli']);
        $this->assertEquals($this->testTrial->export(), $this->obj->export()['Trials'][0]);
    }

    /**
     * @covers Collector\Experiment::add
     */
    public function testAdd()
    {
        $this->assertTrue($this->obj->add('new info 1'));
        $this->assertTrue($this->obj->add('new info 2', 2));
        $this->assertFalse($this->obj->add('new info 1'));
        $this->assertFalse($this->obj->add('new info 1', 1));
        $this->assertFalse($this->obj->add('new info 2', 3));

        $actual = $this->obj->export()['Trials'][0]['main'];
        $this->assertNull($actual['new info 1']);
        $this->assertEquals(2, $actual['new info 2']);
    }

    /**
     * @covers Collector\Experiment::add
     */
    public function testAddInPost()
    {
        $this->obj->advance();

        $this->assertTrue($this->obj->add('new info 1'));
        $this->assertTrue($this->obj->add('new info 2', 2));
        $this->assertFalse($this->obj->add('new info 1'));
        $this->assertFalse($this->obj->add('new info 1', 1));
        $this->assertFalse($this->obj->add('new info 2', 3));

        $actual = $this->obj->export()['Trials'][0]['post 1'];
        $this->assertNull($actual['new info 1']);
        $this->assertEquals(2, $actual['new info 2']);
    }

    /**
     * @covers Collector\Experiment::get
     */
    public function testGet()
    {
        $this->assertNull($this->obj->get('dne'));
        $this->assertEquals(0, $this->obj->get('trial 0'));
    }

    /**
     * @covers Collector\Experiment::add
     */
    public function testGetInPost()
    {
        $this->obj->advance();
        $this->assertNull($this->obj->get('dne'));
        $this->assertEquals(0, $this->obj->get('trial 0'));
        $this->assertEquals(0, $this->obj->get('post trial 0-1'));
    }

    /**
     * @covers Collector\Experiment::update
     */
    public function testUpdate()
    {
        $this->assertFalse($this->obj->update('dne', 1));
        $this->assertTrue($this->obj->update('dne', 1));
        $this->assertEquals(1, $this->obj->get('dne'));
    }

    /**
     * @covers Collector\Experiment::add
     */
    public function testUpdateInPost()
    {
        $this->assertFalse($this->obj->update('dne', 1));
        $this->obj->advance();
        $this->assertFalse($this->obj->update('dne', 2));
        $this->assertTrue($this->obj->update('dne', 2));
        $this->assertEquals(2, $this->obj->get('dne'));
    }

    /**
     * @covers Collector\Experiment::record
     */
    public function testRecord()
    {
        $this->assertTrue($this->obj->record(array('stuff' => 1, 'things' => 2)));
        $this->assertEquals(1, $this->obj->getResponse('stuff'));
        $this->assertTrue($this->obj->record(array('stuff' => 4), false));
        $this->assertEquals(1, $this->obj->getResponse('stuff'));
        $this->assertEquals(2, $this->obj->getResponse('things'));
    }

    /**
     * @covers Collector\Experiment::getResponse
     */
    public function testGetResponse()
    {
        $this->assertEquals(new Response(), $this->obj->getResponse());
        $this->obj->record(array('stuff' => 1));
        $this->assertEquals(1, $this->obj->getResponse('stuff'));
        $this->obj->advance();
        $this->assertEquals(new Response(), $this->obj->getResponse());
        $this->assertNull($this->obj->getResponse('stuff'));
    }

    /**
     * @covers Collector\Experiment::record
     */
    public function testRecordInPost()
    {
        $this->assertTrue($this->obj->record(array('things' => 2)));
        $this->obj->advance();

        $this->assertTrue($this->obj->record(array('stuff' => 1)));
        $this->assertEquals(1, $this->obj->getResponse('stuff'));
        $this->assertTrue($this->obj->record(array('stuff' => 4), false));
        $this->assertEquals(1, $this->obj->getResponse('stuff'));
        $this->assertNull($this->obj->getResponse('things'));
    }

    /**
     * @covers Collector\Experiment::get
     */
    public function testGetOnlyChecksCurrentRecord()
    {
        $this->obj->record(array('things' => 2));
        $this->obj->advance();

        $this->obj->record(array('stuff' => 1));
        $this->assertEquals(1, $this->obj->get('stuff'));
        $this->assertNull($this->obj->get('things'));
    }
    
    /**
     * @convers Collector\Experiment::getCondition
     */
    public function testGetCondition()
    {
        $expt = new Experiment(array('cond' => 1));
        $this->assertEquals(array('cond' => 1), $expt->getCondition());
    }

    /**
     * @covers Collector\Experiment::getStimuli
     */
    public function testGetStimuli()
    {
        $stim = $this->obj->getStimuli();
        $this->assertContains('a', $stim);
        $this->assertContains('b', $stim);
        $this->assertContains('c', $stim);
    }
    
    /**
     * @covers Collector\Experiment::getStimuli
     */
    public function testGetStimuliSubset()
    {
        $stim = $this->obj->getStimuli("1-2");
        $this->assertNotContains('a', $stim);
        $this->assertContains('b', $stim);
        $this->assertContains('c', $stim);
    }

    /**
     * @covers Collector\Experiment::getStimulus
     */
    public function testGetStimulus()
    {
        $stim = array(
            $this->obj->getStimulus(0),
            $this->obj->getStimulus(1),
            $this->obj->getStimulus(2),
        );
        
        $this->assertContains('a', $stim);
        $this->assertContains('b', $stim);
        $this->assertContains('c', $stim);
    }

    /**
     * @covers Collector\Experiment::copy
     */
    public function testCopy()
    {
        $this->obj->skip();
        $copyCurr = $this->obj->copy();
        $copyPrev = $this->obj->copy(-1);
        $copyNext = $this->obj->copy(1);

        $this->assertEquals($this->obj->getTrial()->export(), $copyCurr->export());
        $this->assertEquals($this->obj->getTrial(-1)->export(), $copyPrev->export());
        $this->assertEquals($this->obj->getTrial(1)->export(), $copyNext->export());

        $this->obj->getTrial(1)->markComplete();
        $copyNextTwo = $this->obj->copy(1);
        $this->assertFalse($copyNextTwo->isComplete());
        $this->assertNull($copyNextTwo->position);
        $this->assertEquals($copyNextTwo, $copyNextTwo->getPostTrial());
        $this->assertEquals($copyNextTwo->getResponse(), new Response());
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     * @covers Collector\Experiment::updatePositions
     */
    public function testAddTrialAbsoluteAtEndFromData()
    {
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute(array('a' => 1, 'b' => 2));

        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals($precount, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(count($this->obj) - 1));
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     * @covers Collector\Experiment::updatePositions
     */
    public function testAddTrialAbsoluteAtPosFromData()
    {
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute(array('a' => 1, 'b' => 2), 2);

        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals(2, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(2));
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     * @covers Collector\Experiment::updatePositions
     */
    public function testAddTrialAbsoluteAtEnd()
    {
        $trial = new MainTrial(array('a' => 1, 'b' => 2));
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute($trial);
        
        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals($precount, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(count($this->obj) - 1));
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     * @covers Collector\Experiment::updatePositions
     */
    public function testAddTrialAbsoluteAtPos()
    {
        $trial = new MainTrial(array('a' => 1, 'b' => 2));
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute($trial, 2);
        
        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals(2, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(2));
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     */
    public function testAddTrialAbsoluteFromTrialSameExpt()
    {
        $trial = new MainTrial(array('a' => 1, 'b' => 2), $this->obj);
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute($trial);
        
        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals($precount, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(count($this->obj) - 1));
    }

    /**
     * @covers Collector\Experiment::addTrialAbsolute
     */
    public function testAddTrialAbsoluteFromDiffExpt()
    {
        $trial = new MainTrial(array('a' => 1, 'b' => 2), new Experiment());
        $precount = count($this->obj);
        $added = $this->obj->addTrialAbsolute($trial);
        
        $this->assertInstanceOf('Collector\MainTrial', $added);
        $this->assertCount($precount + 1, $this->obj);
        $this->assertEquals($precount, $added->position);
        $this->assertEquals($added, $this->obj->getTrialAbsolute(count($this->obj) - 1));
    }

    /**
     * @covers Collector\Experiment::addTrialsAbsolute
     */
    public function testAddTrialsAbsoluteFromData()
    {
        $data = array(
            array('a' => 1, 'b' => 2),
            array('c' => 3, 'd' => 4),
            array('e' => 5, 'f' => 6),
        );
        $trials = array(
            new MainTrial($data[0]),
            new MainTrial($data[1]),
            new MainTrial($data[2]),
        );
        $precount = count($this->obj);
        foreach (range(0, 2) as $i) {
            $trials[$i]->position = $precount + $i;
            $trials[$i]->setExperiment($this->obj);
        }
        
        $this->obj->addTrialsAbsolute($data);
        $this->assertCount($precount + 3, $this->obj);
        $this->assertEquals($trials[0], $this->obj->getTrialAbsolute($precount));
        $this->assertEquals($trials[1], $this->obj->getTrialAbsolute($precount + 1));
        $this->assertEquals($trials[2], $this->obj->getTrialAbsolute($precount + 2));
    }
    
    /**
     * @covers Collector\Experiment::addTrialsAbsolute
     */
    public function testAddTrialsAbsoluteAtPos()
    {
        $data = array(
            array('a' => 1, 'b' => 2),
            array('c' => 3, 'd' => 4),
            array('e' => 5, 'f' => 6),
        );
        $trials = array(
            new MainTrial($data[0]),
            new MainTrial($data[1]),
            new MainTrial($data[2]),
        );
        $precount = count($this->obj);
        foreach (range(0, 2) as $i) {
            $trials[$i]->position = $i;
            $trials[$i]->setExperiment($this->obj);
        }
        
        $this->obj->addTrialsAbsolute($data, 0);
        $this->assertCount($precount + 3, $this->obj);
        $this->assertEquals($trials[0], $this->obj->getTrialAbsolute(0));
        $this->assertEquals($trials[1], $this->obj->getTrialAbsolute(1));
        $this->assertEquals($trials[2], $this->obj->getTrialAbsolute(2));
    }
    
    /**
     * @covers Collector\Experiment::addTrialsAbsolute
     */
    public function testAddTrialsAbsoluteFromThisExpt()
    {
        $trials = array(
            new MainTrial(array('a' => 1, 'b' => 2), $this->obj),
            new MainTrial(array('c' => 3, 'd' => 4), $this->obj),
            new MainTrial(array('e' => 5, 'f' => 6), $this->obj),
        );
        $precount = count($this->obj);
        foreach (range(0, 2) as $i) {
            $trials[$i]->position = $precount + $i;
            $trials[$i]->setExperiment($this->obj);
        }
        
        $this->obj->addTrialsAbsolute($trials);
        $this->assertCount($precount + 3, $this->obj);
        $this->assertEquals($trials[0], $this->obj->getTrialAbsolute($precount));
        $this->assertEquals($trials[1], $this->obj->getTrialAbsolute($precount + 1));
        $this->assertEquals($trials[2], $this->obj->getTrialAbsolute($precount + 2));
    }
    
    /**
     * @covers Collector\Experiment::addTrialsAbsolute
     */
    public function testAddTrialsAbsoluteFromDiffExpt()
    {
        $trialsToAdd = array(
            new MainTrial(array('a' => 1, 'b' => 2), $this->obj),
            new MainTrial(array('c' => 3, 'd' => 4), $this->obj),
            new MainTrial(array('e' => 5, 'f' => 6), $this->obj),
        );
        $trials = $trialsToAdd;
        $precount = count($this->obj);
        foreach (range(0, 2) as $i) {
            $trials[$i]->position = $precount + $i;
            $trials[$i]->setExperiment($this->obj);
        }
        
        $this->obj->addTrialsAbsolute($trials);
        $this->assertCount($precount + 3, $this->obj);
        $this->assertEquals($trials[0], $this->obj->getTrialAbsolute($precount));
        $this->assertEquals($trials[1], $this->obj->getTrialAbsolute($precount + 1));
        $this->assertEquals($trials[2], $this->obj->getTrialAbsolute($precount + 2));
    }
    
    /**
     * @covers Collector\Experiment::addTrial
     * @covers Collector\Experiment::getTrial
     */
    public function testAddTrialNext()
    {
        $trial = new MainTrial(array('a' => 1));
        $this->obj->addTrial($trial);
        $actual = $this->getProperty($this->obj->getTrial(1), 'data');
        $this->assertEquals(array('a' => 1), $actual);
    }
    
    /**
     * @covers Collector\Experiment::addTrial
     * @covers Collector\Experiment::getTrial
     */
    public function testAddTrialFuture()
    {
        $trial = new MainTrial(array('a' => 1));
        $this->obj->addTrial($trial, 3);
        $actual = $this->getProperty($this->obj->getTrial(3), 'data');
        $this->assertEquals(array('a' => 1), $actual);
    }
    
    /**
     * @covers Collector\Experiment::addTrial
     * @covers Collector\Experiment::getTrial
     */
    public function testAddTrialTooFar()
    {
        $trial = new MainTrial(array('a' => 1));
        $this->obj->addTrial($trial, 100);
        $actual = $this->getProperty(
            $this->obj->getTrialAbsolute(count($this->obj)-1),
            'data'
        );
        $this->assertEquals(array('a' => 1), $actual);
    }

    /**
     * @covers Collector\Experiment::addTrials
     */
    public function testAddTrialsNext()
    {
        $trials = array(
            new MainTrial(array('a' => 1)),
            new MainTrial(array('b' => 2)),
        );
        $this->obj->addTrials($trials);
        $this->assertEquals(array('a' => 1),
            $this->getProperty($this->obj->getTrial(1), 'data'));
        $this->assertEquals(array('b' => 2),
            $this->getProperty($this->obj->getTrial(2), 'data'));
    }
    
    /**
     * @covers Collector\Experiment::apply
     */
    public function testApply()
    {
        $function = function($trial, $key, $value) {
            $trial->add($key, $value);
        };
        $this->obj->apply($function, array('brand new key', 10));
        
        for ($i = 0; $i < 11; ++$i) {
            $this->assertEquals(10, $this->obj->get('brand new key', true));
            $this->obj->advance();
        }
    }

    /**
     * @covers Collector\Experiment::addValidator
     */
    public function testAddValidator()
    {
        $validator = new Validator(function($trial) {
            return $trial->get('trial') === 0 ? true : "Failed!";
        });
        $this->obj->addValidator('a', $validator);
        
        $test = $this->getProperty($this->obj, 'validators')['a'];
        $this->assertEquals($validator, $test);
        
        $this->obj->addValidator('a', $validator, true);
        $this->assertCount(2, $this->getProperty($test, 'checks'));
    }
    
    /**
     * @covers Collector\Experiment::getValidator
     * @covers Collector\Experiment::loadValidator
     */
    public function testGetValidator()
    {
        $validator = new Validator(function($trial) {
            return $trial->get('trial') === 0 ? true : "Failed!";
        });
        $this->obj->addValidator('a', $validator);
        
        $this->assertEquals($validator, $this->obj->getValidator('a'));
        $this->assertNull($this->obj->getValidator('b'));
    }
    
    /**
     * @covers Collector\Experiment::loadValidator
     */
    public function testLoadValidator()
    {
        $expt = new Experiment(array(), array(), __DIR__.'/validatordir1');
        $this->assertInstanceOf('Collector\Validator', $expt->getValidator('testtrialtype1'));
    }
    
    /**
     * @depends testGetValidator
     * @covers Collector\Experiment::setValidators
     */
    public function testSetValidators()
    {
        $validators = array(
            'a' => new Validator(function($trial) { return $trial; }),
            'b' => new Validator(function($trial) { return $trial; }),
        );
            
        $this->obj->setValidators($validators, false);
        $validator1 = $this->obj->getValidator('a');
        $this->assertEquals($validators['a'], $validator1);
        
        $this->obj->setValidators($validators, false);
        $validator2 = $this->obj->getValidator('a');
        $this->assertEquals($validators['a'], $validator2);
        
        $this->obj->setValidators($validators, true);
        $validator3 = $this->obj->getValidator('a');
        $this->assertCount(2, $this->getProperty($validator3, 'checks'));
    }
    
    /**
     * @covers Collector\Experiment::validate
     */
    public function testValidate()
    {
        $validator = new Validator(function($trial) {
            return $trial->get('trial 0') === 0 ? true : "Failed!";
        });
        $this->obj->addValidator('a', $validator);
        
        $this->obj->update('trial type', 'a');
        $this->assertNotEmpty($this->obj->validate());
        $this->assertEquals('Failed!', $this->obj->validate()[0]['message']);
        
        $this->obj->update('trial 0', 0);
        $trial = $this->obj->getTrial();
        $this->assertEmpty($this->obj->validate());
    }
    
    /**
     * @covers Collector\Experiment::stringToRange
     */
    public function testStringToRange()
    {
        $this->assertEquals(array(1), Experiment::stringToRange(1));
        $this->assertEquals(array(1,2), Experiment::stringToRange('1, 2'));
        $this->assertEquals(array(1,2), Experiment::stringToRange('1; 2'));
        $this->assertEquals(array(1,2), Experiment::stringToRange('1,2'));
        $this->assertEquals(array(1,2), Experiment::stringToRange('1;2'));
        $this->assertEquals(array(1,2,3), Experiment::stringToRange('1::3'));
        $this->assertEquals(array(1,2,3,4), Experiment::stringToRange('1::3, 4'));
        $this->assertEquals(array(1,2,3,5), Experiment::stringToRange('1-3; 5'));
        $this->assertEquals(array(1,2,3,4,5), Experiment::stringToRange('1::2;3,4-5'));
    }
}
