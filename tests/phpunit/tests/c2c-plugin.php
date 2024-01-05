<?php

defined( 'ABSPATH' ) or die();

class c2c_Plugin extends WP_UnitTestCase {

	protected $obj;

	public function setUp() {
		parent::setUp();

		add_filter( 'gettext_configure-smtp', array( $this, 'translate_text' ), 10, 2 );
		$this->obj = c2c_ConfigureSMTP::get_instance();
	}

	public function tearDown() {
		parent::tearDown();
	}


	//
	//
	// HELPERS
	//
	//


	public function translate_text( $translation, $text ) {
		if ( 'Donate' === $text ) {
			$translation = 'Donar';
		}

		return $translation;
	}

	public function count_function_calls( $filename, $function_name ) {
		$content = file_get_contents( $filename );
		$count = 0;

		if ( false === $content ) {
			new Exception( 'Unable to locate or open file: ' . $filename );
			return 0;
		}

		$pattern = '/\b' . preg_quote( $function_name ) . '\(/';
		preg_match_all( $pattern, $content, $matches );

		if ( isset( $matches[0] ) ) {
			$count = count( $matches[0] );
		}

		// Exclude the function declaration.
		if ( preg_match( '/\bfunction\s+' . preg_quote( $function_name ) . '\(/', $content ) ) {
			$count--;
		}

		// TODO: Exclude mentions in comments.

		return $count;
	}

	//
	//
	// DATA PROVIDERS
	//
	//


	public static function wp_version_comparisons() {
		return array(
			//[ WP ver, version to compare to, operator, expected result ]
			[ '5.5', '5.5.1', '>=', false ],
			[ '5.5', '5.6',   '>=', false ],
			[ '5.5', '5.5',   '>=', true ],
			[ '5.5', '5.4.3', '>=', true ],
			[ '5.5', '5.5.1', '',   false ],
			[ '5.5', '5.6',   '',   false ],
			[ '5.5', '5.5',   '',   true ],
			[ '5.5', '5.5',   '>',  false ],
			[ '5.5', '5.5',   '<',  false ],
			[ '5.5', '5.5.1', '<=', true ],
			[ '5.5', '5.6',   '<=', true ],
			[ '5.5', '5.5',   '<=', true ],
			[ '5.5', '5.4.3', '<=', false ],
			[ '5.5', '5.5',   '=',  true ],
			[ '5.5', '5.5.1', '=',  false ],
			[ '5.5', '5.5',   '!=', false ],
		);
	}


	//
	//
	// TESTS
	//
	//


	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_Plugin_066' ) );
	}

	/*
	 * c2c_plugin_version()
	 */

	public function test_plugin_framework_version() {
		$this->assertEquals( '066', $this->obj->c2c_plugin_version() );
	}

	/*
	 * __clone()
	 */

	/**
	 * @expectedException Error
	 */
	public function test_unable_to_clone_object() {
		$clone = clone $this->obj;
		$this->assertEquals( $clone, $this->obj );
	}

	/*
	 * __wakeup()
	 */

	/**
	 * @expectedException Error
	 */
	public function test_unable_to_instantiate_object_from_class() {
		new get_class( $this->obj );
	}

	/**
	 * @expectedException Error
	 */
	public function test_unable_to_unserialize_an_instance_of_the_class() {
		$class = get_class( $this->obj );
		$data = 'O:' . strlen( $class ) . ':"' . $class . '":0:{}';

		unserialize( $data );
	}

	/*
	 * is_wp_version_cmp()
	 */

	/**
	 * @dataProvider wp_version_comparisons
	 */
	public function test_is_wp_version_cmp( $wp_ver, $ver, $op, $expected ) {
		global $wp_version;
		$orig_wp_verion = $wp_version;

		$wp_version = $wp_ver;
		$this->{ $expected ? 'assertTrue' : 'assertFalse' }( $this->obj->is_wp_version_cmp( $ver, $op ) );

		$wp_version = $orig_wp_verion;
	}

	/*
	 * get_c2c_string()
	 */

	/**
	 * Ensure that each translatable string is translated by plugin.
	 *
	 * This assumes a lot and is quite brittle.
	 */
	public function test_get_c2c_string_has_correct_number_of_strings() {
		$this->assertEquals(
			$this->count_function_calls( dirname( CONFIGURE_SMTP_PLUGIN_FILE ) . '/c2c-plugin.php', 'get_c2c_string' ),
			count( $this->obj->get_c2c_string() )
		);
	}

	public function test_get_c2c_string_for_unknown_string() {
		$str = 'unknown string';

		$this->assertEquals( $str, $this->obj->get_c2c_string( $str ) );
	}

	public function test_get_c2c_string_for_known_string_translated() {
		$this->assertEquals( 'Donar', $this->obj->get_c2c_string( 'Donate' ) );
	}

	public function test_get_c2c_string_for_known_string_untranslated() {
		$str = 'A value is required for: "%s"';

		$this->assertEquals( $str, $this->obj->get_c2c_string( $str ) );
	}

	/*
	 * get_manage_options_capability()
	 */

	public function test_get_manage_options_capability() {
		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_filtered() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return 'unfiltered_html'; } );

		$this->assertEquals( 'unfiltered_html', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_blank_string() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), '__return_empty_string' );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_boolean() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), '__return_true' );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_array() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return [ 'capa', 'capb' ]; } );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	public function test_get_manage_options_capability_if_filtered_to_be_int() {
		add_filter( $this->obj->get_hook( 'manage_options_capability' ), function ( $cap ) { return 5; } );

		$this->assertEquals( 'manage_options', $this->obj->get_manage_options_capability() );
	}

	/*
	 * get_hook()
	 */

	public function test_get_hook() {
		$this->assertEquals( 'configure_smtp__example-hook', $this->obj->get_hook( 'example-hook' ) );
	}

}
