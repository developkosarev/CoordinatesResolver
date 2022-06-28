<?php

namespace App\Tests\Service;

use App\Service\CacheGeocoder;
use App\ValueObject\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheGeocoderTest extends KernelTestCase
{
    public function testColdCacheGeocode()
    {
        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');

        $cache = new ArrayAdapter();
        $cacheGeocoder = new CacheGeocoder($cache);
        $coordinates = $cacheGeocoder->geocode($address);

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

        $cacheGeocoder = new CacheGeocoder($cache);
        $coordinates = $cacheGeocoder->geocode($address);

        $this->assertEquals(1, $coordinates->getLat());
        $this->assertEquals(2, $coordinates->getLng());
    }

//    public function testMyCacheGeocode()
//    {
//        $resolvedAddress = new ResolvedAddress();
//        $resolvedAddress->setLat('1');
//        $resolvedAddress->setLng('1');
//
//        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');
//
//        $cache = new ArrayAdapter();
//        $firstResult = $cache->get('my_cache_key', function (ItemInterface $item) {
//            $item->expiresAfter(3600);
//
//            return ['lat' => 1, 'lng' => 2];
//        });
//
//        var_dump($firstResult);
//
//        $secondResult = $cache->get('my_cache_key', function (ItemInterface $item) {
//            $item->expiresAfter(3600);
//
//            return ['lat' => 3, 'lng' => 4];
//        });
//
//        var_dump($secondResult);
//
//        $cacheGeocoder = new CacheGeocoder($cache);
//        $coordinates = $cacheGeocoder->geocode($address);
//
//        $this->assertEquals(1, 1);
//
//        //$this->assertEquals(1, $coordinates->getLat());
//        //$this->assertEquals(1, $coordinates->getLng());
//    }
}
