<?php

namespace SocialiteProviders\Keycloak;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'KEYCLOAK';


    public static function additionalConfigKeys()
    {
        return ['base_url', 'realms'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseUrl()
    {
        return rtrim(rtrim($this->getConfig('base_url'), '/') . '/realms/' . $this->getConfig('realms', 'master'), '/');
    }


    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl() . '/protocol/openid-connect/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->getBaseUrl() . '/protocol/openid-connect/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getBaseUrl() . '/protocol/openid-connect/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => $user['preferred_username'],
            'name' => $user['given_name'],
            'email' => $user['email'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
