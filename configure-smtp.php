<?php
/*
Plugin Name: Configure SMTP
Version: 2.5
Plugin URI: http://coffee2code.com/wp-plugins/configure-smtp
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Configure SMTP mailing in WordPress, including support for sending e-mail via GMail.

This plugin is the renamed, rewritten, and updated version of the wpPHPMailer plugin.

Use this plugin to customize the SMTP mailing system used by default by WordPress to handle *outgoing* e-mails.
It offers you the ability to specify:

	* SMTP host name
	* SMTP port number
	* If SMTPAuth (authentication) should be used.
	* SMTP username
	* SMTP password
	* If the SMTP connection needs to occur over ssl or tls

In addition, you can instead indicate that you wish to use GMail to handle outgoing e-mail, in which case the above
settings are automatically configured to values appropriate for GMail, though you'll need to specify your GMail
e-mail (included the "@gmail.com") and password.

Regardless of whether SMTP is enabled or configured, the plugin provides you the ability to define the name and 
email of the 'From:' field for all outgoing e-mails.

A simple test button is also available that allows you to send a test e-mail to yourself to check if sending
e-mail has been properly configured for your blog.

Compatible with WordPress 2.2+, 2.3+, 2.5+, 2.6+, 2.7+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/configure-smtp.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress.
3. Click the plugin's 'Settings' link next to its 'Deactivate' link (still on the Plugins page), or click on the 
Settings -> SMTP link, to go to the plugin's admin options page.  Optionally customize the options (to configure it 
if the defaults aren't valid for your situation).
4. (optional) Use the built-in test to see if your blog can properly send out e-mails.

*/

/*
Copyright (c) 2004-2009 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( !class_exists('ConfigureSMTP') ) :

class ConfigureSMTP {
	var $plugin_name = '';
	var $admin_options_name = 'c2c_configure_smtp';
	var $nonce_field = 'update-configure_smtp';
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $config = array(
		// input can be 'checkbox', 'short_text', 'text', 'textarea', 'inline_textarea', 'select', 'hidden', 'password', or 'none'
		//	an input type of 'select' must then have an 'options' value (an array) specified
		// datatype can be 'array' or 'hash'
		// can also specify input_attributes
		'use_gmail' => array('input' => 'checkbox', 'default' => false,
				'label' => 'Send e-mail via GMail?',
				'help' => 'Clicking this will override many of the settings defined below.  You will need to input your GMail username and password below.',
				'input_attributes' => 'onclick="return configure_gmail();"'),
		'host' => array('input' => 'text', 'default' => 'localhost',
				'label' => 'SMTP host',
				'help' => 'If "localhost" doesn\'t work for you, check with your host for the SMTP hostname.'),
		'port' => array('input' => 'short_text', 'default' => 25,
				'label' => 'SMTP port',
				'help' => "This is generally 25."),
		'smtp_secure' => array('input' => 'select', 'default' => 'None',
				'label' => 'Secure connection prefix',
				'options' => array('', 'ssl', 'tls'),
				'help' => 'Sets connection prefix for secure connections (prefix method must be supported by your PHP install and your SMTP host)'),
		'smtp_auth'	=> array('input' => 'checkbox', 'default' => false,
				'label' => 'Use SMTPAuth?',
				'help' => 'If checked, you must provide the SMTP username and password below'),
		'smtp_user'	=> array('input' => 'text', 'default' => '',
				'label' => 'SMTP username',
				'help' => ''),
		'smtp_pass'	=> array('input' => 'password', 'default' => '',
				'label' => 'SMTP password',
				'help' => ''),
		'wordwrap' => array('input' => 'short_text', 'default' => '',
				'label' => 'Wordwrap length',
				'help' => 'Sets word wrapping on the body of the message to a given number of characters.'),
		'from_email' => array('input' => 'text', 'default' => '',
				'label' => 'Sender e-mail',
				'help' => 'Sets the From email address for all outgoing messages.  Leave blank to use the
							WordPress default.  This value will be used even if you don\'t enable SMTP.'),
		'from_name'	=> array('input' => 'text', 'default' => '',
				'label' => 'Sender name',
				'help' => 'Sets the From name for all outgoing messages. Leave blank to use the WordPress default.
							This value will be used even if you don\'t enable SMTP.')
	);
	var $gmail_config = array(
		'host' => 'smtp.gmail.com',
		'port' => '465',
		'smtp_auth' => true,
		'smtp_secure' => 'ssl'
	);
	var $options = array(); // Don't use this directly

	function ConfigureSMTP() {
		$this->plugin_name = __('Configure SMTP');
		add_action('admin_head', array(&$this, 'add_js'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('phpmailer_init', array(&$this, 'phpmailer_init'));
		add_action('wp_mail_from', array(&$this, 'wp_mail_from'));
		add_action('wp_mail_from_name', array(&$this, 'wp_mail_from_name'));
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function add_js() {
		echo <<<JS
		<script type="text/javascript">
			function configure_gmail() {
				if (jQuery('#use_gmail').attr('checked') == true) {
					jQuery('#host').val('{$this->gmail_config['host']}');
					jQuery('#port').val('{$this->gmail_config['port']}');
					jQuery('#smtp_auth').attr('checked', {$this->gmail_config['smtp_auth']});
					jQuery('#smtp_secure').val('{$this->gmail_config['smtp_secure']}');
					if (!jQuery('#smtp_user').val().match(/.+@gmail.com$/) ) {
						jQuery('#smtp_user').val('USERNAME@gmail.com').focus().get(0).setSelectionRange(0,8);
					}
					alert('Be sure to specify your GMail email address (with the @gmail.com) as the SMTP username, and your GMail password as the SMTP password.');
					return true;
				}
			}
		</script>
JS;
	}

	function admin_menu() {
		static $plugin_basename;
		if ( $this->show_admin ) {
			global $wp_version;
			if ( current_user_can('edit_posts') ) {
				$plugin_basename = plugin_basename(__FILE__); 
				if ( version_compare( $wp_version, '2.6.999', '>' ) )
					add_filter( 'plugin_action_links_' . $plugin_basename, array(&$this, 'plugin_action_links') );
				add_options_page($this->plugin_name, 'SMTP', 9, $plugin_basename, array(&$this, 'options_page'));
			}
		}
	}

	function plugin_action_links($action_links) {
		static $plugin_basename;
		if ( !$plugin_basename ) $plugin_basename = plugin_basename(__FILE__); 
		$settings_link = '<a href="options-general.php?page='.$plugin_basename.'">' . __('Settings') . '</a>';
		array_unshift( $action_links, $settings_link );

		return $action_links;
	}

	function phpmailer_init($phpmailer) {
		$options = $this->get_options();
		$phpmailer->IsSMTP();
		$phpmailer->Host = $options['host'];
		$phpmailer->Port = $options['port'] ? $options['port'] : 25;
		$phpmailer->SMTPAuth = $options['smtp_auth'] ? $options['smtp_auth'] : false;
		if ($phpmailer->SMTPAuth) {
			$phpmailer->Username = $options['smtp_user'];
			$phpmailer->Password = $options['smtp_pass'];
		}
		if ($options['smtp_secure'] != '')
			$phpmailer->SMTPSecure = $options['smtp_secure'];
		if ($options['wordwrap'] > 0 )
			$phpmailer->WordWrap = $options['wordwrap'];
		return $phpmailer;
	}

	function wp_mail_from($from) {
		$options = $this->get_options();
		if ($options['from_email'])
			$from = $options['from_email'];
		return $from;		
	}

	function wp_mail_from_name($from_name) {
		$options = $this->get_options();
		if ($options['from_name'])
			$from_name = $options['from_name'];
		return $from_name;
	}

	function get_options() {
		if ( !empty($this->options)) return $this->options;
		// Derive options from the config
		$options = array();
		foreach (array_keys($this->config) as $opt) {
			$options[$opt] = $this->config[$opt]['default'];
		}
        $existing_options = get_option($this->admin_options_name);
        if (!empty($existing_options)) {
            foreach ($existing_options as $key => $value)
                $options[$key] = $value;
        }            
		$this->options = $options;
        return $options;
	}

	function options_page() {
		static $plugin_basename;
		if ( !$plugin_basename ) $plugin_basename = plugin_basename(__FILE__); 
		$options = $this->get_options();
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			foreach (array_keys($options) AS $opt) {
				$options[$opt] = htmlspecialchars(stripslashes($_POST[$opt]));
				$input = $this->config[$opt]['input'];
				if (($input == 'checkbox') && !$options[$opt])
					$options[$opt] = 0;
				if ($this->config[$opt]['datatype'] == 'array') {
					if ($input == 'text')
						$options[$opt] = explode(',', str_replace(array(', ', ' ', ','), ',', $options[$opt]));
					else
						$options[$opt] = array_map('trim', explode("\n", trim($options[$opt])));
				}
				elseif ($this->config[$opt]['datatype'] == 'hash') {
					if ( !empty($options[$opt]) ) {
						$new_values = array();
						foreach (explode("\n", $options[$opt]) AS $line) {
							list($shortcut, $text) = array_map('trim', explode("=>", $line, 2));
							if (!empty($shortcut)) $new_values[str_replace('\\', '', $shortcut)] = str_replace('\\', '', $text);
						}
						$options[$opt] = $new_values;
					}
				}
			}
			// If GMail is to be used, those settings take precendence
			if ($options['use_gmail'])
				$options = wp_parse_args($this->gmail_config, $options);

			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);

			echo "<div id='message' class='updated fade'><p><strong>" . __('Settings saved') . '</strong></p></div>';
		}
		elseif ( isset($_POST['submit_test_email']) ) {
			check_admin_referer($this->nonce_field);
			$user = wp_get_current_user();
			$email = $user->user_email;
			$timestamp = current_time('mysql');
			$message = <<<END
Hi, this is the {$this->plugin_name} plugin e-mailing you a test message from your WordPress blog.

This message was sent with this time-stamp: $timestamp

Congratulations!  Your blog is properly configured to send e-mail.
END;
			wp_mail($email, "Test message from your WordPress blog", $message);
			//echo "<div class='updated'>I would have sent $email this message:<br />$message</div>";
			echo "<div class='updated'><p>Test e-mail sent.</p><p>The body of the e-mail includes this time-stamp: $timestamp.</p></div>";
		}

		$action_url = $_SERVER[PHP_SELF] . '?page=' . $plugin_basename;
		$logo = get_option('siteurl') . '/wp-content/plugins/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';

		echo <<<END
		<div class='wrap'>
			<div class="icon32" style="width:44px;"><img src='$logo' alt='A plugin by coffee2code' /><br /></div>
			<h2>{$this->plugin_name} Plugin Options</h2>
			<p>After you've configured your SMTP options, use the <a href="#test">test</a> to send a test message to yourself.</p>
			
			<form name="configure_smtp" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {
					$input = $this->config[$opt]['input'];
					if ($input == 'none') continue;
					$label = $this->config[$opt]['label'];
					$value = $options[$opt];
					if ($input == 'checkbox') {
						$checked = ($value == 1) ? 'checked=checked ' : '';
						$value = 1;
					} else {
						$checked = '';
					};
					if ($this->config[$opt]['datatype'] == 'array') {
						if ($input == 'textarea' || $input == 'inline_textarea')
							$value = implode("\n", $value);
						else
							$value = implode(', ', $value);
					} elseif ($this->config[$opt]['datatype'] == 'hash') {
						$new_value = '';
						foreach ($value AS $shortcut => $replacement) {
							$new_value .= "$shortcut => $replacement\n";
						}
						$value = $new_value;
					}
					echo "<tr valign='top'>";
					if ($input == 'textarea') {
						echo "<td colspan='2'>";
						if ($label) echo "<strong>$label</strong><br />";
						echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
					} else {
						echo "<th scope='row'>$label</th><td>";
						if ($input == "inline_textarea")
							echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
						elseif ($input == 'select') {
							echo "<select name='$opt' id='$opt'>";
							foreach ($this->config[$opt]['options'] as $sopt) {
								$selected = $value == $sopt ? " selected='selected'" : '';
								echo "<option value='$sopt'$selected>$sopt</option>";
							}
							echo "</select>";
						} else {
							$tclass = ($input == 'short_text') ? 'small-text' : 'regular-text';
							echo "<input name='$opt' type='$input' id='$opt' value='$value' class='$tclass' $checked {$this->config[$opt]['input_attributes']} />";
						}
					}
					if ($this->config[$opt]['help']) {
						echo "<br /><span style='color:#777; font-size:x-small;'>";
						echo $this->config[$opt]['help'];
						echo "</span>";
					}
					echo "</td></tr>";
				}
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></div>
		</form>
			</div>
END;
				echo <<<END
				<style type="text/css">
					#c2c {
						text-align:center;
						color:#888;
						background-color:#ffffef;
						padding:5px 0 0;
						margin-top:12px;
						border-style:solid;
						border-color:#dadada;
						border-width:1px 0;
					}
					#c2c div {
						margin:0 auto;
						padding:5px 40px 0 0;
						width:45%;
						min-height:40px;
						background:url('$logo') no-repeat top right;
					}
					#c2c span {
						display:block;
						font-size:x-small;
					}
				</style>
				<div id='c2c' class='wrap'>
					<div>
					This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.
					<span><a href="http://coffee2code.com/donate" title="Please consider a donation">Did you find this plugin useful?</a></span>
					</div>
				</div>
END;
		$user = wp_get_current_user();
		$email = $user->user_email;
		echo <<<END
		<div class='wrap'>
			<h2><a name="test"></a>Send A Test</h2>
			<p>Click the button below to send a test email to yourself to see if things are working.  Be sure to save
			any changes you made to the form above before sending the test e-mail.  Bear in mind it may take a few
			minutes for the e-mail to wind its way through the internet.</p>
			
			<p>This e-mail will be sent to your e-mail address, $email.</p>
			
			<form name="configure_smtp" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo <<<END
			<input type="hidden" name="submit_test_email" value="1" />
			<div class="submit"><input type="submit" name="Submit" value="Send test e-mail" /></div>
			</form>
		</div>
END;
	}

} // end ConfigureSMTP

endif; // end if !class_exists()
if ( class_exists('ConfigureSMTP') ) :
	// Get the ball rolling
	$configure_smtp = new ConfigureSMTP();
	// Actions and filters
	if (isset($configure_smtp)) {
		register_activation_hook( __FILE__, array(&$configure_smtp, 'install') );
	}
endif;

?>