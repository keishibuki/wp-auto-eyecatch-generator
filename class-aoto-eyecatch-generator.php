<?php
/**
 * Plugin Name: WP Auto Eyecatch Generator
 *
 * @package WordPress
 */

class Auto_Eyecatch_Generator {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
		add_action( 'wp_ajax_eyecath_generate', array( $this, 'add_wp_ajax_eyecath_generate' ), 10, 1 );
		add_filter( 'manage_posts_columns', array( $this, 'add_posts_columns_thumbnail' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'add_admin_column' ), 10, 2 );
	}

	/**
	 * Add wp ajax php file
	 *
	 * @return void
	 */
	public function add_wp_ajax_eyecath_generate() {
		require_once plugin_dir_path( __FILE__ ) . '/eyecatch-generate.php';
		die();
	}

	/**
	 * Enqueue plugin scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'eyecatch-generator',
			plugins_url( 'js/eyecatch-generator.js', __FILE__ ),
			[ 'jquery' ],
			filemtime( plugin_dir_path( __FILE__ ) . '/js/eyecatch-generator.js'),
			true,
		);
	}

	/**
	 * Add a thumbnail column to the list of posts
	 *
	 * @param string[] $post_columns
	 * @param string $post_type
	 * @return string[]
	 */
	public function add_posts_columns_thumbnail( $post_columns, $post_type ) {
		$post_columns['thumbnail'] = 'アイキャッチ';

		return $post_columns;
	}

	/**
	 * Add the following to the thumbnail column of the post list
	 * Thumbnail is set: Thumbnail image
	 * Thumbnail is not set: Generate Thumbnail button
	 *
	 * @param string $column_name
	 * @param int $post_id
	 * @return void
	 */
	public function add_admin_column( $column_name, $post_id ) {
		if ( 'thumbnail' == $column_name) {
			if ( has_post_thumbnail( $post_id )) {
				printf( '<img src="%s" style="%s" />', get_the_post_thumbnail_url( $post_id, 'thumbnail' ), "max-width: 100%" );
			} else {
				echo '<button type="button" class="button btn-eyecatch-generate">';
				echo __('Create');
				echo '</butt>';
			}
		}
	}
}

$auto_eyecatch_generator = new Auto_Eyecatch_Generator();
