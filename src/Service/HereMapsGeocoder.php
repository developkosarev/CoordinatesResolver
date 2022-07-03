<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use GuzzleHttp\ClientInterface;

class HereMapsGeocoder implements GeocoderInterface
{
    private const URL = 'https://geocode.search.hereapi.com/v1/geocode';

    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $street = $address->getStreet();
        $country = $address->getCountry();
        $city = $address->getCity();
        $postcode = $address->getPostcode();

        $apiKey = $_ENV["HEREMAPS_GEOCODING_API_KEY"];

        $params = [
            'query' => [
                'qq' => implode(';', ["country={$country}", "city={$city}", "street={$street}", "postalCode={$postcode}"]),
                'apiKey' => $apiKey
            ]
        ];

        try {
            $response = $this->client->get(self::URL, $params);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (count($data['items']) === 0) {
            return null;
        }

        $firstItem = $data['items'][0];

        if ($firstItem['resultType'] !== 'houseNumber') {
            return null;
        }

        return new Coordinates($firstItem['position']['lat'], $firstItem['position']['lng']);
    }
}
