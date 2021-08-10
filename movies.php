<?php

require_once 'models/Movie.php';
require_once 'models/Response.php';

$data       = null;
$body       = file_get_contents("php://input");
$_movies    = new Movie;

header('Content-Type: application/json');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $id         =   $_GET['id']   ?? null;
        $page       =   $_GET['page'] ?? null;

        if ($page)      $data = $_movies->list($page);
        else if ($id)   $data = $_movies->getMovie($id);

        if ($data)      $data = json_encode($data);
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
