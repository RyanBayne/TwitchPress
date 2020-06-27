<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
       
if ( ! class_exists( 'BugNet_Install' ) ) :

class BugNet_Install {
    
    var $installation_type = 'update';
    
    public function __construct() {
        if ( ! defined( 'BUGNET_INSTALLING' ) ) {
            define( 'BUGNET_INSTALLING', true );
        }         
    }
    
    /**
    * First function to begin an installation and we can set our variables
    * before calling it...
    * 
    * @version 1.0
    */
    public function install() {
        require_once( 'class.bugnet-install-database.php' );
        
        switch ( $this->installation_type ) {
           case 'activation':
                return $this->activation();
             break;
           case 'update':
                return $this->update();
             break;
           case 'tracing':
                return $this->tracing();
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
                return $this->update();
             break;
        }        
    }
    
    /**
    * WordPress activation will also run updating methods because we cannot
    * assume it is a first-time activation but could be a re-activation...
    * 
    * The difference between activation() and update() is that the update procedure
    * within this class will only perform smaller changes to an assumed prior
    * installation...
    * 
    * @version 1.0
    */
    public function activation() {
        self::database_activation();    
    }
    
    /**
    * Procedurally apply specific changes without running through an entire
    * installation. This allows user output notices to make more sense too...
    * 
    * @version 1.0 
    */
    public function update() {
        self::database_update();    
    }    
    
    public function tracing() {
        self::database_tracing();    
    }
    
    static function database_activation() {
        $database = new BugNet_Install_Database();
        $database->installation_type = 'activation';
        $database->install();
    }
        
    static function database_update() {
        $database = new BugNet_Install_Database();
        $database->installation_type = 'update';
        $database->install();
    }
    
    static function database_tracing() {
        $database = new BugNet_Install_Database();
        $database->installation_type = 'tracing';
        $database->install();
    }
    
    public function outcome() {
        return array();
    }
    
}

endif;