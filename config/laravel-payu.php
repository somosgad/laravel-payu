<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Customer Device
    |--------------------------------------------------------------------------
    |
    | You can include information about the customer device when sending a
    | Create Authorization or Create Charge request. This information can be
    | used later to route transactions accordingly. To send this information,
    | set this to true and it'll will include the x-client-ip-address and/or
    | the x-client-user-agent headers in the request.
    |
    */

    'customer_device' => false,

    /*
    |--------------------------------------------------------------------------
    | Double Amounts
    |--------------------------------------------------------------------------
    |
    | Determine if will use double amounts. Ex. convert 99.00 to 9900
    |
    */

    'double_amounts' => false,

];
