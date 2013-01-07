<?php

/**
 * Handles OpenID authentication.
 * Other user-related methods are contained in the
 * Users model (models/Users.php).
 */
class OIDAuth
{
    private $openid = null, // The OpenID handler
            $defaultUrl = 'http://steamcommunity.com/openid'; // The OpenID provider

    public function __construct($url = null, $returnUrl = null)
    {
        // If no URL is supplied, use the default one
        if (!$url) $url = $this->defaultUrl;

        // Create the object and use our current URL as the "trusted root"
        $this->openid = new LightOpenID(URL::base());
        if ($returnUrl) $this->openid->returnUrl = $returnUrl;

        // Use the URL as the identity
        $this->openid->identity = $url;
    }

    public function getCurrentIdentity()
    {
        if ($this->openid->validate()) return $this->openid->identity;
        return null;
    }

    public function getAuthURL()
    {
        return $this->openid->authUrl();
    }

    public function getOID()
    {
        return $this->openid;
    }
}