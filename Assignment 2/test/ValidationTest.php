<?php

use \Toolkit\{
    EmailValidator, NameValidator, NumberValidator, ValidatorSet
};


class ValidationTest extends PHPUnit_Framework_TestCase {

    public function testEmailFail()
    {
        $email = new EmailValidator('notanemail');
        $this->assertEquals(true, $email->hasError());
    }

    public function testEmailSuccess()
    {
        $email = new EmailValidator('me@here.com');
        $this->assertEquals(false, $email->hasError());
    }

    public function testEmailRequired()
    {
        $email = new EmailValidator('', true);
        $this->assertEquals('This is a required field', $email->getError());
    }

    public function testNameFail()
    {
        $name = new NameValidator('toomanycharacters---------');
        $this->assertEquals(true, $name->hasError());
    }

    public function testNameSuccess()
    {
        $name = new NameValidator('toomanycharacters--------');
        $this->assertEquals(false, $name->hasError());
    }

    public function testNameRequired()
    {
        $name = new NameValidator('', true);
        $this->assertEquals('This is a required field', $name->getError());
    }

    public function testAgeFailTooLow()
    {
        $age = new NumberValidator('20');
        $this->assertEquals(true, $age->hasError());
    }

    public function testAgeFailTooHigh()
    {
        $age = new NumberValidator('45');
        $this->assertEquals(true, $age->hasError());
    }

    public function testAgeSuccessLowBoundary()
    {
        $age = new NumberValidator('21');
        $this->assertEquals(false, $age->hasError());
    }

    public function testAgeSuccessHighBoundary()
    {
        $age = new NumberValidator('44');
        $this->assertEquals(false, $age->hasError());
    }

    public function testAgeRequired()
    {
        $age = new NumberValidator('', true);
        $this->assertEquals('This is a required field', $age->getError());
    }

    public function testSet()
    {
        $valSet = new ValidatorSet();
        $valSet->addItem(new EmailValidator('notanemail'), 'email');
        $email = $valSet->getItem('email');
        $this->assertEquals(true, $email->hasError());
    }

    public function testSetReturnErrors()
    {
        $valSet = new ValidatorSet();
        $valSet->addItem(new EmailValidator('notanemail'), 'email1');
        $valSet->addItem(new EmailValidator('me@here.com'), 'email2');
        $expected = array ('email1' => 'The email was not of the correct format.');
        $this->assertEquals($expected, $valSet->getErrors());
    }
}
