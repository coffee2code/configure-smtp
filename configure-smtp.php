<?php
/**
 * Plugin Name: Configure SMTP
 * Version:     3.1
 * Plugin URI:  http://coffee2code.com/wp-plugins/configure-smtp/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: configure-smtp
 * Description: Configure SMTP mailing in WordPress, including support for sending email via SSL/TLS (such as GMail).
 *
 * Compatible with WordPress 4.2+ through 4.6+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/configure-smtp/
 *
 * @package Configure_SMTP
 * @author  Scott Reilly
 * @version 3.1
 */

/*
 * TODO:
 * - Add ability to configure plugin via defines in wp-config.php. Include option to disable admin.
 *   However, if admin is being shown but user/pw is set in file, disable those inputs on the form.
 *   (Maybe show smtp username, but definitely don't show pw)
 * - Don't show or include SMTP pw on form. Leave blank to indicate previously entered pw should be used.
 *   (But UI should let user know whether it knows about a pw or not, and if so, that it doesn't need to
 *   be re-entered unless it is being changed)
 */

/*
	Copyright (c) 2004-2016 by Scott Reilly (aka coffee2code)

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

final class c2c_ConfigureSMTP extends c2c_ConfigureSMTP_Plugin_045 {

	/**
	 * The one true instance.
	 *
	 * @access private
	 * @var c2c_ConfigureSMTP
	 */
	private static $instance;

	/**
	 * Default GMail configuration options.
	 *
	 * @access private
	 * @var array
	 */
	private $gmail_config = array(
		'host'        => 'smtp.gmail.com',
		'port'        => '465',
		'smtp_auth'   => true,
		'smtp_secure' => 'ssl'
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
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct( '3.1', 'configure-smtp', 'c2c', __FILE__, array() );
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
		delete_option( 'c2c_configure_smtp' );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 */
	public function load_config() {
		$this->name      = __( 'Configure SMTP', $this->textdomain );
		$this->menu_name = __( 'SMTP', $this->textdomain );

		$this->config = array(
			'use_gmail'    => array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Send email via GMail?', $this->textdomain ),
				'help'     => __( 'Clicking this will override many of the settings defined below. You will need to input your GMail username and password below.', $this->textdomain ),
				'input_attributes' => 'onclick="return configure_gmail();"',
			),
			'host' => array(
				'input'    => 'text',
				'default'  => 'localhost',
				'require'  => true,
				'label'    => __( 'SMTP host', $this->textdomain ),
				'help'     => __( 'If "localhost" doesn\'t work for you, check with your host for the SMTP hostname.', $this->textdomain ),
			),
			'port' => array(
				'input'    => 'short_text',
				'default'  => 25,
				'datatype' => 'int',
				'required' => true,
				'label'    => __( 'SMTP port', $this->textdomain ),
				'help'     => __( 'This is generally 25.', $this->textdomain ),
			),
			'smtp_secure' => array(
				'input'    => 'select',
				'default'  => 'None',
				'label'    => __( 'Secure connection prefix', $this->textdomain ),
				'options'  => array( '', 'ssl', 'tls' ),
				'help'     => __( 'Sets connection prefix for secure connections (prefix method must be supported by your PHP install and your SMTP host)', $this->textdomain ),
			),
			'smtp_auth'	=> array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Use SMTPAuth?', $this->textdomain ),
				'help'     => __( 'If checked, you must provide the SMTP username and password below', $this->textdomain ),
			),
			'smtp_user'	=> array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'SMTP username', $this->textdomain ),
				'help'     => '',
			),
			'smtp_pass'	=> array(
				'input'    => 'password',
				'default'  => '',
				'label'    => __( 'SMTP password', $this->textdomain ),
				'help'     => '',
			),
			'wordwrap' => array(
				'input'    => 'short_text',
				'default'  => '',
				'label'    => __( 'Wordwrap length', $this->textdomain ),
				'help'     => __( 'Sets word wrapping on the body of the message to a given number of characters.', $this->textdomain ),
			),
			'debug' => array(
				'input'    => 'checkbox',
				'default'  => false,
				'label'    => __( 'Enable debugging?', $this->textdomain ),
				'help'     => __( 'Only check this if you are experiencing problems and would like more error reporting to occur. <em>Uncheck this once you have finished debugging.</em>', $this->textdomain ),
			),
			'hr' => array(),
			'from_email' => array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'Sender email', $this->textdomain ),
				'help'     => __( 'Sets the From email address for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP. NOTE: This may not take effect depending on your mail server and settings, especially if using SMTPAuth (such as for GMail).', $this->textdomain ),
			),
			'from_name'	=> array(
				'input'    => 'text',
				'default'  => '',
				'label'    => __( 'Sender name', $this->textdomain ),
				'help'     => __( 'Sets the From name for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP.', $this->textdomain ),
			),
		);
	}

	/**
	 * Override the plugin framework's register_filters() to actually actions against filters.
	 */
	public function register_filters() {
		global $pagenow;

		if ( 'options-general.php' == $pagenow ) {
			add_action( 'admin_print_footer_scripts',          array( $this, 'add_js' ) );
		}
		add_action( 'admin_init',                              array( $this, 'maybe_send_test' ) );
		add_action( 'phpmailer_init',                          array( $this, 'phpmailer_init' ) );
		add_filter( 'wp_mail_from',                            array( $this, 'wp_mail_from' ) );
		add_filter( 'wp_mail_from_name',                       array( $this, 'wp_mail_from_name' ) );
		add_action( $this->get_hook( 'after_settings_form' ),  array( $this, 'send_test_form' ) );
		add_filter( $this->get_hook( 'before_update_option' ), array( $this, 'maybe_gmail_override' ) );
	}

	/**
	 * Outputs the text above the setting form.
	 *
	 * @param string $localized_heading_text Optional. Localized page heading text. Default ''.
	 */
	public function options_page_description( $localized_heading_text = '' ) {
		$options = $this->get_options();
		parent::options_page_description( __( 'Configure SMTP Settings', $this->textdomain ) );

		if ( ! empty( $this->error_msg ) ) {
			echo $this->error_msg;
		}

		$str = '<a href="#test">' . __( 'test', $this->textdomain ) . '</a>';
		if ( empty( $options['host'] ) ) {
			echo '<div class="error"><p>' . __( 'SMTP mailing is currently <strong>NOT ENABLED</strong> because no SMTP host has been specified.' ) . '</p></div>';
		}
		echo '<p>' . sprintf( __( 'After you have configured your SMTP settings, use the %s to send a test message to yourself.', $this->textdomain ), $str ) . '</p>';
	}

	/**
	 * Outputs JavaScript.
	 */
	public function add_js() {
		$alert = __( 'Be sure to specify your full GMail email address (including the "@gmail.com") as the SMTP username, and your GMail password as the SMTP password.', $this->textdomain );
		$checked = $this->gmail_config['smtp_auth'] ? '1' : '';
		echo <<<JS
		<script type="text/javascript">
			function configure_gmail() {
				// The .attr('checked') == true is only for pre-WP3.2
				if (jQuery('#use_gmail').attr('checked') == 'checked' || jQuery('#use_gmail').attr('checked') == true) {
					jQuery('#host').val('{$this->gmail_config['host']}');
					jQuery('#port').val('{$this->gmail_config['port']}');
					if (jQuery('#use_gmail').attr('checked') == 'checked')
						jQuery('#smtp_auth').prop('checked', $checked);
					else // pre WP-3.2 only
						jQuery('#smtp_auth').attr('checked', {$this->gmail_config['smtp_auth']});
					jQuery('#smtp_secure').val('{$this->gmail_config['smtp_secure']}');
					if (!jQuery('#smtp_user').val().match(/.+@gmail.com$/) ) {
						jQuery('#smtp_user').val('USERNAME@gmail.com').focus().get(0).setSelectionRange(0,8);
					}
					alert('{$alert}');
					return true;
				}
			}
		</script>

JS;
	}

	/**
	 * If the 'Use GMail' option is checked, the GMail settings will override whatever the user may have provided.
	 *
	 * @param  array $options The options array prior to saving.
	 * @return array The options array with GMail settings taking precedence, if relevant.
	 */
	public function maybe_gmail_override( $options ) {
		// If GMail is to be used, those settings take precendence
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

			$message = sprintf( __( 'Hi, this is the %s plugin emailing you a test message from your WordPress blog.', $this->textdomain ), $this->name );
			$message .= "\n\n";
			$message .= sprintf( __( 'This message was sent with this time-stamp: %s', $this->textdomain ), $timestamp );
			$message .= "\n\n";
			$message .= __( 'Congratulations!  Your blog is properly configured to send email.', $this->textdomain );

			wp_mail( $email, __( 'Test message from your WordPress blog', $this->textdomain ), $message );

			// Check success
			global $phpmailer;

			if ( $phpmailer->ErrorInfo != "" ) {
				$this->error_msg  = '<div class="error"><p>' . __( 'An error was encountered while trying to send the test email.', $this->textdomain ) . '</p>';
				$this->error_msg .= '<blockquote style="font-weight:bold;">';
				$this->error_msg .= '<p>' . $phpmailer->ErrorInfo . '</p>';
				$this->error_msg .= '</p></blockquote>';
				$this->error_msg .= '</div>';
			} else {
				$this->error_msg  = '<div class="updated"><p>' . __( 'Test email sent.', $this->textdomain ) . '</p>';
				$this->error_msg .= '<p>' . sprintf( __( 'The body of the email includes this time-stamp: %s.', $this->textdomain ), $timestamp ) . '</p></div>';
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

		echo '<div class="wrap"><h2><a name="test"></a>' . __( 'Send A Test', $this->textdomain ) . "</h2>\n";
		echo '<p>' . __( 'Click the button below to send a test email to yourself to see if things are working. Be sure to save any changes you made to the form above before sending the test email. Bear in mind it may take a few minutes for the email to wind its way through the internet.', $this->textdomain ) . "</p>\n";
		echo '<p>' . sprintf( __( 'This email will be sent to your email address, %s.', $this->textdomain ), $email ) . "</p>\n";
		echo '<p><em>You must save any changes to the form above before attempting to send a test email.</em></p>';
		echo "<form name='configure_smtp' action='$action_url' method='post'>\n";
		wp_nonce_field( $this->nonce_field );
		echo '<input type="hidden" name="' . $this->get_form_submit_name( 'submit_test_email' ) .'" value="1" />';
		echo '<div class="submit"><input type="submit" name="Submit" value="' . esc_attr__( 'Send test email', $this->textdomain ) . '" /></div>';
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
		$phpmailer->Port = $options['port'] ? $options['port'] : 25;
		$phpmailer->SMTPAuth = $options['smtp_auth'] ? $options['smtp_auth'] : false;
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = $options['smtp_user'];
			$phpmailer->Password = $options['smtp_pass'];
		}
		if ( $options['smtp_secure'] != '' ) {
			$phpmailer->SMTPSecure = $options['smtp_secure'];
		}
		if ( $options['wordwrap'] > 0 ) {
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

// Note: Support for the global is deprecated and will be removed.
$GLOBALS['c2c_configure_smtp'] = c2c_ConfigureSMTP::get_instance();

endif; // end if !class_exists()
