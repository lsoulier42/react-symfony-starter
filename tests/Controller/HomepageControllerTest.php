<?php

namespace App\Tests\Controller;

use App\Tests\AbstractWebTestCase;

class HomepageControllerTest extends AbstractWebTestCase
{
    public function testHomepageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Homepage', $client->getResponse()->getContent());
    }
}
