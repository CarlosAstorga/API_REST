<?php

class Response
{

    static function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    static function json(int $code, string $message = "", $data = [])
    {
        self::setStatusCode($code);
        $arr['Response'] = $code == 200 ? "True" : "False";

        if (!empty($message)) {
            $code == 200 ? $arr['Message'] = $message : $arr['Error'] = $message;
        }

        if (!empty($data)) {
            $arr = array_merge($arr, $data);
        }

        return json_encode($arr);
    }
}
