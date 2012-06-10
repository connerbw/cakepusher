<?php

error_reporting(E_ALL | E_STRICT); // Development
// error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Hosting

$CONFIG['DEBUG'] = true;

// -----------------------------------------------------------------------------
// Vars, capitalized for easier recognition
// -----------------------------------------------------------------------------

// Word => [ Synonym1, Synonym2, ... ]
$CONFIG['RECOMMENDATIONS'] = array(
    'cupcakes' => array('cupcakes', 'cupcake'),
    'cake' => array('cakes', 'cake'),
);

// Seconds
$CONFIG['MAX_TIMELAPSE'] = 30 * 60;

// Kilometers
$CONFIG['MAX_DISTANCE'] = 50;

// -----------------------------------------------------------------------------
// Yellow API stuff
// Register here: http://developer.yellowapi.com/member/register
// -----------------------------------------------------------------------------

// API Key
$CONFIG['YELLOW_API_KEY'] = '__REPLACE_ME__';

// Server, No trailing slash!
$CONFIG['YELLOW_API_SERVER'] = 'http://api.sandbox.yellowapi.com';

// -----------------------------------------------------------------------------
// Status.net API stuff
// -----------------------------------------------------------------------------

// Status.Net timeline to scan
$CONFIG['STATUSNET_USERNAME'] = '__REPLACE_ME__';

// Server, No trailing slash!
$CONFIG['STATUSNET_SERVER'] = 'http://identi.ca';

// Either password, or oauth
$CONFIG['AUTH_TYPE'] = 'password';

// If AUTH_TYPE is passord, set these
$CONFIG['USERNAME'] = '__REPLACE_ME__';
$CONFIG['PASSWORD'] = '__REPLACE_ME__^';

// Else, if AUTH_TYPE is oauthk get OAuth Tokens for Status.net:
// 1) Register: http://identi.ca/settings/oauthapps
// 2) Get CONSUMER keys: http://identi.ca/settings/oauthapps/show/__REPLACE_ME__
// 3) Follow instruction in: ./mainline/tests/oauth/README
$CONFIG['OAUTH_CONSUMER_KEY'] = '__REPLACE_ME__';
$CONFIG['OAUTH_CONSUMER_SECRET'] = '__REPLACE_ME__';
$CONFIG['OAUTH_TOKEN'] = '__REPLACE_ME__';
$CONFIG['OAUTH_TOKEN_SECRET'] = '__REPLACE_ME__';

// -----------------------------------------------------------------------------
// Generic PHP stuff
// -----------------------------------------------------------------------------

// Timzeone, pick yours from the list available at http://php.net/manual/en/timezones.php
$CONFIG['TIMEZONE'] = 'America/Montreal';

// The auto-detected path to your web app installation.
// If you set this yourself, no trailing slash!
$CONFIG['PATH'] = dirname(__FILE__);
