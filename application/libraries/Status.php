<?php

class Status
{
    private function __construct() {}

    private static function initMessages()
    {
        Session::put('status', [
            'error' => [],
            'success' => []
        ]);
    }

    private static function addMessage($text, $type = 0)
    {
        if (!Session::has('status')) self::initMessages();

        $status = Session::get('status');

        if ($type == 0)
            $status['success'][] = $text;
        else
            $status['error'][] = $text;

        Session::put('status', $status);
    }

    public static function getMessages($flush = true)
    {
        if (!Session::has('status')) self::initMessages();
        $status = Session::get('status');

        if ($flush) Session::forget('status');

        return $status;
    }

    public static function addSuccess($text)
    {
        self::addMessage($text, 0);
    }

    public static function addError($text)
    {
        self::addMessage($text, 1);
    }
}