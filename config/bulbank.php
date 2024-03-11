<?php

return [
    'merchant_id' => env('BULBANK_MERCHANT_ID', ''),
    'merchant_name' => env('BULBANK_MERCHANT_NAME', ''),
    'terminal_id' => env('BULBANK_TERMINAL_ID', ''),
    'pass' => env('BULBANK_PASS', ''),
    'public_key_path' => env('BULBANK_PUBLIC_KEY_PATH', ''),
    'private_key_path' => env('BULBANK_PRIVATE_KEY_PATH', ''),
    'private_key_pass' => env('BULBANK_PRIVATE_KEY_PASS', ''),
    'public_cer_path' => env('BULBANK_PUBLIC_CER_PATH', ''),
];
