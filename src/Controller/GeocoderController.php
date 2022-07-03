<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GeocoderService;
use App\ValueObject\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeocoderController extends AbstractController
{
    private GeocoderService $geocoderService;

    public function __construct(GeocoderService $geocoderService)
    {
        $this->geocoderService = $geocoderService;
    }

    /**
     * @Route(path="/geocoder", name="geocoder_service")
     * @param Request $request
     * @return Response
     */
    public function geocodeAction(Request $request): Response
    {
        $country = $request->get('countryCode', 'lt');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postcode', '01112');

        $address = new Address($country, $city, $street, $postcode);

        $coordinates = $this->geocoderService
            ->setCacheEnabled(false)
            ->geocode($address);

        if (null === $coordinates) {
            return new JsonResponse([]);
        }

        return new JsonResponse(['lat' => $coordinates->getLat(), 'lng' => $coordinates->getLng()]);
    }
}