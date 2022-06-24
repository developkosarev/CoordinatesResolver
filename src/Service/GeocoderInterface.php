<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;

interface GeocoderInterface
{
    public function geocode(Address $address): ?Coordinates;
}
