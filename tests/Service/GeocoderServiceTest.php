<?php

namespace App\Tests\Service;

use App\Entity\ResolvedAddress;
use App\Repository\ResolvedAddressRepositoryInterface;
use App\Service\GeocoderInterface;
use App\Service\GeocoderService;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GeocoderServiceTest extends KernelTestCase
{
    public function testGeocodeByDefault()
    {
        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');

        $resolvedAddress = (new ResolvedAddress())
            ->setLat('1')
            ->setLng('1');

        $coordinates = new Coordinates(1, 1);

        $resolvedAddressRepository = $this->createMock(ResolvedAddressRepositoryInterface::class);
        $resolvedAddressRepository
            ->expects($this->any())
            ->method('getByAddress')
            ->willReturn($resolvedAddress);

        $nullGeocoder = $this->createMock(GeocoderInterface::class);
        $nullGeocoder
            ->expects($this->any())
            ->method('geocode')
            ->willReturn(null);

        $vendorGeocoder = $this->createMock(GeocoderInterface::class);
        $vendorGeocoder
            ->expects($this->any())
            ->method('geocode')
            ->willReturn($coordinates);

        $result = (new GeocoderService($resolvedAddressRepository))
            ->addGeocoder($nullGeocoder)
            ->addGeocoder($vendorGeocoder)
            ->geocode($address);

        $this->assertEquals(1, $result->getLat());
        $this->assertEquals(1, $result->getLng());
    }

    public function testGeocodeNull()
    {
        $address = new Address('lt', 'vilnius', 'jasinskio 16', '01112');

        $resolvedAddress = (new ResolvedAddress())
            ->setLat('1')
            ->setLng('1');

        $resolvedAddressRepository = $this->createMock(ResolvedAddressRepositoryInterface::class);
        $resolvedAddressRepository
            ->expects($this->any())
            ->method('getByAddress')
            ->willReturn($resolvedAddress);

        $nullGeocoder = $this->createMock(GeocoderInterface::class);
        $nullGeocoder
            ->expects($this->any())
            ->method('geocode')
            ->willReturn(null);

        $result = (new GeocoderService($resolvedAddressRepository))
            ->setCacheEnabled(false)
            ->addGeocoder($nullGeocoder)
            ->geocode($address);

        $this->assertNull($result);
    }
}
