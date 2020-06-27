<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Post_Type_Giveaways' ) ) :

/**
 * Methods for handling all post types including "post", "page" and 
 * registrating custom post types.
 *
 * @class     TwitchPress_Post_Type_Giveaways
 * @version   1.0.0
 * @package   TwitchPress/Giveaways
 */
class TwitchPress_Post_Type_Giveaways {     

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
        //add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
        add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );  
        //add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_submitbox' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_custom_boxes' ) );
    }                                
    
    public static function register_taxonomies() {
        
        if ( ! is_blog_installed() ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();
        
        if ( !taxonomy_exists( 'giveaways_type' ) ) {
            
        }
        
        do_action( 'twitchpress_after_register_taxonomy' );        
    }

    /**
     * Register giveaways post type...
     * 
     * @link https://developer.wordpress.org/reference/functions/register_post_type/  
     * 
     * @version 2.0 
     */
    public static function register_post_type() {             
        if ( ! is_blog_installed() || post_type_exists( 'giveaways' ) ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();  
        
        register_post_type( 'giveaways',
            apply_filters( 'twitchpress_register_post_type_giveaways',
                array(
                    'labels' => array(
                            'name'                  => __( 'Twitch Giveaways', 'twitchpress' ),
                            'singular_name'         => __( 'Twitch Giveaway', 'twitchpress' ),
                            'menu_name'             => _x( 'Giveaways', 'Admin menu name', 'twitchpress' ),
                            'add_new'               => __( 'Create a Giveaway', 'twitchpress' ),
                            'add_new_item'          => __( 'Create New Giveaway', 'twitchpress' ),
                            'edit'                  => __( 'Edit', 'twitchpress' ),
                            'edit_item'             => __( 'Edit Giveaway', 'twitchpress' ),
                            'new_item'              => __( 'New giveaway', 'twitchpress' ),
                            'view'                  => __( 'View giveaway', 'twitchpress' ),
                            'view_item'             => __( 'View giveaway', 'twitchpress' ),
                            'search_items'          => __( 'Search giveaways', 'twitchpress' ),
                            'not_found'             => __( 'No giveways found', 'twitchpress' ),
                            'not_found_in_trash'    => __( 'No giveaways found in trash', 'twitchpress' ),
                            'parent'                => __( 'Parent giveaway', 'twitchpress' ),
                            'featured_image'        => __( 'Giveaway image', 'twitchpress' ),
                            'set_featured_image'    => __( 'Set giveaway image', 'twitchpress' ),
                            'remove_featured_image' => __( 'Remove giveaway image', 'twitchpress' ),
                            'use_featured_image'    => __( 'Use as giveaway image', 'twitchpress' ),
                            'insert_into_item'      => __( 'Insert into giveaway', 'twitchpress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this giveaway post', 'twitchpress' ),
                            'filter_items_list'     => __( 'Filter giveaways', 'twitchpress' ),
                            'items_list_navigation' => __( 'Twitch giveaway navigation', 'twitchpress' ),
                            'items_list'            => __( 'Giveaways list', 'twitchpress' ),
                        ),
                    'description'         => __( 'This is where you can add giveaway posts.', 'twitchpress' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, 
                    'rewrite'             => $permalinks[ 'giveaways_rewrite_slug'] ? array( 'slug' => $permalinks[ 'giveaways_rewrite_slug'], 'with_front' => false, 'giveaways' => true ) : false,
                    'query_var'           => true,
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'wpcom-markdown' ),
                    'has_archive'         => true,
                    'show_in_nav_menus'   => true,
                    'show_in_rest'        => true,
                    'show_in_menu'        => false,
                    'map_meta_cap'        => true,
                    'capability_type'     => 'post'
                )
            )
        );
    }

    /**
    * Add custom meta boxes to the giveaways post editor...
    * 
    * @version 1.0
    */
    public static function add_custom_boxes( $post_type ) {     

        // General giveaway options....
        add_meta_box(
            'twitchpress_post_giveaway_options', // Unique ID
            __( 'Giveaway Options', 'twitchpress' ),  
            array( __CLASS__, 'html_twitchpress_post_giveaway_options' ),
            'giveaways'
        );
        
        // Start timer....
        add_meta_box(
            'twitchpress_post_giveaway_starttimer', // Unique ID
            __( 'Start Timer', 'twitchpress' ),  
            array( __CLASS__, 'html_twitchpress_post_giveaway_start_timer' ),
            'giveaways'
        );        
        
        // End timer....
        add_meta_box(
            'twitchpress_post_giveaway_endtimer', // Unique ID
            __( 'End Timer', 'twitchpress' ),  
            array( __CLASS__, 'html_twitchpress_post_giveaway_end_timer' ),
            'giveaways'
        );
        
    }
    
    /**
    * Options for locking post content.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_twitchpress_post_giveaway_options( $post ) { ?>
        <label for="_twitchpress_post_giveaway_type"><?php _e( 'Type', 'twitchpress' ); ?></label>
        <select name="_twitchpress_post_giveaway_type" id="_twitchpress_post_giveaway_type" class="postbox">
            <option value="singleentryraffle"><?php _e( 'Single Entry Raffle', 'twitchpress' ); ?></option>
            <option value="multipleentryraffle" <?php selected( get_post_meta( $post->ID, '_twitchpress_post_giveaway_type', true ), 'multipleentryraffle'); ?>><?php _e( 'Multiple Entry Raffle', 'twitchpress' ); ?></option>
        </select>
        
        <br>
        
        <label for="_twitchpress_post_giveaway_twitch_status"><?php _e( 'Twitch Requirement', 'twitchpress' ); ?></label>
        <select name="_twitchpress_post_giveaway_twitch_status" id="_twitchpress_post_giveaway_twitch_status" class="postbox">
            <option value="none">None</option>
            <option value="follower" <?php selected(get_post_meta( $post->ID, '_twitchpress_post_giveaway_twitch_status', true ), 'follower'); ?>><?php _e( 'Twitch Follower', 'twitchpress' ); ?></option>
            <option value="subscriber" <?php selected(get_post_meta( $post->ID, '_twitchpress_post_giveaway_twitch_status', true ), 'subscriber'); ?>><?php _e( 'Twitch Subscriber', 'twitchpress' ); ?></option>
        </select>
        
        <br>
        
        <label for="_twitchpress_post_giveaway_closure_method"><?php _e( 'Closure Method', 'twitchpress' ); ?></label>
        <select name="_twitchpress_post_giveaway_closure_method" id="_twitchpress_post_giveaway_closure_method" class="postbox">
            <option value="timer">Timer (automatic closure)</option>
            <option value="ticketlimit" <?php selected(get_post_meta( $post->ID, '_twitchpress_post_giveaway_closure_method', true ), 'ticketlimit'); ?>><?php _e( 'Ticket Limit (automatic closure)', 'twitchpress' ); ?></option>
            <option value="manual" <?php selected(get_post_meta( $post->ID, '_twitchpress_post_giveaway_closure_method', true ), 'manual'); ?>><?php _e( 'Manual Closure (Maximum 32 Days)', 'twitchpress' ); ?></option>
        </select>
        
        <br>
                
        <label for="_twitchpress_post_giveaway_winner_selection"><?php _e( 'Winner Selection', 'twitchpress' ); ?></label>
        <select name="_twitchpress_post_giveaway_winner_selection" id="_twitchpress_post_giveaway_winner_selection" class="postbox">
            <option value="instant"><?php _e( 'Instant', 'twitchpress' ); ?></option>
            <option value="manual" <?php selected(get_post_meta( $post->ID, '_twitchpress_post_giveaway_winner_selection', true ), 'manual'); ?>><?php _e( 'Manual Randomizer', 'twitchpress' ); ?></option>
        </select>
        
        <?php
    }
    
    public static function html_twitchpress_post_giveaway_start_timer( $post ) {?>
        <label for="_twitchpress_post_giveaway_start"><?php _e( 'Start Timer', 'twitchpress' ); ?></label>
        <input name="_twitchpress_post_giveaway_start" id="_twitchpress_post_giveaway_start" class="postbox" type="text">
        <select name="_twitchpress_giveaway_timer_start_multiplier" id="_twitchpress_giveaway_timer_start_multiplier" class="postbox">
            <option value="hours"><?php _e( 'Hours', 'twitchpress' ); ?></option>
            <option value="minutes" <?php selected(get_post_meta( $post->ID, '_twitchpress_giveaway_timer_start_multiplier', true ), 'minutes'); ?>><?php _e( 'Minutes', 'twitchpress' ); ?></option>
            <option value="days" <?php selected(get_post_meta( $post->ID, '_twitchpress_giveaway_timer_start_multiplier', true ), 'days' ); ?>><?php _e( 'Days', 'twitchpress' ); ?></option>
        </select>
        <?php       
    }       
    
    public static function html_twitchpress_post_giveaway_end_timer( $post ) {?>
        <label for="_twitchpress_post_giveaway_end"><?php _e( 'End Timer', 'twitchpress' ); ?></label>
        <input name="_twitchpress_post_giveaway_end" id="_twitchpress_post_giveaway_end" class="postbox" type="text">
        <select name="_twitchpress_giveaway_multiplier_end" id="_twitchpress_giveaway_multiplier_end" class="postbox">
            <option value="hours"><?php _e( 'Hours', 'twitchpress' ); ?></option>
            <option value="minutes" <?php selected(get_post_meta( $post->ID, '_twitchpress_giveaway_multiplier_end', true ), 'minutes'); ?>><?php _e( 'Minutes', 'twitchpress' ); ?></option>
            <option value="days" <?php selected(get_post_meta( $post->ID, '_twitchpress_giveaway_multiplier_end', true ), 'days' ); ?>><?php _e( 'Days', 'twitchpress' ); ?></option>
        </select>
        <?php        
    }   
      
    public static function save_twitchpress_post_giveaway_options( $post_id ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
        if ( !current_user_can( 'edit_post', $post_id ) ) { return $post_id; }                        

        $expected_keys = array(
            '_twitchpress_post_giveaway_type',
            '_twitchpress_post_giveaway_twitch_status',
            '_twitchpress_post_giveaway_closure_method',
            '_twitchpress_post_giveaway_winner_selection',
            '_twitchpress_post_giveaway_start',
            '_twitchpress_post_giveaway_end'
        );
        
        foreach( $expected_keys as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                update_post_meta(
                    $post_id,
                    $key,
                    $_POST[$key]
                );
            }            
        }
    
        return $post_id;
    }    
      
    public static function save_twitchpress_post_giveaway_start_timer( $post_id ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
        if ( !current_user_can( 'edit_post', $post_id ) ) { return $post_id; }                        

        $expected_keys = array(
            '_twitchpress_post_giveaway_start',
            '_twitchpress_giveaway_timer_start_multiplier'
        );
        
        foreach( $expected_keys as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                update_post_meta(
                    $post_id,
                    $key,
                    $_POST[$key]
                );
            }            
        }
    
        return $post_id;
    }      
    public static function save_twitchpress_post_giveaway_end_timer( $post_id ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
        if ( !current_user_can( 'edit_post', $post_id ) ) { return $post_id; }                        

        $expected_keys = array(
            '_twitchpress_post_giveaway_end',
            '_twitchpress_giveaway_timer_end_multiplier'
        );
        
        foreach( $expected_keys as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                update_post_meta(
                    $post_id,
                    $key,
                    $_POST[$key]
                );
            }            
        }
    
        return $post_id;
    }
    
    /**
     * Register our custom post statuses, used for order status.
     * 
     * @version 1.0
     */
    public static function register_post_status() {
        $order_statuses = apply_filters( 'twitchpress_register_giveaways_post_statuses',
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
     * Allow twitchfeed posts in API controlled by JetPack.
     *
     * @param  array $post_types
     * @return array
     */
    public static function rest_api_allowed_post_types( $post_types ) {
        return $post_types;
    }
 
    
    /**
    * Display custom fields in the publish box. 
    * 
    * @version 1.0
    */
    public static function post_submitbox() {
        global $post;
        
        /*
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type($post);
     
        $twitch_share = get_option( 'twitchpress_shareable_posttype_' . $post_type );
        if ( $twitch_share == 'yes' ) { 
            echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
            wp_nonce_field( plugin_basename(__FILE__), 'nonce_twitchpress_share_post_option' );
            echo '<input type="checkbox" name="twitchpress_share_post_option" id="twitchpress_share_post_option" value="share" /> <label for="twitchpress_share_post_option">Share to Twitch Feed</label><br />';
            echo '</div>';
        }  
        */
    }   
}

endif;