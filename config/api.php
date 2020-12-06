<?php

return [

    //API url
    'url' => env('API_URL', 'https://api.contractorcompliance.io'),

    //Success email
    'success_email' => ['success@contractorcompliance.io'],

    //bcc email
    'bcc_email' => ['emailtesting@contractorcompliance.ca'],

    // NOTE: users with validation date below ARE NOT validated
    // this was set to make sure we are not sending emails to buggy emails
    'email_validation_date' => "2000-01-01 00:00:00",

    // contractor registered after this will be asked for subcontractor survey
    'subcontractor_survey' => [
        'limit_contractors' => 1000, // 0 = survey everyone
        'roles_registered_after' => "2020-02-01 00:00:00",
        'roles_must_be_days_old' => 7, // 7 days after registration
        'roles_receive_email_after' => 37, // 30 days after the popup start to show
        'lead_email' => ['contractorleads@contractorcompliance.ca'],
        'do_not_survey' => [144], // Ids from HOs which we shouldn't survey about subcontractors.
    ],

    // How long every lock will last before it removed from db
    'lock_ttl' => 20 //min

];
