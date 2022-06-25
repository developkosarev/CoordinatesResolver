<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ResolvedAddressRepository;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

class GeocoderMiddleware implements GeocoderInterface
{
    private ResolvedAddressRepository $repository;

    private array $stack = [];

    public function __construct(ResolvedAddressRepository $repository, array $stack)
    {
        $this->repository = $repository;
        $this->stack = $stack;
    }

    public function geocode(Address $address): ?Coordinates
    {
        //$coordinates = $this->geocoder->geocode($address);

        $row = $this->repository->getByAddress($address);

        if ($row === null) {
            return null;
        }

        return new Coordinates((float)$row->getLat(), (float)$row->getLng());
    }
}