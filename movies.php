<?php

require_once 'classes/Movie.php';
require_once 'classes/Response.php';

$data       = null;
$body       = file_get_contents("php://input");
$_movies    = new Movie($body);

header('Content-Type: application/json');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $imdbID     =   $_GET['i']          ?? null;
        $search     =   $_GET['s']          ?? null;
        $apikey     =   $_GET['apikey']     ?? null;
        $title      =   $_GET['t']          ?? null;

        if (($imdbID || $search || $title)  && !$apikey) {
            $data   = Response::json(401, 'No API key provided.');
        } else if ($imdbID) {
            $data   = $_movies->getMovieById($imdbID);
        } else if ($search) {
            $data   = $_movies->list($_GET);
        } else if ($title) {
            $data   = $_movies->getMovieByTitle($_GET);
        } else {
            $data   = Response::json(401, 'Incorrect IMDb ID.');
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
