<?php
require_once("../config.php");
require_once("/var/www/tmsqd.co/internal/twitch.php");

// Getting the access token, the API requiring to send 
$accessToken = $authAPI->getAccessToken($_GET['code'], $redirectURI);

$uri = !empty($_COOKIE["return_uri"]) ? $_COOKIE["return_uri"] : $config["uris"]["panel_home"];
    
if ($accessToken) {
    // Create a stream
    $opts = array(
        'http'=>array(
        'method'=>"GET",
        'header'=>"Authorization: Bearer " . $accessToken["token"] . "\r\n" .
                "Client-Id: $clientId"
        )
    );
    
    $context = stream_context_create($opts);
    
    // Open the file using the HTTP headers set above
    $file = file_get_contents('https://api.twitch.tv/helix/users', false, $context);
    $json_response = json_decode($file, true);

    //TODO: Add more error checking here.
    $user = $json_response["data"][0];

    $userRow = $tus->getUser($user["id"]);

    $identityId;

    $session = $ss->getSession();
    if ($session !== null && $session["identity_id"] !== $userRow["identity_id"]) {
        $identityId = $session["identity_id"];
    } else if ($userRow === null || $userRow["identity_id"] === null) {
        $identityId = $is->createIdentity($user["display_name"]);
    } else {
        $identityId = $userRow["identity_id"];
    }

    if ($userRow === null) {
        $tus->createUser($user["id"], $user["display_name"], $identityId, $user["email"], $user["profile_image_url"], $user["offline_image_url"], $user["description"], $user["view_count"], null, $user["broadcaster_type"] === "" ? null : $user["broadcaster_type"]);
    } else if ($userRow["identity_id"] != $identityId) {
        $tus->updateIdentity($user["id"], $identityId);
    }

    if ($userRow !== null) {
        $tus->updateEmail($user["id"], $user["email"]);
    }

    $session = $ss->getSession();

    if ($session === null || $session["identity_id"] != $identityId) {
        $ss->createSession($identityId);
    }

    header("Location: " . $uri);
    echo "sending to " . $uri;
} else {
    // Get an authorize URL with some scope (here the one to allow the app to change the stream title and game)
    $authorizeURL = $authAPI->getAuthorizeURL($redirectURI, ['user:read:email']);
    
    // Redirect the user to the authorize page
    header('Location: '. $authorizeURL);
}