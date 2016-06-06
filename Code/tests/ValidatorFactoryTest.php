<?php

namespace Collector;

/**
 * Description of ValidatorFactoryTest
 *
 * @author Adam
 */
class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dirOne = __DIR__.'/validators/validatordir1';
        $this->dirTwo = __DIR__.'/validators/validatordir2';
    }
    
    protected function getProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecific
     * @covers Collector\ValidatorFactory::filter
     */
    public function testCreateSpecificFromDirSingle()
    {
        $validator1 = ValidatorFactory::createSpecific('dne', $this->dirOne);
        $this->assertNull($validator1);
        
        $validator2 = ValidatorFactory::createSpecific('testtrialtype1',
                $this->dirOne);
        $this->assertInstanceOf('Collector\Validator', $validator2);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecific
     * @covers Collector\ValidatorFactory::filter
     */
    public function testCreateSpecificFromDirMultiple()
    {
        $validators = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'dne', 'testtrialtype2'),
            $this->dirOne
        );
        $this->assertNull($validators['dne']);
        $this->assertInstanceOf('Collector\Validator', $validators['testtrialtype1']);
        $this->assertInstanceOf('Collector\Validator', $validators['testtrialtype2']);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecific
     * @covers Collector\ValidatorFactory::filter
     */
    public function testCreateSpecificFromDirsSingle()
    {
        $validator1 = ValidatorFactory::createSpecific('dne', 
                array($this->dirOne, $this->dirTwo)
        );
        $this->assertNull($validator1);
        
        $validator2 = ValidatorFactory::createSpecific('testtrialtype3', 
                array($this->dirOne, $this->dirTwo)
        );
        $this->assertInstanceOf('Collector\Validator', $validator2);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecific
     * @covers Collector\ValidatorFactory::filter
     */
    public function testCreateSpecificFromDirsMultipleStrict()
    {
        $validators = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'dne', 'testtrialtype2'),
            array($this->dirOne, $this->dirTwo)
        );
        $this->assertNull($validators['dne']);
        $this->assertCount(1, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype2'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromMultipleDirs
     */
    public function testCreateSpecificFromDirsMultipleLoose()
    {
        $validators = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'dne', 'testtrialtype3'),
            array($this->dirOne, $this->dirTwo),
            true
        );
        $this->assertNull($validators['dne']);
        $this->assertCount(2, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype3'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::getPaths
     */
    public function testGetPaths()
    {
        $basepath = str_replace('\\', '/', $this->dirOne);
        $expected = array(
            'testtrialtype1' => $basepath.'/testtrialtype1/validator.php',
            'testtrialtype2' => $basepath.'/testtrialtype2/validator.php',
        );
        $this->assertEquals($expected, ValidatorFactory::getPaths($basepath));
    }
    
    /**
     * @covers Collector\ValidatorFactory::merge
     */
    public function testMerge()
    {
        $validator1 = new Validator(function (Trial $trial) {
            if (!$trial->get('key exists')) {
                return 'Failed!';
            }
        });
        $validator2 = new Validator(function (Trial $trial) {
            if (!$trial->get('key exists')) {
                return 'Failed!';
            }
        });
        
        foreach(array($validator1, $validator2) as $validator) {
            $this->assertCount(1, $this->getProperty($validator, 'checks'));
        }
        $merged = ValidatorFactory::merge($validator1, $validator2);
        $this->assertCount(1, $this->getProperty($validator1, 'checks'));
        $this->assertCount(1, $this->getProperty($validator2, 'checks'));
        $this->assertCount(2, $this->getProperty($merged, 'checks'));
        
    }

    /**
     * @covers Collector\ValidatorFactory::createSpecific
     * @covers Collector\ValidatorFactory::mergeGroup
     */
    public function testMergeGroupLoose()
    {
        $group1 = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'testtrialtype2'), $this->dirOne);
        $group2 = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'testtrialtype2', 'testtrialtype3'), $this->dirTwo);
        $validators = ValidatorFactory::mergeGroup($group1, $group2, true);
        
        $this->assertCount(3, $validators);
        foreach ($validators as $validator) {
            $this->assertInstanceOf('Collector\Validator', $validator);
        }
        $this->assertCount(2, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(2, $this->getProperty($validators['testtrialtype2'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype3'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::mergeGroup
     */
    public function testMergeGroupStrict()
    {
        $group1 = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'testtrialtype2'), $this->dirOne);
        $group2 = ValidatorFactory::createSpecific(
            array('testtrialtype1', 'testtrialtype2', 'testtrialtype3'), $this->dirTwo);
        $validators = ValidatorFactory::mergeGroup($group1, $group2, false);
        
        $this->assertCount(3, $validators);
        foreach ($validators as $validator) {
            $this->assertInstanceOf('Collector\Validator', $validator);
        }
        $this->assertCount(1, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype2'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype3'], 'checks'));
    }
}
