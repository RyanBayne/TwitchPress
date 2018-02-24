<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Methods for handling all post types including "post", "page" and 
 * registrating custom post types.
 *
 * @class     TwitchPress_Post_types
 * @version   1.0.0
 * @package   TwitchPress/Includes/Post_Types
 * @category  Class
 * @author    Ryan Bayne
 */
class TwitchPress_Post_types {

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
        add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
        add_action( 'twitchpress_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_custom_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_twitchpress_post_sharing_options' ) );  
        add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_submitbox' ) );
    }

    /**
     * Register core taxonomies.
     */
    public static function register_taxonomies() {

        if ( ! is_blog_installed() ) {
            return;
        }

        if ( taxonomy_exists( 'twitchfeed_type' ) ) {
            return;
        }

        do_action( 'twitchpress_register_taxonomy' );

        $permalinks = twitchpress_get_permalink_structure();

        register_taxonomy( 'twitchfeed_type',
            apply_filters( 'twitchpress_taxonomy_objects_twitchfeed_type', array( 'twitchfeed' ) ),
            apply_filters( 'twitchpress_taxonomy_args_twitchfeed_type', array(
                'hierarchical'      => false,
                'show_ui'           => false,
                'show_in_nav_menus' => false,
                'query_var'         => is_admin(),
                'rewrite'           => false,
                'public'            => false,
            ) )
        );

        register_taxonomy( 'twitchfeed_visibility',
            apply_filters( 'twitchpress_taxonomy_objects_twitchpress_visibility', array( 'twitchfeed', 'twitchfeed_variation' ) ),
            apply_filters( 'twitchpress_taxonomy_args_twitchpress_visibility', array(
                'hierarchical'      => false,
                'show_ui'           => false,
                'show_in_nav_menus' => false,
                'query_var'         => is_admin(),
                'rewrite'           => false,
                'public'            => false,
            ) )
        );

        register_taxonomy( 'twitchfeed_cat',
            apply_filters( 'twitchpress_taxonomy_objects_twitchfeed_cat', array( 'twitchfeed' ) ),
            apply_filters( 'twitchpress_taxonomy_args_twitchfeed_cat', array(
                'hierarchical'          => true,
                'label'                 => __( 'Categories', 'twitchpress' ),
                'labels' => array(
                        'name'              => __( 'Twitch Feed Categories', 'twitchpress' ),
                        'singular_name'     => __( 'Category', 'twitchpress' ),
                        'menu_name'         => _x( 'Categories', 'Admin menu name', 'twitchpress' ),
                        'search_items'      => __( 'Search categories', 'twitchpress' ),
                        'all_items'         => __( 'All categories', 'twitchpress' ),
                        'parent_item'       => __( 'Parent category', 'twitchpress' ),
                        'parent_item_colon' => __( 'Parent category:', 'twitchpress' ),
                        'edit_item'         => __( 'Edit category', 'twitchpress' ),
                        'update_item'       => __( 'Update category', 'twitchpress' ),
                        'add_new_item'      => __( 'Add new category', 'twitchpress' ),
                        'new_item_name'     => __( 'New category name', 'twitchpress' ),
                        'not_found'         => __( 'No categories found', 'twitchpress' ),
                    ),
                'show_ui'               => true,
                'query_var'             => true,
                'rewrite'          => array(
                    'slug'         => $permalinks['category_rewrite_slug'],
                    'with_front'   => false,
                    'hierarchical' => true,
                ),
            ) )
        );

        register_taxonomy( 'twitchfeed_tag',
            apply_filters( 'twitchpress_taxonomy_objects_twitchfeed_tag', array( 'twitchfeed' ) ),
            apply_filters( 'twitchpress_taxonomy_args_twitchfeed_tag', array(
                'hierarchical'          => false,
                'label'                 => __( 'Twitch Feed tags', 'twitchfeed' ),
                'labels'                => array(
                        'name'                       => __( 'Twitch Feed Tags', 'twitchpress' ),
                        'singular_name'              => __( 'Tag', 'twitchpress' ),
                        'menu_name'                  => _x( 'Tags', 'Admin menu name', 'twitchpress' ),
                        'search_items'               => __( 'Search tags', 'twitchpress' ),
                        'all_items'                  => __( 'All tags', 'twitchpress' ),
                        'edit_item'                  => __( 'Edit tag', 'twitchpress' ),
                        'update_item'                => __( 'Update tag', 'twitchpress' ),
                        'add_new_item'               => __( 'Add new tag', 'twitchpress' ),
                        'new_item_name'              => __( 'New tag name', 'twitchpress' ),
                        'popular_items'              => __( 'Popular tags', 'twitchpress' ),
                        'separate_items_with_commas' => __( 'Separate tags with commas', 'twitchpress' ),
                        'add_or_remove_items'        => __( 'Add or remove tags', 'twitchpress' ),
                        'choose_from_most_used'      => __( 'Choose from the most used tags', 'twitchpress' ),
                        'not_found'                  => __( 'No tags found', 'twitchpress' ),
                    ),    
                'show_ui'               => true,
                'query_var'             => true,
                'rewrite'               => array(
                    'slug'       => $permalinks['tag_rewrite_slug'],
                    'with_front' => false,
                ),
            ) )
        );

        do_action( 'twitchpress_after_register_taxonomy' );
    }

    /**
     * Register core post types.
     * 
     * @link https://developer.wordpress.org/reference/functions/register_post_type/
     */
    public static function register_post_types() {
        if ( ! is_blog_installed() || post_type_exists( 'twitchfeed' ) ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();
        
        register_post_type( 'twitchfeed',
            apply_filters( 'twitchpress_register_post_type_twitchfeed',
                array(
                    'labels'              => array(
                            'name'                  => __( 'Twitch Feed Entries', 'twitchpress' ),
                            'singular_name'         => __( 'Twitch Feed Entry', 'twitchpress' ),
                            'menu_name'             => _x( 'TwitchPress', 'Admin menu name', 'twitchpress' ),
                            'add_new'               => __( 'Post to Twitch', 'twitchpress' ),
                            'add_new_item'          => __( 'Create Twitch Feed Post', 'twitchpress' ),
                            'edit'                  => __( 'Edit', 'twitchpress' ),
                            'edit_item'             => __( 'Edit TwitchPress post', 'twitchpress' ),
                            'new_item'              => __( 'New post', 'twitchpress' ),
                            'view'                  => __( 'View post', 'twitchpress' ),
                            'view_item'             => __( 'View post', 'twitchpress' ),
                            'search_items'          => __( 'Search posts', 'twitchpress' ),
                            'not_found'             => __( 'No posts found', 'twitchpress' ),
                            'not_found_in_trash'    => __( 'No posts found in trash', 'twitchpress' ),
                            'parent'                => __( 'Parent post', 'twitchpress' ),
                            'featured_image'        => __( 'Post image', 'twitchpress' ),
                            'set_featured_image'    => __( 'Set post image', 'twitchpress' ),
                            'remove_featured_image' => __( 'Remove post image', 'twitchpress' ),
                            'use_featured_image'    => __( 'Use as post image', 'twitchpress' ),
                            'insert_into_item'      => __( 'Insert into post', 'twitchpress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this post', 'twitchpress' ),
                            'filter_items_list'     => __( 'Filter posts', 'twitchpress' ),
                            'items_list_navigation' => __( 'Twitch Feed navigation', 'twitchpress' ),
                            'items_list'            => __( 'Feed posts list', 'twitchpress' ),
                        ),
                    'description'         => __( 'This is where you can publish new Twitch feed posts.', 'twitchpress' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
                    'rewrite'             => $permalinks['twitchfeed_rewrite_slug'] ? array( 'slug' => $permalinks['twitchfeed_rewrite_slug'], 'with_front' => false, 'feeds' => true ) : false,
                    'query_var'           => true,
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'publicize', 'wpcom-markdown' ),
                    'has_archive'         => true,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => true,
                    'show_in_menu'        => false,
                )
            )
        );        
        
        register_post_type( 'twitchchannels',
            apply_filters( 'twitchpress_register_post_type_twitchchannels',
                array(
                    'labels'              => array(
                            'name'                  => __( 'Twitch Channels', 'twitchpress' ),
                            'singular_name'         => __( 'Twitch Channel', 'twitchpress' ),
                            'menu_name'             => _x( 'Channels', 'Admin menu name', 'twitchpress' ),
                            'add_new'               => __( 'Add a channel', 'twitchpress' ),
                            'add_new_item'          => __( 'Add New Twitch.tv channel', 'twitchpress' ),
                            'edit'                  => __( 'Edit', 'twitchpress' ),
                            'edit_item'             => __( 'Edit TwitchPress channel', 'twitchpress' ),
                            'new_item'              => __( 'New channel', 'twitchpress' ),
                            'view'                  => __( 'View channel', 'twitchpress' ),
                            'view_item'             => __( 'View channel', 'twitchpress' ),
                            'search_items'          => __( 'Search channels', 'twitchpress' ),
                            'not_found'             => __( 'No channels found', 'twitchpress' ),
                            'not_found_in_trash'    => __( 'No channels found in trash', 'twitchpress' ),
                            'parent'                => __( 'Parent channel', 'twitchpress' ),
                            'featured_image'        => __( 'Channel image', 'twitchpress' ),
                            'set_featured_image'    => __( 'Set channel image', 'twitchpress' ),
                            'remove_featured_image' => __( 'Remove channel image', 'twitchpress' ),
                            'use_featured_image'    => __( 'Use as channel image', 'twitchpress' ),
                            'insert_into_item'      => __( 'Insert into content', 'twitchpress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this channel post', 'twitchpress' ),
                            'filter_items_list'     => __( 'Filter channels', 'twitchpress' ),
                            'items_list_navigation' => __( 'Twitch channel navigation', 'twitchpress' ),
                            'items_list'            => __( 'Feed channels list', 'twitchpress' ),
                        ),
                    'description'         => __( 'This is where you can add channel posts.', 'twitchpress' ),
                    'public'              => false,
                    'show_ui'             => true,
                    'publicly_queryable'  => false,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, 
                    'rewrite'             => $permalinks['twitchfeed_rewrite_slug'] ? array( 'slug' => $permalinks['twitchfeed_rewrite_slug'], 'with_front' => false, 'feeds' => true ) : false,
                    'query_var'           => true,
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'wpcom-markdown' ),
                    'has_archive'         => true,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => true,
                    'show_in_menu'        => false,
                    'map_meta_cap'        => true,
                    'capability_type'     => 'post'
                )
            )
        );
    }

    /**
     * Register our custom post statuses, used for order status.
     * 
     * @version 1.0
     */
    public static function register_post_status() {

        $order_statuses = apply_filters( 'twitchpress_register_twitchfeed_post_statuses',
            array(
                'twitchpress-awaitingtrigger'    => array(
                    'label'                     => _x( 'Awaiting Trigger', 'Order status', 'twitchpress' ),
                    'public'                    => false,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Awaiting Trigger <span class="count">(%s)</span>', 'Awaiting Trigger <span class="count">(%s)</span>', 'twitchpress' ),
                )
            )
        );

        foreach ( $order_statuses as $order_status => $values ) {
            register_post_status( $order_status, $values );
        }
    }

    /**
     * Flush rewrite rules.
     */
    public static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Allow twitchfeed posts in API controlled by JetPack.
     *
     * @param  array $post_types
     * @return array
     */
    public static function rest_api_allowed_post_types( $post_types ) {
        $post_types[] = 'twitchfeed';

        return $post_types;
    }
    
    /**
    * Add all custom meta boxes. 
    * 
    * @version 1.0
    */
    public static function add_custom_boxes() {
        global $post;
        
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type( $post );
        
        // Should this post-type get Twitch sharing controls? 
        if( 'yes' == get_option( 'twitchpress_shareable_posttype_' . $post_type ) ) {
            add_meta_box(
                'twitchpress_post_sharing_options', // Unique ID
                __( 'Twitch Channel Feed', 'twitchpress' ),  
                array( __CLASS__, 'html_twitchpress_post_sharing_options' ),        
                $post_type // Post type
            );
        }
    } 
    
    /**
    * Options for sharing post content to Twitch feed.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_twitchpress_post_sharing_options($post) {
        
        /*
        ?>
        <label for="twitchpress_whentoshare"><?php _e( 'When should the content be shared?', 'twitchpress' ); ?></label>
        <select name="twitchpress_whentoshare" id="twitchpress_whentoshare" class="postbox">
            <option value="">Select something...</option>
            <option value="publishing">ASAP</option>
        </select>
        <?php
        */
        
    }
 
    /**
    * Saves and processes share to feed options.
    * 
    * @param mixed $post_id
    * 
    * @version 1.0
    */
    public static function save_twitchpress_post_sharing_options($post_id){
        
        /*
        if ( array_key_exists( 'twitchpress_whentoshare', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_whentoshare',
                $_POST['twitchpress_whentoshare']
            );
        }
        */
        
        return $post_id;
    }  
    
    /**
    * Display custom fields in the publish box. 
    * 
    * @version 1.0
    */
    public static function post_submitbox() {
        global $post;
        
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type($post);
     
        $twitch_share = get_option( 'twitchpress_shareable_posttype_' . $post_type );
        if ( $twitch_share == 'yes' ) { 
            echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
            wp_nonce_field( plugin_basename(__FILE__), 'nonce_twitchpress_share_post_option' );
            echo '<input type="checkbox" name="twitchpress_share_post_option" id="twitchpress_share_post_option" value="share" /> <label for="twitchpress_share_post_option">Share to Twitch Feed</label><br />';
            echo '</div>';
        }  

    }
    
}

TwitchPress_Post_types::init();
