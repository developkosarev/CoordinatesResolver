<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeocoderControllerTest extends WebTestCase
{
    public function testCoordinates()
    {
        $client = static::createClient();
        $client->request('GET', '/geocoder');

        $this->assertResponseIsSuccessful();
    }
}
