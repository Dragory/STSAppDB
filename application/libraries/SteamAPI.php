<?php

/**
 * A library for using the Steam API.
 */
class SteamAPI
{
    public $settings = null;

    /**
     * When the class is created, load the Steam API settings.
     */
    public function __construct()
    {
        $this->settings = $this->readConfigFile();
    }

    /**
     * Reads a config file and returns settings specified in it.
     * @param  string $path Path to the config file
     * @return array        An array of settings.
     */
    public function readConfigFile($path = 'application/config/steamapi.php')
    {
        return include($path);
    }

    /**
     * Makes a request to the Steam API.
     * @param  string $interface The interface to use
     * @param  string $method    The method to use
     * @param  string $version   The version to use
     * @param  array  $query     Additional query values
     * @return object            A json_decoded object containing the result of the request
     */
    public function request($interface, $method, $version, $query = [])
    {
        $url = sprintf('http://api.steampowered.com/%s/%s/v%s/?key=%s&format=%s',
            $interface,
            $method,
            $version,
            $this->settings['apiKey'],
            'json'
        );

        if ($query) $url .= '&'.http_build_query($query);

        $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }
}