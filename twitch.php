<?php
require("/var/www/tmsqd.co/vendor/autoload.php");
require_once("../config.php");

use TwitchClient\API\Auth\Authentication;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Authentication\DefaultTokenProvider;

$clientId = $config["twitch"]["client_id"];
$clientSecret = $config["twitch"]["client_secret"];

// Create the token provider using the client ID and secret.
$tokenProvider = new DefaultTokenProvider($clientId, $clientSecret);
$redirectURI = 'https://twitchmodsquad.com/twitch'; // The redirect URI configured in the app settings on Twitch.
                                    // Here we'll suppose that we're on a single page that handles both.

$authAPI = new Authentication($tokenProvider);
$helix = new Helix($tokenProvider);
$helix->discoverServices();
$usersService = $helix->getService("users");