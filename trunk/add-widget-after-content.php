<?php
/**
 *
 * @package   Add Widget After Content
 * @author    Arelthia Phillips
 * @license   GPL-3.0+
 * @link      http://pintopsolutions.com
 * @copyright Copyright (C) 2014 Arelthia Phillips
 *
 * Plugin Name: 		Add Widget After Content
 * Description: 		This plugin adds a widget area after post content. You can also tell it not to display on a specific post. 
 * Plugin URI: 			http://pintopsolutions.com/plugins/add-widget-after-content
 * Author: 				Arelthia Phillips
 * Author URI: 			http://www.arelthiaphillips.com
 * Version: 			1.0.0
 * License: 			GPL-3.0+
 * License URI:       	http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: 		add-widget-after-content
 * Domain Path:       	/languages
 * GitHub Plugin URI: 	https://github.com/pintop/add-widget-after-content
 * GitHub Branch:     	master
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


if ( !class_exists( 'AddWidgetAfterContent' ) ) {
	/**
	 * @package AddWidgetAfterContent
	 * @author  Arelthia Phillips
	 */
	class AddWidgetAfterContent {
		/**
		 * Unique identifier for your plugin.
		 *
		 * The variable name is used as the text domain when internationalizing strings
		 * of text. 
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_slug = 'add-widget-after-content';	

		/**
		 * Initialize the plugin 
		 * @access public
		 * @return AddWidgetAfterContent
		 */
		function __construct() {
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_action('widgets_init', array( $this,'register_sidebar'));
			add_action( 'add_meta_boxes', array( $this,'after_content_create_metabox') );
			add_action( 'save_post', array( $this,'after_content_save_meta') );
			add_filter ('the_content', array( $this,'insert_after_content'));
		}

		/**
		 * Fired when the plugin is activated
		 * 
		 */
		public static function activate() {
		   		    
		}

		/**
		 * Fired when the plugin is uninstalled
		 * 
		 */
		public static function uninstall() {
		    delete_post_meta_by_key( '_awac_hide_widget' );
		    unregister_sidebar( 'add-widget-after-content' );
		    remove_shortcode('psac');
		}
		
		/**
		 * Register the widget area/sidebar that will go after the content
		 * 
		 */
		function register_sidebar() {
			register_sidebar( array(
	                'id' => 'add-widget-after-content',
	                'name' => __( 'After Content' ),
	                'description' => __( 'This widget section shows after the content on single post pages', $this->plugin_slug ),
	                'before_widget' => '<div id="awac-wrapper"><div id="awac" class="widget %1$s">',
	                'after_widget' => '</div></div>',
	                'before_title' => '<h4 class="widget-title">',
	                'after_title' => '</h4>'
	    	) );
		} 

		/**
		 * Return the plugin slug.
		 *
		 * @since    1.0.0
		 * @return   Plugin slug variable.
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Add the widget after the post content if the widget is not set to be hide
		 * @param  string $content content of the current post
		 * @return $content the post content plus the widget area content
		 */
		function insert_after_content( $content ) {
		   //should the widget be shown after the content
		   $ps_hide_widget = get_post_meta( get_the_ID(), '_awac_hide_widget', true ); 
		  //if this is a single page and the widget is not set to be hide
		   if( is_single() &&  $ps_hide_widget != 1 ) {
		      $content.= $this->get_after_content();
		   }
		   return $content;
		}

		/**
		 * Get what ever is to be in the widget area, but don't display it yet
		 * @return string the content of the add-widget-after-content sidebar/widget
		 */
		function get_after_content() {
			ob_start();
			dynamic_sidebar( 'add-widget-after-content' ); 
			$sidebar = ob_get_contents();
			ob_end_clean();
			return $sidebar;
		}

		/**
		 * Add a meta box to post admin pages
		 */
		function after_content_create_metabox() {
		    add_meta_box( 'ps-meta', 'Widget After Content', array( $this, 'after_content_metabox' ), 'post', 'normal', 'high' );
		}

		/**
		 * Fills the Widget After Content metabox with its content
		 * @param  object $post the post object for the post
		 */
		function after_content_metabox( $post ) {
			
			$ps_hide_widget = get_post_meta( $post->ID, '_awac_hide_widget', true ); 
			$status = checked( $ps_hide_widget, 1, false );
			$html = __( 'Remove widget after content for this post.', $this->plugin_slug );
			$html .='<p>Yes: <input type="checkbox" name="ps_hide_widget"';
			$html .= esc_attr( $status );
			$html .='/></p>';

			echo $html;       
		}


		/**
		 * Saves _awac_hide_widget when the post is saved
		 * @param  int $post_id the id of the current post being saved
		 */
		function after_content_save_meta( $post_id) {
    
		    //only do if post meta is set
		    if ( isset( $_POST['ps_hide_widget'] ) ){
		        update_post_meta( $post_id, '_awac_hide_widget', TRUE );
		    } else {
		        update_post_meta( $post_id, '_awac_hide_widget', FALSE );
		    }

		} 	

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_textdomain() {

			$domain = $this->plugin_slug;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

		}
	}//end class AddWidgetAfterContent

}//end check for class



if( class_exists( 'AddWidgetAfterContent' ) ) { 
	
	/**
	 * Register callback to be fired when plugin is activated
	 */
	register_activation_hook( __FILE__, array( 'AddWidgetAfterContent', 'activate' ) ); 
	
	/**
	 * Register callback to be fired when plugin is uninstalled
	 */
	register_uninstall_hook(  __FILE__, array( 'AddWidgetAfterContent','uninstall' ) );

	/**
	 * instantiate the plugin class  
	 */
	$wp_plugin_template = new AddWidgetAfterContent(); 
}