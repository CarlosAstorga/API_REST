<?php
require_once 'Connection.php';
require_once 'Response.php';

class Auth extends Connection
{

    public function login($json)
    {
        $data = json_decode($json, true);
        if (!isset($data['user']) || !isset($data['password'])) return Response::json(400);

        $currentUser = $this->getUser($data['user']);
        if (!$currentUser) return Response::json(404, "User {$data['user']} not found");

        $password = parent::encrypt($data['password']);
        if ($currentUser->password !== $password) return Response::json(422, "The password doesn't match");

        if (!$currentUser->active) return Response::json(403, "The user isn't active");

        $token = $this->insertToken($currentUser->id);
        if (!$token) return Response::json(500);

        return Response::json(200, $token);
    }

    private function getUser($email)
    {
        $query  = "SELECT * FROM users WHERE email = '$email'";
        $user   = parent::getData($query);

        return isset($user[0]['id']) ? (object)$user[0] : false;
    }

    private function insertToken($user_id)
    {
        date_default_timezone_set('America/Mexico_City');

        $bool   = true;
        $token  = bin2hex(openssl_random_pseudo_bytes(16, $bool));
        $date   = date('Y-m-d H:i');
        $status = 1;
        $query  = "INSERT INTO user_token (user_id, token, status, date) VALUES ('$user_id', '$token', '$status', '$date')";
        $result = parent::execute($query);

        return $result ? $token : $result;
    }
}
