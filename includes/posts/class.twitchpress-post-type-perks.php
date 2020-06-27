<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TwitchPress_Post_Type_Perks' ) ) :

/**
 * Registers custom post type "twitchperks"
 *
 * @class     TwitchPress_Post_Type_Perks
 * @version   1.0.0
 * @package   TwitchPress/Includes/Post_Types
 * @category  Class
 * @author    Ryan Bayne
 */
class TwitchPress_Post_Type_Perks {
    
    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );  
    }

    public static function register_taxonomies() {

        if ( ! is_blog_installed() ) {
            return;
        }

        if ( !taxonomy_exists( 'twitchperks_type' ) ) {
            self::register_taxonomy_perks();
        }

        do_action( 'twitchpress_register_perks_taxonomy' );

    }
            
    public static function register_taxonomy_perks() {
        $permalinks = twitchpress_get_permalink_structure();
        
        do_action( 'twitchpress_after_register_perks_taxonomy' );        
    }

    /**
     * Register twitchperks post types.
     * 
     * @link https://developer.wordpress.org/reference/functions/register_post_type/  
     * 
     * @version 1.0 
     */
    public static function register_post_type() {
        if ( ! is_blog_installed() || post_type_exists( 'perks' ) ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();  
        
        register_post_type( 'perks',
            apply_filters( 'twitchpress_register_post_type_perks',
                array(
                    'labels'              => array(
                            'name'                  => __( 'Follower Perks', 'twitchpress' ),
                            'singular_name'         => __( 'Follower Perk', 'twitchpress' ),
                            'menu_name'             => _x( 'Perks', 'Admin menu name', 'twitchpress' ),
                            'add_new'               => __( 'Add a perk', 'twitchpress' ),
                            'add_new_item'          => __( 'Add new Perk', 'twitchpress' ),
                            'edit'                  => __( 'Edit', 'twitchpress' ),
                            'edit_item'             => __( 'Edit TwitchPress perk', 'twitchpress' ),
                            'new_item'              => __( 'New perk', 'twitchpress' ),
                            'view'                  => __( 'View perk', 'twitchpress' ),
                            'view_item'             => __( 'View perk', 'twitchpress' ),
                            'search_items'          => __( 'Search perks', 'twitchpress' ),
                            'not_found'             => __( 'No perks found', 'twitchpress' ),
                            'not_found_in_trash'    => __( 'No perks found in trash', 'twitchpress' ),
                            'parent'                => __( 'Parent perk', 'twitchpress' ),
                            'featured_image'        => __( 'Perk image', 'twitchpress' ),
                            'set_featured_image'    => __( 'Set perk image', 'twitchpress' ),
                            'remove_featured_image' => __( 'Remove perk image', 'twitchpress' ),
                            'use_featured_image'    => __( 'Use as perk image', 'twitchpress' ),
                            'insert_into_item'      => __( 'Insert into content', 'twitchpress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this perk post', 'twitchpress' ),
                            'filter_items_list'     => __( 'Filter perks', 'twitchpress' ),
                            'items_list_navigation' => __( 'Perk navigation', 'twitchpress' ),
                            'items_list'            => __( 'Perk list', 'twitchpress' ),
                        ),
                    'description'         => __( 'This is where you can add perk posts.', 'twitchpress' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, 
                    'rewrite'             => $permalinks['perks_rewrite_slug'] ? array( 'slug' => $permalinks['perks_rewrite_slug'], 'with_front' => false, 'perks' => true ) : false,
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
        /*
        $order_statuses = apply_filters( 'twitchpress_register_twitchperk_post_statuses',
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
        */
    }           
}

endif;
