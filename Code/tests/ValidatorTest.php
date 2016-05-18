<?php

namespace Collector;

/**
 * Description of ValidatorTest
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $expt = new Experiment();
        $this->trial = $expt->addTrial(new MainTrial(array('key1' => 'set')));
        $this->passCheck = __DIR__ . './validators/passCheck.php';
        $this->failCheck = __DIR__ . './validators/failCheck.php';
        $this->invalid = __DIR__ . './validators/invalidValidator.php';
        $this->obj = new Validator($this->passCheck);
    }
    
    /**
     * @covers Collector\Validator::validate
     * @covers Collector\Validator::checkReturnType
     */
    public function testValidatePassing()
    {
        $this->assertEquals('set', $this->trial->get('key1'));
        $this->assertEmpty($this->obj->validate($this->trial));
    }
    
    /**
     * @covers Collector\Validator::__construct
     * @covers Collector\Validator::validate
     */
    public function testValidateFailing()
    {
        $obj = new Validator($this->failCheck);
        
        $expected = array(array(
            'message' => 'Failed!', 
            'info' => $this->trial->getDebugInfo())
        );
        $this->assertEquals($expected, $obj->validate($this->trial));
    }
    
    /**
     * @covers Collector\Validator::checkReturnType
     */
    public function testBadValidatorError()
    {
        $obj = new Validator($this->invalid);
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Validator check functions must return '
            . 'true or null for valid trials, and must return an error message '
            . 'of the type string for invalid trials ');
        $obj->validate(new MainTrial());
    }
    
    /**
     * @covers Collector\Validator::addCheck
     */
    public function testAddCheck()
    {
        $this->obj->addCheck($this->failCheck);
        
        $expected = array(array(
            'message' => 'Failed!', 
            'info' => $this->trial->getDebugInfo())
        );
        $this->assertEquals($expected, $this->obj->validate($this->trial));        
    }
    
    /**
     * @covers Collector\Validator::addCheck
     * @covers Collector\Validator::getChecks
     */
    public function testGetChecks()
    {
        $this->obj->addCheck($this->failCheck);
        $checks = $this->obj->getChecks();
        
        $this->assertCount(2, $checks);
        $this->assertEquals($this->passCheck, $checks[0]);
        $this->assertEquals($this->failCheck, $checks[1]);
    }
    
    /**
     * @covers Collector\Validator::merge
     */
    public function testMerge()
    {
        $new = new Validator($this->failCheck);
        $this->obj->merge($new);
        
        $checks = $this->obj->getChecks();
        $this->assertCount(2, $checks);
        $this->assertEquals($this->passCheck, $checks[0]);
        $this->assertEquals($this->failCheck, $checks[1]);
    }
}
