<?php

namespace App\Tests\Service;

use App\Entity\ResolvedAddress;
use App\Repository\ResolvedAddressRepository;
use App\Service\GeocoderMiddleware;
use App\ValueObject\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GeocoderMiddlewareTest extends KernelTestCase
{
    public function testGeocode()
    {
        $resolvedAddress = new ResolvedAddress();
        $resolvedAddress->setLat('1');
        $resolvedAddress->setLng('1');

        $resolvedAddressRepository = $this->createMock(ResolvedAddressRepository::class);
        $resolvedAddressRepository->expects($this->any())
            ->method('getByAddress')
            ->willReturn($resolvedAddress);

        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');

        $geocoder = new GeocoderMiddleware($resolvedAddressRepository, []);
        $coordinates = $geocoder->geocode($address);

        $this->assertEquals(1, $coordinates->getLat());
        $this->assertEquals(1, $coordinates->getLng());
    }
}
