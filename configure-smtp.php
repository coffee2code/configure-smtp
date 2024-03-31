<?php
/**
 * Plugin Name: Configure SMTP
 * Version:     3.5
 * Plugin URI:  https://coffee2code.com/wp-plugins/configure-smtp/
 * Author:      Scott Reilly
 * Author URI:  https://coffee2code.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: configure-smtp
 * Description: Configure SMTP mailing in WordPress, including support for sending email via SSL/TLS (such as Gmail).
 *x
 * Compatible with WordPress 5.5+ through 6.5+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/configure-smtp/
 *
 * @package Configure_SMTP
 * @author  Scott Reilly
 * @version 3.5
 */

/*
	Copyright (c) 2004-2024 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_ConfigureSMTP' ) ) :

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'c2c-plugin.php' );

final class c2c_ConfigureSMTP extends c2c_Plugin_067 {

	/**
	 * Name of plugin's setting.
	 *
	 * @var string
	 */
	const SETTING_NAME = 'c2c_configure_smtp';

	/**
	 * The one true instance.
	 *
	 * @access private
	 * @var c2c_ConfigureSMTP
	 */
	private static $instance;

	/**
	 * Default Gmail configuration options.
	 *
	 * @access private
	 * @var array
	 */
	private $gmail_config = array(
		'host'        => 'smtp.gmail.com',
		'port'        => '465',
		'smtp_auth'   => true,
		'smtp_secure' => 'ssl',
	);

	/**
	 * Error message.
	 *
	 * @access private
	 * @var string
	 */
	private $error_msg = '';

	/**
	 * Get singleton instance.
	 *
	 * @since 3.2
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
			// Note: Support for the global is deprecated and will be removed.
			$GLOBALS['c2c_configure_smtp'] = self::$instance;
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct( '3.5', 'configure-smtp', 'c2c', __FILE__, array() );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );

		return self::$instance = $this;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 3.1
	 */
	public static function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * @since 3.1
	 */
	public static function uninstall() {
		delete_option( self::SETTING_NAME );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 */
	public function load_config() {
		$this->name      = __( 'Configure SMTP', 'configure-smtp' );
		$this->menu_name = __( 'SMTP', 'configure-smtp' );

		$this->config = array(
			'use_gmail'    => array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Send email via Gmail?', 'configure-smtp' ),
				'help'     => __( 'Clicking this will overwrite many of the settings defined below. You will need to input your Gmail username and password below.', 'configure-smtp' ),
			),
			'host' => array(
				'input'    => 'text',
				'default'  => 'localhost',
				'require'  => true,
				'label'    => __( 'SMTP host', 'configure-smtp' ),
				'help'     => __( 'If "localhost" doesn\'t work for you, check with your host for the SMTP hostname.', 'configure-smtp' ),
			),
			'port' => array(
				'input'    => 'short_text',
				'default'  => 25,
				'datatype' => 'int',
				'required' => true,
				'label'    => __( 'SMTP port', 'configure-smtp' ),
				'help'     => __( 'This is generally 25.', 'configure-smtp' ),
			),
			'smtp_secure' => array(
				'input'    => 'select',
				'default'  => 'None',
				'label'    => __( 'Secure connection prefix', 'configure-smtp' ),
				'options'  => array( '', 'ssl', 'tls' ),
				'help'     => __( 'Sets connection prefix for secure connections (prefix method must be supported by your PHP install and your SMTP host)', 'configure-smtp' ),
			),
			'smtp_auth'	=> array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Use SMTPAuth?', 'configure-smtp' ),
				'help'     => __( 'If checked, you must provide the SMTP username and password below', 'configure-smtp' ),
			),
			'smtp_user'	=> array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'SMTP username', 'configure-smtp' ),
				'help'     => '',
			),
			'smtp_pass'	=> array(
				'input'    => 'password',
				'default'  => '',
				'label'    => __( 'SMTP password', 'configure-smtp' ),
				'help'     => '',
			),
			'wordwrap' => array(
				'input'    => 'short_text',
				'default'  => '',
				'label'    => __( 'Wordwrap length', 'configure-smtp' ),
				'help'     => __( 'Sets word wrapping on the body of the message to a given number of characters.', 'configure-smtp' ),
			),
			'debug' => array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Enable debugging?', 'configure-smtp' ),
				'help'     => __( 'Only check this if you are experiencing problems and would like more error reporting to occur. <em>Uncheck this once you have finished debugging.</em>', 'configure-smtp' ),
			),
			'hr' => array(),
			'from_email' => array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'Sender email', 'configure-smtp' ),
				'help'     => __( 'Sets the From email address for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP. NOTE: This may not take effect depending on your mail server and settings, especially if using SMTPAuth (such as for Gmail).', 'configure-smtp' ),
			),
			'from_name'	=> array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'Sender name', 'configure-smtp' ),
				'help'     => __( 'Sets the From name for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP.', 'configure-smtp' ),
			),
		);
	}

	/**
	 * Override the plugin framework's register_filters() to actually actions against filters.
	 */
	public function register_filters() {
		add_action( 'admin_enqueue_scripts',                   array( $this, 'enqueue_admin_js' ) );
		add_action( 'admin_init',                              array( $this, 'maybe_send_test' ) );
		add_action( 'phpmailer_init',                          array( $this, 'phpmailer_init' ) );
		add_filter( 'wp_mail_from',                            array( $this, 'wp_mail_from' ) );
		add_filter( 'wp_mail_from_name',                       array( $this, 'wp_mail_from_name' ) );
		add_action( $this->get_hook( 'after_settings_form' ),  array( $this, 'send_test_form' ) );
		add_filter( $this->get_hook( 'before_update_option' ), array( $this, 'maybe_gmail_override' ) );
	}

	/**
	 * Returns translated strings used by c2c_Plugin parent class.
	 *
	 * @since 3.5
	 *
	 * @param string $string Optional. The string whose translation should be
	 *                       returned, or an empty string to return all strings.
	 *                       Default ''.
	 * @return string|string[] The translated string, or if a string was provided
	 *                         but a translation was not found then the original
	 *                         string, or an array of all strings if $string is ''.
	 */
	public function get_c2c_string( $string = '' ) {
		$strings = array(
			'%s cannot be cloned.'
				/* translators: %s: Name of plugin class. */
				=> __( '%s cannot be cloned.', 'configure-smtp' ),
			'%s cannot be unserialized.'
				/* translators: %s: Name of plugin class. */
				=> __( '%s cannot be unserialized.', 'configure-smtp' ),
			'A value is required for: "%s"'
				/* translators: %s: Label for setting. */
				=> __( 'A value is required for: "%s"', 'configure-smtp' ),
			'Click for more help on this plugin'
				=> __( 'Click for more help on this plugin', 'configure-smtp' ),
			' (especially check out the "Other Notes" tab, if present)'
				=> __( ' (especially check out the "Other Notes" tab, if present)', 'configure-smtp' ),
			'Coffee fuels my coding.'
				=> __( 'Coffee fuels my coding.', 'configure-smtp' ),
			'Did you find this plugin useful?'
				=> __( 'Did you find this plugin useful?', 'configure-smtp' ),
			'Donate'
				=> __( 'Donate', 'configure-smtp' ),
			'Expected integer value for: %s'
				/* translators: %s: Label for setting. */
				=> __( 'Expected integer value for: %s', 'configure-smtp' ),
			'Invalid file specified for C2C_Plugin: %s'
				/* translators: %s: Path to the plugin file. */
				=> __( 'Invalid file specified for C2C_Plugin: %s', 'configure-smtp' ),
			'More information about %1$s %2$s'
				/* translators: 1: plugin name 2: plugin version */
				=> __( 'More information about %1$s %2$s', 'configure-smtp' ),
			'More Help'
				=> __( 'More Help', 'configure-smtp' ),
			'More Plugin Help'
				=> __( 'More Plugin Help', 'configure-smtp' ),
			'Please consider a donation'
				=> __( 'Please consider a donation', 'configure-smtp' ),
			'Reset Settings'
				=> __( 'Reset Settings', 'configure-smtp' ),
			'Save Changes'
				=> __( 'Save Changes', 'configure-smtp' ),
			'See the "Help" link to the top-right of the page for more help.'
				=> __( 'See the "Help" link to the top-right of the page for more help.', 'configure-smtp' ),
			'Settings'
				=> __( 'Settings', 'configure-smtp' ),
			'Settings reset.'
				=> __( 'Settings reset.', 'configure-smtp' ),
			'Something went wrong.'
				=> __( 'Something went wrong.', 'configure-smtp' ),
			'The method %1$s should not be called until after the %2$s action.'
				/* translators: 1: The name of a code function, 2: The name of a WordPress action. */
				=> __( 'The method %1$s should not be called until after the %2$s action.', 'configure-smtp' ),
			'The plugin author homepage.'
				=> __( 'The plugin author homepage.', 'configure-smtp' ),
			"The plugin configuration option '%s' must be supplied."
				/* translators: %s: The setting configuration key name. */
				=>__( "The plugin configuration option '%s' must be supplied.", 'configure-smtp' ),
			'This plugin brought to you by %s.'
				/* translators: %s: Link to plugin author's homepage. */
				=> __( 'This plugin brought to you by %s.', 'configure-smtp' ),
		);

		if ( ! $string ) {
			return array_values( $strings );
		}

		return ! empty( $strings[ $string ] ) ? $strings[ $string ] : $string;
	}

	/**
	 * Outputs the text above the setting form.
	 *
	 * @param string $localized_heading_text Optional. Localized page heading text. Default ''.
	 */
	public function options_page_description( $localized_heading_text = '' ) {
		$options = $this->get_options();
		parent::options_page_description( __( 'Configure SMTP Settings', 'configure-smtp' ) );

		if ( ! empty( $this->error_msg ) ) {
			echo wp_kses_post( $this->error_msg );
		}

		$str = '<a href="#test">' . __( 'test', 'configure-smtp' ) . '</a>';
		if ( empty( $options['host'] ) ) {
			echo '<div class="error"><p>' . wp_kses_data( __( 'SMTP mailing is currently <strong>NOT ENABLED</strong> because no SMTP host has been specified.' ) ) . '</p></div>';
		}
		/* translators: %s: Link to the test tool section of the page. */
		echo '<p>' . wp_kses_data( sprintf( __( 'After you have configured your SMTP settings, use the %s to send a test message to yourself.', 'configure-smtp' ), $str ) ) . '</p>';
	}

	/**
	 * Enqueues JavaScript.
	 *
	 * @since 3.2
	 */
	public function enqueue_admin_js() {
		$screen = get_current_screen();

		if ( $screen->id === $this->options_page ) {
			// Register script.
			wp_register_script( $this->id_base, plugins_url( 'assets/configure-smtp.js', __FILE__ ), array( 'jquery' ), self::version(), true );

			// Localize script.
			wp_localize_script( $this->id_base, 'c2c_configure_smtp', array(
				'alert'     => __( 'Be sure to specify your full Gmail email address (including the "@gmail.com") as the SMTP username, and your Gmail password as the SMTP password.', 'configure-smtp' ),
				'checked'   => $this->gmail_config['smtp_auth'] ? '1' : '',
				'host'      => $this->gmail_config['host'],
				'port'      => $this->gmail_config['port'],
				'smtp_auth' => $this->gmail_config['smtp_secure'],
			) );

			// Enqueue script.
			wp_enqueue_script( $this->id_base );
		}
	}

	/**
	 * If the 'Use Gmail' option is checked, the Gmail settings will overwrite whatever the user may have provided.
	 *
	 * @param  array $options The options array prior to saving.
	 * @return array The options array with Gmail settings taking precedence, if relevant.
	 */
	public function maybe_gmail_override( $options ) {
		// If Gmail is to be used, those settings take precendence
		if ( $options['use_gmail'] ) {
			$options = wp_parse_args( $this->gmail_config, $options );
		}

		return $options;
	}

	/**
	 * Sends test email if form was submitted requesting to do so.
	 *
	 */
	public function maybe_send_test() {
		if ( isset( $_POST[ $this->get_form_submit_name( 'submit_test_email' ) ] ) ) {
			check_admin_referer( $this->nonce_field );

			$user      = wp_get_current_user();
			$email     = $user->user_email;
			$timestamp = current_time( 'mysql' );

			/* translators: %s: Name of the plugin. */
			$message = sprintf( __( 'Hi, this is the %s plugin emailing you a test message from your WordPress site.', 'configure-smtp' ), $this->name );
			$message .= "\n\n";
			/* translators: %s: A timestamp. */
			$message .= sprintf( __( 'This message was sent with this time-stamp: %s', 'configure-smtp' ), $timestamp );
			$message .= "\n\n";
			$message .= __( 'Congratulations! Your site is properly configured to send email.', 'configure-smtp' );

			wp_mail( $email, __( 'Test message from your WordPress site', 'configure-smtp' ), $message );

			// Check success
			global $phpmailer;

			if ( $phpmailer->ErrorInfo != "" ) {
				$this->error_msg  = '<div class="error"><p>' . esc_html__( 'An error was encountered while trying to send the test email.', 'configure-smtp' ) . '</p>';
				$this->error_msg .= '<blockquote style="font-weight:bold;">';
				$this->error_msg .= '<p>' . esc_html( $phpmailer->ErrorInfo ) . '</p>';
				$this->error_msg .= '</p></blockquote>';
				$this->error_msg .= '</div>';
			} else {
				$this->error_msg  = '<div class="updated"><p>' . esc_html__( 'Test email sent.', 'configure-smtp' ) . '</p>';
				/* translators: %s: A timestamp. */
				$this->error_msg .= '<p>' . sprintf( esc_html__( 'The body of the email includes this time-stamp: %s.', 'configure-smtp' ), $timestamp ) . '</p></div>';
			}
		}
	}

	/*
	 * Outputs form to send test email.
	 */
	public function send_test_form() {
		$user       = wp_get_current_user();
		$email      = $user->user_email;
		$action_url = $this->form_action_url();

		echo '<div class="wrap"><h2><a name="test"></a>' . esc_html__( 'Send A Test', 'configure-smtp' ) . "</h2>\n";
		echo '<p>' . esc_html__( 'Click the button below to send a test email to yourself to see if things are working. Be sure to save any changes you made to the form above before sending the test email. Bear in mind it may take a few minutes for the email to wind its way through the internet.', 'configure-smtp' ) . "</p>\n";
		/* translators: %s: An email address. */
		echo '<p>' . esc_html( sprintf( __( 'This email will be sent to your email address, %s.', 'configure-smtp' ), $email ) ) . "</p>\n";
		echo '<p><em>' . esc_html__( 'You must save any changes to the form above before attempting to send a test email.', 'configure-smtp' ) . '</em></p>';
		printf(
			"<form name='configure_smtp' action='%s' method='post'>\n",
			esc_url( $action_url )
		);
		wp_nonce_field( $this->nonce_field );
		printf(
			'<input type="hidden" name="%s" value="1" />',
			esc_attr( $this->get_form_submit_name( 'submit_test_email' ) )
		);
		printf(
			'<div class="submit"><input type="submit" name="%s" value="%s" /></div>',
			esc_attr__( 'Submit', 'configure-smtp' ),
			esc_attr__( 'Send test email', 'configure-smtp' )
		);
		echo '</form></div>';
	}

	/**
	 * Configures PHPMailer object during its initialization stage.
	 *
	 * @param object $phpmailer PHPMailer object.
	 */
	public function phpmailer_init( $phpmailer ) {
		$options = $this->get_options();

		// Don't configure for SMTP if no host is provided.
		if ( empty( $options['host'] ) ) {
			return;
		}

		$phpmailer->IsSMTP();
		$phpmailer->Host = $options['host'];
		$phpmailer->Port = $options['port'] ? (int) $options['port'] : 25;
		$phpmailer->SMTPAuth = (bool) $options['smtp_auth'];
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = $options['smtp_user'];
			$phpmailer->Password = $options['smtp_pass'];
		}
		if ( $options['smtp_secure'] && in_array( $options['smtp_secure'], $this->config['smtp_secure']['options'] ) ) {
			$phpmailer->SMTPSecure = $options['smtp_secure'];
		}
		if ( (int) $options['wordwrap'] > 0 ) {
			$phpmailer->WordWrap = (int) $options['wordwrap'];
		}
		if ( $options['debug'] ) {
			$phpmailer->SMTPDebug = true;
		}
	}

	/**
	 * Configures the "From:" email address for outgoing emails.
	 *
	 * @param  string $from The "from" email address used by WordPress by default
	 * @return string The potentially new "from" email address, if overridden via the plugin's settings.
	 */
	public function wp_mail_from( $from ) {
		$options = $this->get_options();

		if ( ! empty( $options['from_email'] ) ) {
			$from = $options['from_email'];
		}

		return $from;
	}

	/**
	 * Configures the "From:" name for outgoing emails.
	 *
	 * @param  string $from The "from" name used by WordPress by default
	 * @return string The potentially new "from" name, if overridden via the plugin's settings.
	 */
	public function wp_mail_from_name( $from_name ) {
		$options = $this->get_options();

		if ( ! empty( $options['from_name'] ) ) {
			$from_name = wp_specialchars_decode( $options['from_name'], ENT_QUOTES );
		}

		return $from_name;
	}

} // end c2c_ConfigureSMTP

add_action( 'plugins_loaded', array( 'c2c_ConfigureSMTP', 'get_instance' ) );

endif; // end if !class_exists()
