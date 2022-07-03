<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResolvedAddress;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

interface ResolvedAddressRepositoryInterface
{
    public function getByAddress(Address $address): ?ResolvedAddress;

    public function saveResolvedAddress(Address $address, ?Coordinates $coordinates): void;
}
