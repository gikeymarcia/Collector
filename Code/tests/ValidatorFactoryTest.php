<?php

namespace Collector;

/**
 * Description of ValidatorFactoryTest
 *
 * @author Adam
 */
class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function getProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createFromDir
     */
    public function testCreateFromDir()
    {
        $validators = ValidatorFactory::createFromDir(__DIR__.'/validatordir1');
        foreach (array('testtrialtype1', 'testtrialtype2') as $trialtype) {
            $this->assertArrayHasKey($trialtype, $validators);
            $this->assertInstanceOf('Collector\Validator', $validators[$trialtype]);
        }
    }
    
    /**
     * @covers Collector\ValidatorFactory::createFromDirs
     */
    public function testCreateFromDirsLoose()
    {
        $validators = ValidatorFactory::createFromDirs(
                array(__DIR__.'/validatordir1', __DIR__.'/validatordir2'),
                true
        );
        
        foreach ($validators as $validator) {
            $this->assertInstanceOf('Collector\Validator', $validator);
        }
        $this->assertCount(2, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(2, $this->getProperty($validators['testtrialtype2'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype3'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::createFromDirs
     */
    public function testCreateFromDirsStrict()
    {
        $validators = ValidatorFactory::createFromDirs(
                array(__DIR__.'/validatordir1', __DIR__.'/validatordir2'),
                false
        );
        
        foreach ($validators as $validator) {
            $this->assertInstanceOf('Collector\Validator', $validator);
        }
        $this->assertCount(1, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype2'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype3'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromDir
     * @covers Collector\ValidatorFactory::filterSpecific
     */
    public function testCreateSpecificFromDirSingle()
    {
        $validator1 = ValidatorFactory::createSpecificFromDir('dne',
                __DIR__.'/validatordir1');
        $this->assertNull($validator1);
        
        $validator2 = ValidatorFactory::createSpecificFromDir('testtrialtype1',
                __DIR__.'/validatordir1');
        $this->assertInstanceOf('Collector\Validator', $validator2);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromDir
     * @covers Collector\ValidatorFactory::createFromDirs
     * @covers Collector\ValidatorFactory::filterSpecific
     */
    public function testCreateSpecificFromDirMultiple()
    {
        $validators = ValidatorFactory::createSpecificFromDir(
            array('testtrialtype1', 'dne', 'testtrialtype2'),
            __DIR__.'/validatordir1'
        );
        $this->assertNull($validators['dne']);
        $this->assertInstanceOf('Collector\Validator', $validators['testtrialtype1']);
        $this->assertInstanceOf('Collector\Validator', $validators['testtrialtype2']);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromDirs
     * @covers Collector\ValidatorFactory::createFromDirs
     * @covers Collector\ValidatorFactory::filterSpecific
     */
    public function testCreateSpecificFromDirsSingle()
    {
        $validator1 = ValidatorFactory::createSpecificFromDirs('dne', 
                array(__DIR__.'/validatordir1', __DIR__.'/validatordir2')
        );
        $this->assertNull($validator1);
        
        $validator2 = ValidatorFactory::createSpecificFromDirs('testtrialtype3', 
                array(__DIR__.'/validatordir1', __DIR__.'/validatordir2')
        );
        $this->assertInstanceOf('Collector\Validator', $validator2);
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromDirs
     * @covers Collector\ValidatorFactory::filterSpecific
     */
    public function testCreateSpecificFromDirsMultipleStrict()
    {
        $validators = ValidatorFactory::createSpecificFromDirs(
            array('testtrialtype1', 'dne', 'testtrialtype2'),
            array(__DIR__.'/validatordir1', __DIR__.'/validatordir2')
        );
        $this->assertNull($validators['dne']);
        $this->assertCount(1, $this->getProperty($validators['testtrialtype1'], 'checks'));
        $this->assertCount(1, $this->getProperty($validators['testtrialtype2'], 'checks'));
    }
    
    /**
     * @covers Collector\ValidatorFactory::createSpecificFromDirs
     */
    public function testCreateSpecificFromDirsMultipleLoose()
    {
        $validators = ValidatorFactory::createSpecificFromDirs(
            array('testtrialtype1', 'dne', 'testtrialtype3'),
            array(__DIR__.'/validatordir1', __DIR__.'/validatordir2'),
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
        $basepath = str_replace('\\', '/', __DIR__.'/validatordir1');
        $expected = array(
            $basepath.'/testtrialtype1/validator.php',
            $basepath.'/testtrialtype2/validator.php',
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
     * @covers Collector\ValidatorFactory::createFromDir
     * @covers Collector\ValidatorFactory::mergeGroup
     */
    public function testMergeGroupLoose()
    {
        $group1 = ValidatorFactory::createFromDir(__DIR__.'/validatordir1');
        $group2 = ValidatorFactory::createFromDir(__DIR__.'/validatordir2');
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
        $group1 = ValidatorFactory::createFromDir(__DIR__.'/validatordir1');
        $group2 = ValidatorFactory::createFromDir(__DIR__.'/validatordir2');
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
