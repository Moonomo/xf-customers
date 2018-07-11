<?php

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Plugin {

	/**
	 * Key name for the Custom Post Type.
	 */
	const CUSTOMER_POST_TYPE = 'xf-customer';

	/**
	 * Taxonomy key for Customers Category.
	 */
	const CUSTOMER_CATEGORY_KEY = 'xf_customer_category';

	/**
	 * Taxonomy key for Customers Tags.
	 */
	const CUSTOMER_TAG_KEY = 'xf_customer_tag';

	/**
	 * Meta Key for Customers Data
	 */
	const CUSTOMER_SETTINGS_KEY = 'xf_customer_data';

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	private $version = '0.0.1';

	/**
	 * The path to the root file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The URL to the plugin.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Plugin constructor.
	 *
	 * @param string $file The path to the plugins root file.
	 */
	public function __construct( $file ) {

		$this->file       = $file;
		$this->plugin_url = plugins_url( '/', $file );
		$this->post_type  = self::CUSTOMER_POST_TYPE;
	}

	/**
	 * Return plugin version
	 *
	 * @return string
	 */
	public function get_plugin_version() {

		return $this->version;
	}

	/**
	 * Setup the plugin.
	 */
	public function setup() {

		add_action( 'init', array( $this, 'register' ) );

		// Languages
		load_plugin_textdomain( 'xf-customers', false, dirname( $this->file ) . '/languages' );

		if ( is_admin() ) {
			// Filters.
			add_filter( 'enter_title_here', array( $this, 'post_type_default_title' ) );
			add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'post_type_columns' ) );
			add_filter( "manage_{$this->post_type}_posts_custom_column", array( $this, 'post_type_custom_column' ), 10, 2 );
		}
	}

	/**
	 * Register Post Type and Taxonomies for Customers.
	 */
	public function register() {

		/**
		 * The post type for Customers.
		 */
		register_post_type( $this->post_type, array(
			'labels'             => array(
				'name'               => esc_html_x( 'Customers', 'post type general name', 'xf-customers' ),
				'singular_name'      => esc_html_x( 'Customer', 'post type singular name', 'xf-customers' ),
				'menu_name'          => esc_html_x( 'Customers', 'admin menu', 'xf-customers' ),
				'name_admin_bar'     => esc_html_x( 'Customers', 'add new on admin bar', 'xf-customers' ),
				'add_new'            => esc_html__( 'Add New', 'xf-customers' ),
				'add_new_item'       => esc_html__( 'Add New Customer', 'xf-customers' ),
				'new_item'           => esc_html__( 'New Customer', 'xf-customers' ),
				'edit_item'          => esc_html__( 'Edit Customer', 'xf-customers' ),
				'view_item'          => esc_html__( 'View Customer', 'xf-customers' ),
				'all_items'          => esc_html__( 'All Customers', 'xf-customers' ),
				'search_items'       => esc_html__( 'Search Customers', 'xf-customers' ),
				'parent_item_colon'  => esc_html__( 'Parent Customers:', 'xf-customers' ),
				'not_found'          => esc_html__( 'No Customers found.', 'xf-customers' ),
				'not_found_in_trash' => esc_html__( 'No Customers found in Trash.', 'xf-customers' ),
			),
			'capability_type'    => 'post',
			'menu_icon'          => 'dashicons-id',
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array(
				//'title',
				//'editor',
			),
		) );

		/**
		 * Customer Categories.
		 */
		register_taxonomy( self::CUSTOMER_CATEGORY_KEY, array( $this->post_type ), array(
			'labels'             => array(
				'name'                       => esc_html__( 'Customer Categories', 'xf-customers' ),
				'singular_name'              => esc_html__( 'Customer Category', 'xf-customers' ),
				'menu_name'                  => esc_html__( 'Customer Categories', 'xf-customers' ),
				'search_items'               => esc_html__( 'Search Customer Categories', 'xf-customers' ),
				'popular_items'              => esc_html__( 'Popular Customer Categories', 'xf-customers' ),
				'all_items'                  => esc_html__( 'All Customer Categories', 'xf-customers' ),
				'edit_item'                  => esc_html__( 'Edit Customer Category', 'xf-customers' ),
				'update_item'                => esc_html__( 'Update Customer Category', 'xf-customers' ),
				'add_new_item'               => esc_html__( 'Add New Customer Category', 'xf-customers' ),
				'new_item_name'              => esc_html__( 'New Customer Category', 'xf-customers' ),
				'separate_items_with_commas' => esc_html__( 'Separate customer categories with commas', 'xf-customers' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove customer categories', 'xf-customers' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most popular customer categories', 'xf-customers' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'query_var'          => false,
			'hierarchical'       => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => false,
		) );

		/**
		 * Customer Tags.
		 */
		register_taxonomy( self::CUSTOMER_TAG_KEY, array( $this->post_type ), array(
			'labels'             => array(
				'name'                       => esc_html__( 'Customer Tags', 'xf-customers' ),
				'singular_name'              => esc_html__( 'Customer Tag', 'xf-customers' ),
				'menu_name'                  => esc_html__( 'Customer Tags', 'xf-customers' ),
				'search_items'               => esc_html__( 'Search Customer Tags', 'xf-customers' ),
				'popular_items'              => esc_html__( 'Popular Customer Tags', 'xf-customers' ),
				'all_items'                  => esc_html__( 'All Customer Tags', 'xf-customers' ),
				'edit_item'                  => esc_html__( 'Edit Customer Tag', 'xf-customers' ),
				'update_item'                => esc_html__( 'Update Customer Tag', 'xf-customers' ),
				'add_new_item'               => esc_html__( 'Add New Customer Tag', 'xf-customers' ),
				'new_item_name'              => esc_html__( 'New Customer Tag', 'xf-customers' ),
				'separate_items_with_commas' => esc_html__( 'Separate customer tags with commas', 'xf-customers' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove customer tags', 'xf-customers' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most popular customer tags', 'xf-customers' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'query_var'          => false,
			'hierarchical'       => false,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => false,
		) );
	}

	/**
	 * Change ‘Enter Title Here’ text for the Customer.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function post_type_default_title( $title ) {

		if ( get_current_screen()->post_type === $this->post_type ) {
			$title = esc_html__( 'Enter the customer name', 'xf-customers' );
		}

		return $title;
	}

	/**
	 * Add custom column to List Table.
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function post_type_columns( $columns ) {
		unset( $columns['date'] );

		$columns['title']  = esc_html__( 'Name', 'xf-customers' );
		$columns['phone']  = esc_html__( 'Phone Number', 'xf-customers' );
		$columns['email']  = esc_html__( 'Email Address', 'xf-customers' );
		$columns['budget'] = esc_html__( 'Desired Budget', 'xf-customers' );
		$columns['date']   = esc_html__( 'Date', 'xf-customers' );

		return $columns;
	}

	/**
	 * Add custom column content to List Table.
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function post_type_custom_column( $column, $post_id ) {

		$customer_data = get_post_meta( $post_id, self::CUSTOMER_SETTINGS_KEY, true );

		if (
			empty( $customer_data['phone'] )
			|| empty( $customer_data['email'] )
			|| empty( $customer_data['budget'] )
		) {
			return;
		}

		switch ( $column ) {
			case 'phone':
				echo esc_html( $customer_data['phone'] );
				break;

			case 'email':
				echo esc_html( $customer_data['email'] );
				break;

			case 'budget':
				echo esc_html( $customer_data['budget'] );
				break;
		}
	}

	/**
	 * Render template file.
	 *
	 * @param object $data
	 * @param string $template
	 *
	 * @return string
	 */
	public function render( $data, $template ) {

		$file = dirname( $this->file ) . '/templates/' . $template . '.php';

		if ( empty( $data ) || ! is_readable( $file ) ) {
			return '';
		}

		ob_start();
		include( $file );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
