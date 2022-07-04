<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GeocoderInterface;
use App\Service\GeocoderServiceInterface;
use App\ValueObject\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeocoderController extends AbstractController
{
    private GeocoderServiceInterface $geocoderService;

    public function __construct(GeocoderServiceInterface $geocoderService)
    {
        $this->geocoderService = $geocoderService;
    }

    /**
     * @Route(path="/geocoder", name="geocoder_service")
     * @param Request $request
     * @param GeocoderInterface $googleGeocoder
     * @param GeocoderInterface $hereMapsGeocoder
     * @return Response
     */
    public function geocodeAction(Request $request, GeocoderInterface $googleGeocoder, GeocoderInterface $hereMapsGeocoder): Response
    {
        $country = $request->get('countryCode', 'lt');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postcode', '01112');

        $address = new Address($country, $city, $street, $postcode);

        $coordinates = $this->geocoderService
            ->setCacheEnabled(false)
            ->addGeocoder($googleGeocoder)
            ->addGeocoder($hereMapsGeocoder)
            ->geocode($address);

        if (null === $coordinates) {
            return new JsonResponse([]);
        }

        return new JsonResponse(['lat' => $coordinates->getLat(), 'lng' => $coordinates->getLng()]);
    }
}
