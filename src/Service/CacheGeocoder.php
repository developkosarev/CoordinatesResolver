<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ResolvedAddressRepository;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

class CacheGeocoder implements GeocoderInterface
{
    private ResolvedAddressRepository $repository;

    public function __construct(ResolvedAddressRepository $repository)
    {
        $this->repository = $repository;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $row = $this->repository->getByAddress($address);

        if ($row === null) {
            return null;
        }

        return new Coordinates((float)$row->getLat(), (float)$row->getLng());
    }

    public function saveResolvedAddress(Address $address, ?Coordinates $coordinates): void
    {

    }
}
