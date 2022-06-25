<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use GuzzleHttp\ClientInterface;

class GoogleGeocoder implements GeocoderInterface
{
    private const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    private string $apiKey;

    private ClientInterface $client;

    public function __construct(string $googleGeocodingApiKey, ClientInterface $client)
    {
        $this->apiKey = $googleGeocodingApiKey;
        $this->client = $client;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $street = $address->getStreet();
        $country = $address->getCountry();
        $city = $address->getCity();
        $postcode = $address->getPostcode();

        $params = [
            'query' => [
                'address' => $street,
                'components' => implode('|', ["country:{$country}", "locality:{$city}", "postal_code:{$postcode}"]),
                'key' => $this->apiKey
            ]
        ];

        $response = $this->client->get(self::URL, $params);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (count($data['results']) === 0) {
            return null;
        }

        $firstResult = $data['results'][0];

        if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
            return null;
        }

        return new Coordinates($firstResult['geometry']['location']['lat'], $firstResult['geometry']['location']['lng']);
    }
}
