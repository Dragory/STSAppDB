<?php

class Login_Controller extends Base_Controller
{
    private $auth = null;

    public function __construct()
    {
        parent::__construct();

        $this->auth = new OIDAuth(null, URL::to_route('checkLogin'));
    }

    public function action_loginPage()
    {
        return View::make('login');
    }

    /**
     * Start the OpenID login by redirecting.
     * @return Redirect
     */
    public function action_startLogin()
    {
        return Redirect::to($this->auth->getAuthURL());
    }

    /**
     * Post-OpenID checks.
     * @return Redirect A redirect to the next location.
     */
    public function action_checkLogin()
    {
        $identity = $this->auth->getCurrentIdentity();
        if (!$identity)
        {
            return Redirect::to_route('login');
        }

        $steam64 = explode('/', $identity);
        $steam64 = array_pop($steam64);

        // Check if we already have the logged in user on record
        $users = new Users;
        $user = $users->getUserBySteam64($steam64);

        // If we're not yet in the database, register.
        if (!$user) $users->register($steam64);

        // Log in
        $status = $users->login($steam64);

        // If everything was successful, redirect to the dashboard
        if ($status)
        {
            return Redirect::to_route('index');
        }

        // Otherwise, redirect to the login page
        // and tell the user something went wrong.
        return Redirect::to_route('login');
    }
}