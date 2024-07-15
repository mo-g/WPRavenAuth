WPRavenThor - Raven Authorization for Wordpress
================================================

WPRavenThor is a Wordpress plugin for authorizing University of Cambridge network ("Raven") accounts for page-level access control. 

Created by [Gideon Farrell](https://github.com/gfarrell/) and [Connor Burgess](https://github.com/Burgch) this project is now being maintained by [@mo-g](https://github.com/mo-g) without the legacy authentication options, to give page-level access control based on institution affiliations from the Lookup service via IBIS, while using alternative logon mechanisms for Authentication.

If you need the old WPRavenAuth plugin for any reason, if is archived as the ["AuthenticationArchive" branch](https://github.com/mo-g/WPRavenThor/tree/AuthenticationArchive).

Version: 2.0.0
License: [BSD 3-Clause](http://opensource.org/licenses/BSD-3-Clause)

---

**Backstory**: The Legacy Raven Authentication Service on which this plugin depends will be decommissioned in Q3 2024. Spinning this plugin off to provide the Authorization functions features only allows continuity of access control while changing the login system. Doing it this way allows sites to retain their existing access control data without needing to manually reset permissions at a page level; as long as they were previously using WPRavenAuth. As it stands, this plugin works, however you should consider this to be experimental and not safe for personal data or official secrets, etc, etc.

---

Requirements
------------

WPRavenThor requires hosting *within the University of Cambridge network*, so that it may perform IBIS queries to Lookup, which is what we use to determine College and so on. Other than that it can run on any webserver (it doesn't require `mod_ucam_webauth`).

Recent testing has only been on [supported versions](https://www.php.net/supported-versions.php) of PHP. For what limited value of "support" that you'll get from the issues page, assume the only supported version is PHP 8.1.

Installation
------------

The plugin has a single guaranteed pre-requisite: [WPEngine Advanced Custom Fields](https://en-gb.wordpress.org/plugins/advanced-custom-fields/). Either the pro or free versions will work fine. Currently, this is a little bugged and it might not work without the internal one. Exploring.

Otherwise, the only authentication system that has currently been tested is [Daggerhart's OpenID Connect Generic](https://en-gb.wordpress.org/plugins/daggerhart-openid-connect-generic/) with an Identity Key `preferred_username` and Email Formatting `{email}`. 

To install the plugin, cd to the `wp-content/plugins` directory, and then run `git clone https://github.com/mo-g/WPRavenThor.git WPRavenThor --recurse-submodules`.

Once you've done that, activate the plugin and go to the WPRavenThor settings in the Wordpress Dashboard (under Settings). Here you can configure which colleges should be available to select for individual post or page visibility. You MUST also change the cookie key to be a long random string with alphanumeric characters and punctuation, which is used for preventing malicious attacks via cookie tampering. You MUST do this immediately after plugin activation or the plugin will continue to throw a warning.

Note that the `php_override.ini` file included in the root of the plugin directory should be moved to the root of your `public_html` directory if you are using the SRCF server for hosting. This is required to enable the `allow_fopen_url` directive, which Ibis requires to function.

Usage
-----

To use the visibility settings, you can select the desired levels of visibility for any page or post individually. These options should appear as custom fields on every post or page. You can also configure the error message which is displayed to users with insufficient privilidges to view the content.

The plugin can also be used in combination with other visibility plugins, such as for menu item visibility, with something like the following as the visibility criterion:

    ((is_user_logged_in()) && (WPRavenAuth\Ibis::isMemberOfCollege(WPRavenAuth\Ibis::getPerson(wp_get_current_user()->user_login), 'EDMUND')))
