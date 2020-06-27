<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
       
if ( ! class_exists( 'TwitchPress_Giveaways_Install_Database' ) ) :

class TwitchPress_Giveaways_Install_Database {
    
    var $installation_type = 'update';
    
    public $tables = array(
        'twitchpress_giveaways',
        'twitchpress_entrants',
        'twitchpress_entries'
    );
        
    public function __construct() {
        if ( ! defined( 'GIVEAWAYS_INSTALLING' ) ) {
            define( 'GIVEAWAYS_INSTALLING', true );
        }         
    }
    
    public function install() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Register primary tables...
        add_action( 'init', array( $this, 'primary_tables_registration' ) );
        add_action( 'switch_blog', array( $this, 'primary_tables_registration' ) );   

        // Install groups of tables or specific services tables...
        switch ( $this->installation_type ) {
           case 'activation':
                $this->activation();
             break;
           case 'update':
                 $this->update();
             break;
           default:
                $this->update();
             break;
        }
    }
    
    /**
    * Procedure ran when first-time or re-activation is happening. 
    * 
    * @param mixed $request_type
    */
    public function activation() {
        global $wpdb;
        
        $this->primary_tables(); 
    }  
    
    public function update() {
        global $wpdb;
        
        $this->primary_tables();
    }
    
    /**
    * Minimum tables for BugNet to operate in the recommended manner...
    * 
    * @version 1.0
    */
    static function primary_tables() {
        self::table_giveaways();
        self::table_giveaways_entrants();
        self::table_giveaways_entries();
    }    

    /**
    * Individual issues of various types are inserted into this table first...
    * 
    * @version 1.0
    */
    static function table_giveaways() {
        global $charset_collate, $wpdb;

        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_giveaways (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
type varchar(45) NOT NULL,
post_id bigint(20) unsigned NOT NULL,
status varchar(45) NOT NULL,
closure varchar(45) NOT NULL,
ticket_scheme varchar(45) NOT NULL DEFAULT 'single',
created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) $charset_collate; ";
        dbDelta( $table );               
    }

    /**
    * Giveaway Entrants
    * 
    * @version 1.0
    */
    static function table_giveaways_entrants() {
        global $charset_collate, $wpdb;  
        
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_entrants (
entrant_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
wp_user_id bigint(20) unsigned DEFAULT 0,
entered timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (entrant_id)
) $charset_collate; ";

        dbDelta( $table );  
    } 
                
    /**
    * Giveaway entry data
    * 
    * @version 1.0
    */
    static function table_giveaways_entries() {
        global $charset_collate, $wpdb;  
        
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_entries (
entry_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
entrant_id bigint(20) unsigned NOT NULL,
action_type varchar(45) NOT NULL,
tickets bigint(20) unsigned DEFAULT 1,
entered timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (entry_id)
) $charset_collate; ";

        dbDelta( $table );  
    }     
}                   
    
endif;