<?php

class Main_Controller extends ActionFilter\Filter_Controller
{
    protected $user = null, $lang = null;
    public $layout = '__layout';

    public function before()
    {
        // Make sure we're logged in
        $this->before_filter('getUserAndEnsureLogin');

        // Make sure we have a valid language selected
        $this->before_filter('getLanguageAndEnsureValidity');
    }

    /**
     * A filter that gets the current user's information
     * and makes sure it's valid.
     * @return mixed Returns a Redirect if the user is not logged in or the queried user was not valid.
     */
    protected function getUserAndEnsureLogin()
    {
        // Get the currently logged in user's identifier
        $users = new Users;
        $id_user = $users->getCurrentUser();

        // No user? No access!
        if (!$id_user) return Redirect::to_route('login');

        // Get (and even update) the user's information
        $this->user = $users->getUserById($id_user);

        // Logically this shouldn't happen, but let's have it
        // here just in case someone plays with the model.
        if (!$this->user) return Redirect::to_route('login');
    }

    /**
     * A filter that gets the chosen language, falling back to the default language
     * available to the user if no other is chosen.
     * @return mixed Returns a Redirect if no available language was found.
     */
    protected function getLanguageAndEnsureValidity()
    {
        $users = new Users;

        // Check if the user has access to any languages
        if (!$this->user->lang_access)
        {
            // TO-DO: Add a status message here
            return Redirect::to_route('login');
        }

        // Get the chosen or default language
        $lang_name_safe = Session::get('lang');
        if (!$lang_name_safe) $lang_name_safe = Cookie::get('lang');

        // If no language was chosen or the user doesn't have access
        // to their chosen language, fall back to the default one.
        if (!$lang_name_safe || !in_array($lang_name_safe, $this->user->lang_access))
        {
            $lang_name_safe = $users->getDefaultLanguage($this->user->user_steam64);
        }

        // If we still don't have a valid language,
        // the user doesn't have access to the site.
        if (!$lang_name_safe) return Redirect::to_route('login');

        // Get the language's information
        $this->lang = $users->getLanguage($lang_name_safe);

        // If the language's information wasn't found,
        // switch to the default language and try again.
        if (!$this->lang)
        {
            $lang_name_safe = $users->getDefaultLanguage($this->user->user_steam64);
            $this->lang = $users->getLanguage($lang_name_safe);
        }

        // If the language was STILL not found, there's an error
        // in the database (language access without the language).
        // Inform the user of a server error and redirect.
        if (!$this->lang)
        {
            // TO-DO: Add a status message here
            return Redirect::to_route('login');
        }
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
            Cookie::forever('lang', $lang->lang_name_safe);
        }

        return Redirect::to_route('index');
    }

    public function action_index()
    {
        $this->loadPage('dashboard');
    }

    public function action_applications()
    {
        $applications = new Applications;
        $appList = $applications->getApplicationsForLanguage($this->lang->lang_name_safe);

        $requiredIds = [];
        foreach ($appList as $app)
        {
            $requiredIds[] = $app->app_moderator_steam64;
            $requiredIds[] = $app->app_applicant_steam64;
        }

        $requiredIds = array_unique($requiredIds);

        $users = new Users;
        $accounts = $users->getSteamAccounts($requiredIds);

        $this->loadPage('applications', ['appList' => $appList, 'accounts' => $accounts]);
    }

    public function action_applicationSearch()
    {
        // Make sure the search term is valid
        if (empty($_GET['term'])) return Redirect::to_route('applications');
        $term = $_GET['term'];

        // Get the applications and the required Steam Account information
        $applications = new Applications;
        $appList = $applications->getApplicationsForLanguage($this->lang->lang_name_safe);

        $requiredIds = [];
        foreach ($appList as $app)
        {
            $requiredIds[] = $app->app_moderator_steam64;
            $requiredIds[] = $app->app_applicant_steam64;
        }

        $requiredIds = array_unique($requiredIds);

        $users = new Users;
        $accounts = $users->getSteamAccounts($requiredIds);

        // Perform the search
        $results = $applications->searchApplications($appList, $accounts, $term);

        $this->loadPage('application_search', ['appList' => $results, 'accounts' => $accounts]);
    }

    public function action_addApplication()
    {
        $this->loadPage('add_application');
    }
}