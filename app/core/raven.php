<?php
/*
    Raven
    -----

    Provides wrappers for logging in and out, as well as user validation.

    @file    raven.php
    @license BSD 3-Clause
    @package WPRavenAuth
    @author  Gideon Farrell <me@gideonfarrell.co.uk>
 */

namespace WPRavenAuth;

require_once(ABSPATH . '/wp-settings.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

if (!defined('DS'))
    define('DS', '/');
if (!defined('WPRavenAuth_dir'))
    define('WPRavenAuth_dir', substr(__FILE__, 0, strpos(__FILE__, 'app') - 1));
if (!defined('WPRavenAuth_keys'))
    define('WPRavenAuth_keys', WPRavenAuth_dir . DS . 'keys');
require_once(WPRavenAuth_dir . '/app/lib/ucam_webauth.php');

require_once(WPRavenAuth_dir . '/app/lib/ibis-client/ibisclient/client/IbisClientConnection.php');
require_once(WPRavenAuth_dir . '/app/lib/ibis-client/ibisclient/methods/PersonMethods.php');

require_once(WPRavenAuth_dir . '/app/core/ibis.php');

class Raven
{
    /**
     * $webauth
     * Contains the ucam_webauth instance.
     *
     * @var    Ucam_Webauth
     * @access protected
     */
    protected $webauth = null;

    /**
     * __construct
     * Stop anyone else making a Raven instance.
     *
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * getInstance
     * Creates or retrieves the Singleton instance.
     *
     * @access public
     *
     * @return Raven instance
     */
    public static function &getInstance()
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new Raven();
        }

        return $instance;
    }


    /**
     * login
     * Logs the user in.
     *
     * @access public
     *
     * @return void
     */
    public function login()
    {
        if (is_null($this->webauth)) {
            $this->webauth = new Ucam_Webauth(array(
                'key_dir'       => WPRavenAuth_keys,
                'cookie_key'    => Config::get('cookie_key'),
                'cookie_name'   => Config::get('cookie'),
                'hostname'      => $_SERVER['HTTP_HOST'],
            ));
        }
        $auth = $this->webauth->authenticate();
        if (!$auth) {
            // This isn't an error, it just means the authentication process isn't yet complete
            // and there's about to be another redirect, so we need to return.
            return;
        }

        if (!($this->webauth->success())) {
            throw new AuthException("Raven Authentication not completed.");
        }

        $username = $this->webauth->principal();
        $email = $username . '@cam.ac.uk';

        if (function_exists('get_user_by') && function_exists('wp_create_user')) {
            $registration = !$this->userExists($username);
            if ($registration) {
                // User is not in the WordPress database
                // they passed Raven and so are authorized
                // add them to the database (password field is arbitrary, but must
                // be hard to guess)
                $user_id = wp_create_user($username, Raven::_pwd($username), $email);

                if (!$user_id)
                    throw new AuthException('Could not create user');

                $person = Ibis::getPerson($username);
                update_user_meta($user_id, 'display_name', $person->visibleName);
            }

            $user = $this->getWpUser($username);
            wp_set_auth_cookie($user->ID, false, '');
            do_action('wp_login', $user->user_login, $user);

            session_start();

            $redirect_url = $_SESSION["raven_redirect_to"] ?? admin_url();
            unset($_SESSION["raven_redirect_to"]);

            if ($registration)
                $redirect_url = apply_filters('registration_redirect', $redirect_url);

            wp_safe_redirect($redirect_url);
        } else {
            throw new AuthException('Could not load user data');
        }
    }

    /**
     * logout
     * Logs the user out.
     *
     * @access public
     *
     * @return void
     */
    public function logout()
    {
        setcookie(Config::get('cookie'), '');
        wp_clear_auth_cookie();
    }

    /**
     * userExists
     * Checks if a given user exists by crsid
     *
     * @param string $crsid User's CRSID.
     *
     * @access public
     *
     * @return Boolean
     */
    public function userExists($crsid)
    {
        return (get_user_by('login', $crsid) != false);
    }

    /**
     * getWpUser
     * Retrieves the WP User object
     *
     * @param string $crsid User's CRSID.
     *
     * @access public
     *
     * @return WPUser object
     */
    public function getWpUser($crsid)
    {
        return get_user_by('login', $crsid);
    }

    /**
     * _pwd
     * Returns the generic password hash, since passwords aren't important for SSO, but are for WP.
     *
     * @static
     *
     * @access public
     *
     * @return string password
     */
    public static function _pwd($username)
    {
        return md5(Config::get('salt') . $username);
    }
}
