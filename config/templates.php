<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template Types
    |--------------------------------------------------------------------------
    |
    | The employee events a template can be written for. Both SMS and email
    | templates are tagged with one of these, and each type has one default
    | template that the system sends.
    |
    */

    'types' => [
        'birthday'    => 'Birthday',
        'anniversary' => 'Anniversary',
        'welcome'     => 'Welcome',
        'farewell'    => 'Farewell',
        'general'     => 'General',
    ],

    /*
    | Types the system sends on its own, once enabled on the Events page.
    | 'general' is excluded: it is only sent manually via Broadcast.
    */

    'automated' => ['birthday', 'anniversary', 'welcome', 'farewell'],

    /*
    | How each event is triggered - shown on the Events page.
    */

    'triggers' => [
        'birthday'    => 'Sent by the daily scheduler when the birthday matches today',
        'anniversary' => 'Sent by the daily scheduler on the joining anniversary',
        'welcome'     => 'Sent once, when a new active employee is added',
        'farewell'    => 'Sent once, when an employee is marked inactive',
        'general'     => 'Sent manually from Broadcast - never automatic',
    ],

];
