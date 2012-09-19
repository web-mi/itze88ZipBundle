<?php

namespace WEBMI\TrackingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/itze88');

        $this->assertTrue($crawler->filter('html:contains("Hello Itze")')->count() > 0);
    }
}
