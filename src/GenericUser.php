<?php namespace Reshadman\LmAuth;

use Illuminate\Auth\GenericUser as BaseGenericUser;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class GenericUser extends BaseGenericUser implements UserContract {

    /**
     * @var array
     */
    private static $config;

    /**
     * Get auth identifier
     *
     * @return string
     */
    public function getAuthIdentifier()
    {
        return $this->attributes[$this->getConfig()['auth_id_field']];
    }

    /**
     * Get config array
     *
     * @return array
     */
    private function getConfig()
    {
        if(empty(self::$config))
        {
            self::$config = config('lmauth', []);
        }

        return self::$config;
    }

    /**
     * Get auth password
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->attributes[$this->getConfig()['auth_password_field']];
    }

    public function getRememberToken()
    {

    }
}