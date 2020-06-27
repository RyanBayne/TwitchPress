<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Display Permanent Notice
 *
 * Displays a permanent notice which is updated with new information
 * when it is available. This is mainly a developer tool for constant monitoring
 * while testing.
 *
 * @author   Ryan Bayne
 * @category Notices
 * @package  BugNet/Notices
 * @since    1.0
 */
class BugNet_Notices_AdministratorPermanent {
    
    public function __construct() {
        //add_action( 'admin_notices', array( $this, 'trace_boxes' ) );          
        //add_action( 'admin_notices', array( $this, 'info_boxes' ) );    
        //add_action( 'admin_notices', array( $this, 'report_boxes' ) );    
    }
    
    /**
    * Box for presenting the status and progress of a delayed trace to let
    * administrators/developers know when a trace will be complete.
    * 
    * @version 1.0
    * 
    * @todo Function is incomplete.
    */    
    public function trace_boxes( $title, $intro, $progress_array = array() ){    
        
        // Only do if tracing service is active.
        if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
        
        // TODO: create a current user meta option for displaying the trace box
        echo '
        <div class="bugnet_status_box_container">
            <div class="welcome-panel">
            
                <h3>' . ucfirst( $title ) . '</h3>
                
                <div class="welcome-panel-content">
                    <p class="about-description">' . ucfirst( $intro ) . '...</p>
                    
                    <h4>Section Development Progress</h4>

                    ' . self::info_boxes( '', '                    
                    Free Edition: <progress max="100" value="24"></progress> <br>
                    Premium Edition: <progress max="100" value="36"></progress> <br>
                    Support Content: <progress max="100" value="67"></progress> <br>
                    Translation: <progress max="100" value="87"></progress>' ) .'
                    <p>' . __( 'A reminder can go here.' ) . '</p>                                                     
                </div>

            </div> 
        </div>';  
    }
    
    /**
    * Present detailed information at the head of a page.   
    * 
    * @version 1.0
    * 
    * @todo Function is incomplete.
    */
    public function report_boxes( $title, $intro, $info_area = false, $info_area_title = '', $info_area_content = '', $footer = false, $dismissable_id = false ){

        global $current_user;
                                               
        // handling user action - hide notice and update user meta
        if ( isset($_GET[ $dismissable_id ]) && 'dismiss' == $_GET[ $dismissable_id ] ) {
            add_user_meta( $current_user->ID, $dismissable_id, 'true', true );
            return;
        }
               
        // a dismissed intro
        if ( $dismissable_id !== false && get_user_meta( $current_user->ID, $dismissable_id ) ) {
            return;
        }
                              
        // highlighted area within the larger box
        $highlighted_info = '';
        if( $info_area == true && is_string( $info_area_title ) ) {
            $highlighted_info = '<h4>' . $info_area_title . '</h4>';
            $highlighted_info .= self::info_area( false, $info_area_content );
        }
                      
        // footer text within the larger box, smaller text, good for contact info or a link
        $footer_text = '<br>';
        if( $footer ) {
            $footer_text = '<p>' . $footer . '</p>';    
        }
        
        // add dismiss button
        $dismissable_button = '';
        if( $dismissable_id !== false ) {
            $dismissable_button = sprintf( 
                ' <a href="%s&%s=dismiss" class="button button-primary"> ' . __( 'Hide', 'twitchpress' ) . ' </a>', 
                $_SERVER['REQUEST_URI'], 
                $dismissable_id 
            );
        }
                
        echo '
        <div class="bugnet_status_box_container">
            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    
                    <h1>' . ucfirst( $title ) . $dismissable_button . '</h1>
                    
                    <p class="about-description">' . ucfirst( $intro ) . '</p>
 
                    ' . $highlighted_info . '
                    
                    ' . $footer_text . '
                  
                </div>
            </div> 
        </div>';  
    } 

    /**
    * A mid grey area for attracting focus to a small amount of information. Is 
    * an alternative to standard WordPress notices and good for tips. 
    * 
    * @version 1.0
    * 
    * @todo Move style to .css file.
    */
    public function info_boxes( $title, $message, $admin_only = true ){   
        if( $admin_only == true && current_user_can( 'manage_options' ) || $admin_only !== true){
            
            $area_title = '';
            if( $title ){
                $area_title = '<h4>' . $title . '</h4>';
            }
            
            return '
            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                ' . $area_title  . '
                <p>' . $message . '</p>
            </div>';          
        }
    }              
}