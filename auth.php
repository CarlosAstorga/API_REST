<?php
require_once 'classes/Auth.php';

$body   = file_get_contents("php://input");
$_auth  = new Auth($body);
header('Content-Type: application/json');
echo $_SERVER['REQUEST_METHOD'] == 'POST' ? $_auth->login() : Response::json(405);
