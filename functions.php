<?php

/**
 * Kalaq Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 2.0.1
 */

/**
 * Define Constants
 */
define('CHILD_THEME_ASTRA_CHILD_VERSION', '2.0.1');

/**
 * Enqueue styles
 */
function child_enqueue_styles()
{

	wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all');
}

add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);

/** FUNCKTIONFV */


/** * Completely Remove jQuery From WordPress */
function my_init()
{
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', false);
	}
}
add_action('init', 'my_init');

/** *  Remove Google fonts From WordPress */
function _remove_google_fonts()
{

	wp_deregister_script('olsen-light-google-font');
}
add_action('wp_enqueue_scripts', '_remove_google_fonts', 20);

add_filter('cs_load_google_fonts', '__return_false');
//** Read time */
function eg_read_time_shortcode() {
    $content = get_post_field( 'post_content', get_the_ID() );
    $word_count = str_word_count( strip_tags( $content ) );
    $read_time = ceil( $word_count / 2 );
    $read_time_text = __(' دقیقه زمان مطالعه', 'your-text-domain'); // Replace "your-text-domain" with your theme or plugin's text domain
    return $read_time . ' ' . $read_time_text;
}
add_shortcode( 'read_time', 'eg_read_time_shortcode' );
//** View */ 
function eg_increment_post_views() {
    if (is_singular('post')) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, 'eg_post_views_count', true);
        if ($views == '') {
            $views = 0;
        }
        $views++;
        update_post_meta($post_id, 'eg_post_views_count', $views);
    }
}
add_action('wp_head', 'eg_increment_post_views');
//** */
function eg_post_views_shortcode() {
    $post_id = get_the_ID();
    $views = get_post_meta($post_id, 'eg_post_views_count', true);
    if ($views == '') {
        $views = 0;
    }
    return $views;
}
add_shortcode('post_views', 'eg_post_views_shortcode');
// Add avatar upload support to user profile
function custom_user_profile_fields($user) {
?>
    <h3><?php _e('آواتار', 'your_textdomain'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="avatar"><?php _e('آپلود آواتار', 'your_textdomain'); ?></label></th>
            <td>
                <?php
                $avatar_url = get_user_meta($user->ID, 'avatar', true);
                if (empty($avatar_url)) {
                    echo '<span class="description">' . __('هنوز هیچ آواتاری آپلود نشده است.', 'your_textdomain') . '</span>';
                } else {
                    echo '<img src="' . esc_attr($avatar_url) . '" height="125" width="125"><br>';
                    echo '<span class="description">' . __('آواتار آپلود شده', 'your_textdomain') . '</span>';
                }
                ?>
                <br><br>
                <input type="file" id="avatar" name="avatar" value="" /><br>
                <span class="description"><?php _e('حداقل اندازه‌ی مجاز آپلود ۱۲۵*۱۲۵ پیکسل است.', 'your_textdomain'); ?></span>
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');

// Save uploaded avatar
function save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    if (!empty($_FILES['avatar']['name'])) {
        $upload = wp_upload_bits($_FILES['avatar']['name'], null, file_get_contents($_FILES['avatar']['tmp_name']));
        if (isset($upload['error']) && $upload['error'] != 0) {
            wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
        } else {
            update_user_meta($user_id, 'avatar', $upload['url']);
        }
    }
}
add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');
/** Active Classic Editor */
add_filter('use_block_editor_for_post', '__return_false');
/** Hide Author page and redirect to home page */ 
function redirect_author_page() {
    $author_name = get_query_var( 'author_name' );
    $author_id = get_query_var( 'author' );
    $author = null;
    
    if ( ! empty( $author_name ) ) {
        $author = get_user_by( 'slug', $author_name );
    } elseif ( ! empty( $author_id ) ) {
        $author = get_userdata( $author_id );
    }
    
    if ( $author === false ) {
        wp_redirect( home_url() );
        exit;
    }
}
add_action( 'template_redirect', 'redirect_author_page' );
/** Remove version number CSS and JS*/
function remove_css_js_version( $src ) {
    if ( strpos( $src, '?ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'style_loader_src', 'remove_css_js_version', 10, 2 );
add_filter( 'script_loader_src', 'remove_css_js_version', 10, 2 );
/** */
remove_action( 'wp_head', 'wp_generator' );
/** Delete Elementor Codes */
	wp_dequeue_style('elementor-common');
	wp_deregister_style('elementor-common');
	
	wp_dequeue_style('elementor-animations');
	wp_deregister_style('elementor-animations');

	wp_dequeue_style('elementor-pro');
	wp_deregister_style('elementor-pro');
/** Deny multiple purchace*/
add_filter( 'woocommerce_is_purchasable', 'kalaq_deny_purchase_if_already_purchased', 9999, 2 );
  
function kalaq_deny_purchase_if_already_purchased( $is_purchasable, $product ) {
    

   if ( is_user_logged_in() && wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) && 0 == $product->get_price() ) {
      $is_purchasable = false;
   }
   return $is_purchasable;
}
//* Remove DNS prefetch*/
add_action( 'init', 'remove_dns_prefetch' );
function  remove_dns_prefetch () {
   remove_action( 'wp_head', 'wp_resource_hints', 2, 99 );
}
/** Remove SKU */
add_filter( 'wc_product_sku_enabled', '__return_false' );
/** Disable xmlrpc */
add_filter( 'xmlrpc_enabled', '__return_false' );
/** Woo Cleaner Script */ 
function kalaq_woocommerce_script_cleaner() {
	
	// Remove the generator tag
	remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );

	// Unless we're in the store, remove all the cruft!

		wp_dequeue_style( 'woocommerce_frontend_styles' );
		wp_dequeue_style( 'woocommerce-general');
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_dequeue_style( 'woocommerce_fancybox_styles' );
		wp_dequeue_style( 'woocommerce_chosen_styles' );
		wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
		wp_dequeue_script( 'selectWoo' );
		wp_deregister_script( 'selectWoo' );
		wp_dequeue_script( 'wc-add-payment-method' );
		wp_dequeue_script( 'wc-lost-password' );
		wp_dequeue_script( 'wc_price_slider' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-add-to-cart' );
		wp_dequeue_script( 'wc-cart-fragments' );
		wp_dequeue_script( 'wc-credit-card-form' );
		wp_dequeue_script( 'wc-checkout' );
		wp_dequeue_script( 'wc-add-to-cart-variation' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-cart' );
		wp_dequeue_script( 'wc-chosen' );
		wp_dequeue_script( 'woocommerce' );
		wp_dequeue_script( 'prettyPhoto' );
		wp_dequeue_script( 'prettyPhoto-init' );
		wp_dequeue_script( 'jquery-blockui' );
		wp_dequeue_script( 'jquery-placeholder' );
		wp_dequeue_script( 'jquery-payment' );
		wp_dequeue_script( 'js.cookie' );
		wp_dequeue_script( 'jqueryui' );
	
}
add_action( 'wp_enqueue_scripts', 'kalaq_woocommerce_script_cleaner', 99 );
//* Remove Dashboard Widgets WP */ 
 function remove_dashboard_widgets() {
    global $wp_meta_boxes;
  
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
  
}
  
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );
 // Remove Dashboard widget Elementor 
 function remove_elementor_dashboard_widget() {
    remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'remove_elementor_dashboard_widget' );
//*  disable cart fragments after 1 hour /
function disable_cart_fragments() {
    if ( is_admin() ) {
        return;
    }
    if ( isset( $_COOKIE['woocommerce_items_in_cart'] ) ) {
        setcookie( 'woocommerce_items_in_cart', $_COOKIE['woocommerce_items_in_cart'], time() + 3600, COOKIEPATH, COOKIE_DOMAIN );
    }
}
add_action( 'init', 'disable_cart_fragments' );
//* Disable remote patterns*/ 
 function disable_remote_patterns_filter() {
  return false;
}
add_filter( 'should_load_remote_block_patterns', 'disable_remote_patterns_filter' );
//* */ 
 add_action('wp_dashboard_setup', 'themeprefix_remove_dashboard_widget' );
/**
 *  Remove Site Health Dashboard Widget
 *
 */
function themeprefix_remove_dashboard_widget() {
    remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
}

add_action( 'admin_menu', 'remove_site_health_menu' );	
/**
 * Remove Site Health Sub Menu Item
 */
function remove_site_health_menu(){
  remove_submenu_page( 'tools.php','site-health.php' ); 
}

add_filter( 'wp_fatal_error_handler_enabled', '__return_false' );
//* Encode Emails / 
function encode_emails( $content ) {
    $pattern = '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i';
    $replacement = '<span class="encoded-email" data-email="$1"></span>';
    $content = preg_replace( $pattern, $replacement, $content );
    return $content;
}

add_filter( 'the_content', 'encode_emails' );
