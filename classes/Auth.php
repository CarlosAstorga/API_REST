<?php
require_once 'Connection.php';
require_once 'Response.php';
require_once 'Validation.php';
date_default_timezone_set('America/Mexico_City');

class Auth extends Connection
{
    protected $table = 'users';

    public function __construct($input)
    {
        parent::__construct();
        $data                       = json_decode($input);
        $this->Name                 = !empty($data->Name)               ? validate($data->Name)             : '';
        $this->User                 = !empty($data->User)               ? validate($data->User)             : '';
        $this->Password             = !empty($data->Password)           ? validate($data->Password)         : '';
        $this->confirmPassword      = !empty($data->confirmPassword)    ? validate($data->confirmPassword)  : '';
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

    public function register()
    {
        if (!$this->Name)                                       return Response::json(400, 'Name field required');
        if (!$this->User)                                       return Response::json(400, 'Email field required');
        if (!filter_var($this->User, FILTER_VALIDATE_EMAIL))    return Response::json(400, 'Invalid email format');
        if (!$this->Password)                                   return Response::json(400, 'Password field required');
        if (!$this->confirmPassword)                            return Response::json(400, 'confirmPassword field required');
        if ($this->Password !== $this->confirmPassword)         return Response::json(400, "The passwords don't match");
        if ($this->loggedUser)                                  return Response::json(400, 'The user already exists');

        $createdAt = date('Y-m-d H:i');
        $this->Password = parent::encrypt($this->Password);

        $query  = "INSERT INTO " . $this->table . " (name, email, password, created_at) " . "VALUES (?, ?, ?, ?)";
        $stmt   = $this->connection->prepare($query);
        $stmt->bind_param("ssss", $this->Name, $this->User, $this->Password, $createdAt);
        $stmt->execute();

        $userId = $this->connection->insert_id;
        if ($userId) {
            $stmt->close();
            return Response::json(200, 'User created successfully!', ['id' => $userId]);
        } else {
            return Response::json(500);
        }
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
