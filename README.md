WPRavenAuth - Raven authentication for Wordpress
================================================

WPRavenAuth is a Wordpress plugin for authenticating University of Cambridge network ("Raven") accounts. 

Created by [Gideon Farrell](https://github.com/gfarrell/) and [Connor Burgess](https://github.com/Burgch) this project is now being maintained by [@mo-g](https://github.com/mo-g) until such a time as it can adequately be replaced with modern standards.

Version: 1.0.3
License: [BSD 3-Clause](http://opensource.org/licenses/BSD-3-Clause)

---

**Warning**: The Legacy Raven Authentication Service on which this plugin depends will be decommissioned in early 2024. Any service using the plugin must transition to a native OAuth2 + LDAP based access control system before that time.

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

The plugin will replace the login system with a Raven login page - if a user who has never used your site before logs in with their Raven account, a new Wordpress account will be automatically created for them (with their CRSID as the username of the account and their lookup visible name as their display name).

NB: You can access the original Wordpress Login by adding `?super-admin=1` to your login url (e.g. `http://www.mywebsite.com/wp-login.php?super-admin=1`).

If any existing users are set up with their university email addresses *@cam.ac.uk* for the email field, they will never be able to log in with the new system (unless their username is also their crsid in lower case). If such users exist, they should be deleted, or if their username is NOT their crsid, they can change the email associated with their user to an external (i.e. non *@cam.ac.uk*) address. This should be done before activating this plugin.

By default, the newly created users will have *Subscriber* permissions. To promote a user to another permission level, find their account in the normal Wordpress *Users* section and modify it in the normal manner.

To use the visibility settings, you can select the desired levels of visibility for any page or post individually. These options should appear as custom fields on every post or page. You can also configure the error message which is displayed to users with insufficient privilidges to view the content.

The plugin can also be used in combination with other visibility plugins, such as for menu item visibility, with something like the following as the visibility criterion:

    ((is_user_logged_in()) && (WPRavenAuth\Ibis::isMemberOfCollege(WPRavenAuth\Ibis::getPerson(wp_get_current_user()->user_login), 'EDMUND')))
