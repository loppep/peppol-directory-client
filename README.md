# Peppol directory client

## Getting started

Install our library
```bash
composer require loppep/peppol-directory-client
```

Install a PSR-18 library, e.g. Symfony HttpClient
```bash
composer require symfony/http-client
```

Install a PSR-7 library, e.g. Nyholm PSR-7
```bash
composer require nyholm/psr7
```

## Search for a Peppol participant

```php
use Http\Discovery\Psr18Client;
use Loppep\PeppolDirectoryClient\Enum\Environment;
use Loppep\PeppolDirectoryClient\PeppolDirectoryClient;

$directory = new PeppolDirectoryClient(
    new Psr18Client(),
    Environment::production()
);
$directory->search('DE343985244');
```

## Get a Peppol participant by their ID
```php
use Http\Discovery\Psr18Client;
use Loppep\PeppolDirectoryClient\Data\IdType;
use Loppep\PeppolDirectoryClient\Enum\Environment;
use Loppep\PeppolDirectoryClient\PeppolDirectoryClient;

$directory = new PeppolDirectoryClient(
    new Psr18Client(),
    Environment::production()
);
$directory->get(
    new IdType(
        $scheme = 'iso6523-actorid-upis',
        $value = '9930:de343985244'
    )
);
```