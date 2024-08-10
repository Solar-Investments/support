<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that can access the application. You can use an
    | asterisk to allow all IP addresses. CIDR notation is supported.
    |
    */

    'ip_addresses' => env('VPN_IP_ADDRESSES', '*'),

];
