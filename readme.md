# Solar Investments Support

This package adds the following global middleware:

- `SolarInvestments\Middleware\EnableSecurePaginationLinks`
- `SolarInvestments\Middleware\HideFromRobotsOnOrigin`
- `SolarInvestments\Middleware\LowerPathCasing`
- `SolarInvestments\Middleware\RemoveTrailingSlash`

The following middleware is added to the `web` middleware group:

- `SolarInvestments\Middleware\RequireVpn`

The following middleware is available for use:

- `SolarInvestments\Middleware\SetFastlySurrogateKey`

## Installation

```bash
composer require solar-investments/support
```

## Configuration

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

To restrict access from specific IP addresses, set the `VPN_IP_ADDRESSES` environment variable in your `.env` file.

Alternatively, you can specify the IP addresses in the `config/vpn.php` file once you've published it:

```bash
php artisan vendor:publish --tag=vpn-config
```
