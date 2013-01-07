<?php

class Main_Controller extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function action_index()
    {
        return '<a href="'.URL::to_route('startLogin').'">Login</a>';
        $auth = new OIDAuth(null, URL::to_route('checkLogin'));
        return print_r($auth->getAuthURL(), true);
    }
}