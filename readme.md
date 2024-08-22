# Solar Investments Support

[![Packagist](https://img.shields.io/packagist/v/solar-investments/support)](https://packagist.org/packages/solar-investments/support)
![Tests](https://img.shields.io/github/actions/workflow/status/Solar-Investments/support/test.yml)
![Dependencies](https://img.shields.io/librariesio/github/Solar-Investments/support)

Support package for Solar Investments projects.

---
- [Installation](#installation)
- [Middleware](#middleware)
    - [Fastly Middleware](#fastly-middleware)
    - [VPN Middleware](#vpn-middleware)
- [Testing Traits](#testing-traits)
---

## Installation

```bash
composer require solar-investments/support
```

```bash
php artisan vendor:publish --tag=vpn-config
```

## Middleware

This package adds the following global middleware:

- `SolarInvestments\Middleware\EnableSecurePaginationLinks`
- `SolarInvestments\Middleware\HideFromRobotsOnOrigin`
- `SolarInvestments\Middleware\LowerPathCasing`
- `SolarInvestments\Middleware\RemoveTrailingSlash`

The following middleware is added to the `web` middleware group:

- `SolarInvestments\Middleware\RequireVpn`

The following middleware is available for use:

- `SolarInvestments\Middleware\SetFastlySurrogateKey`

### Fastly Middleware

To use the `SetFastlySurrogateKey` middleware, publish the configuration file:

```bash
php artisan vendor:publish --tag=fastly-config
```

Then, set the `surrogate_keys` in the `config/fastly.php` file, e.g.:

```php
return [

    'surrogate_keys' => [

        [
            /*
             * The key to use for requests that match the specified paths.
             *
             * The default key will be prepended to this value.
             */
            'key' => 'meals',

            /*
             * List of paths that should use the specified key.
             *
             * A path is a match if it starts with any of the specified paths.
             * 
             * Note: Paths are case-insensitive. The forward slash is optional.
             */
            'paths' => [
                'breakfast',
                'lunch',
                'dinner',
            ],
        ],

    ],

    // ...

];
```

### VPN Middleware

By default, this middleware is "disabled" and all IP addresses are allowed.

To restrict access from specific IP addresses, set the `VPN_IP_ADDRESSES` environment variable in your `.env` file, e.g.:

```dotenv
VPN_IP_ADDRESSES=192.168.1.192,10.0.0.1/8
```

Alternatively, you can specify the IP addresses in the `config/vpn.php` file.

## Testing Traits

### SkipTestWhenRunningCI

This trait can be used to skip tests when running in a CI environment.

```php
use SolarInvestments\Testing\SkipTestWhenRunningCI;

class ExampleTest extends TestCase
{
    use SkipTestWhenRunningCI;

    public function test_example()
    {
        // ...
    }
}
```
