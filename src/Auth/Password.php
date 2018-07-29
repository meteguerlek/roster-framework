<?php

namespace Roster\Auth;

class Password
{
    /**
     * Create password
     *
     * @param $password
     * @param array $options
     * @return bool|string
     */
    public static function create($password, $options = [])
    {
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * Verify password
     *
     * @param $password
     * @param $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Change password
     *
     * @param $oldPassword
     * @param $hash
     * @param $newPassword
     * @return bool|string
     */
    public static function change($oldPassword, $hash, $newPassword)
    {
        return self::verify($oldPassword, $hash)
            ? self::create($newPassword)
            : false;
    }
}
