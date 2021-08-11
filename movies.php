<?php

require_once 'classes/Movie.php';
require_once 'classes/Response.php';

$data       = null;
$body       = file_get_contents("php://input");
$_movies    = new Movie($body);

header('Content-Type: application/json');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $id         =   $_GET['id']     ?? null;
        $title      =   $_GET['s']      ?? null;
        $apikey     =   $_GET['apikey'] ?? null;

        if ($id || $title && !$apikey) {
            $data = Response::json(401, 'apikey required');
        } else if ($id) {
            $data = $_movies->getMovie($id);
        } else if ($title) {
            $data = $_movies->list($_GET);
        }

        break;
    case 'POST':
        $data       =   $_movies->create($body);
        break;
    case 'PUT':
        $data       =   $_movies->update($body);
        break;
    case 'DELETE':
        $data       =   $_movies->delete($body);
        break;
    default:
        $data       =   Response::json(405);
        break;
}

echo $data;
