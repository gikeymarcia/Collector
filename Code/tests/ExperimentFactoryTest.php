<?php

namespace Collector;

class ExperimentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_PATH = new Pathfinder();
    }
    
    protected function getProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
    
    /**
     * @covers Collector\ExperimentFactory::create
     */
    public function testCreate()
    {
        $expected = array(
            'condition' => array('a' => 1),
            'procedure' => array(array('b' => 2)),
            'stimuli' => array('c' => 3),
            'validatorDir' => 'd',
        );
        $expt = ExperimentFactory::create(
            $expected['condition'],
            $expected['procedure'],
            $expected['stimuli'],
            $expected['validatorDir']
        );
        
        $this->assertEquals($expected['condition'], $expt->getCondition());
        $this->assertEquals($expected['stimuli'], $expt->getStimuli());
        $this->assertEquals($expected['validatorDir'], 
            $this->getProperty($expt, 'validatorDirs')
        );
        $this->assertCount(1, $expt);
    }
    
    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::separatePostTrials
     */
    public function testCreateWithPost()
    {
        $expt = ExperimentFactory::create([], [['b' => 2, 'post 1 a' => 1]]);
        $this->assertEquals(1, $expt->getTrial()->getPostTrial(1)->get('a'));
    }
}
