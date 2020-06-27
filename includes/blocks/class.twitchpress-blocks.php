<?php
/**
 * Gutenberg editor custom block categories. 
 *
 * @version  1.0
 * @package  TwitchPress
 * @category Class
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Blocks_Core' ) ) :

class TwitchPress_Blocks_Core {
    
    var $js_path = 'includes/blocks/scripts/'; 
    var $css_path = 'includes/blocks/css/'; 
    
    public function init() {

        // Register custom block categories...
        add_filter( 'block_categories', array( $this, 'block_categories' ), 10, 2 );
        
        // Register the blocks...
        add_action( 'init', array( $this, 'register_blocks' ) );     
    }
    
    public function block_categories( $categories ) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug' => 'twitchpress_block_category',
                    'title' => __( 'TwitchPress', 'twitchpress' ),
                    'icon'  => 'wordpress',
                ),
            )
        );    
    }

    /**
    * First function to work with when creating a new block in TwitchPress. 
    * 
    * @version 1.0
    */
    public function blocks_array() {
        
        $blocks_array = array(         
            
            // Twitch.tv Embed Everything 
            
            array(
                'name'      => 'twitchpress-block/embed-everything', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-embed-everything', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'embed-everything.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-embed-everything' ),
            ),
        
            // Twitch.tv Members Only
            array(
                'name'      => 'twitchpress-block/twitch-members-only', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-twitch-members-only', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-members-only.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-twitch-members-only' ),
            ),
            
            // Twitch.tv Display Bits Leaderboard: https://dev.twitch.tv/docs/api/reference/#get-bits-leaderboard
            array(
                'name'      => 'twitchpress-block/twitch-bits-leaderboard', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-twitch-bits-leaderboard', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-bits-leaderboard.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-bits-leaderboard' ),
            ),
                        
            // Twitch.tv Display Top Games List: https://dev.twitch.tv/docs/api/reference/#get-top-games
            array(
                'name'      => 'twitchpress-block/twitch-top-games-list', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-top-games-list', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-top-games-list.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-top-games-list' ),
            ),
            
            // Show Single Video - get the latest video: https://dev.twitch.tv/docs/api/reference/#get-videos
            array(
                'name'      => 'twitchpress-block/twitch-display-single-video', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-display-single-video', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-display-single-video.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-display-single-video' ),
            ),                   
            
            // Display Video Range - get a range of recent videos for gallery output
            // https://dev.twitch.tv/docs/api/reference/#get-videos
            array(
                'name'      => 'twitchpress-block/twitch-display-videos', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-display-videos', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-display-videos.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-display-videos' ),
            ),                    
            
            // Display Chat for Main Channel ID: https://dev.twitch.tv/docs/embed/chat/    
            array(
                'name'      => 'twitchpress-block/twitch-main-channel-chat', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-main-channel-chat', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-main-channel-chat.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-main-channel-chat' ),
            ),                    
            
            // Display Chat for entered channel ID: https://dev.twitch.tv/docs/embed/chat/
            array(
                'name'      => 'twitchpress-block/twitch-giving-channel-chat', // register_block_type() -> name
                'handle_js' => 'twitchpress-block-giving-channel-chat', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-giving-channel-chat.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-giving-channel-chat' ),
            ),                    
            
            // None Interactive Live Stream Video: https://dev.twitch.tv/docs/embed/video-and-clips/#non-interactive-inline-frames-for-live-streams-and-vods
            array(
                'name'      => 'twitchpress-block/twitch-live-vid-none-interactive', // register_block_type() -> name            
                'handle_js' => 'twitchpress-block-live-vid-none-interactive', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-live-vid-none-interactive.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-live-vid-none-interactive' ),
            ),                    
            
            // Interactive Live Stream Video: https://dev.twitch.tv/docs/embed/video-and-clips/#interactive-frames-for-live-streams-and-vods
            array(
                'name'      => 'twitchpress-block/twitch-live-vid-interactive', // register_block_type() -> name            
                'handle_js' => 'twitchpress-block-live-vid-interactive', // wp_register_script() -> handle
                'source_js' => plugins_url( $this->js_path . 'twitch-live-vid-interactive.js', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-blocks.php' ), // js filename, full path is built in register_blocks()
                'deps_js'   => array( 'wp-blocks', 'wp-element' ), // wp_register_script() -> deps (dependancies),
                'args'      => array( 'editor_script' => 'twitchpress-block-live-vid-interactive' ),
            )            
        );

        return apply_filters( 'twitchpress_blocks', $blocks_array ); 
    }
    
    /**
    * Loops through blocks_array() and registers the block script and block-type.
    * 
    * @version 1.0
    */
    public function register_blocks() {
        foreach( $this->blocks_array() as $key => $block ) {
            wp_register_script(
                $block['handle_js'],
                $block['source_js'],
                $block['deps_js']
            );
            
            if( isset( $block['source_css'] ) ) {
                wp_enqueue_style(
                    $block['handle_css'],
                    $block['source_css'],
                    $block['deps_css']
                );                
            }

            register_block_type( $block['name'], $block['args'] );
        }        
    }
}

endif;
