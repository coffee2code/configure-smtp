=== Configure SMTP ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: email, smtp, gmail, phpmailer, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.5
Tested up to: 6.5
Stable tag: 3.5

Configure SMTP mailing in WordPress, including support for sending email via SSL/TLS (such as Gmail).


== Description ==

Configure SMTP mailing in WordPress, including support for sending email via SSL/TLS (such as Gmail).

This plugin is the official successor to the original SMTP plugin for WordPress (wpPHPMailer).

Use this plugin to customize the SMTP mailing system used by default by WordPress to handle *outgoing* emails. It offers you the ability to specify:

* SMTP host name
* SMTP port number
* If SMTPAuth (authentication) should be used
* SMTP username
* SMTP password
* If the SMTP connection needs to occur over ssl or tls

In addition, you can instead indicate that you wish to use Gmail to handle outgoing email, in which case the above settings are automatically configured to values appropriate for Gmail, though you'll need to specify your Gmail email address (including the "@gmail.com") and password.

Regardless of whether SMTP is enabled, the plugin provides you the ability to define the name and email of the 'From:' field for all outgoing emails.

A simple test button is also available that allows you to send a test email to yourself to check if sending email has been properly configured for your site.

Links: [Plugin Homepage](https://coffee2code.com/wp-plugins/configure-smtp/) | [Plugin Directory Page](https://wordpress.org/plugins/configure-smtp/) | [GitHub](https://github.com/coffee2code/configure-smtp/) | [Author Homepage](https://coffee2code.com)


== Installation ==

1. Whether installing or updating, whether this plugin or any other, it is always advisable to back-up your data before starting.
1. Install via the built-in WordPress plugin installer. Or install the plugin code inside the plugins directory for your site (typically `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to "Settings" -> "Configure SMTP" and configure the plugin's settings. (You can also use the "Settings" link in the plugin's entry on the admin "Plugins" page).
1. (Optional.) Use the built-in test tool (also located on the plugin's settings page) to see if your blog can properly send out emails.


== Frequently Asked Questions ==

= I am already able to receive email sent by my site, so would I have any use or need for this plugin? =

Most likely, no. Not unless you have a preference for having your mail sent out via a different SMTP server, such as Gmail.

= How do I find out my SMTP host, and/or if I need to use SMTPAuth and what my username and password for that are? =

Check out the settings for your local email program. More than likely that is configured to use an outgoing SMTP server. Otherwise, read through documentation provided by your host or contact them (or someone more intimately knowledgeable about your situation) directly.

= I've sent out a few test emails using the test button after having tried different values for some of the settings; how do I know which one worked? =

If your settings worked, you should receive the test email at the email address associated with your WordPress site user account. That email contains a timestamp which was reported to you by the plugin when the email was sent. If you are trying out various setting values, be sure to record what your settings were and what the timestamp was when sending with those settings.

= Why am I getting this error when attempting to send a test message: `SMTP Error: Could not connect to SMTP host.`? =

There are a number of reasons you could be getting this error:

* Your server (or a router to which it is connected) may be blocking all outgoing SMTP traffic.
* Your mail server may be configured to allow SMTP connections only from certain servers.
* You have supplied incorrect server settings (hostname, port number, secure protocol type).

= What am I getting this error: `SMTP Error: Could not authenticate.`? =

The connection to the SMTP server was successful, but the credentials you provided (username and/or password) are not correct. Bear in mind these credentials are likely unrelated to the ones you use to log into your site.

= Does this plugin include unit tests? =

Yes.


== Screenshots ==

1. A screenshot of the plugin's admin settings page.


== Changelog ==

= 3.2 (2016-11-14) =
* New: Add unit tests.
* Change: Handle 'Send email via Gmail?' checkbox changes with JS event listener rather than explicit 'onclick'.
* Change: Move JS into file and enqueue rather outputting directly into footer.
* Change: Update plugin framework to 045. (Too many changes to list.)
* Change: Improve singleton implementation.
    * Add `get_instance()` static method for returning/creating singleton instance.
    * Make static variable 'instance' private.
    * Make constructor protected.
    * Make class final.
    * Additional related changes in plugin framework (protected constructor, erroring `__clone()` and `__wakeup()`).
* Change: Cast submitted 'smtp_auth' (bool), 'port' (int), and 'wordwrap' (int) values to proper type before use.
* Change: Verify submitted 'smtp_secure' value is one of the viable options.
* New: Add class constant `SETTING_NAME` (to store setting name) and use it in `uninstall()`.
* Change: Remove pre-WP3.2-only JavaScript code.
* Change: Use explicit path when requiring plugin framework.
* Fix: Explicitly declare `activation()` and `uninstall()` static.
* Fix: For `options_page_description()`, match method signature of parent class.
* Change: Add support for language packs:
    * Set textdomain using a string instead of a variable.
    * Remove .pot file and /lang subdirectory.
* Change: Discontinue use of PHP4-style constructor.
* Change: Prevent execution of code if file is directly accessed.
* Change: Minor code reformatting (spacing, bracing, conditional comparison order).
* Change: Minor documentation reformatting (spacing, punctuation).
* Change: Re-license as GPLv2 or later (from X11).
* New: Add 'License' and 'License URI' header tags to readme.txt and plugin file.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Use 'email' instead of 'e-mail' on-screen and in documentation.
* Change: Use 'Gmail' instead of 'GMail' on-screen and in documentation.
* Change: Remove file-ending PHP close tag.
* Change: Reformat plugin header.
* Change: Note compatibility through WP 4.7+.
* Change: Dropped compatibility with version of WP older than 4.2.
* Change: Update donate link.
* Change: Update copyright date (2017).

= 3.1 =
* Add new debugging configuration option
* Fix bug that resulted from WP 3.2's update to a new phpmailer
* Fix bug with checking 'Use GMail?' did not auto-reset settings accordingly (jQuery bug regarding .attr() vs .prop() introduced in jQ 1.6 in WP 3.2)
* Fix to call add_filter() instead of add_action() for 'wp_mail_from' (props Callum Macdonald)
* Fix to call add_filter() instead of add_action() for 'wp_mail_from_name'
* Store error messages for later display rather than immediately outputting (too early)
* Save a static version of itself in class variable $instance
* Deprecate use of global variable $c2c_configure_smtp to store instance
* Add explicit empty() checks in a couple places
* Delete plugin settings on uninstallation
* Add __construct(), activation(), and uninstall()
* Add more FAQ questions
* Regenerate .pot
* Update plugin framework to version 023
* Note compatibility through WP 3.2+
* Drop compatibility with versions of WP older than 3.0
* Explicitly declare all functions as public and class variables as private
* Minor code formatting changes (spacing)
* Update copyright date (2011)
* Add plugin homepage and author links in description in readme.txt

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/blog-time/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 3.2 =
Recommended long overdue update. Mostly minor backend improvements and code modernization.

= 3.1 =
Recommended update. Highlights: fixed numerous bugs; added a debug mode; updated compatibility through WP 3.2; dropped compatibility with version of WP older than 3.0; updated plugin framework.

= 3.0.1 =
Minor update. Use password input field for SMTP password instead of regular text input field.

= 3.0 =
Recommended update! This release includes a major re-implementation, bug fixes, localization support, and more.