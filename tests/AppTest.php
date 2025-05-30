<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{

    public function testExample()
    {
        $this->assertTrue(true, 'This is a simple test to ensure the testing framework is working.');
        $this->assertFalse(false, 'This is another simple test to ensure the testing framework is working.');
        $this->assertEquals(4, 2 + 2, '2 + 2 should equal 4');
        $this->assertNotEquals(5, 2 + 2, '2 + 2 should not equal 5');
        $this->assertGreaterThan(3, 5, '5 is greater than 3');
        $this->assertLessThan(10, 5, '5 is less than 10');
        $this->assertEmpty([], 'This array should be empty');
        $this->assertNotEmpty([1, 2, 3], 'This array should not be empty');
        $this->assertCount(3, [1, 2, 3], 'This array should have 3 elements');
        $this->assertSame('Hello', 'Hello', 'These two strings should be the same');
        $this->assertNotSame('Hello', 'World', 'These two strings should not be the same');
        $this->assertInstanceOf(\stdClass::class, new \stdClass(), 'This should be an instance of stdClass');
    }

    public function testAnotherExample()
    {
        $this->assertEquals(2, 1 + 1, '1 + 1 should equal 2');
    }
    public function testStringContains()
    {
        $this->assertStringContainsString('Hello', 'Hello, World!', 'The string should contain "Hello"');
    }
    public function testArrayHasKey()
    {
        $array = ['key' => 'value'];
        $this->assertIsArray($array, 'This should be an array');
        $this->assertArrayHasKey('key', $array, 'The array should have the key "key"');
        $this->assertArrayNotHasKey('nonexistent', $array, 'The array should not have the key "nonexistent"');
    }   

}
