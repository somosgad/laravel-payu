<?php

if ( ! function_exists('is_not_null')) {
    function is_not_null($var) {
        return ! is_null($var);
    }
}

if ( ! function_exists('tokenData')) {
    function tokenData(
        string $card_number,
        string $credit_card_cvv,
        string $expiration_date,
        string $holder_name,
        string $token_type
    ) {
        return [ $card_number, $credit_card_cvv, $expiration_date, $holder_name, $token_type ];
    }
}
