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

### Create Payment

```php
$amount = 2000;
$currency = 'USD';
$payment = $payu->createPayment($amount, $currency);
```

### Create Charge

```php
$charge = $payu->createCharge($payment['id'], $token);
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
