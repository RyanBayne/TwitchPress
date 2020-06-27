<?php
/**
 * TwitchPress Clips Gallery  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Clips_Gallery' ) ) :

class TwitchPress_Shortcode_Clips_Gallery {
    
    var $atts = null;
    var $clips_response = null;
    
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles'), 4 );
        $this->register_styles();
        $this->get_twitch_data(); 
    }
    
    public function get_twitch_data() {
        switch ( $this->atts['type'] ) {
           case 'default':
                $helix = new TwitchPress_Twitch_API();
                $this->clips_response = $helix->get_clips( $this->atts['channel_id'], null, null, null, null, null, null, null );
                unset( $helix );
             break;
           case 'latest':/* as in latest stream */
                $helix = new TwitchPress_Twitch_API();                                                 
                $this->clips_response = $helix->get_clips( $this->atts['channel_id'], null,  null, null, null, null, null, null );
                unset( $helix );
             break;
           default:
                
             break;
        }            
    }
    
    public function register_scripts() {
        
    }
    
    public function register_styles() {
        wp_register_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PLUGIN_URL . '/pro/shortcodes/clipsgallery/twitchpress-shortcode-channellist.css' );   
        wp_enqueue_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PLUGIN_URL . '/pro/shortcodes/clipsgallery/twitchpress-shortcode-channellist.css' );
    }
    
    public function output() {
        switch ( $this->atts['style'] ) {
           case 'basic':
                return $this->style_basic();
             break;
           default:
                return $this->style_basic();
             break;
        }    
    }            

    public function style_basic() {
        ob_start(); 
        
        $online = '';
        $offline = '';
        $closed = '';
        ?>

        <ul class="wp-block-gallery columns-3 is-cropped">
        
        <?php 
        foreach( $this->clips_response->data as $key => $clip ) {    
        ?>
            <li class="blocks-gallery-item">
                <figure>
                    <img src="<?php echo $clip->thumbnail_url; ?>" alt="<?php echo $clip->title; ?>" data-link="<?php echo $clip->url; ?>" class="wp-image"/>
                </figure>
            </li>

        <?php 
        }
        ?>
        
        </ul>
    
        <?php 
        return ob_get_clean();
    }         
}

endif;