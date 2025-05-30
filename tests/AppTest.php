<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{

    public function testExample()
    {
        $this->assertTrue(true, 'This is a simple test to ensure the testing framework is working.');
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
        $this->assertArrayHasKey('key', $array, 'The array should have the key "key"');
        $this->assertArrayNotHasKey('nonexistent', $array, 'The array should not have the key "nonexistent"');
    }   

}
