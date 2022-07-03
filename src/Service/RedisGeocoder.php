<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class RedisGeocoder implements GeocoderInterface
{
    private const EXPIRES_AFTER = 60*60*24*365;

    private AdapterInterface $cache;

    public function __construct(AdapterInterface $geocoderCachePool)
    {
        $this->cache = $geocoderCachePool;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $key = $this->getKey($address);

        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        $result = $item->get();

        return new Coordinates($result['lat'], $result['lng']);
    }

    public function saveResolvedAddress(Address $address, ?Coordinates $coordinates): void
    {
        $key = $this->getKey($address);

        if ($coordinates == null) {
            $this->cache->deleteItem($key);
            return;
        }

        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            $item->set(['lat' => $coordinates->getLat(), 'lng' => $coordinates->getLng()]);
            $item->expiresAfter(self::EXPIRES_AFTER);
            $this->cache->save($item);
        }
    }

    protected function getKey(Address $address): string
    {
        $country = $address->getCountry();
        $city = $address->getCity();
        $street = $address->getStreet();
        $postcode = $address->getPostcode();

        return "country={$country};city={$city};street={$street};postalCode={$postcode}";
    }
}
