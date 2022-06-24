<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoordinatesControllerTest extends WebTestCase
{
    public function testCoordinates()
    {
        $client = static::createClient();
        $client->request('GET', '/coordinates');

        $this->assertResponseIsSuccessful();
    }
}
