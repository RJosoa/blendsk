<?php

namespace App\Tests;

use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class PostFormValidationTest extends TestCase
{
    public function testValidDataType()
    {
        $post = new Post();
        $post->setTitle('Hello World');
        $post->setContent('This is a test content');

        $this->assertIsString($post->getTitle());
        $this->assertIsString($post->getContent());
    }

    public function testInvalidDataType()
    {
        $post = new Post();
        $post->setTitle(true);
        $post->setContent(1111);

        $this->assertIsString($post->getTitle());
        $this->assertIsString($post->getContent());
    }

    public function testInvalidData()
    {
        $post = new Post();
        $post->setTitle('Hello World');
        $post->setContent('1111');

        $this->assertSame('', $post->getTitle());
        $this->assertSame('111', $post->getContent());
    }
}
