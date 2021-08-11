<?php
require_once 'Connection.php';
require_once 'Response.php';

class Auth extends Connection
{
    public function __construct($input)
    {
        parent::__construct();
        $data                       = json_decode($input);
        $this->User                 = $data->User       ?? '';
        $this->Password             = $data->Password   ?? '';
        $this->loggedUser           = $this->getUser();
    }

    public function login()
    {
        if (!$this->User || !$this->Password) return Response::json(400, 'User AND Password are required');
        if (!$this->loggedUser) return Response::json(404, "User $this->User not found!");

        $password = parent::encrypt($this->Password);
        if ($this->loggedUser->password !== $password) return Response::json(422, "The password doesn't match");

        if (!$this->loggedUser->active) return Response::json(403, "The user isn't active");

        $token = $this->insertToken($this->loggedUser->id);
        if (!$token) return Response::json(500);

        return Response::json(200, 'Token created successfully', ['Token' => $token]);
    }

    private function getUser()
    {
        $query  = "SELECT * FROM users WHERE email = '$this->User'";
        $user   = parent::getData($query);

        return isset($user[0]['id']) ? (object)$user[0] : null;
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
