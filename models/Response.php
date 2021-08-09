<?php

class Response
{

    static function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    static function json(int $code, string $message = "")
    {
        self::setStatusCode($code);
        $arr['status_code'] = $code;
        if (!empty($message)) $arr['message'] = $message;
        return json_encode($arr);
    }
}
