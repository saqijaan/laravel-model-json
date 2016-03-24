<?php

class JsonModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * Assert that defined JSON attributes are made available.
     */
    public function testInspectJson()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the inspect call
        $mock->inspectJson();

        // Assert that the column was properly made available and
        // contains the data we provided
        $this->assertTrue(is_callable([$mock, 'testColumn']));
        $this->assertEquals($mock->testColumn()->foo, 'bar');
        $this->assertArrayHasKey('testColumn', $mock->toArray());
        $this->assertTrue(is_array($mock->toArray()['testColumn']));
        $this->assertContains('bar', $mock->toArray()['testColumn']);
    }

    /**
     * Assert that defined JSON attributes are made available.
     */
    public function testInspectJsonWithEmpty()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', '');

        // Execute the inspect call
        $mock->inspectJson();

        // Assert that the column was properly made available and
        // contains the data we provided
        $this->assertTrue(is_callable([$mock, 'testColumn']));
    }

    /**
     * Assert that JSON attributes can be changed, and new attribute added.
     */
    public function testSetAttribute()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the insepect call
        $mock->inspectJson();

        $mock->testColumn()->foo = 'bar2';
        $mock->testColumn()->foo2 = 'bar3';

        // Assert that the column was properly made available and
        // contains the data we provided
        $this->assertEquals($mock->testColumn()->foo, 'bar2');
        $this->assertEquals($mock->testColumn()->foo2, 'bar3');
        $this->assertArrayHasKey('foo2', $mock->toArray()['testColumn']);
    }

    /**
     * Assert that JSON attribute can handle multidimensions.
     */
    public function testMultiDimension()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => ['bar1' => ['bar2'], 'bar3' => 'bar4']]));

        // Execute the insepect call
        $mock->inspectJson();

        // Assert that the column was properly made available and
        // contains the data we provided
        $this->assertEquals($mock->testColumn()->foo['bar1'][0], 'bar2');
        $this->assertEquals($mock->testColumn()->foo['bar3'], 'bar4');
    }

    /**
     * Assert that JSON attribute can handle multidimensional updates.
     */
    public function testUpdateMultiDimension()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => ['bar1' => ['bar2'], 'bar3' => 'bar4']]));

        // Execute the insepect call
        $mock->inspectJson();

        $mock->testColumn()->foo['bar3'] = ['bar4', 'bar5' => 'bar6'];

        // Assert that the column was properly made available and
        // contains the data we provided
        $this->assertEquals($mock->testColumn()->foo['bar3'][0], 'bar4');
        $this->assertEquals($mock->testColumn()->foo['bar3']['bar5'], 'bar6');
        $this->assertEquals($mock->toArray()['testColumn']['foo']['bar3']['bar5'], 'bar6');
    }

    /**
     * Assert that JSON attribute reports the changes correctly.
     */
    public function testDirtyJson()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the insepect call
        $mock->inspectJson();

        // This should not be dirty
        $this->assertArrayNotHasKey('foo', $mock->getDirty(true));

        $mock->testColumn()->foo = 'bar2';

        // This should be dirty
        $this->assertArrayHasKey('testColumn', $mock->getDirty(true));
        $this->assertArrayHasKey('testColumn.foo', $mock->getDirty(true));
    }

    /**
     * Assert that JSON attribute reports changes correctly.
     */
    public function testDirtyJsonMultiDimension()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the insepect call
        $mock->inspectJson();

        // This should not be dirty
        $this->assertArrayNotHasKey('foo', $mock->getDirty(true));

        $mock->testColumn()->foo = 'bar2';
        $mock->testColumn()->foo2 = 'bar2';

        // This should be dirty
        $this->assertArrayHasKey('testColumn.foo2', $mock->getDirty(true));

        // Check assigning to a new variable with a multidimensional array
        $mock->testColumn()->foo3['foo5'] = 'bar3';

        $this->assertArrayHasKey('testColumn.foo3', $mock->getDirty(true));
        $this->assertArrayHasKey('foo5', $mock->getDirty(true)['testColumn.foo3']);
    }

    /**
     * Assert that JSON attribute can set defaults.
     */
    public function testDefaults()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setJsonColumnDefaults('testColumn', ['bar2' => 'bar3', 'bar3' => 'bar5']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar', 'bar3' => 'bar4']));

        $this->assertArrayHasKey('bar2', $mock->toArray()['testColumn']);
        $this->assertEquals($mock->testColumn()->bar2, 'bar3');
        $this->assertEquals($mock->testColumn()->bar3, 'bar4');
    }

    /**
     * Assert that JSON attribute can set defaults.
     */
    public function testNoSavingDefaults()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setJsonColumnOptions('testColumn', ['no_saving_default_values' => true]);
        $mock->setJsonColumnDefaults('testColumn', ['foo2' => 'bar2', 'foo3' => 'bar3']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        $mock->testColumn()->foo3 = 'bar4';

        $this->assertArrayNotHasKey('testColumn.foo2', $mock->getDirty(true));
        $this->assertArrayHasKey('testColumn.foo3', $mock->getDirty(true));
    }

    /**
     * Assert that JSON attribute can have isset used.
     */
    public function testIsset()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar', 'foo2' => ['bar2' => 'bar3']]));

        $this->assertTrue(isset($mock->testColumn()->foo));
        $this->assertFalse(isset($mock->testColumn()->foo3));
        $this->assertTrue(isset($mock->testColumn()->foo2['bar2']));
    }

    /**
     * Assert that JSON attribute can have unset used.
     */
    public function testUnset()
    {
        // Mock the model with data
        $mock = new MockJsonModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setCastsColumns(['testColumn' => 'json']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar', 'foo2' => ['bar2' => 'bar3']]));

        unset($mock->testColumn()->foo);
        unset($mock->testColumn()->foo2['bar2']);

        $this->assertArrayNotHasKey('foo', $mock->toArray()['testColumn']);
        $this->assertArrayNotHasKey('bar2', $mock->toArray()['testColumn']['foo2']);
    }
}
