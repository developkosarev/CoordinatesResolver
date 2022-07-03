<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;

interface GeocoderServiceInterface
{
    public function setCacheEnabled(bool $cacheEnabled): self;

    public function addGeocoder(GeocoderInterface $geocoder): self;

    public function geocode(Address $address): ?Coordinates;
}
