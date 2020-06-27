<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
       
if ( ! class_exists( 'BugNet_Install_Database' ) ) :

class BugNet_Install_Database {
    
    var $installation_type = 'update';
    
    public $tables = array(
        'bugnet_issues',        /* time and ID and type (error, other) and user ID and visitor IP */
        'bugnet_issues_meta',   /* Issue ID + key + value: user login + visitor IP + slug (plugin or theme) + name (plugin or theme) + file + line + function */
        'bugnet_testers',       /* Tester ID + WP ID */ 
        'bugnet_testers_meta',  /* Tester ID + key + value */
        'bugnet_testers_tasks', /* Tasks ID + Tester ID */
        'bugnet_tasks',         /* Task ID + type + priority + progress + title + creation time */
        'bugnet_tasks_meta',    /* Task ID + key + value */
        'bugnet_api',           /* ID + API name (slug format) + API code + tech message (errors mainly) */          
        'bugnet_api_meta',      /* ID + key + value */
        'bugnet_wp_caches',     /* ID + cache key */
        'bugnet_reports',       /* Report ID + type + time */
        'bugnet_reports_meta',  /* ID + key + value */
        'bugnet_tracing',       /* Trace ID + name + start_code */ 
        'bugnet_tracing_meta',  /* Meta ID + Trace ID + Meta Key + Meta Value + Microtime */
        'bugnet_tracing_steps'   
    );
        
    public function __construct() {
        if ( ! defined( 'BUGNET_INSTALLING' ) ) {
            define( 'BUGNET_INSTALLING', true );
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
           case 'tracing':
                self::table_bugnet_tracing();
                self::table_bugnet_tracing_meta();
                self::table_bugnet_tracing_steps();
             break;
           case 'issues':
                 
             break;
           case 'reports':
                 
             break;
           case 'testers':
                 
             break;
           case 'tasks':
                 
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
        self::table_bugnet_issues();
        self::table_bugnet_issues_meta();
        self::table_bugnet_reports();
        self::table_bugnet_reports_meta();
        self::table_bugnet_wp_caches();
    }    

    /**
    * Individual issues of various types are inserted into this table first...
    * 
    * @version 1.0
    */
    static function table_bugnet_issues() {
        global $charset_collate, $wpdb;

        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_issues (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
type varchar(45) NOT NULL,
outcome tinyint(1) unsigned DEFAULT '0',
name varchar(45) NOT NULL,
title varchar(45) NOT NULL,
endpoint varchar(250) NOT NULL,
reason varchar(500) NOT NULL,
line int(5) unsigned DEFAULT '0',
function varchar(125) DEFAULT NULL,
file varchar(500) DEFAULT NULL,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) $charset_collate; ";
        dbDelta( $table );       
                
    }
    
    /**
    * Meta data for issues...
    * 
    * @version 1.0
    */
    static function table_bugnet_issues_meta() {
        global $charset_collate, $wpdb;  
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_issues_meta (
meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
issue_id bigint(20) unsigned DEFAULT 0,
meta_key varchar(50) DEFAULT NULL,
meta_value varchar(250) DEFAULT NULL,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (meta_id)
) $charset_collate; ";

        dbDelta( $table );  
    }
    
    /**
    * Report creation begins here giving each report a unique ID...
    * 
    * @version 1.0
    */
    static function table_bugnet_reports() {
        global $charset_collate, $wpdb;
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_reports (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
type varchar(45) DEFAULT NULL,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) $charset_collate; ";

        dbDelta( $table );    
    }
    
    /**
    * Meta data for reports...
    * 
    * @version 1.0
    */
    static function table_bugnet_reports_meta() {
        global $charset_collate, $wpdb;
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_reports_meta (
meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
report_id bigint(20) unsigned DEFAULT 0,
name varchar(250) DEFAULT NULL,
meta_key varchar (50) NOT NULL DEFAULT '0',
met_value varchar(500) DEFAULT NULL,
PRIMARY KEY (meta_id)
) $charset_collate; ";

        dbDelta( $table );    
    }
    
    /**
    * Caching directory for all other BugNet records of all types and used
    * when a record requires a dump of data...
    * 
    * @version 1.0
    */
    static function table_bugnet_wp_caches() {
        global $charset_collate, $wpdb;
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_wp_caches (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
item_id bigint(20) unsigned,
item_type varchar (50) NOT NULL,
wp_cache_key varchar(250) NOT NULL,
wp_cache_group varchar(250) DEFAULT NULL,
wp_cache_expiry DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (id)
) $charset_collate; ";
        dbDelta( $table );    
    }
    
    /**
    * Store a new trace...
    * 
    * @version 1.0
    */
    static function table_bugnet_tracing() {
        global $charset_collate, $wpdb;

        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_tracing (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
name varchar(250) NOT NULL,
line int(20) DEFAULT NULL,
function varchar(250) NOT NULL,
code varchar(125) DEFAULT NULL,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) $charset_collate; ";
        dbDelta( $table );       
                
    }                       
    
    /**
    * Add meta data to a trace...
    * 
    * @version 1.0
    */
    static function table_bugnet_tracing_meta() {
        global $charset_collate, $wpdb;  
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_tracing_meta (
meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
code varchar(125) DEFAULT NULL,
request varchar(50) DEFAULT NULL,
meta_key varchar(50) DEFAULT NULL,  
meta_value varchar(250) DEFAULT NULL,
PRIMARY KEY (meta_id)
) $charset_collate; ";

        dbDelta( $table );  
    }    
    
    /**
    * Add steps to a trace...
    * 
    * @version 1.0
    */
    static function table_bugnet_tracing_steps() {
        global $charset_collate, $wpdb;  
        
        $table = "CREATE TABLE " . $wpdb->prefix . "bugnet_tracing_steps (
step_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
code varchar(125) DEFAULT NULL,
request varchar(50) DEFAULT NULL,
description varchar(250) DEFAULT NULL,
microtime decimal(45,4) DEFAULT NULL,
line int(20) DEFAULT NULL,
function varchar(250) NOT NULL,
PRIMARY KEY (step_id)
) $charset_collate; ";

        dbDelta( $table );  
    }    
}                   
    
endif;