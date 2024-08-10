# Solar Investments Support

This package adds the following global middleware:

- `SolarInvestments\Middleware\EnableSecurePaginationLinks`
- `SolarInvestments\Middleware\HideFromRobotsOnOrigin`
- `SolarInvestments\Middleware\LowerPathCasing`
- `SolarInvestments\Middleware\RemoveTrailingSlash`

The following middleware is added to the `web` middleware group:

- `SolarInvestments\Middleware\RequireVpn`

## Installation

```bash
composer require solar-investments/support
```

## Configuration

### VPN Middleware

By default, this middleware is "disabled" and all IP addresses are allowed.

To restrict access from specific IP addresses, set the `VPN_IP_ADDRESSES` environment variable in your `.env` file.

Alternatively, you can specify the IP addresses in the `config/vpn.php` file once you've published it:

```bash
php artisan vendor:publish --tag=vpn-config
```
