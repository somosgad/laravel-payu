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

    /*
    |--------------------------------------------------------------------------
    | Expand Objects
    |--------------------------------------------------------------------------
    |
    | GET requests sometimes retrieve associated resource objects. For example,
    | Payment attributes may include associated Authorization objects and
    | Capture objects. By including the expand parameter in your request, you
    | can retrieve the full set of attributes for selected associated
    | resources. Enter a list of resources to expand, or 'all' to expand all
    | associated resources. For example, to expand the Payment Method resource
    | returned in a GET payments request, add the following to the end of the
    | request, ?expand=payment_method.
    |
    */

    'expand_objects' => false,

    /*
    |--------------------------------------------------------------------------
    | Zooz Request ID
    |--------------------------------------------------------------------------
    |
    | Each API request has a unique ID, which is returned in the
    | x-zooz-request-id header, of every response. You can use this ID as a
    | reference when contacting us about a specific request. If this is
    | enabled, x-zooz-request-id will be inserted at every body array response.
    |
    */

    'zooz_request_id' => false,

];
