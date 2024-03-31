<?php

defined( 'ABSPATH' ) or die();

class c2c_Plugin extends WP_UnitTestCase {

	protected $obj;

	protected static $example_option = [
		'input'    => 'short_text',
		'default'  => 25,
		'datatype' => 'int',
		'required' => true,
		'label'    => 'Short text field',
		'input_attributes' => [],
	];

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

	public static function invalid_config_attributes() {
		return [
			// [ attribute name, value with invalid datatype for attribute ]
			[ 'allow_html',       '' ], // supposed to be... bool
			[ 'class',            '' ], // array
			[ 'datatype',         [] ], // string
			[ 'help',             1 ], // string
			[ 'inline_help',      15 ], // string
			[ 'input',            13.4 ], // string
			[ 'input_attributes', '' ], // array
			[ 'input_attributes', 7 ], // array
			[ 'input_attributes', 'row="40"' ], // array
			[ 'label',            false ], // string
			[ 'more_help',        [] ], // string
			[ 'no_wrap',          '0' ], // bool
			[ 'numbered',         5 ], // bool
			[ 'options',          'cat' ], // array
			[ 'raw_help',         [ 'cat', 'emu' ] ], // string
			[ 'required',         [] ], // bool
		];
	}



	//
	//
	// TESTS
	//
	//


	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_Plugin_067' ) );
	}

	/*
	 * c2c_plugin_version()
	 */

	public function test_plugin_framework_version() {
		$this->assertEquals( '067', $this->obj->c2c_plugin_version() );
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

	/*
	 * add_option()
	 */

	public function test_valid_option_attribute() {
		$this->obj->add_option( 'testoption', self::$example_option );
		$this->assertTrue( true );
	}

	/**
	 * @expectedIncorrectUsage c2c_Plugin::verify_options
	 * @dataProvider invalid_config_attributes
	 */
	public function test_invalid_option_attribute( $key, $val ) {
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ $key => $val ] ) );
	}

	public function test_config_attribute_default_can_be_any_datatype() {
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => false ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => true ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => 0 ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => 1 ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => 9 ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => 9.5 ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => '' ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => 'cat' ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => [] ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => [ 'cat', 'dog' ] ] ) );
		$this->obj->add_option( 'testoption', array_merge( self::$example_option, [ 'default' => [ 'doctor' => 11, 'quirk' => [ 'bowtie', 'fex' ] ] ] ) );
		// Implicitly asserting that none of the above triggered a warning/error.
		$this->assertTrue( true );
	}

	/*
	 * esc_attributes()
	 */

	public function test_esc_attributes() {
		$this->assertEquals( 'vehicle="Tardis" doctor="10"', $this->obj->esc_attributes( [ 'vehicle' => 'Tardis', 'doctor' => 10 ] ) );
		$this->assertEquals( 'title="This is a &quot;quoted string&quot;"', $this->obj->esc_attributes( [ 'title' => 'This is a "quoted string"' ] ) );
		$this->assertEquals( 'title="This shan&#039;t not be apostrophed."', $this->obj->esc_attributes( [ 'title' => "This shan't not be apostrophed." ] ) );
		$this->assertEquals( 'title="HTML tags are a no go."', $this->obj->esc_attributes( [ 'title' => 'HTML tags are a <strong>no go</strong>.' ] ) );
	}

	/*
	 * display_option()
	 */

	public function test_display_option_short_text_field() {
		$this->obj->add_option( 'shorttextfield', [
			'input'    => 'short_text',
			'default'  => 25,
			'datatype' => 'int',
			'required' => true,
			'label'    => 'Short text field',
			'help'     => 'This is help.',
			'input_attributes' => [],
		] );

		$expected = '<input type="text" class="c2c-short_text small-text" id="shorttextfield" name="c2c_configure_smtp[shorttextfield]" value="25" />'
			. "\n"
			. '<p class="description">This is help.</p>'
			. "\n";

		$this->expectOutputRegex( '~^' . preg_quote( $expected ) . '$~', $this->obj->display_option( 'shorttextfield' ) );
	}

}
