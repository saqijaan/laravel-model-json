<?php

class JsonModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * Assert that defined JSON attributes are properly parsed and exposed through
     * mutators.
     */
    public function testInspectJson()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the insepect call
        $mock->inspectJson();

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertTrue($mock->hasGetMutator('foo'));
        $this->assertContains('foo', $mock->getMutatedAttributes());
        $this->assertArrayNotHasKey('testColumn', $mock->toArray());
        $this->assertArrayHasKey('foo', $mock->toArray());
        $this->assertEquals($mock->testColumn()->foo, 'bar');
    }

    /**
     * Assert that JSON attributes can be set through mutators.
     */
    public function testSetAttribute()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the insepect call
        $mock->inspectJson();

        $mock->testColumn()->foo1 = 'baz';
        $mock->setJsonAttribute('testColumn', 'fizz', 'buzz');

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertEquals($mock->testColumn()->foo1, 'baz');
        $this->assertEquals($mock->testColumn()->fizz, 'buzz');
    }
}
