<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\Providers\PayUArgentina;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\PayUArgentina\ArgentinaCash;

class CashTest extends TestCase
{
    public function dataProvider()
    {
        $data = [
            ['COBRO_EXPRESS'],
            ['PAGOFACIL'],
            ['RAPIPAGO'],
        ];
        return $data;
    }

    /**
     * Test create charge for cash requests.
     *
     * @dataProvider dataProvider
     * @return void
     */
    public function testCreateCharge(string $vendor)
    {
        // init the payu argentina
        $payu = new PayUArgentina;

        // check if the customer already exists
        $customers = $payu->getCustomerByReference('johntravolta18021954');

        // delete the customer if it already exists
        if (count($customers)) {
            $payu->deleteCustomer($customers[0]['id']);
        }

        // create the customer
        $customer = $payu->createCustomer([
            'customer_reference' => 'johntravolta18021954',
            'shipping_address' => [
                'country' => 'USA',
            ],
        ]);

        // create the payment schema
        $amount = 9900;
        $currency = 'ARS';
        $statement_soft_descriptor = "RunClub 5km race ticket";
        $payment_schema = new ArgentinaCash($amount, $currency, $statement_soft_descriptor, null, null, $customer['id']);

        // create the payment
        $payment = $payu->createPayment2($payment_schema);

        // create the charge request
        $charge = $payu->createCharge2($payment['id'], [
            'payment_method' => [
                'source_type' => 'cash',
                'type' => 'untokenized',
                'vendor' => $vendor,
                'additional_details' => [
                    'order_language' => 'en',
                    'cash_payment_method_vendor' => $vendor,
                    'payment_method' => 'PSE',
                    'payment_country' => 'ARG',
                ],
            ],
            'reconciliation_id' => time(),
        ]);

        // store the charge at the vars array
        $vars[] = $charge;

        // delete the created customer
        $payu->deleteCustomer($customer['id']);

        // charge
        $this->assertIsArray($charge);

        // // id
        // $this->assertArrayHasKey('id', $charge);
        // $this->assertIsString($charge['id']);

        // // created
        // $this->assertArrayHasKey('created', $charge);
        // $this->assertIsNumeric($charge['created']);

        // // reconciliation_id
        // $this->assertArrayHasKey('reconciliation_id', $charge);
        // $this->assertIsNumeric($charge['reconciliation_id']);

        // provider_specific_data
        $this->assertArrayHasKey('provider_specific_data', $charge);
        $this->assertIsArray($charge['provider_specific_data']);

        // additional_details
        $this->assertArrayHasKey('additional_details', $charge['provider_specific_data']);
        $this->assertIsArray($charge['provider_specific_data']['additional_details']);

        // // payment_country
        // $this->assertArrayHasKey('payment_country', $charge['provider_specific_data']['additional_details']);
        // $this->assertIsString($charge['provider_specific_data']['additional_details']['payment_country']);

        // // payment_method
        // $this->assertArrayHasKey('payment_method', $charge['provider_specific_data']['additional_details']);
        // $this->assertIsString($charge['provider_specific_data']['additional_details']['payment_method']);

        // // order_language
        // $this->assertArrayHasKey('order_language', $charge['provider_specific_data']['additional_details']);
        // $this->assertIsString($charge['provider_specific_data']['additional_details']['order_language']);

        // cash_payment_method_vendor
        $this->assertArrayHasKey('cash_payment_method_vendor', $charge['provider_specific_data']['additional_details']);
        $this->assertIsString($charge['provider_specific_data']['additional_details']['cash_payment_method_vendor']);
        $this->assertSame($vendor, $charge['provider_specific_data']['additional_details']['cash_payment_method_vendor']);

        // payment_method
        $this->assertArrayHasKey('payment_method', $charge);
        $this->assertIsArray($charge['payment_method']);

        // alternative_payment
        $this->assertArrayHasKey('alternative_payment', $charge['payment_method']);
        $this->assertIsArray($charge['payment_method']['alternative_payment']);

        // vendor
        $this->assertArrayHasKey('vendor', $charge['payment_method']['alternative_payment']);
        $this->assertIsString($charge['payment_method']['alternative_payment']['vendor']);
        $this->assertSame($vendor, $charge['payment_method']['alternative_payment']['vendor']);

        // source_type
        $this->assertArrayHasKey('source_type', $charge['payment_method']);
        $this->assertIsString($charge['payment_method']['source_type']);
        $this->assertSame('Cash', $charge['payment_method']['source_type']);

        // type
        $this->assertArrayHasKey('type', $charge['payment_method']);
        $this->assertIsString($charge['payment_method']['type']);
        $this->assertSame('untokenized', $charge['payment_method']['type']);

        // result
        $this->assertArrayHasKey('result', $charge);
        $this->assertIsArray($charge['result']);

        // status
        $this->assertArrayHasKey('status', $charge['result']);
        $this->assertIsString($charge['result']['status']);
        $this->assertSame('Pending', $charge['result']['status']);

        // provider_data
        $this->assertArrayHasKey('provider_data', $charge);
        $this->assertIsArray($charge['provider_data']);

        // // provider_name
        // $this->assertArrayHasKey('provider_name', $charge['provider_data']);
        // $this->assertIsString($charge['provider_data']['provider_name']);
        // $this->assertSame('PayULatam', $charge['provider_data']['provider_name']);

        // response_code
        $this->assertArrayHasKey('response_code', $charge['provider_data']);
        $this->assertIsString($charge['provider_data']['response_code']);
        $this->assertSame('AWAITING_NOTIFICATION', $charge['provider_data']['response_code']);

        // description
        $this->assertArrayHasKey('description', $charge['provider_data']);
        $this->assertIsString($charge['provider_data']['description']);
        $this->assertSame('PENDING', $charge['provider_data']['description']);

        // // raw_response
        // $this->assertArrayHasKey('raw_response', $charge['provider_data']);
        // $this->assertIsString($charge['provider_data']['raw_response']);

        // // transaction_id
        // $this->assertArrayHasKey('transaction_id', $charge['provider_data']);
        // $this->assertIsString($charge['provider_data']['transaction_id']);

        // // external_id
        // $this->assertArrayHasKey('external_id', $charge['provider_data']);
        // $this->assertIsNumeric($charge['provider_data']['external_id']);

        // documents
        $this->assertArrayHasKey('documents', $charge['provider_data']);
        $this->assertIsArray($charge['provider_data']['documents']);
        $this->assertCount(2, $charge['provider_data']['documents']);
        foreach ($charge['provider_data']['documents'] as $document) {
            $this->assertIsArray($document);

            // descriptor
            $this->assertArrayHasKey('descriptor', $document);
            $this->assertIsString($document['descriptor']);
            $this->assertSame('receipt_url', $document['descriptor']);

            // content_type
            $this->assertArrayHasKey('content_type', $document);
            $this->assertIsString($document['content_type']);
            $this->assertContains($document['content_type'], ['html', 'pdf']);

            // href
            $this->assertArrayHasKey('href', $document);
            $this->assertIsString($document['href']);
        }

        // // additional_information
        // $this->assertArrayHasKey('additional_information', $charge['provider_data']);
        // $this->assertIsArray($charge['provider_data']['additional_information']);
        // $this->assertArrayHasKey('barcode', $charge['provider_data']['additional_information']);
        // $this->assertIsNumeric($charge['provider_data']['additional_information']['barcode']);

        // // amount
        // $this->assertArrayHasKey('amount', $charge);
        // $this->assertIsNumeric($charge['amount']);

        // // provider configuration
        // $this->assertArrayHasKey('provider_configuration', $charge);
        // $this->assertIsArray($charge['provider_configuration']);

        // // id
        // $this->assertArrayHasKey('id', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['id']);

        // // name
        // $this->assertArrayHasKey('name', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['name']);

        // // created
        // $this->assertArrayHasKey('created', $charge['provider_configuration']);
        // $this->assertIsNumeric($charge['provider_configuration']['created']);

        // // modified
        // $this->assertArrayHasKey('modified', $charge['provider_configuration']);
        // $this->assertIsNumeric($charge['provider_configuration']['modified']);

        // // account_id
        // $this->assertArrayHasKey('account_id', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['account_id']);

        // // provider_id
        // $this->assertArrayHasKey('provider_id', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['provider_id']);

        // // type
        // $this->assertArrayHasKey('type', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['type']);
        // $this->assertSame('cc_processor', $charge['provider_configuration']['type']);

        // // href
        // $this->assertArrayHasKey('href', $charge['provider_configuration']);
        // $this->assertIsString($charge['provider_configuration']['href']);
    }
}
