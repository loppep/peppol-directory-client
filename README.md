# Peppol Directory Client
An API client for searching Peppol participants in the Peppol Directory.  
The additional SMP check ensures that the participant is actually reachable via Peppol.  
This was mainly implemented because of the following reason found in the documentation of the Peppol Directory:
> A crucial governance note: updating the Directory is performed by SMP providers and is not mandatory. Therefore, a company can be fully routable on Peppol and still not appear in the public Directory. This does not invalidate their Peppol status; it only means their SMP has not published a “business card” to the Directory.


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
This performs a search in the Peppol Directory and additionally checks the SMP.
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
This bypasses the Peppol Directory and checks directly with the SMP.
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

## Quickly check if a Peppol participant is registered
This bypasses the Peppol Directory and checks directly with the SMP.
```php
use Http\Discovery\Psr18Client;
use Loppep\PeppolDirectoryClient\Data\IdType;
use Loppep\PeppolDirectoryClient\Enum\Environment;
use Loppep\PeppolDirectoryClient\PeppolDirectoryClient;

$directory = new PeppolDirectoryClient(
    new Psr18Client(),
    Environment::production()
);
$directory->isRegistered(
    new IdType(
        $scheme = 'iso6523-actorid-upis',
        $value = '9930:de343985244'
    )
);
```