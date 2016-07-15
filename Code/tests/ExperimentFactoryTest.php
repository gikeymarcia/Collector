<?php

namespace Collector;

class ExperimentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pathfinder = new \Pathfinder();
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
            'procedure' => array(array('b' => 2, 'trial type' => 'cue')),
            'stimuli' => array('c' => 3),
            'pathfinder' => $this->pathfinder,
        );
        $expt = ExperimentFactory::create(
            $expected['condition'],
            $expected['procedure'],
            $expected['stimuli'],
            $expected['pathfinder']
        );

        $this->assertEquals($expected['condition'], $expt->getCondition());
        $this->assertEquals($expected['stimuli'], $expt->getStimuli());
        $this->assertEquals($expected['pathfinder'], $expt->getPathfinder());
        $this->assertCount(1, $expt);
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::separatePostTrials
     */
    public function testCreateWithPost()
    {
        $expt = ExperimentFactory::create(
            array(),
            array(array(
                'a' => 2, 'trial type' => 'cue',
                'post 1 a' => 1, 'post 1 trial type' => 'cue'
            )),
            array(),
            $this->pathfinder
        );
        $this->assertEquals(1, $expt->getTrial()->getPostTrial(1)->get('a', true));
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::addRelatedFiles
     */
    public function testCreateWithNoTrialTypeInMain()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/type \'\' at position 1/');
        ExperimentFactory::create(
            array(),
            array(array('trial type' => 'cue'), array('a' => 2, 'post 1 a' => 1)),
            array(),
            $this->pathfinder
        );
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::addRelatedFiles
     */
    public function testCreateWithNoTrialTypeInPost()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/type \'\' at position 0 \(post: 1\)/');
        ExperimentFactory::create(
            array(),
            array(array('trial type' => 'cue', 'post 1 a' => 1)),
            array(),
            $this->pathfinder
        );
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::removeOffPostTrials
     */
    public function testCreateWithPostTrialOffUsingValueOff()
    {
        $expt = ExperimentFactory::create(
            array(),
            array(
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => 'off', 'post 2 trial type' => 'off'),
                array('trial type' => 'cue', 'post 1 trial type' => 'off', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => 'off'),
            ),
            array(),
            $this->pathfinder
        );

        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(1));
        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(2));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(2));
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::removeOffPostTrials
     */
    public function testCreateWithPostTrialOffUsingValueNo()
    {
        $expt = ExperimentFactory::create(
            array(),
            array(
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => 'no', 'post 2 trial type' => 'no'),
                array('trial type' => 'cue', 'post 1 trial type' => 'no', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => 'no'),
            ),
            array(),
            $this->pathfinder
        );

        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(1));
        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(2));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(2));
    }

    /**
     * @covers Collector\ExperimentFactory::create
     * @covers Collector\ExperimentFactory::removeOffPostTrials
     */
    public function testCreateWithPostTrialOffUsingValueBlank()
    {
        $expt = ExperimentFactory::create(
            array(),
            array(
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => '', 'post 2 trial type' => ''),
                array('trial type' => 'cue', 'post 1 trial type' => '', 'post 2 trial type' => 'cue'),
                array('trial type' => 'cue', 'post 1 trial type' => 'cue', 'post 2 trial type' => ''),
            ),
            array(),
            $this->pathfinder
        );

        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(1));
        $this->assertNotNull($expt->getTrialAbsolute(0)->getPostTrialAbsolute(2));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(1)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(2)->getPostTrialAbsolute(2));
        $this->assertNotNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(1));
        $this->assertNull($expt->getTrialAbsolute(3)->getPostTrialAbsolute(2));
    }
}
