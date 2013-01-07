<?php

class Login_Controller extends Base_Controller
{
    private $auth = null;

    public function __construct()
    {
        parent::__construct();

        $this->auth = new OIDAuth(null, URL::to_route('checkLogin'));
    }

    /**
     * Start the OpenID login by redirecting.
     * @return Redirect
     */
    public function action_startLogin()
    {
        return Redirect::to($this->auth->getAuthURL());
    }

    public function action_checkLogin()
    {
        $identity = $this->auth->getCurrentIdentity();

        $return = '';

        if ($identity) $return .= 'Login successful! Identity: '.$identity;
        else $return .= 'Something happened!';

        $return .= '<br><br>'.print_r($this->auth->getOID()->getAttributes(), true);
        return $return;
    }
}