<?php

defined( 'ABSPATH' ) or die();

class Configure_SMTP_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		c2c_ConfigureSMTP::get_instance()->reset_options();
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	public function get_default_hooks() {
		return array(
			array( array( 10, 'action', 'admin_enqueue_scripts', 'enqueue_admin_js' ) ),
			array( array( 10, 'action', 'admin_init', 'maybe_send_test' ) ),
			array( array( 10, 'action', 'phpmailer_init', 'phpmailer_init' ) ),
			array( array( 10, 'filter', 'wp_mail_from', 'wp_mail_from' ) ),
			array( array( 10, 'filter', 'wp_mail_from_name', 'wp_mail_from_name' ) ),
		);
	}

	public function get_settings_with_defaults() {
		return array(
			array( array( 'use_gmail',   false ) ),
			array( array( 'host',        'localhost' ) ),
			array( array( 'port',        25 ) ),
			array( array( 'smtp_secure', 'None' ) ),
			array( array( 'smtp_auth',   false ) ),
			array( array( 'smtp_user',   '' ) ),
			array( array( 'smtp_pass',   '' ) ),
			array( array( 'wordwrap',    '' ) ),
			array( array( 'from_email',  '' ) ),
			array( array( 'from_name',   '' ) ),
		);
	}


	//
	//
	// HELPER FUNCTIONS
	//
	//


	protected function set_option( $settings = array() ) {
		$options = c2c_ConfigureSMTP::get_instance()->get_options();

		foreach ( $settings as $setting => $val ) {
			$options[ $setting ] = $val;
		}

		c2c_ConfigureSMTP::get_instance()->update_option( $options, true );
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_ConfigureSMTP' ) );
	}

	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_ConfigureSMTP_Plugin_046' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '046', c2c_ConfigureSMTP::get_instance()->c2c_plugin_version() );
	}

	public function test_version() {
		$this->assertEquals( '3.2', c2c_ConfigureSMTP::get_instance()->version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_ConfigureSMTP::get_instance(), 'c2c_ConfigureSMTP' ) );
	}

	/**
	 * @dataProvider get_settings_with_defaults
	 */
	public function test_default_of_setting( $data ) {
		list( $field, $val ) = $data;

		$options = c2c_ConfigureSMTP::get_instance()->get_options();

		if ( is_bool( $val ) ) {
			if ( $val === true ) {
				$this->assertTrue( $options[ $field ] );
			} else {
				$this->assertFalse( $options[ $field ] );
			}
		} elseif ( ! $val ) {
			$this->assertEmpty( $val );
		} else {
			$this->assertEquals( $val, $options[ $field ] );
		}
	}

	public function test_does_not_affect_from_email_by_default() {
		$from = 'sample@example.com';

		$this->assertEquals( $from, apply_filters( 'wp_mail_from', $from ) );
	}

	public function test_does_override_from_email() {
		$from1 = 'sample@example.com';
		$from2 = 'another@example.com';

		$this->set_option( array( 'from_email' => $from2 ) );
		c2c_ConfigureSMTP::get_instance()->register_filters();

		$this->assertEquals( $from2, apply_filters( 'wp_mail_from', $from1 ) );
	}

	public function test_does_not_affect_from_name_by_default() {
		$from = 'Site Owner';

		$this->assertEquals( $from, apply_filters( 'wp_mail_from_name', $from ) );
	}

	public function test_does_override_from_name() {
		$from1 = 'Site Owner';
		$from2 = 'Another Person';

		$this->set_option( array( 'from_name' => $from2 ) );
		c2c_ConfigureSMTP::get_instance()->register_filters();

		$this->assertEquals( $from2, apply_filters( 'wp_mail_from_name', $from1 ) );
	}

	/* Default filters */

	/**
	 * @dataProvider get_default_hooks
	 */
	public function test_hooks_admin_enqueue_scripts( $data ) {
		list( $priority, $type, $hook, $function ) = $data;

		if ( 'action' === $type ) {
			$this->assertEquals( $priority, has_action( $hook, array( c2c_ConfigureSMTP::get_instance(), $function ) ) );
		} else {
			$this->assertEquals( $priority, has_filter( $hook, array( c2c_ConfigureSMTP::get_instance(), $function ) ) );
		}
	}

	public function test_does_not_immediately_store_default_settings_in_db() {
		$option_name = c2c_ConfigureSMTP::get_instance()->admin_options_name;
		// Get the options just to see if they may get saved.
		$options     = c2c_ConfigureSMTP::get_instance()->get_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	public function test_uninstall_deletes_option() {
		$option_name = c2c_ConfigureSMTP::SETTING_NAME;
		$options     = c2c_ConfigureSMTP::get_instance()->get_options();

		// Explicitly set an option to ensure options get saved to the database.
		$this->set_option( array( 'host' => 'smtp.example.com' ) );

		$this->assertNotEmpty( $options );
		$this->assertNotFalse( get_option( $option_name ) );

		c2c_ConfigureSMTP::uninstall();

		$this->assertFalse( get_option( $option_name ) );
	}
}
