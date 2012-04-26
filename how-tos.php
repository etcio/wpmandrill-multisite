<?php
/* 
Plugin Name: wpMandrill How-Tos
Description: This plugin show you how to use the different aspects of wpMandrill.
Author: Mandrill
Author URI: http://mandrillapp.com/
Plugin URI: http://connect.mailchimp.com/integrations/wpmandrill
Version: 1.0
Text Domain: wpmandrill
 */

MandrillTest::on_load();

class MandrillTest {
	static $settings;

	static function on_load() {
        if ( !class_exists('wpMandrill') ) {
            return;
        }
        
		add_action('admin_init', array(__CLASS__, 'adminInit'));
		add_action('admin_menu', array(__CLASS__, 'adminMenu'));
		add_filter('contextual_help', array(__CLASS__, 'showContextualHelp'), 10, 3);		
		add_action('admin_print_footer_scripts', array(__CLASS__,'openContextualHelp'));
		load_plugin_textdomain('wpmandrill', false, dirname( plugin_basename( __FILE__ ) ).'/lang');
	}

	/**
	 * @return boolean
	 */
	static function isPluginPage() {

		return ( isset( $_GET['page'] ) && $_GET['page'] == 'wpmandrilltest' );
	}

	/**
	 * Sets up options page and sections.
	 */
	static function adminInit() {

		add_filter('plugin_action_links',array(__CLASS__,'showPluginActionLinks'), 10,5);
		// SMTP Settings
		add_settings_section('wpmandrilltest-auto', __('Mandrill: How to tell WordPress to use wpMandrill.', 'wpmandrill'), '__return_false', 'wpmandrilltest');
    		add_settings_field('auto', __('&nbsp;', 'wpmandrill'), array(__CLASS__, 'showSectionAuto'), 'wpmandrilltest', 'wpmandrilltest-auto');

		add_settings_section('wpmandrilltest-regular', __('Mandrill: How to send a regular email.', 'wpmandrill'), '__return_false', 'wpmandrilltest');
    		add_settings_field('regular', __('&nbsp;', 'wpmandrill'), array(__CLASS__, 'showSectionRegular'), 'wpmandrilltest', 'wpmandrilltest-regular');

		add_settings_section('wpmandrilltest-filter', __('Mandrill: How to modify a certain email using the <em>mandrill_payload</em> WordPress filter.', 'wpmandrill'), '__return_false', 'wpmandrilltest');
    		add_settings_field('filter', __('&nbsp;', 'wpmandrill'), array(__CLASS__, 'showSectionFilter'), 'wpmandrilltest', 'wpmandrilltest-filter');

		add_settings_section('wpmandrilltest-direct', __('Mandrill: How to send emails from within your plugins.', 'wpmandrill'), '__return_false', 'wpmandrilltest');
    		add_settings_field('direct', __('&nbsp;', 'wpmandrill'), array(__CLASS__, 'showSectionDirect'), 'wpmandrilltest', 'wpmandrilltest-direct');
	}

    static function showSectionAuto() {
        echo '
    <span class="setting-description">
        <p>'.__('Simply install wpMandrill and configure it to make it handle all the email functions of your WordPress installation.', 'wpmandrill').'</p>
        <p>'.__('Once it has been properly configured, it will replace the regular WordPress emailing processes, so it\'s basically transparent for you and for WordPress.', 'wpmandrill').'</p>
        <p>'.__('To test wpMandrill, log out, and try to use the <em>Forgot your password?</em> feature in WordPress (you don\'t need to reset your password though. Just check the headers of the email that it sends you, and you\'ll see that it comes from Mandrill\'s servers).', 'wpmandrill').'</p>
    </span>
        ';
    }

    static function showSectionRegular() {
        echo '
    <span class="setting-description">
        <p>'.__('If you\'re a Plugin Developer, and you need to send a regular email using wpMandrill, you don\'t need to learn anything else. You can use the good ol\' <strong>wp_mail</strong> function, as you would normally do if you were not using this plugin.', 'wpmandrill').'</p>
        <p>'.__('For example:', 'wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php wp_mail(\'your@address.com\', \'Your subject\', \'Your message\'); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
    </span>
        ';
    }

    static function showSectionFilter() {
        echo '
    <span class="setting-description">
        <p>'.__('if you need to fine tune one or some of the emails sent through your WordPress installation, you will need to use the <em>mandrill_payload</em> filter.', 'wpmandrill').'</p>
        <p>'.__('To use it, you must create a function that analyzes the payload that is about to be sent to Mandrill, and modify it based on your requirements. Then you\'ll need to add this function as the callback of the mentioned filter, using the <em>add_filter</em> WordPress call. And finally, insert it into your theme\'s functions.php file or you own plugin\'s file.', 'wpmandrill').'</p>
        <p>'.__('You can use the following code as an skeleton for your own callbacks:', 'wpmandrill').'</p>
        <p>
            <blockquote><pre>
                &lt;?php
                &nbsp;&nbsp;&nbsp;function my_callback($message) {
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( my_condition($message) ) {
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$message = my_process($message)
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $message;
                &nbsp;&nbsp;&nbsp;}
                &nbsp;&nbsp;&nbsp;add_filter( \'mandrill_payload\', \'my_callback\' );
                ?&gt;
            </pre></blockquote>
        </p>
        <p>'.__('Let\'s say you\'re using the <a href="http://wordpress.org/extend/plugins/cart66-lite/" target="_blank">Cart66 Lite Ecommerce plugin</a> and you want to modify the emails sent from this plugin. Here\'s what you should do:', 'wpmandrill').'</p>
        <p>
            <blockquote><pre>
                &lt;?php
                &nbsp;&nbsp;&nbsp;function cart66Emails($message) {	                
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( in_array( \'wp_Cart66Common::mail\', $message[\'tags\'][\'automatic\'] ) ) {
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// do anything funny here...
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( isset($message[\'template\'][\'content\'][0][\'content\']) )
			                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = &$message[\'template\'][\'content\'][0][\'content\'];
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;else
			                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = &$message[\'html\'];

		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = nl2br($html);

	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $message;
                &nbsp;&nbsp;&nbsp;}
                &nbsp;&nbsp;&nbsp;add_filter( \'mandrill_payload\', \'cart66Emails\' );
                ?&gt;
            </pre></blockquote>
        </p>
    </span>
        ';
    }

    static function showSectionDirect() {
        echo '
    <span class="setting-description">
        <p>'.__('If you are a Plugin Developer and you need to create a deep integration between Mandrill and your WordPress installation, wpMandrill will make your life easier.', 'wpmandrill').'</p>
        <p>'.__('We have exposed a simple function that allows you to add tags and specify the template to use, in addition to specifying the To, Subject and Body sections of the email:','wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php wpMandrill::mail($to, $subject, $html, $headers = \'\', $attachments = array(), $tags = array(), $from_name = \'\', $from_email = \'\', $template_name = \'\'); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
        <p>'.__('But if you need Mandrill Powers, we have included a full-featured PHP class called Mandrill. It has every API call defined in Mandrill\'s API. Check it out at <em>/wp-content/plugin/wpmandrill/lib/mandrill.class.php</em>.', 'wpmandrill').'</p>
        <p>'.__('To use it, just instanciate an object passing your API key, and make the calls:', 'wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php $mandrill = Mandrill($my_api_key); echo $mandrill->ping(); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
    </span>
        ';
    }

	/**
	 * Creates option page's entry in Settings section of menu.
	 */
	static function adminMenu() {

		self::$settings = add_options_page( __('Mandrill How-Tos', 'wpmandrill'), __('Mandrill Test', 'wpmandrill'), 'manage_options', 'wpmandrilltest', array(__CLASS__,'showOptionsPage'));
	}
	
	/**
	 * Generates source of contextual help panel.
	 */
	static function showContextualHelp($contextual_help, $screen_id, $screen) {

		if ($screen_id == self::$settings) {
		    
			return  '<p>' . __('The purpose of this plugin is to show you how easy it is to start using the awesome platform that Mandrill offers to handle your transactional emails.', 'wpmandrilltest').'</p>'
					. '<ol>'
					. '<li>'. __('Just by setting it up, all the emails sent from your WordPress installation will be sent using the power of Mandrill.', 'wpmandrill') . '</li>'
					. '<li>'. __('If you want further customization, you can use the <strong>mandrill_payload</strong> filter we\'ve provided.', 'wpmandrill') . '</li>'
					. '<li>'. __('And if you want an even greater integration between your application and Mandrill, we\'ve created a convenient call to send emails from within your plugins.', 'wpmandrill') . '</li>'
					. '</ol>'
					.'<p>' . __('You can learn more about all of these features right from this page.', 'wpmandrill').'</p>'
					
					;
		}

		return $contextual_help;
	}

	/**
	 * Adds link to settings page in list of plugins
	 */
	static function showPluginActionLinks($actions, $plugin_file) {

		static $plugin;

		if (!isset($plugin))
			$plugin = plugin_basename(__FILE__);

		if ($plugin == $plugin_file) {

			$settings = array('settings' => '<a href="options-general.php?page=wpmandrilltest">' . __('Settings', 'wpmandrill') . '</a>');
			$actions = array_merge($settings, $actions);
		}

		return $actions;
	}

	/**
	 * Generates source of options page.
	 */
	static function showOptionsPage() {

		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.','wpmandrill') );

		?>
<div class="wrap">
    <div class="icon32" style="background: url('<?php echo plugins_url('images/mandrill-head-icon.png',__FILE__); ?>');"><br /></div>
    <div style="float: left;width: 70%;">
        <h2><?php _e('Mandrill How-tos', 'wpmandrill'); ?></h2>

        <form method="post" action="options.php">
        <?php settings_fields('wpmandrilltest'); ?>
        <?php do_settings_sections('wpmandrilltest'); ?>
        </form>
    </div>
</div>
		<?php
	}
	
	/**
	 * Opens contextual help section.
	 */
	static function openContextualHelp() {

		if ( !self::isPluginPage() )
			return;

		?>
<script type="text/javascript">
jQuery(document).bind( 'ready', function() {
    jQuery('a#contextual-help-link').trigger('click');
});
</script>
		<?php
	}
}

?>
