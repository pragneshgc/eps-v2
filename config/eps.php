<?php

return [
    'send_test_mail_to' => env('TEST_EMAIL_TO', 'pragnesh@goodcareit.com'),
    'telescope_access_email' => env('TELESCOPE_ACCESS_EMAIL'),
    'send_error_to_slack' => env('SEND_ERROR_TO_SLACK', false)
];
