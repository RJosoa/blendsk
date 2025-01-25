<?php
// filepath: tests/GetCategoriesTest.php
namespace App\Tests;

use App\Tests\ApiTestCase;

class GetCategoriesTest extends ApiTestCase
{
    public function testSomething(): void
    {
        $response = static::createClient()->request('GET', '/api/categories');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['id' => '/api/categories']);
    }
}
