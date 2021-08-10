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
        $arr['status_code'] = $code;
        if (!empty($message)) $arr['message'] = $message;
        if (!empty($data)) $arr['results'] = $data;
        return json_encode($arr);
    }
}
