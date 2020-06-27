<?php 
/**
 * Twitch Webhook support in WordPress
 * 
 * Developer guide here...
 * @link https://www.engagewp.com/adding-metadata-api-support-to-custom-objects-in-wordpress-the-complete-guide/
 * 
 * @author   Ryan Bayne
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

// Action Hooks...
add_action( 'init', 'twitchpress_custom_post_type_webhooks', 0 );
// Handler for payload notifications from Twitch...
add_action( 'wp_ajax_twitchpress_twitch_webhooks_handler', 'twitchpress_twitch_webhooks_handler' );
// hook into init for single site, priority 0 = highest priority
add_action( 'init', 'twitchpress_integrate_wpdb_webhookmeta', 0);
// hook in to switch blog to support multisite
add_action( 'switch_blog', 'twitchpress_integrate_wpdb_webhookmeta', 0 );

if( !class_exists( 'TwitchPress_Twitch_Webhooks' ) ) :

class TwitchPress_Twitch_Webhooks {
    
    public $callback = null;
    public $mode = null;
    public $topic = null;
    public $lease_seconds = null;
    public $secret = null;
    
    public function __construct() {
        $this->default_settings();     
    }
    
    /**
    * Set default settings. 
    * 
    * @version 1.0
    */
    public function default_settings() {
        $this->callback = get_admin_url();
        $this->mode     = 'subscribe';
        $this->topic    = 'https://api.twitch.tv/helix/streams';
        $this->lease    = 0;
        $this->secret   = null;    
    }   
    
    public function subscribe( $topic_parameters, $lease_seconds = 0, $body_additions = array() ) {
        $http = new WP_Http();
        $curl_version_object = curl_version();

        $topic = add_query_arg( $body_additions, $this->topic );
        
        $body = array ( 
            'hub.callback'      => $this->callback,
            'hub.mode'          => $this->mode,
            'hub.topic'         => $topic,
            'hub.lease_seconds' => $this->lease,
            'hub.secret'        => $this->secret,
        );
        
        $body = array_merge( $body, $body_additions );
                
        return $http->request(
            'https://api.twitch.tv/helix/webhooks/hub',
            array(
                'method'     => 'POST', 
                'stream'     => false,
                'filename'   => false,
                'decompress' => false,
                'headers' => array(
                    'Accept'            => 'application/json',
                    'Content-Type'      => 'application/json; charset=utf-8',
                    'Client-ID'         => twitchpress_get_app_id(),
                ),
                'body' => json_encode( $body )
            )
        );        
    }    
    
    public function users_follows( $lease_seconds, $first, $from_id = null, $to_id = null ) {
        // we require either $from_id or $to_id
        if( !$from_id && !$to_id ) { return; }
        
        $this->lease_seconds = $lease_seconds; 
        
        return $this->subscribe( $lease_seconds, array(
                'first'         => $first,
                'from_id'       => $from_id,
                'to_id'         => $to_id        
            ) 
        );    
    }
}

endif;

/**
* Webhook services are not be ready until manual installation is run. 
* 
* @version 1.0
*/
function twitchpress_webhooks_activate_service() {
    twitchpress_create_table_webhooks(); 
    twitchpress_create_table_webhooks_meta();   
}

function twitchpress_twitch_webhooks_handler() {
       
    //$example = stripslashes_deep();
 
    // At the end of the hook, the script _must_ terminate
    wp_die();
}

function twitchpress_create_table_webhooks_meta() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webhookmeta';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // see wpdb_get_schema() in https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/schema.php
    $max_index_length = 191;
    
    $install_query = "CREATE TABLE $table_name (
        meta_id bigint(20) unsigned NOT NULL auto_increment,
        webhook_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) default NULL,
        meta_value longtext,
        PRIMARY KEY  (meta_id),
        KEY badge (badge_id),
        KEY meta_key (meta_key($max_index_length))
    ) $charset_collate;";
    
    dbDelta( $install_query );
}

/**
 * Adds meta data field to a webhook.
 *
 * @param int    $webhook_id  Webhook ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value.
 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
 * @return int|false Meta ID on success, false on failure.
 */
function add_webhook_meta($webhook_id, $meta_key, $meta_value, $unique = false) {
    return add_metadata('webhook', $webhook_id, $meta_key, $meta_value, $unique);
}

/**
 * Removes metadata matching criteria from a webhook.
 *
 * @param int    $webhook_id    Webhook ID
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 * @return bool True on success, false on failure.
 */
function delete_webhook_meta($webhook_id, $meta_key, $meta_value = '') {
    return delete_metadata('webhook', $webhook_id, $meta_key, $meta_value);
}

/**
 * Retrieve meta field for a webhook.
 *
 * @param int    $badge_id Webhook ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single  Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_webhook_meta($webhook_id, $key = '', $single = false) {
    return get_metadata('webhook', $webhook_id, $key, $single);
}

/**
 * Update webhook meta field based on webhook ID.
 *
 * @param int    $webhook_id   Webhook ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function update_webhook_meta($webhook_id, $meta_key, $meta_value, $prev_value = '') {
    return update_metadata('webhook', $webhook_id, $meta_key, $meta_value, $prev_value);
}

/**
 * Integrates webhookmeta table with $wpdb
 *
 * @version 1.0
 */
function twitchpress_integrate_wpdb_webhookmeta() {
    global $wpdb;
    
    $wpdb->webhookmeta = $wpdb->prefix . 'webhookmeta';
    $wpdb->tables[] = 'webhookmeta';
    
    return;
}

// Register Custom Post Type
function twitchpress_custom_post_type_webhooks() {

    $labels = array(
        'name'                  => _x( 'Webhooks', 'Post Type General Name', 'twitchpress' ),
        'singular_name'         => _x( 'Webhook', 'Post Type Singular Name', 'twitchpress' ),
        'menu_name'             => __( 'Webhooks', 'twitchpress' ),
        'name_admin_bar'        => __( 'Webhook', 'twitchpress' ),
        'archives'              => __( 'Item Archives', 'twitchpress' ),
        'attributes'            => __( 'Item Attributes', 'twitchpress' ),
        'parent_item_colon'     => __( 'Parent Item:', 'twitchpress' ),
        'all_items'             => __( 'All Items', 'twitchpress' ),
        'add_new_item'          => __( 'Add New Item', 'twitchpress' ),
        'add_new'               => __( 'Add New', 'twitchpress' ),
        'new_item'              => __( 'New Item', 'twitchpress' ),
        'edit_item'             => __( 'Edit Item', 'twitchpress' ),
        'update_item'           => __( 'Update Item', 'twitchpress' ),
        'view_item'             => __( 'View Item', 'twitchpress' ),
        'view_items'            => __( 'View Items', 'twitchpress' ),
        'search_items'          => __( 'Search Item', 'twitchpress' ),
        'not_found'             => __( 'Not found', 'twitchpress' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'twitchpress' ),
        'featured_image'        => __( 'Featured Image', 'twitchpress' ),
        'set_featured_image'    => __( 'Set featured image', 'twitchpress' ),
        'remove_featured_image' => __( 'Remove featured image', 'twitchpress' ),
        'use_featured_image'    => __( 'Use as featured image', 'twitchpress' ),
        'insert_into_item'      => __( 'Insert into item', 'twitchpress' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'twitchpress' ),
        'items_list'            => __( 'Items list', 'twitchpress' ),
        'items_list_navigation' => __( 'Items list navigation', 'twitchpress' ),
        'filter_items_list'     => __( 'Filter items list', 'twitchpress' ),
    );
    $capabilities = array(
        'edit_post'             => 'edit_post',
        'read_post'             => 'activate_plugins',
        'delete_post'           => 'delete_post',
        'edit_posts'            => 'edit_posts',
        'edit_others_posts'     => 'edit_others_posts',
        'publish_posts'         => 'publish_posts',
        'read_private_posts'    => 'read_private_posts',
    );
    $args = array(
        'label'                 => __( 'Webhook', 'twitchpress' ),
        'description'           => __( 'Twitch web subscriptions using TwitchPress', 'twitchpress' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'taxonomies'            => array( 'category' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 10,
        'menu_icon'             => 'dashicon-admin-posta',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => false,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capabilities'          => $capabilities,
        'show_in_rest'          => false,
    );
    register_post_type( 'webhooks', $args );
}