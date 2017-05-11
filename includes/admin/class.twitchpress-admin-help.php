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
    exit; // Exit if accessed directly
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
     */
    public function add_tabs() {
        $screen = get_current_screen();
                                       
        if ( ! $screen || ! in_array( $screen->id, twitchpress_get_screen_ids() ) ) {
            return;
        }
        
        $page      = empty( $_GET['page'] ) ? '' : sanitize_title( $_GET['page'] );
        $tab       = empty( $_GET['tab'] ) ? '' : sanitize_title( $_GET['tab'] );

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
            '<p><a href="https://pluginseed.wordpress.com" target="_blank">' . __( 'Blog', 'twitchpress' ) . '</a></p>'
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
                <form action="//webtechglobal.us9.list-manage.com/subscribe/post?u=99272fe1772de14ff2be02fe6&amp;id=570668cac5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
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
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_99272fe1772de14ff2be02fe6_570668cac5" tabindex="-1" value=""></div>
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
            '<h4>' . __( 'Automattic - they created the best way to create plugins so we can all get more from WP.' ) . '</h4>' .
            '<h4>' . __( 'Brian at WPMUDEV - our discussion led to this project and entirely new approach in my development.' ) . '</h4>' . 
            '<h4>' . __( 'Ignacio Cruz at WPMUDEV - has giving us a good approach to handling shortcodes.' ) . '</h4>' .
            '<h4>' . __( 'Ashley Rich (A5shleyRich) - author of a crucial piece of the puzzle, related to asynchronous background tasks.' ) . '</h4>' .
            '<h4>' . __( 'Igor Vaynberg - thank you for an elegant solution to searching within a menu.' ) . '</h4>'
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
            1 => __( "How can I restart the tutorial?", 'appointments' ),
            2 => __( "What is the importance of Time Base and how should I set it?", 'appointments' ),
            3 => __( "I don't see the time base that I need. For example I need 240 minutes appointments. How can I do that?", 'appointments' ),
            4 => __( "What is the complete process for an appointment?", 'appointments' ),
            5 => __( "Is it necessary to have at least one service?", 'appointments' )
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
                <?php _e('To restart tutorial about settings click here:', 'appointments');?>
                <?php
                $link = add_query_arg( array( "tutorial"=>"restart1" ), admin_url("admin.php?page=app_settings") );
                ?>
                <a href="<?php echo $link ?>" ><?php _e( 'Settings Tutorial Restart', 'appointments' ) ?></a>

                <?php _e('To restart tutorial about entering and editing Appointments click here:', 'appointments');?>
                <?php
                $link = add_query_arg( array( "tutorial"=>"restart2" ), admin_url("admin.php?page=app_settings") );
                ?>
                <a href="<?php echo $link ?>" ><?php _e( 'Appointments Creation and Editing Tutorial Restart', 'appointments' ) ?></a>
            </li>
            <li class="faq-answer" id='q2'>
                <p> <?php _e('<i>Time Base</i> is the most important parameter of Appointments+. It is the minimum time that you can select for your appointments. If you set it too high then you may not be possible to optimize your appointments. If you set it too low, your schedule will be too crowded and you may have difficulty in managing your appointments. You should enter here the duration of the shortest service you are providing. Please also note that service durations can only be multiples of the time base. So if you need 30 and 45 minutes services, you should select 15 minutes as the time base.', 'appointments');?> </p>
            </li>

            <li class="faq-answer" id='q3'>
                <p> <?php _e('You can add one more time base using <i>Additional time base</i> setting. You must select this setting in <i>time base</i> setting to be effective.', 'appointments');?> </p>
            </li>

            <li class="faq-answer" id='q4'>
                <p><?php _e('With the widest settings, client will do the followings on the front page:', 'appointments');?></p>
                <p> <?php _e('Select a service', 'appointments');?> </p>
                <p> <?php _e('Select a service provider', 'appointments');?> </p>
                <p> <?php _e('Select a free time on the schedule', 'appointments');?> </p>
                <p> <?php _e('Login (if required)', 'appointments');?> </p>
                <p> <?php _e('Enter the required fields (name, email, phone, address, city) and confirm the selected appointment', 'appointments');?> </p>
                <p> <?php _e('Click Paypal payment button (if required)', 'appointments');?> </p>
                <p> <?php _e('Redirected to a Thank You page after Paypal payment', 'appointments');?> </p>
            </li>
            <li class="faq-answer" id='q5'>
                <p> <?php _e('Yes. Appointments+ requires at least one service to be defined. Please note that a default service should have been already installed during installation. If you delete it, and no other service remains, then you will get a warning message. In this case plugin may not function properly.', 'appointments');?> </p>
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
    * @version 1.0
    */
    public function status() {
        // Check for existing cache.
        $cache = get_transient( 'twitchpresshelptabstatus' );
        if( $cache ) {
            print $cache;
            _e( 'You are viewing cached results which could be up to 60 seconds old. Refresh one minute after any changes you make to get the real status.', 'twitchpress' ); 
            return;
        }
        
        // No existing cache found, so test Kraken, generate output, cache output, output output!
        $output = '';
        $kraken = new TWITCHPRESS_Kraken5_Calls();
        
        // Token
        $token = $kraken->checkToken();
        $output .= '<h2>' . __( 'Existing Token Check', 'twitchpress' ) . '</h2>';
        if( is_string( $token['token'] ) ) {
            $output .= __( 'The existing token passed and is ready.', 'twitchpress' );        
        } elseif( $token['token'] === false ) {
            $output .= __( 'The existing token was rejected. TwitchPress will now request a new token.', 'twitchpress' );
            $new_token = $kraken->generateToken();
        }
        
        // Get authenticated channel object. 
        $output .= '<h2>' . __( 'Get Main Channel', 'twitchpress' ) . '</h2>';
        $channel = $kraken->getChannelObject_Authd();
        if( !$channel ) {
            $output .= __( 'Attempt failed, there might be a fault or Twitch.tv is hungover today! Refresh the page and if this message continues please let Ryan know.', 'twitchpress' );    
        } else {
            $output .= __( 'Great news! TwitchPress is communicating with Twitch.tv. Here\'s some of your main channels information...to prove it!', 'twitchpress' );    
            $output .= '<ul>';
            $output .= '<li><strong>Display Name: </strong>' . $channel['display_name'] . '</li>';
            $output .= '<li><strong>Status: </strong>' . $channel['status'] . '</li>';
            $output .= '<li><strong>Game: </strong>' . $channel['game'] . '</li>';
            $output .= '</ul>';
        }
        
        set_transient( 'twitchpresshelptabstatus', $output, 60 );
        
        print $output;  
        
        _e( 'You are viewing real-time results (not cached). The results will be cached for 60 seconds to reduce the risk of flooding the Twitch API.', 'twitchpress' );  
    }
}

endif;

return new TwitchPress_Admin_Help();
