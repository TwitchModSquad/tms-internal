<?php
require_once("../internal/connect.php");

$session = $ss->getSession();

if ($session === null) {
    header("Location: " . $config["uris"]["public_home"]);
}