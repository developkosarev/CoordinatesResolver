<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ResolvedAddressRepositoryInterface;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use SplObjectStorage;

class GeocoderService implements GeocoderInterface
{
    private ResolvedAddressRepositoryInterface $repository;

    private bool $cacheEnabled = true;

    private array $geocoderStack = [];

    private splObjectStorage $splObjectStorage;

    public function __construct(ResolvedAddressRepositoryInterface $repository)
    {
        $this->splObjectStorage = new splObjectStorage();
        $this->repository = $repository;
    }

    public function setCacheEnabled(bool $cacheEnabled): self
    {
        $this->cacheEnabled = $cacheEnabled;
        return $this;
    }

    public function addGeocoder(GeocoderInterface $geocoder): self
    {
        $this->geocoderStack[] = $geocoder;
        $this->splObjectStorage->attach($geocoder, true);

        return $this;
    }

    public function geocode(Address $address): ?Coordinates
    {
        if ($this->cacheEnabled) {
            $coordinates = $this->geocodeFromCache($address);

            if ($coordinates !== null) {
                return $coordinates;
            }
        }

        $coordinates = $this->geocodeFromStack($address);

        if ($this->cacheEnabled) {
            $this->repository->saveResolvedAddress($address, $coordinates);
        }

        return $coordinates;
    }

    private function geocodeFromCache(Address $address): ?Coordinates
    {
        $row = $this->repository->getByAddress($address);

        if ($row === null) {
            return null;
        }

        return new Coordinates((float)$row->getLat(), (float)$row->getLng());
    }

    private function geocodeFromStack(Address $address): ?Coordinates
    {
        $this->splObjectStorage->rewind();
        while ($this->splObjectStorage->valid()) {
            $enabled = $this->splObjectStorage->getInfo();
            $geocoder = $this->splObjectStorage->current();

            $coordinate = $geocoder->geocode($address);

            if ($coordinate !== null) {
                return $coordinate;
            }
            $this->splObjectStorage->next();
        }

        return null;
    }

}