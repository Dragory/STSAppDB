<?php

class Main_Controller extends Base_Controller
{
    protected $user = null, $lang = null;
    public $layout = '__layout';

    public function __construct()
    {
        parent::__construct();

        // Get the current user
        $users = new Users;
        $id_user = $users->getCurrentUser();

        if (!$id_user) return Redirect::to_route('login');

        $this->user = $users->getUserById($id_user);

        // Get the chosen or default language
        $lang_name_safe = Session::get('lang');
        if (!$lang_name_safe) $lang_name_safe = Cookie::get('lang');
        if (!$lang_name_safe) $lang_name_safe = $users->getDefaultLanguage($this->user->user_steam64);

        $this->lang = $users->getLanguage($lang_name_safe);
    }

    public function loadPage($view, $data = [])
    {
        $data['user'] = $this->user;
        $data['lang'] = $this->lang;

        $this->layout->nest('header', '__header', ['user' => $this->user, 'lang' => $this->lang]);
        $this->layout->nest('content', $view, $data);
        $this->layout->nest('footer', '__footer');
    }

    public function action_changeLanguage()
    {
        if (!isset($_POST['language'])) return Redirect::to_route('index');

        $users = new Users;
        $lang_name_safe = $_POST['language'];
        $lang = $users->getLanguage($lang_name_safe);

        if ($lang)
        {
            Session::put('lang', $lang->lang_name_safe);
            Cookie::put('lang', $lang->lang_name_safe);
        }

        return Redirect::to_route('index');
    }

    public function action_index()
    {
        $this->loadPage('dashboard');
    }
}