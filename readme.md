# Solar Investments Support

[![Packagist](https://img.shields.io/packagist/v/solar-investments/support)](https://packagist.org/packages/solar-investments/support)
![Tests](https://img.shields.io/github/actions/workflow/status/Solar-Investments/support/test.yml)
![Dependencies](https://img.shields.io/librariesio/github/Solar-Investments/support)

Support package for Solar Investments projects.

---
- [Installation](#installation)
- [Middleware](#middleware)
    - [Enable Secure Pagination Links](#enable-secure-pagination-links)
    - [Hide From Robots On Origin](#hide-from-robots-on-origin)
    - [Lower Path Casing](#lower-path-casing)
    - [Remove Trailing Slash](#remove-trailing-slash)
    - [Require VPN](#require-vpn)
    - [Set Fastly Surrogate Key](#set-fastly-surrogate-key)
- [URLs](#urls)
- [Testing Traits](#testing-traits)
---

## Installation

```bash
composer require solar-investments/support
```

## Middleware

You can [register](https://laravel.com/docs/11.x/middleware#registering-middleware) any of the following middleware:

- `SolarInvestments\Middleware\EnableSecurePaginationLinks`
- `SolarInvestments\Middleware\HideFromRobotsOnOrigin`
- `SolarInvestments\Middleware\LowerPathCasing`
- `SolarInvestments\Middleware\RemoveTrailingSlash`
- `SolarInvestments\Middleware\RequireVpn`
- `SolarInvestments\Middleware\SetFastlySurrogateKey`

### Enable Secure Pagination Links

This middleware fixes an issue where the `next` and `prev` links in the pagination response are generated using `http` instead of `https`.

### Hide From Robots On Origin

This middleware adds the `X-Robots-Tag` header to the response with the value `none` (same as `noindex, nofollow`) to prevent search engines from indexing the page. This is only done when the site is accessed directly on the origin server, e.g. `http://origin.example.com` instead of `http://www.example.com`.

### Lower Path Casing

> If [Statamic](https://statamic.dev) is installed, control panel paths are not converted to lowercase.

This middleware converts the path of the request to lowercase.

### Remove Trailing Slash

This middleware removes the trailing slash from the path of the request.

### Require VPN

This middleware restricts access to the application to specific IP addresses. This is useful when you want to restrict access to the application to only users on a VPN (or anywhere really).

To use the `RequireVpn` middleware, publish the configuration file:

```bash
php artisan vendor:publish --tag=vpn-config
```

By default, this middleware is "disabled" and all IP addresses are allowed.

To restrict access from specific IP addresses, set the `VPN_IP_ADDRESSES` environment variable in your `.env` file, e.g.:

```dotenv
VPN_IP_ADDRESSES=192.168.1.192,10.0.0.1/8
```

Alternatively, you can specify the IP addresses in the `config/vpn.php` file.

### Set Fastly Surrogate Key

This middleware adds the `Surrogate-Key` header to the response with the configured value, allowing you to purge the cache for specific pages.

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

## URLs

The `UrlServiceProvider` does the following when the application is **not** running locally:

1. Forces the `https` scheme for all URLs generated by Laravel.
2. Forces the root URL to whatever is set for the `APP_URL` environment variable.

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
