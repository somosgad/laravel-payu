# laravel-payu

A Laravel package to encapsulate global PayU requests. More info at [PaymentsOS Docs][link-payudocs].<br>

<!-- [![Latest Version on Packagist][ico-version]][link-packagist] -->
[![Total Downloads][ico-downloads]][link-downloads] 
<!-- [![Build Status][ico-travis]][link-travis] -->
<!-- [![StyleCI][ico-styleci]][link-styleci] -->

**Currently in development**

<!-- Take a look at [contributing.md][link-contributing] to see a to do list. -->

## Installation

Via [Composer][link-composer]

```bash
composer require somosgad/laravel-payu:dev-master
```

## Configuration

### Set API variables

Set your PayU configs at `.env` file

```ini
PAYU_APP_ID=
PAYU_ENV=
PAYU_PUBLIC_KEY=
PAYU_PRIVATE_KEY=
PAYU_PROVIDER=
```

Your `.env` file must end up looking like:


```ini
PAYU_APP_ID=com.foo.bar
PAYU_ENV=test
PAYU_PUBLIC_KEY=g6l2g4yn-nvgp-uiil-6fm7-d2337cegunmz
PAYU_PRIVATE_KEY=68lhkww3-lkgw-4mcc-r21m-cf8nnnx3wj2k
PAYU_PROVIDER="PayU Argentina"
```

### Export Config

```bash
php artisan vendor:publish --provider="SomosGAD_\LaravelPayU\LaravelPayUServiceProvider"
```

## Usage

```php
use SomosGAD_\LaravelPayU\LaravelPayU;

$payu = new LaravelPayU;
```

---

### Payment

#### Create
```php
$amount = 2000;
$currency = 'USD';
$payment = $payu->createPayment($amount, $currency);
```

---

### Create Token

```php
$card_number = '4111111111111111';
$credit_card_cvv = '123';
$expiration_date = '10/29';
$holder_name = 'John Doe';
$token_type = 'credit_card';
$token = $payu->createToken(
    $card_number,
    $credit_card_cvv,
    $expiration_date,
    $holder_name,
    $token_type
);
```

### Create Authorization

```php
$authorization = $payu->createAuthorization($payment['id'], $encrypted_cvv, $token);
```

### Create Capture

```php
$capture = $payu->createCapture($payment['id'], $payment['amount']);
```

---

### Charge

#### Create Card Charge

```php
$charge = $payu->createCharge($payment['id'], $token);
```

#### Create Cash Charge

```php
$charge = $payu->createCharge2($payment['id'], [
    'payment_method' => [
        'source_type' => 'cash',
        'type' => 'untokenized',
        'vendor' => 'COBRO_EXPRESS',
        'additional_details' => [
            'order_language' => 'en',
            'cash_payment_method_vendor' => 'COBRO_EXPRESS',
            'payment_method' => 'PSE',
            'payment_country' => 'ARG',
        ],
    ],
    'reconciliation_id' => time(),
];
```

\* Notes:

1. `order_language` is always uppercased for you; 
2. Omitted `reconciliation_id` gets created;
3. Only `ARG` `payment_country` are able to create cash charges for Argentina;
4. Payments for cash charges must be created with `customer_id` set and customer must have `shipping_address` set with at least one field, otherwise, the receipt won't be printable or downloadable.

---

### Customer

#### Create Customer

##### Required Props

```php
$customer = $payu->createCustomer([
    'customer_reference' => 'johntravolta18021954',
]);
```

\* Notes:

1. `customer_reference`s are unique, API won't create customers for same references. Choose something like an ID, document or anything else unique and immutable to set as `customer_reference`.
2. PayU Argentina won't print or download PDF if you haven't set customer's `shipping_address` with at least one info (like `country` or any other field).

##### Customer Sample from PayU API Docs

```php
$customer = $payu->createCustomer([
    'customer_reference' => 'johntravolta18021954',
    'email' => 'john@travolta.com',
]);
```

##### Optional Props

```php
$customer = $payu->createCustomer([
    'customer_reference' => 'johntravolta18021954',
    'email' => 'john@travolta.com',
    'first_name' => 'John',
    'last_name' => 'Travolta',
    'additional_details' => [
        'extra1' => 'Info Extra 1',
        'extra2' => 'Info Extra 2',
    ],
    'shipping_address' => [
        'country' => 'ARG',
        'state' => 'TX',
        'city' => 'Customer Shipping City',
        'line1' => '10705 Old Mill Rd',
        'line2' => '10706 Young Mill Rd',
        'zip_code' => '75402-3435',
        'title' => 'Dr.',
        'first_name' => 'John',
        'last_name' => 'Travolta',
        'phone' => '23645963',
        'email' => 'john@travolta.com',
    ],
]);
```

#### Delete Customer

```php
$customer_id = '0ab5511c-3a62-4b4b-8682-cb3c15172965';
$delete = $payu->deleteCustomer($customer_id);
```

#### Get Customer by ID

```php
$customer_id = '0ab5511c-3a62-4b4b-8682-cb3c15172965';
$customer = $payu->getCustomerById($customer_id);
```

#### Get Customers by Reference

```php
$customer_reference = 'johntravolta18021954';
$customers = $payu->getCustomerByReference($customer_reference);
```
\* Note: `getCustomerByReference` returns an `array` not a single record like `getCustomerById`;

---

### Create Payment Method

```php
$payment_method = $payu->createPaymentMethod($customer['id'], $token);
```

<!-- ## Change log

Please see the [changelog][link-changelog] for more information on what has changed recently. -->

## Testing

<!-- # $ composer test -->

``` bash
phpunit
```

## Contributing

Feel free to contribute with anything on this package or contact us about it.

<!-- Please see [contributing.md][link-contributing] for details and a todolist. -->

## Security

If you discover any security related issues, please email giovanni@somosgad.com instead of using the issue tracker.

## Credits

- [Somos GAD_][link-author]
- [Giovanni Pires da Silva][link-giovanni]
- [Camilo Cunha de Azevedo][link-camilo]
- [Danner Terra][link-danner]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/somosgad/laravel-payu.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/somosgad/laravel-payu.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/somosgad/laravel-payu/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/somosgad/laravel-payu
[link-downloads]: https://packagist.org/packages/somosgad/laravel-payu
[link-travis]: https://travis-ci.org/somosgad/laravel-payu
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/somosgad
[link-giovanni]: https://github.com/giovannipds
[link-camilo]: https://github.com/Camilotk
[link-danner]: https://github.com/DannerTerra
[link-contributors]: ../../contributors
[link-composer]: https://getcomposer.org
[link-payudocs]: https://developers.paymentsos.com
[link-changelog]: changelog.md
[link-contributing]: contributing.md
