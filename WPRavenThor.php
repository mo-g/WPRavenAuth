<?php
/*
    WPRavenThor - Raven authentication for Wordpress
    ================================================

    @license BSD 3-Clause http://opensource.org/licenses/BSD-3-Clause
    @author  Gideon Farrell <me@gideonfarrell.co.uk>, Conor Burgess <Burgess.Conor@gmail.com>
    @url     https://github.com/mo-g/WPRavenThor

    Plugin Name: WPRavenThor
    Plugin URI: https://github.com/mo-g/WPRavenThor
    Description: Replace wordpress login with Raven authentication.
    Version: 2.0.0
    Author: Gideon Farrell <me@gideonfarrell.co.uk>, Conor Burgess <Burgess.Conor@gmail.com>, mo-g Gray <10463172+mo-g@users.noreply.github.com>
 */

namespace WPRavenAuth {

// Some quickly bootstrapped definitions
if(!defined('DS')) {
    define('DS', '/');
}
define('WPRavenAuth_dir', dirname(__file__));
define('WPRavenAuth_keys', WPRavenAuth_dir . DS . 'keys');

// Load required files
require('app/core/set.php');                // Array manipulation library
require('app/core/config.php');             // Configuration wrapper
require('app/core/ibis.php');               // Use Ibis database; much more robust than ldap
require('pages/options.php');               // Options page for wp-admin

// Initialise Raven
add_action('init', 'WPRavenAuth\setup');

function setup()
{
    // Need to require here so other ACF plugins are loaded first
    require('app/core/custom_fields.php');      // Custom fields for visibility settings

    // Add filters for authentication on pages
    add_filter('the_posts', 'WPRavenAuth\showPost');
    add_filter('get_pages', 'WPRavenAuth\showPost');
}

// Used to disable unnecessary functions
function  disable_function()
{
    die('Disabled');
}

/**
 * Returns the current user.
 *
 * @return WP_User
 */
function getCurrentUser()
{
    if (!function_exists('get_userdata')) {
        require_once(ABSPATH . WPINC . '/pluggable.php');
    }

    //Force user information
    return wp_get_current_user();
}

function userCanAccessPost($postID, $crsid)
{
    $postVisibility = get_field('custom_visibility', $postID);

    if (!is_array($postVisibility))
        $postVisibility = array('public');

    if (in_array('public', $postVisibility))
        return true;
    elseif (in_array('raven', $postVisibility))
        return is_user_logged_in();
    elseif (is_user_logged_in())
    {
        $person = Ibis::getPerson($crsid);
        foreach ($postVisibility as $inst)
        {
            $inst_split = explode('-',$inst);
            if (strcmp($inst_split[0], 'COLL') == 0)
            {
                if (Ibis::isMemberOfCollege($person, $inst_split[1]))
                    return true;
            }
            elseif (strcmp($inst_split[0], 'INST') == 0)
            {
                if (Ibis::isMemberOfInst($person, $inst_split[1]))
                    return true;
            }
        }
    }
    return false;
}

function showPost($aPosts = array())
{
    $aShowPosts = array();
    $userCRSID = '';
    if (is_user_logged_in())
    {
        $currentUser = getCurrentUser();
        $userCRSID = $currentUser->user_login;
    }
    foreach ($aPosts as $aPost)
    {
        if (!userCanAccessPost($aPost->ID,$userCRSID))
        {
            //$aPost->post_title = "Restricted Content";
            $postContent = get_field('error_message', $aPost->ID);
            $excerptContent = $postContent;
            if (!is_user_logged_in())
            {
                $postContent .= '<p>You may be able to access this content if you <a href="' . wp_login_url() . '?redirect_to=' . get_permalink($aPost->ID) . '">login</a>.</p>';
                $excerptContent .= ' <a href="' . wp_login_url() . '?redirect_to=' . get_permalink($aPost->ID) . '">Try logging in?</a>.';
            }
            $aPost->post_content = $postContent;
            $aPost->post_excerpt = $excerptContent;
        }
        $aShowPosts[] = $aPost;
    }

    $aPosts = $aShowPosts;

    return $aPosts;
}

} // End namespace

namespace { // Global namespace

} // End Global Namespace
