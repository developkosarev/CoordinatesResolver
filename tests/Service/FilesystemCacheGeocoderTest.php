<?php

namespace App\Tests\Service;

use App\Service\FilesystemGeocoder;
use App\ValueObject\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class FilesystemCacheGeocoderTest extends KernelTestCase
{
    public function testColdCacheGeocode()
    {
        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');

        $cache = new ArrayAdapter();
        $redisGeocoder = new FilesystemGeocoder($cache);
        $coordinates = $redisGeocoder->geocode($address);

        $this->assertNull($coordinates);
    }

    public function testWarmCacheGeocode()
    {
        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01113');

        $key = "country={$address->getCountry()};city={$address->getCity()};street={$address->getStreet()};postalCode={$address->getPostcode()}";

        $cache = new ArrayAdapter();
        $cache->get($key, function (ItemInterface $item) {
            $item->expiresAfter(3600);

            return ['lat' => 1, 'lng' => 2];
        });

        $redisGeocoder = new FilesystemGeocoder($cache);
        $coordinates = $redisGeocoder->geocode($address);

        $this->assertEquals(1, $coordinates->getLat());
        $this->assertEquals(2, $coordinates->getLng());
    }
}
