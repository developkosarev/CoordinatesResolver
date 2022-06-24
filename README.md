# Coordinates resolver

This project is aimed to evaluate OOP and overall code design skills.

## High level overview

Main functionality of this project is this: have a /coordinates endpoint which accepts 4 params: country code, city, street and postcode and as a response API should return coordinates (latitude and longitude) of provided address by using geocoding services.

To make things a bit more challenging, API should support:
* more than one external geocoding provider (Google maps and Here maps) which would be called sequentially if first provider does not find that address
* implement layer responsible for caching results to DB (MySQL) 
* I should be able to use either whole stack (cache+here maps+google maps) or individual geocoder (google maps or here maps) or cached geocoder (cache+google maps for example)

## What this project already contains 

It is fully prepared project: 
* Symfony 5 project with all dependencies already installed
* Doctrine entity already prepared to be used + repository with two methods required for retrieving and saving (\App\Repository\ResolvedAddressRepository)
* Already prepared examples how to make geocoding requests to Google Maps and Here maps so you won't need to read documentation how to use those ( \App\Controller\CoordinatesController::gmapsAction and \App\Controller\CoordinatesController::hmapsAction )
* API endpoint and controller action with DummyGeocoder injected as dependency placeholder.

## What is expected from you

Implement main services which does all the coordination / combined logic: checks DB, if no results, make request to google maps, if fails or not found, check here maps, and store result to DB (even if not found) and return result as JSON. Feel free to copy-paste already mentioned code examples to other classes / components where you feel is right place for it to be.

Keep in mind that this code design should support multiple and not fixed number of geocoders, and those geocoders at the same time could be used in isolation somewhere else, so all components should be interchangeable and reusable.

**Also cover at least one component with unit tests.**

## How to start project

These are following steps to setup project:

```
cp .env.dist .env
```

then inside of .env file, replace set correct values for GOOGLE_GEOCODING_API_KEY and HEREMAPS_GEOCODING_API_KEY variables, and those keys will be sent separately in the email. 

then prepare docker environment:
```
docker-compose build
docker-compose up -d
docker-compose run php bash
```

final project steps inside of docker container:
```
composer install
bin/console doctrine:database:create
bin/console doctrine:schema:create
```

then go to `http://localhost/coordinates` and it should return 

```
{"lat":55.90742079144914,"lng":21.135541627577837}
```

JSON. If you want to check different address, then add params to url: http://localhost/coordinates?countryCode=lithuania&city=vilnius&street=gedimino+9&postcode=12345 . 

And that's it, good luck!
