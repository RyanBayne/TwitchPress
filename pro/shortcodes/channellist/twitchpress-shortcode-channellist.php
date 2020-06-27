<?php
/**
 * TwitchPress Shortcode - Channel List
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Channel_List' ) ) :

class TwitchPress_Shortcode_Channel_List {
    
    var $atts = array( 'empty' );
    var $response= null;
    var $returned_channels = null;
    var $all_streams_obj = array(); 
    
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles'), 4 );   
        $this->register_styles();
        $this->get_twitch_data();
        $this->prepare_data(); // orderby, blacklist, priority channel positioning etc 
    }
    
    public function get_twitch_data() {
        $helix = new TwitchPress_Twitch_API();
        switch ( $this->atts['type'] ) {
           case 'team':
                $this->response = $helix->get_team( $this->atts['team'] );                
                $this->returned_channels = $this->response->users;
                unset( $helix );
             break;          
           case 'followers':

             break;
           case 'specific':
                
             break;
           default:
                $this->response = $helix->get_team( twitchpress_get_default_stream_team() ); 
                $this->returned_channels = $this->response->users;
                unset( $helix );
             break;             
        }  
        unset($helix);          
    }
    
    /**
    * Does not apply to raw responses. Each request for
    * Twitch.tv data requires a custom approach to extracting
    * the channel/user/stream data. 
    * 
    * @version 1.0
    */
    public function prepare_data() {
        // Order
        if( $this->atts['orderby'] ) {
            $this->returned_channels = wp_list_sort(
                $this->returned_channels,
                $this->atts['orderby'],
                'DESC',
                true
            );
        }
        
        // Maximum
        if( $this->atts['max'] ) {
            
        }      
    }
    
    public function register_scripts() {
  
    }  
    
    /**
    * Register styles for channel list shortcode. 
    * Constants currently set in core pending proper integration using API. 
    *   
    * @version 1.0
    */
    public function register_styles() {
        wp_enqueue_style( 'dashicons' );                                             
        wp_register_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PRO_DIR_URL . 'shortcodes/channellist/twitchpress-shortcode-channellist.css' );   
        wp_enqueue_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PRO_DIR_URL . 'shortcodes/channellist/twitchpress-shortcode-channellist.css' );
    }
    
    public function output() {
        switch ( $this->atts['style'] ) {
           case 'error':
                return $this->atts['error'];
             break; 
           case 'shutters':
                return $this->style_shutters();
             break;
           default:
                return $this->style_shutters();
             break;
        }    
    }
    
    public function style_shutters() {
        ob_start(); 
        
        $online = '';
        $offline = '';
        $closed = '';
        $articles = 0; /* number of html articles generated */

        $helix = new TwitchPress_Twitch_API();
        
        // Get all the user ID's for adding to a single API call...
        $user_id_array = array();
        
        // If no team members, the team name is probably incorrect...
        if( !$this->returned_channels ) {
            ?>
            
            <main>
                <section>No channels returned by Twitch.tv</section>   
                <section id="open"></section>
            </main>
               
            <?php 
            return ob_get_clean();
        }
        
        foreach( $this->returned_channels as $key => $user ) {  
            $user_id_array[] = $user->_id;   
        }
        
        $this->all_streams_obj = $helix->get_streams_by_userid( $user_id_array );

        unset( $helix );
        
        foreach( $this->returned_channels as $key => $user ) {   
            
            // If zero members only this object will be empty...khuu
            if( $this->all_streams_obj ) {
                // Does this user have an active stream...
                foreach( $this->all_streams_obj as $key => $stream ) {
                    if( $stream->user_id == $user->_id ) {
                        $stream_obj = $stream;
                        break;    
                    }
                }
            }
     
            // Build article HTML based on the output demanded i.e. online or offline only or all...
            if( isset( $stream_obj ) && $this->atts['display'] !== 'offline' ) {
                $thumbnail_url = str_replace( array( '{width}', '{height}'), array( '640', '360' ), $stream_obj->thumbnail_url );
                $online .= $this->shutter_article( $user, 'online', $stream_obj->viewer_count, $thumbnail_url );
            } elseif( $this->atts['display'] == 'all' || $this->atts['display'] == 'offline' ) {
                $offline .= $this->shutter_article( $user, 'offline', 0 );
            } 
               
            unset( $stream_obj );
        }         
        
        // Wrap articles in section html...
        $online_section = '<section id="online">' . $online . '</section>';
        $html_offline = '<section id="offline">' . $offline . '</section>'; 
        ?>
        
        <main>
            <?php 
            // All this is simply to avoid outputting empty section HTML...
            if( $this->atts['display'] == 'all' || $this->atts['display'] == 'online' ){ echo $online_section; } 
            if( $this->atts['display'] == 'all' || $this->atts['display'] == 'offline' ){ echo $html_offline; } 
            ?>
        </main>
           
        <?php  
        return ob_get_clean();
    }
    
    /**
    * HTML structure for a single channel (article)
    * 
    * @param mixed $user
    * @param mixed $status
    * @param mixed $viewer_count
    * @param mixed $preview
    * 
    * @version 2.0
    */
    static function shutter_article( $user, $status, $viewer_count = 0, $preview = '' ) {
        ob_start(); 
        ?>
            <article class="channel" id="<?php echo esc_attr( $user->name ); ?>">                                
            
                <a class="channel-link" href="<?php echo esc_url( $user->url ); ?>" target="_blank">                                    
                
                    <header class="channel-primary row">                                        
                        <div class="channel-logo col-s">
                            <img src="<?php echo esc_url( $user->logo ); ?>">
                        </div>                                        
                        <div class="col-lg">                                            
                            <div class="row">                                                
                                <h3 class="channel-name"><?php echo esc_attr( $user->display_name ); ?></h3>                                                
                                <div class="channel-curr-status"><?php echo esc_attr( $status ); ?></div>                                            
                            </div>                                            
                            <div class="channel-status row"><?php echo esc_attr( $user->status ); ?></div>                                        
                        </div>                                    
                    </header>
                    
                    <div class="stream-preview row">
                        <img src="<?php echo esc_attr( $preview ); ?>">
                    </div>
                    <div class="channel-details row">                                    
                        <ul class="channel-stats">                                        
                            <li><i class="dashicons dashicons-heart"></i><?php echo esc_attr( $user->followers ); ?></li>  
                            <li><i class="dashicons dashicons-visibility"></i><?php echo esc_attr( $user->views ); ?></li>                                    
                        </ul>
                        <div class="stream-details">                                    
                            <span class="stream-game"><?php echo esc_attr( $user->game ); ?></span>
                            <span class="stream-stats">
                            <i class="dashicons dashicons-admin-users"></i><?php echo esc_attr( $viewer_count ); ?></span>                                
                        </div>
                        <div class="more-btn">
                            <i class="fa fa-chevron-down"></i> 
                        </div>
                    </div>
                </a>
            </article>
        <?php        
        return ob_get_clean();   
    }

}

endif;
