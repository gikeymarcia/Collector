<?php

namespace Collector;

class TrialTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('Collector\Trial');
    }

    /**
     * @covers Collector\Trial::getExperiment
     * @covers Collector\Trial::setExperiment
     */
    public function testGetSetExperiment()
    {
        $expt = $this->obj->getExperiment();
        $this->assertNull($expt);
        
        $expt = new Experiment();
        $this->obj->setExperiment($expt);
        $this->assertSame($expt, $this->obj->getExperiment());
    }

    /**
     * @covers Collector\Trial::isComplete
     */
    public function testIsComplete()
    {
        $this->assertFalse($this->obj->isComplete());
    }

    /**
     *  @covers Collector\Trial::update
     *  @covers Collector\Trial::injectStimulus
     */
    public function testInjectStimulus()
    {
        $stim = array(1 => array('cue' => 'cue1'), 2 => array('cue' => 'cue2'));
        $this->obj->setExperiment(new Experiment(array(), $stim));
        
        $this->obj->update('item', '1');
        $this->assertEquals(array('cue' => 'cue1'), $this->obj->get('item'));
        
        $this->obj->update('item', '2');
        $this->assertEquals(array('cue' => 'cue2'), $this->obj->get('item'));
    }

    /**
     * @covers Collector\Trial::add
     * @covers Collector\Trial::update
     * @covers Collector\Trial::injectStimulus
     */
    public function testInjectStimulusSurvey()
    {
        $this->obj->update('trial type', 'survey');
        $this->obj->add('item', 'survey1.csv');
        $this->assertEquals('survey1.csv', $this->obj->get('item'));
    }
    
    /**
     * @covers Collector\Trial::getFromStimuli
     */
    public function testGetFromStimuli()
    {
        $stim = array(1 => array('cue' => 'cue1'), 2 => array('cue' => 'cue2'));
        $this->obj->setExperiment(new Experiment(array(), $stim));
        $this->obj->add('item', 1);
        $this->assertEquals('cue1', $this->obj->getFromStimuli('Cue'));
    }
    
    /**
     * @covers Collector\Trial::getDebugInfo
     */
    public function testGetDebugInfo()
    {
        $expected = $this->obj->getDebugInfo();
        $this->assertNull($expected['position']);
        $this->assertInternalType('array', $expected['data']);
        $this->assertSame($this->obj, $expected['object']);
    }
}

