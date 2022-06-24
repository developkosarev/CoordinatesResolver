<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ResolvedAddressRepository;
use App\Service\GeocoderInterface;
use App\ValueObject\Address;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoordinatesController extends AbstractController
{
    private GeocoderInterface $geocoder;

    public function __construct(GeocoderInterface $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    /**
     * @Route(path="/coordinates", name="geocode")
     * @param Request $request
     * @return Response
     */
    public function geocodeAction(Request $request, ResolvedAddressRepository $repository): Response
    {
        $country = $request->get('countryCode', 'lt');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postcode', '01112');

        $address = new Address($country, $city, $street, $postcode);

        $row = $repository->getByAddress($address);

        $coordinates = $this->geocoder->geocode($address);

        if (null === $coordinates) {
            return new JsonResponse([]);
        }

        $repository->saveResolvedAddress($address, $coordinates);

        return new JsonResponse(['lat' => $coordinates->getLat(), 'lng' => $coordinates->getLng()]);
    }

    /**
     * @Route(path="/gmaps", name="gmaps")
     * @param Request $request
     * @return Response
     */
    public function gmapsAction(Request $request): Response
    {
        $country = $request->get('country', 'lithuania');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postcode', '01112');

        $apiKey = $_ENV["GOOGLE_GEOCODING_API_KEY"];

        $params = [
            'query' => [
                'address' => $street,
                'components' => implode('|', ["country:{$country}", "locality:{$city}", "postal_code:{$postcode}"]),
                'key' => $apiKey
            ]
        ];

        $client = new Client();

        $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', $params);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (count($data['results']) === 0) {
            return new JsonResponse([]);
        }

        $firstResult = $data['results'][0];

        if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
            return new JsonResponse([]);
        }

        return new JsonResponse($firstResult['geometry']['location']);
    }

    /**
     * @Route(path="/hmaps", name="hmaps")
     * @param Request $request
     * @return Response
     */
    public function hmapsAction(Request $request): Response
    {
        $country = $request->get('country', 'lithuania');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postcode', '01112');

        $apiKey = $_ENV["HEREMAPS_GEOCODING_API_KEY"];

        $params = [
            'query' => [
                'qq' => implode(';', ["country={$country}", "city={$city}", "street={$street}", "postalCode={$postcode}"]),
                'apiKey' => $apiKey
            ]
        ];

        $client = new Client();

        $response = $client->get('https://geocode.search.hereapi.com/v1/geocode', $params);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (count($data['items']) === 0) {
            return new JsonResponse([]);
        }

        $firstItem = $data['items'][0];

        if ($firstItem['resultType'] !== 'houseNumber') {
            return new JsonResponse([]);
        }

        return new JsonResponse($firstItem['position']);
    }
}