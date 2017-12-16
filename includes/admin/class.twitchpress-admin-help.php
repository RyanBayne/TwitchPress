<?php
/**
 * Add the default content to the help tab.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.0
 */
          
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

if ( ! class_exists( 'TwitchPress_Admin_Help', false ) ) :

/**
 * TwitchPress_Admin_Help Class.
 */
class TwitchPress_Admin_Help {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
    }

    /**
     * Add Contextual help tabs.
     * 
     * @version 1.0
     */
    public function add_tabs() {
        $screen = get_current_screen();
                                       
        if ( ! $screen || ! in_array( $screen->id, twitchpress_get_screen_ids() ) ) {
            return;
        }
        
        $page = empty( $_GET['page'] ) ? '' : sanitize_title( $_GET['page'] );
        $tab  = empty( $_GET['tab'] )  ? '' : sanitize_title( $_GET['tab'] );

        /**
        * This is the right side sidebar, usually displaying a list of links. 
        * 
        * @var {WP_Screen|WP_Screen}
        */
        $screen->set_help_sidebar(
            '<p><strong>' . __( 'For more information:', 'twitchpress' ) . '</strong></p>' .
            '<p><a href="https://github.com/ryanbayne/twitchpress/wiki" target="_blank">' . __( 'About TwitchPress', 'twitchpress' ) . '</a></p>' .
            '<p><a href="https://github.com/ryanbayne/twitchpress" target="_blank">' . __( 'Github project', 'twitchpress' ) . '</a></p>' .
            '<p><a href="https://github.com/ryanbayne/twitchpress/blob/master/CHANGELOG.txt" target="_blank">' . __( 'Change Log', 'twitchpress' ) . '</a></p>' .
            '<p><a href="https://twitchpress.wordpress.com" target="_blank">' . __( 'Blog', 'twitchpress' ) . '</a></p>'
        );
                
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_support_tab',
            'title'     => __( 'Help &amp; Support', 'twitchpress' ),
            'content'   => '<h2>' . __( 'Help &amp; Support', 'twitchpress' ) . '</h2>' . 
            '<p><a href="https://join.skype.com/bVtDaGHd9Nnl/" class="button button-primary">' . __( 'Skype', 'twitchpress' ) . '</a> <a href="https://twitchpress.slack.com/" class="button button-primary">' . __( 'Slack', 'twitchpress' ) . '</a> <a href="https://trello.com/b/PEkkYDAJ/twitchpress" class="button button-primary">' . __( 'Trello', 'twitchpress' ) . '</a> <a href="https://github.com/RyanBayne/twitchpress/issues" class="button button-primary">' . __( 'Bugs', 'twitchpress' ) . '</a> </p>' . 
            '<h2>' . __( 'Pointers Tutorial', 'twitchpress' ) . '</h2>' .
            '<p>' . __( 'The plugin will explain some features using WordPress pointers.', 'twitchpress' ) . '</p>' .
            '<p><a href="' . admin_url( 'admin.php?page=twitchpress&amp;twitchpresstutorial=normal' ) . '" class="button button-primary">' . __( 'Start Tutorial', 'twitchpress' ) . '</a></p>' .
            '<h2>' . __( 'Report A Bug', 'twitchpress' ) . '</h2>' .
            '<p>You could save a lot of people a lot of time by reporting issues. Tell the developers and community what has gone wrong by creating a ticket. Please explain what you were doing, what you expected from your actions and what actually happened. Screenshots and short videos are often a big help as the evidence saves us time, we will give you cookies in return.</p>' .  
            '<p><a href="' . TWITCHPRESS_GITHUB . '/issues?state=open' . '" class="button button-primary">' . __( 'Report a bug', 'twitchpress' ) . '</a></p>',
        ) );
        
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_wizard_tab',
            'title'     => __( 'Setup wizard', 'twitchpress' ),
            'content'   =>
                '<h2>' . __( 'Setup wizard', 'twitchpress' ) . '</h2>' .
                '<p>' . __( 'If you need to access the setup wizard again, please click on the button below.', 'twitchpress' ) . '</p>' .
                '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'twitchpress' ) . '</a></p>',
        ) );   
  
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_contribute_tab',
            'title'     => __( 'Contribute', 'twitchpress' ),
            'content'   => '<h2>' . __( 'Everyone Can Contribute', 'twitchpress' ) . '</h2>' .
            '<p>' . __( 'You can contribute in many ways and by doing so you will help the project thrive.' ) . '</p>' .
            '<p><a href="' . TWITCHPRESS_DONATE . '" class="button button-primary">' . __( 'Donate', 'twitchpress' ) . '</a> <a href="' . TWITCHPRESS_GITHUB . '/wiki" class="button button-primary">' . __( 'Update Wiki', 'twitchpress' ) . '</a> <a href="' . TWITCHPRESS_GITHUB . '/issues" class="button button-primary">' . __( 'Fix Bugs', 'twitchpress' ) . '</a></p>',
        ) );

        $screen->add_help_tab( array(
            'id'        => 'twitchpress_newsletter_tab',
            'title'     => __( 'Newsletter', 'twitchpress' ),
            'content'   => '<h2>' . __( 'Annual Newsletter', 'twitchpress' ) . '</h2>' .
            '<p>' . __( 'Mailchip is used to manage the projects newsletter subscribers list.' ) . '</p>' .
            '<p>' . '<!-- Begin MailChimp Signup Form -->
                <link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
                <style type="text/css">         
                    #mc_embed_signup{background:#f6fbfd; clear:left; font:14px Helvetica,Arial,sans-serif; }
                    /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                       We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
                </style>
                <div id="mc_embed_signup">
                <form action="//webtechglobal.us9.list-manage.com/subscribe/post?u=99272fe1772de14ff2be02fe6&amp;id=b9058458e5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div id="mc_embed_signup_scroll">
                    <h2>TwitchPress Annual Newsletter</h2>
                <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                <div class="mc-field-group">
                    <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
                </label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                <div class="mc-field-group">
                    <label for="mce-FNAME">First Name </label>
                    <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                </div>
                <div class="mc-field-group">
                    <label for="mce-LNAME">Last Name </label>
                    <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                </div>
                <p>Powered by <a href="http://eepurl.com/2W_2n" title="MailChimp - email marketing made easy and fun">MailChimp</a></p>
                    <div id="mce-responses" class="clear">
                        <div class="response" id="mce-error-response" style="display:none"></div>
                        <div class="response" id="mce-success-response" style="display:none"></div>
                    </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_99272fe1772de14ff2be02fe6_b9058458e5" tabindex="-1" value=""></div>
                    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
                </div>
                <script type=\'text/javascript\' src=\'//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js\'></script><script type=\'text/javascript\'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]=\'EMAIL\';ftypes[0]=\'email\';fnames[1]=\'FNAME\';ftypes[1]=\'text\';fnames[2]=\'LNAME\';ftypes[2]=\'text\';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                <!--End mc_embed_signup-->' . '</p>',
        ) );
        
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_credits_tab',
            'title'     => __( 'Credits', 'twitchpress' ),
            'content'   => '<h2>' . __( 'Credits', 'twitchpress' ) . '</h2>' .
            '<p>Please do not remove credits from the plugin. You may edit them or give credit somewhere else in your project.</p>' . 
            '<h4>' . __( 'Automattic - This plugins core is largely based on their WooCommerce plugin.' ) . '</h4>' .
            '<h4>' . __( 'Brian at WPMUDEV - our discussion led to this project and entirely new approach in my development.' ) . '</h4>' . 
            '<h4>' . __( 'Ignacio Cruz at WPMUDEV - has giving us a good approach to handling shortcodes.' ) . '</h4>' .
            '<h4>' . __( 'Ashley Rich (A5shleyRich) - author of a crucial piece of the puzzle, related to asynchronous background tasks.' ) . '</h4>' .
            '<h4>' . __( 'Igor Vaynberg - thank you for an elegant solution to searching within a menu.' ) . '</h4>',
            '<h4>' . __( 'Nookyyy - a constant supporter who is building Nookyyy.com using TwitchPress.' ) . '</h4>'
        ) );
                    
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_faq_tab',
            'title'     => __( 'FAQ', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'faq' ),
        ) );
        
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_status_tab',
            'title'     => __( 'Status', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'status' ),
        ) );
              
    }
    
    public function faq() {
        $questions = array(
            0 => __( '-- Select a question --', 'appointments' ),
            1 => __( "Can I create my own extensions?", 'appointments' ),
            2 => __( "How much would it cost for a custom extension?", 'appointments' ),
        );  
        
        ?>

        <style>
            .faq-answers li {
                background:white;
                padding:10px 20px;
                border:1px solid #cacaca;
            }
        </style>

        <p>
            <ul id="faq-index">
                <?php foreach ( $questions as $question_index => $question ): ?>
                    <li data-answer="<?php echo $question_index; ?>"><a href="#q<?php echo $question_index; ?>"><?php echo $question; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </p>
        
        <ul class="faq-answers">
            <li class="faq-answer" id='q1'>
                <?php _e('Yes, if you have experience with PHP and WordPress you can create an extension for TwitchPress. You can submit your extension to the WordPress.org repository for the community to use or keep it private or sell it as a premium extension. Please invite me to the projects GitHub for support.', 'appointments');?>
            </li>
            <li class="faq-answer" id='q2'>
                <p> <?php _e('You can hire me to create a new extension from as little as $30.00 and if you make the extension available to the WordPress community I will charge 50% less. I will also put from free hours into improving it which I cannot do if you request a private extension.', 'appointments');?> </p>
            </li>        
        </ul>
             
        <script>
            jQuery( document).ready( function( $ ) {
                var selectedQuestion = '';

                function selectQuestion() {
                    var q = $( '#' + $(this).val() );
                    if ( selectedQuestion.length ) {
                        selectedQuestion.hide();
                    }
                    q.show();
                    selectedQuestion = q;
                }

                var faqAnswers = $('.faq-answer');
                var faqIndex = $('#faq-index');
                faqAnswers.hide();
                faqIndex.hide();

                var indexSelector = $('<select/>')
                    .attr( 'id', 'question-selector' )
                    .addClass( 'widefat' );
                var questions = faqIndex.find( 'li' );
                var advancedGroup = false;
                questions.each( function () {
                    var self = $(this);
                    var answer = self.data('answer');
                    var text = self.text();
                    var option;

                    if ( answer === 39 ) {
                        advancedGroup = $( '<optgroup />' )
                            .attr( 'label', "<?php _e( 'Advanced: This part of FAQ requires some knowledge about HTML, PHP and/or WordPress coding.', 'appointments' ); ?>" );

                        indexSelector.append( advancedGroup );
                    }

                    if ( answer !== '' && text !== '' ) {
                        option = $( '<option/>' )
                            .val( 'q' + answer )
                            .text( text );
                        if ( advancedGroup ) {
                            advancedGroup.append( option );
                        }
                        else {
                            indexSelector.append( option );
                        }

                    }

                });

                faqIndex.after( indexSelector );
                indexSelector.before(
                    $('<label />')
                        .attr( 'for', 'question-selector' )
                        .text( "<?php _e( 'Select a question', 'appointments' ); ?>" )
                        .addClass( 'screen-reader-text' )
                );

                indexSelector.change( selectQuestion );
            });
        </script>        

        <?php 
    }
    
    /**
    * Displays Twitch application status. 
    * 
    * This focuses on the services main Twitch application credentials only.
    * 
    * @author Ryan Bayne
    * @version 2.2
    */
    public function status() {
        $overall_result = true;
        $channel_display_name = __( 'Not Found', 'twitchpress' );
        $channel_status = __( 'Not Found', 'twitchpress' );
        $channel_game = __( 'Not Found', 'twitchpress' );
        $current_user_id = get_current_user_id();
        
        // Check for existing cache.
        $cache = get_transient( 'twitchpresshelptabstatus' );
        if( $cache ) {
            print $cache;
            _e( '<p>You are viewing cached results which could be up to 60 seconds old. Refresh one minute after any changes you make to get the real status.</p>', 'twitchpress' ); 
            return;
        }
        
        // No existing cache found, so test Kraken, generate output, cache output, output output!
        $output = __( '<p>You are viewing real-time data on this request (not cached). The data will be cached for 60 seconds.</p>', 'twitchpress' );  

        $kraken = new TWITCHPRESS_Kraken_Calls();

        // Test Top Game 
        $output .= '<h2>' . __( 'Test: Get Top Game', 'twitchpress' ) . '</h2>';
        $channel = $kraken->get_top_games();
        $output .= $channel['top'][0]['game']['name'];        
        
        // Test Check Users Token
        $output .= '<h2>' . __( 'Test: Get Current Users Token', 'twitchpress' ) . '</h2>';
        $token_result = $kraken->establish_user_token( __FUNCTION__, $current_user_id );
        if( $token_result ){$output .= __( 'Result: User has a token!' ); }
        else{ $output .= __( 'Result: User does not have a token!' ); $overall_result = false; }

        // Test Check Users Token
        $output .= '<h2>' . __( 'Test: Validate Current Users Token', 'twitchpress' ) . '</h2>';
        $token_result = $kraken->check_user_token( $current_user_id );
        if( isset( $token_result['token'] ) && isset( $token_result['scopes'] ) && isset( $token_result['name'] ) ) {
            $output .= __( 'Result: User token is valid. ' );    
        }else{$output .= __( 'Result: Users token does not appear to be valid. ' ); $overall_result = false; }
 
        // Test Get Users Channel
        $output .=  '<h2>Test: Get Current Users Channel</h2>';
        $current_user_token = twitchpress_get_user_token( $current_user_id );
        $channel = $kraken->get_tokens_channel( $current_user_token );

        if( !isset( $channel['display_name'] ) || !$channel['display_name'] ) 
        {
            $output .= __( 'Could not get channel because: ', 'twitchpress' );
            $overall_result = false;    
        }
        elseif( is_numeric( $channel['status'] ) )
        {
            $output .= '<h3>' . __( 'Result: Error ', 'twitchpress' ) . $channel['status'] . '</h3>'; 
            $output .= kraken_httpstatuses( $channel['status'], 'wiki' ); 
            $overall_result = false;   
        } 
        else 
        { 
            if( isset( $channel['display_name'] ) ) { $channel_display_name = $channel['display_name']; }
            if( isset( $channel['status'] ) ) { $channel_status = $channel['status']; }
            if( isset( $channel['game'] ) ) { $channel_game = $channel['game']; }
            
            $output .= '<ul>';
            $output .= '<li><strong>Display Name: </strong>' . $channel_display_name . '</li>';
            $output .= '<li><strong>Status: </strong>' . $channel_status . '</li>';
            $output .= '<li><strong>Game: </strong>' . $channel_game . '</li>';
            $output .= '</ul>';
        }
        
        // Test Check Application Token
        $output .= '<h2>' . __( 'Test: Check Application Token', 'twitchpress' ) . '</h2>';
        $app_token_result = $kraken->check_application_token();
        if( isset( $app_token_result['token']['authorization']['scopes'] ) ) {
            $output .= __( 'Result: Application token is valid.' );    
        } else {
            $output .= __( 'Result: Application token is not valid.' );  
            $overall_result = false;  
        }
        
        if( !$overall_result ) {
            $output .= '<h3>' . __( 'Overall Result: Not Ready!', 'twitchpress' ) . '</h3>';
        } else {
            $output .= '<h3>' . __( 'Overall Result: Ready!', 'twitchpress' ) . '</h3>';            
        }
        
        // Avoid making these requests for every admin page request. 
        set_transient( 'twitchpresshelptabstatus', $output, 60 );
        
        print $output;       
    }
}

endif;

return new TwitchPress_Admin_Help();
