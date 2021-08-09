<?php
require_once 'models/Auth.php';

$_auth = new Auth;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body       = file_get_contents("php://input");
    $data       = $_auth->login($body);

    header('Content-Type: application/json');
    echo $data;
} else {
    header('Content-Type: application/json');
    echo Response::json(405);
}
