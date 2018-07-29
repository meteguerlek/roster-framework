<?php

namespace Roster\Auth\Roster;

use App\Models\Role;
use App\Models\User;
use Roster\Sessions\Session;

class Auth
{
    protected static $user = [];

    /**
     * Check if user exist
     *
     * @param $email
     * @return bool
     */
    public static function userExist($email)
    {
        return User::where('email', $email)->count();
    }

    /**
     * Check if user logged else redirect Home
     *
     * @param string $sessions
     * @param $redirect
     * @return bool|void
     */
    public static function isUserLoggedIn($redirect = '/', $sessions = 'user_id')
    {
        return Session::has($sessions)
            ? redirect($redirect)
            : false;
    }

    /**
     * Get user
     *
     * @param string $session
     * @return User|bool
     */
    public static function user($session = 'user_id')
    {
        if (Session::has($session))
        {
            $userId = Session::get($session);

            if (isset(static::$user[$session]))
                return static::$user[$session];

            $user = User::find($userId);

            static::$user[$session] = $user;

            return $user
                ? $user
                : static::logout();
        }

        return false;
    }

    /**
     * @param string $session
     * @return void
     */
    public static function logout($session = 'user_id')
    {
        Session::unset($session);

        return redirect(route('login'));
    }

    /**
     * Check if user is logged
     *
     * @param string $session
     * @return bool
     */
    public static function check($session = 'user_id')
    {
        return Session::has($session)
            ? true
            : false;
    }
}