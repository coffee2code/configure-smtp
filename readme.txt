=== Configure SMTP ===
Contributors: coffee2code
Donate link: http://coffee2code.com
Tags: email, smtp, sendmail, outgoing mail
Requires at least: 2.2
Tested up to: 2.5
Stable tag: trunk
Version: 2.0

Configure and activate SMTP mailing in WordPress.

== Description ==

Configure and activate SMTP mailing in WordPress.

This plugin is the renamed, rewritten, and updated version of the wpPHPMailer plugin.

Use this plugin to send email via SMTP instead of sendmail.  It allows you to configure the SMTP host and port.  You can enable SMTPAuth support, in which case you must provide an SMTP username and password.

Regardless of whether SMTP is enabled, the plugin provides you the ability to define the name and email of the 'From:' field for all outgoing e-mails.

A simple test button is also available that allows you to send a test e-mail to yourself to check if sending e-mail has been properly configured for your blog.

== Installation ==

1. Unzip `configure-smtp.zip` inside the `/wp-content/plugins/` directory, or upload `configure-smtp.php` to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Options` -> `SMTP` (or in WP 2.5: `Settings` -> `SMTP`) admin options page.  Optionally customize the options (namely to activate SMTP mailing in the first place, and to configure it if the defaults aren't valid for your situation).
1. (optional) Use the built-in test to see if your blog can properly send out e-mails.

== Frequently Asked Questions =

= I am already able to receive e-mail sent by my blog, so would I have any use or need for this plugin? =

Most likely, no.  Not unless you have a preference for having your mail sent out via an SMTP server.

= How do I find out my SMTP host, and/or if I need to use SMTPAuth and what my username and password for that are? =

Check out the settings for your local e-mail program.  More than likely that is configured to use an outgoing SMTP server.  Otherwise, contact your host or someone more intimately knowledgeable about your situation.

= I've sent out a few test e-mails using the test button after having tried different values for some of the settings; how do I know which one worked? =

If your settings worked, you should receive the test e-mail at e-mail address associated with your WordPress blog user account.  That e-mail contains a timestamp which was reported to you by the plugin when the e-mail was sent.  If you are trying out various setting values, be sure to record what your settings were and what the timestamp was when sending with those settings.

== Screenshot ==

1. A screenshot of the plugin's admin options page.