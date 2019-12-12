<?php

if ( ! function_exists('is_not_null')) {
    function is_not_null($var) {
        return ! is_null($var);
    }
}
