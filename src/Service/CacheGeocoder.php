<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheGeocoder implements GeocoderInterface
{
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $geocoderCachePool)
    {
        $this->cache = $geocoderCachePool;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $country = $address->getCountry();
        $city = $address->getCity();
        $street = $address->getStreet();
        $postcode = $address->getPostcode();

        $key = "country={$country};city={$city};street={$street};postalCode={$postcode}";

        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        $result = $item->get();

        return new Coordinates($result['lat'], $result['lng']);
    }

    //public function saveResolvedAddress(Address $address, ?Coordinates $coordinates): void
    //{
    //    $firstResult = $this->cache->get('my_cache_key', function (ItemInterface $item) {
    //        $item->expiresAfter(3600);
    //
    //        return null;
    //    });
    //}
}
