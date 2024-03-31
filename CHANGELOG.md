# Changelog

## 3.5 _(2024-03-31)_
* Change: Update plugin framework to 067
  * A significant update from the previous version 046.
  * 067:
    * Breaking: Require config attribute 'input_attributes' to be an array
    * Hardening: Treat input attributes as array and escape each element before output
    * Change: Ensure config attribute values are of the same datatype as their defaults
    * Change: Simplify `form_action_url()` to avoid using a server global
    * Change: Use `form_action_url()` in `plugin_action_links()` rather than duplicating its functionality
    * Change: Escape output of all translated strings
    * Change: Make `get_hook()` public rather than protected
    * Change: Explicitly declare object variables rather than doing so dynamically
    * Change: Convert `register_filters()` to an abstract declaration
    * Change: Use double quotes for attribute of paragraph for setting description
    * Change: Prevent unwarranted PHPCS complaints about nonces
    * Change: Improve function documentation
    * Change: Adjust function documentation formatting to align with WP core
    * Change: Note compatibility through WP 6.5+
    * Change: Drop compatibility with version of WP older than 5.5
    * Change: Update copyright date (2024)
* Change: Initialize plugin on `plugins_loaded` action instead of on load
* Change: Escape all translated text before display
* Change: Replace a few terms used in translated strings
* Change: Add translator comments for all strings with placeholders
* New: Add README.md
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add TODO.md file and move existing TODO list from top of main plugin file into it (and add to it)
* New: Add `.gitignore` file
* Change: Use `wp_add_inline_script()` instead of `wp_localize_script()`
* Unit tests:
    * Fix: Allow tests to run against current versions of WordPress
    * New: Add `composer.json` for PHPUnit Polyfill dependency
    * Change: Prevent PHP warnings due to missing core-related generated files
    * New: Add test to check that the appropriate number of framework strings are translatable
    * Change: In bootstrap, add backcompat for PHPUnit pre-v6.0
    * Change: Restructure unit test file structure
        * New: Create new subdirectory `tests/phpunit/` to house all files related to unit testing PHP
        * Change: Move `bin/` to `tests/bin/`
        * Change: Move `tests/` to `tests/phpunit/tests/`
        * Change: Rename `phpunit.xml` to `phpunit.xml.dist` per best practices
    * Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable
    * Enable more error output for unit tests
* Change: Tweak installation instructions
* Change: Update links to coffee2code.com to be HTTPS
* Change: Add GitHub link to readme
* Change: Reduce number of tags defined in readme.txt
* Change: Note compatibility through WP 6.5+
* Change: Drop compatibility with versions of WP older than 5.5
* Change: Update copyright date (2024)

## 3.2 _(2016-11-14)_
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

## 3.1
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

## 3.0.1
* Update plugin framework to 017 to use password input field instead of text field for SMTP password

## 3.0
* Re-implementation by extending C2C_Plugin_016, which among other things adds support for:
    * Reset of options to default values
    * Better sanitization of input values
    * Offload of core/basic functionality to generic plugin framework
    * Additional hooks for various stages/places of plugin operation
    * Easier localization support
* Add error checking and reporting when attempting to send test e-mail
* Don't configure the mailer to use SMTP if no host is provided
* Fix localization support
* Store plugin instance in global variable, $c2c_configure_smtp, to allow for external manipulation
* Rename class from 'ConfigureSMTP' to 'c2c_ConfigureSMTP'
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note compatibility with WP 3.0+
* Minor tweaks to code formatting (spacing)
* Add Upgrade Notice section to readme.txt
* Add PHPDoc documentation
* Add package info to top of file
* Update copyright date
* Remove trailing whitespace
* Update screenshot
* Update .pot file

## 2.7
* Fix to prevent HTML entities from appearing in the From name value in outgoing e-mails
* Added full support for localization
* Added .pot file
* Noted that overriding the From e-mail value may not take effect depending on mail server and settings, particular if SMTPAuth is used (i.e. GMail)
* Changed invocation of plugin's install function to action hooked in constructor rather than in global space
* Update object's option buffer after saving changed submitted by user
* Miscellaneous tweaks to update plugin to my current plugin conventions
* Noted compatibility with WP2.9+
* Dropped compatibility with versions of WP older than 2.8
* Updated readme.txt
* Updated copyright date

## 2.6
* Now show settings page JS in footer, and only on the admin settings page
* Removed hardcoded path to plugins dir
* Changed permission check
* Minor reformatting (added spaces)
* Tweaked readme.txt
* Removed compatibility with versions of WP older than 2.6
* Noted compatibility with WP 2.8+

## 2.5
* NEW
* Added support for GMail, including configuring the various settings to be appropriate for GMail
* Added support for SMTPSecure setting (acceptable values of '', 'ssl', or 'tls')
* Added "Settings" link next to "Activate"/"Deactivate" link next to the plugin on the admin plugin listings page
* CHANGED
* Tweaked plugin's admin options page to conform to newer WP 2.7 style
* Tweaked test e-mail subject and body
* Removed the use_smtp option since WP uses SMTP by default, the plugin can't help you if it isn't using SMTP already, and the plugin should just go ahead and apply if it is active
* Updated description, installation instructions, extended description, copyright
* Extended compatibility to WP 2.7+
* Facilitated translation of some text
* FIXED
* Fixed bug where specified wordwrap value wasn't taken into account

## 2.0
* Initial release after rewrite from wpPHPMailer

## pre-2.0
* Earlier versions of this plugin existed as my wpPHPMailer plugin, which due to the inclusion of PHPMailer within WordPress's core and necessary changes to the plugin warranted a rebranding/renaming.

