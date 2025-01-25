<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testIsHomePageExist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span', 'Personalize');
    }


    public function testLoginLinkRedirectsToLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $link = $crawler->selectLink('To Login')->link();
        $client->click($link);

        $this->assertResponseIsSuccessful();
    }

}
