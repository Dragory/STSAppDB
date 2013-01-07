<?php

class Users
{
    private $api = null;
    private $tokenChars = 'abcdefghijklmnopqrstuvwxyz';

    public function __construct()
    {
        $this->api = new SteamAPI;
    }

    protected function getUser($where)
    {
        // Find a user according to the $where lines/rules
        $query = DB::table('users');

        foreach ($where as $line) $query->where($line[0], $line[1], $line[2]);
        
        $user = $query->first();

        // If no user was found, return null (as the query would in case of no results)
        if (!$user) return null;

        // Get the user's Steam Account information
        $user->account = $this->getSteamAccount($user->user_steam64);

        // Get the user's language access rules
        $user->lang_access = $this->getLanguageAccess($user->user_steam64);

        // Return the user
        return $user;
    }

    public function getLanguage($lang_name_safe)
    {
        if (!$lang_name_safe) return null;

        return DB::table('languages')
            ->where('lang_name_safe', '=', $lang_name_safe)
            ->first();
    }

    public function getLanguageAccess($user_steam64)
    {
        /*$access = DB::table('access')
            ->where('user_steam64', '=', $user_steam64)
            ->get();*/

        $access = DB::table('languages')
            ->get();

        $return = [];
        foreach ($access as $lang) $return[] = $lang->lang_name_safe;

        return $return;
    }

    public function getDefaultLanguage($user_steam64)
    {
        $access = $this->getLanguageAccess($user_steam64);

        if (!$access) return null;
        return $access[0];
    }

    public function getSteamAccounts($steam64Ids, $iteration = 0)
    {
        if (empty($steam64Ids)) return [];

        // Calculate the expiry date (when we should update the data)
        $expiryDate = date('Y-m-d H:i:s', time()-60*60*6);

        // Get the accounts we want
        $accounts = DB::table('steam_accounts')
            ->where_in('acc_steam64', $steam64Ids)
            ->where('acc_updated', '>', $expiryDate)
            ->get();

        // Loop through the accounts and build a filtering array for array_filter.
        $missingIds = $steam64Ids;
        $foundIds = [];
        foreach ($accounts as $account)
        {
            $foundIds[] = $account->acc_steam64;
        }

        $missingIds = array_diff($missingIds, $foundIds);

        // If there were any missing IDs, get their information
        // from the API and save that to the database.
        if ($missingIds)
        {
            // However, if we've already done this once (iteration #2), we can assume the
            // data simply is not there for whatever reason. In that
            // case we'll just return "null" values for those entries.
            if ($iteration > 0)
            {
                foreach ($missingIds as $id)
                {
                    $tempAccount = new stdClass;
                    $tempAccount->acc_steam64 = $id;
                    $tempAccount->id_account  = null;
                    $tempAccount->acc_name    = null;
                    $tempAccount->acc_avatar  = null;
                    $tempAccount->acc_updated = null;

                    $accounts[] = $tempAccount;
                }
            }
            // Otherwise go for the API
            else
            {
                Log::write('info', 'Hitting the API for '.count($missingIds).' 64-bit Steam IDs.');
                $request = $this->api->request(
                    'ISteamUser',
                    'GetPlayerSummaries',
                    '0002',
                    ['steamids' => implode(',', $missingIds)]
                );

                // If we got the API response, delete any possible previous
                // entries from the database and add the new ones according
                // to the API response.
                $apiFoundIds = [];
                if ($request && !empty($request->response->players))
                {
                    // Deleting...
                    DB::table('steam_accounts')
                        ->where_in('acc_steam64', $missingIds)
                        ->delete();

                    // Inserting...
                    $insertArray = [];
                    foreach ($request->response->players as $player)
                    {
                        $insertArray[] = [
                            'acc_steam64' => $player->steamid,
                            'acc_name'    => $player->personaname,
                            'acc_avatar'  => $player->avatar,
                            'acc_updated' => DB::raw('NOW()')
                        ];
                    }

                    DB::table('steam_accounts')
                        ->insert($insertArray);
                }

                // Alright, data fixed.
                // Let's try looking for the data in the database again.
                return $this->getSteamAccounts($steam64Ids, 1);
            }
        }
        
        // Now let's format the ID array
        $return = [];
        foreach ($accounts as $account)
        {
            $return[$account->acc_steam64] = $account;
        }

        return $return;
    }

    public function getSteamAccount($steam64)
    {
        $accounts = $this->getSteamAccounts([$steam64]);

        if (!$accounts) return null;
        return array_shift($accounts);
    }

    /**
     * Gets a user by their User ID.
     * @param  int   $id_user The ID of the user.
     * @return mixed          Null if the user was not found, an object containing the user's information otherwise.
     */
    public function getUserById($id_user)
    {
        return $this->getUser([
            ['id_user', '=', $id_user]
        ]);
    }

    /**
     * Gets a user by their 64-bit Steam ID.
     * @param  int   $user_steam64 The Steam64 ID of the user.
     * @return mixed               Null if the user was not found, an object containing the user's information otherwise.
     */
    public function getUserBySteam64($user_steam64)
    {
        return $this->getUser([
            ['user_steam64', '=', $user_steam64]
        ]);
    }

    /**
     * If the current visitor is logged in, return their user.
     * @return mixed Null if the visitor is not logged in, the user's ID if they are.
     */
    public function getCurrentUser()
    {
        // See if we have a login token stored somewhere
        $token = null;
        $token = Session::get('token');
        if (!$token) $token = Cookie::get('token');

        // If not, we're not logged in
        if (!$token) return null;

        // Otherwise we probably are. Try to find the user.
        $user = DB::table('users')
            ->where('user_token', '=', $token)
            ->first();

        if (!$user) return null;

        // Alright, so we are logged in. Refresh our login (session)
        // and return the user's ID.
        DB::table('users')
            ->where('id_user', '=', $user->id_user)
            ->update([
                'user_time_active' => DB::raw('NOW()')
            ]);

        Session::put('token', $token);

        return $user->id_user;
    }

    /**
     * Logs a user in by their 64-bit Steam ID.
     * Also updates their information from the Steam Web API.
     * @param  int  $user_steam64 The Steam64 ID of the user.
     * @return bool               Whether the login was successful.
     */
    public function login($user_steam64)
    {
        // Get the user in question
        $user = $this->getUserBySteam64($user_steam64);
        if (!$user) return false;

        // Get the user's login token and generate a new one
        // if none was found. Also save the new token.
        $token = $user->user_token;
        if (!$token)
        {
            $token = $this->generateToken($user->id_user);

            DB::table('users')
                ->where('id_user', '=', $user->id_user)
                ->update([
                    'user_token'  => $token
                ]);
        }

        // Log the user in by saving the login token
        // in SESSION and in a cookie
        Session::put('token', $token);
        Cookie::forever('token', $token);

        // Everything went as expected!
        return true;
    }

    /**
     * Generates a token to use for authentication.
     * The length is always 59 or 60 characters (bcrypt).
     * @param  mixed  $prefix A prefix to the token. Used to reduce collisions.
     * @return string          The token.
     */
    protected function generateToken($prefix)
    {
        // Generate a "password" we're going to hash for the token
        $return = (string)$prefix;
        while (strlen($return) < 32)
        {
            if (mt_rand(0, 1) == 1)
                $return .= strtolower($this->tokenChars[mt_rand(0, strlen($this->tokenChars) - 1)]);
            else
                $return .= strtoupper($this->tokenChars[mt_rand(0, strlen($this->tokenChars) - 1)]);
        }

        // Now hash it
        $return = password_hash($return, PASSWORD_BCRYPT, ["cost" => 10]);

        // And return it
        return $return;
    }

    /**
     * Registers a user by their Steam64 ID.
     * @param  int  $steam64 The user's Steam64 ID
     * @return bool          Whether the registration was successful or not.
     */
    public function register($steam64)
    {
        DB::table('users')
            ->insert([
                'user_steam64' => $steam64,
                'user_time_register' => DB::raw('NOW()'),
                'user_time_active' => DB::raw('NOW()')
            ]);

        return true;
    }
}