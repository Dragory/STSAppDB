<?php

/**
 * A library for using an other origin Steam API relay.
 * NOTE: Not actually used. See SteamAPI.php.
 */
class SteamAPIRelay
{
    public $settings = null;

    public function __construct()
    {
        $this->settings = $this->readConfigFile();
    }

    public function readConfigFile($path = 'application/config/steamapi.php')
    {
        return include($path);
    }

    public function request($interface, $method, $version, $query = [])
    {
        $url = $this->settings['relay'];
        $url .= '?'.http_build_query([
            'interface' => $interface,
            'method'    => $method,
            'version'   => $version,
            'authKey'   => $this->settings['authKey']
        ]);

        if ($query) $url .= '&'.http_build_query($query);

        $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }
}