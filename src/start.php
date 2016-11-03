<?php
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

// session start
session_start();
// fake the user id signin
$_SESSION['user_id'] = 1;
// autoload paypal rest api
require __DIR__ . '/../vendor/autoload.php';
// create new api context for authentication
$api = new ApiContext(
    new OAuthTokenCredential(
        // api client id
        'AYm034tVoDnzAbUvo4HoHEjlh3jVoxkbQLMOCuaqXDwJd1XdNXU35GOV7s7aYN-Sj_3YvXGCHfZDf_Ky',
        // api secret
        'EKmVubvPmQpwF6wJmK2PrX-S53RDQdzEHnQinM6r8ME7AnYjFGEssHTd4V8oVV_oLKpVitBFmo_8MQN5'
    )
);
// api configuration settings
$api->setConfig([
    'mode' => 'sandbox',
    'http.ConnectionTimeout' => 30,
    'log.LogEnabled' => false,
    'log.FileName' => '',
    'log.LogLevel' => 'FINE',
    'validation.level' => 'log',
]);

// load the database connection configuration
$db = new PDO('mysql:host=localhost;dbname=paypal' , 'root' , '');

$user = $db->prepare('
    SELECT * FROM users
    WHERE id = :user_id
');

$user->execute([
    'user_id' => $_SESSION['user_id']
]);
// fetch the user data object
$user = $user->fetchObject();
