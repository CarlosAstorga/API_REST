<?php

class Connection
{
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $connection;

    function __construct()
    {
        $data = $this->getConnectionData();
        foreach ($data as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }

        $this->connection = new mysqli($this->server, $this->user, $this->password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            die('Connection error: ' . $this->connection->connect_error);
        }
    }

    private function getConnectionData()
    {
        try {
            $config_file_path = dirname(__FILE__) . "/" . "config";

            if (!file_exists($config_file_path)) {
                throw new Exception("Configuration file not found.");
            }
            return json_decode(file_get_contents($config_file_path), true);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function toUTF8($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $array;
    }

    public function getData($query)
    {
        $results = $this->connection->query($query);
        $array_results = [];
        foreach ($results as $key) {
            $array_results[] = $key;
        }

        return $this->toUTF8($array_results);
    }
}
