WPRavenThor - Raven Authorization for Wordpress
================================================

WPRavenAuth is a Wordpress plugin for authorizing University of Cambridge network ("Raven") accounts. 

Created by [Gideon Farrell](https://github.com/gfarrell/) and [Connor Burgess](https://github.com/Burgch) this project is now being maintained by [@mo-g](https://github.com/mo-g) without the legacy authentication options, to give page-level access control via IBIS while using OIDC Connect Generic for AUthentication.

If you need the old WPRavenAUth plugin for any reason, if is archived as the ["AuthenticationArchive" branch](https://github.com/mo-g/WPRavenAuth/tree/AuthenticationArchive).

Version: 1.0.4a0
License: [BSD 3-Clause](http://opensource.org/licenses/BSD-3-Clause)

---

**Backstory**: The Legacy Raven Authentication Service on which this plugin depends will be decommissioned in Q3 2024. Spinning this plugin off to provide the Authorization functions features only allows continuity of access control while changing the login system.

---

Requirements
------------

WPRavenAuth requires hosting *within the University of Cambridge network*, so that it may perform LDAP lookups, which is what we use to determine College and so on. Other than that it can run on any webserver (it doesn't require `mod_ucam_webauth`).

Recent testing has only been on [supported versions](https://www.php.net/supported-versions.php) of PHP. For what limited value of "support" that you'll get from the issues page, assume the only supported version is PHP 8.1.

Installation
------------

The plugin has a single pre-requisite: [WPEngine Advanced Custom Fields](https://en-gb.wordpress.org/plugins/advanced-custom-fields/). Either the pro or free versions will work fine.

To install the plugin, cd to the `wp-content/plugins` directory, and then run `git clone https://github.com/mo-g/WPRavenAuth.git WPRavenAuth`. I would also recommend checking out the latest stable tag.

In the `WPRavenAuth` directory, create a directory called "keys", and add the Raven public key/cert. (Filenames should be `2` and `2.crt` instead of `pubkey2` and `pubkey2.crt` as they are commonly distributed).

Current canonical source for the keys is https://wiki.cam.ac.uk/raven/Raven_keys however there is a mirror at https://w3.charliejonas.co.uk/mirror/raven/keys/ in case there is a change of availability at the canonical source - follow best practice about validating the keys. Previous source https://raven.cam.ac.uk/project/keys/ still contains the key, but not the cert.

Once you've done that, activate the plugin and go to the WPRavenAuth settings in the Wordpress Dashboard (under Settings). Here you can configure which colleges should be available to select for individual post or page visibility. You MUST also change the cookie key to be a long random string with alphanumeric characters and punctuation, which is used for preventing malicious attacks via cookie tampering. You MUST do this immediately after plugin activation or the plugin will continue to throw a warning.

Note that the `php_override.ini` file included in the root of the plugin directory should be moved to the root of your `public_html` directory if you are using the SRCF server for hosting. This is required to enable the `allow_fopen_url` directive, which Ibis requires to function.

Usage
-----

To use the visibility settings, you can select the desired levels of visibility for any page or post individually. These options should appear as custom fields on every post or page. You can also configure the error message which is displayed to users with insufficient privilidges to view the content.

The plugin can also be used in combination with other visibility plugins, such as for menu item visibility, with something like the following as the visibility criterion:

    ((is_user_logged_in()) && (WPRavenAuth\Ibis::isMemberOfCollege(WPRavenAuth\Ibis::getPerson(wp_get_current_user()->user_login), 'EDMUND')))
