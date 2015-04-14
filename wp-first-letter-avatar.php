<?php

/**
 * Plugin Name: WP First Letter Avatar
 * Plugin URI: https://github.com/DanielAGW/wp-first-letter-avatar
 * Contributors: DanielAGW
 * Description: Set custom avatars for users with no Gravatar. The avatar will be a first (or any other) letter of the users's name.
 * Version: 1.2.7
 * Author: Daniel Wroblewski
 * Author URI: https://github.com/DanielAGW
 * Tags: avatars, comments, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar
 * Requires at least: 4.0
 * Tested up to: 4.2
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



class WP_First_Letter_Avatar {

	// Setup (these values always stay the same):
	const MINIMUM_PHP = '5.4';
	const MINIMUM_WP = '4.0';
	const WPFLA_IMAGES_PATH = 'images'; // avatars root directory
	const WPFLA_GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';    // default url for gravatar - we're using HTTPS to avoid annoying warnings
	const PLUGIN_NAME = 'WP First Letter Avatar';

	// Default configuration (this is the default configuration only for the first plugin usage):
	const WPFLA_USE_GRAVATAR = TRUE;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars even when gravatar is set
	const WPFLA_USE_JS = FALSE;  // TRUE: use JS to replace avatars to Gravatar; FALSE: generate avatars and gravatars here in PHP
	const WPFLA_AVATAR_SET = 'default'; // directory where avatars are stored
	const WPFLA_LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const WPFLA_IMAGES_FORMAT = 'png';   // file format of the avatars
	const WPFLA_ROUND_AVATARS = FALSE;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const WPFLA_IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning
										// with symbol other than one from a-z range
	// variables duplicating const values (will be changed in constructor after reading config from DB):
	private $use_gravatar = self::WPFLA_USE_GRAVATAR;
	private $use_js = self::WPFLA_USE_JS;
	private $avatar_set = self::WPFLA_AVATAR_SET;
	private $letter_index = self::WPFLA_LETTER_INDEX;
	private $images_format = self::WPFLA_IMAGES_FORMAT;
	private $round_avatars = self::WPFLA_ROUND_AVATARS;
	private $image_unknown = self::WPFLA_IMAGE_UNKNOWN;



	public function __construct(){

		// add plugin activation hook:
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));

		// add plugin deactivation hook:
		register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));

		// add new avatar to Settings > Discussion page:
		add_filter('avatar_defaults', array($this, 'add_discussion_page_avatar'));

		// check for currently set default avatar:
		$avatar_default = get_option('avatar_default');
		$plugin_avatar = plugins_url(self::WPFLA_IMAGES_PATH . '/wp-first-letter-avatar.png', __FILE__);
		if ($avatar_default != $plugin_avatar){ // if first letter avatar is not activated in settings > discussion page...
			return; // cancel plugin execution
		}

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wpfla_add_settings_link'));

		// add stylesheets/scripts for front-end and admin:
		add_action('wp_enqueue_scripts', array($this, 'wpfla_add_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'wpfla_add_scripts'));

		// add Ajax action for asynchronous Gravatar verification:
		add_action('wp_ajax_gravatar_verify', array($this, 'ajax_gravatar_exists'));
		add_action('wp_ajax_nopriv_gravatar_verify', array($this, 'ajax_gravatar_exists'));

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), 10, 5);

		// add additional filter for userbar avatar, but only when not in admin:
		if (!is_admin()){
			add_action('admin_bar_menu', function(){
				add_filter('get_avatar', array($this, 'set_userbar_avatar'), 10, 5);
			}, 0);
		} else { // when in admin, make sure first letter avatars are not displayed on discussion settings page
			global $pagenow;
			if ($pagenow == 'options-discussion.php'){
				remove_filter('get_avatar', array($this, 'set_comment_avatar'));
			}
		}

		// get plugin configuration from database:
		$options = get_option('wpfla_settings');
		if (empty($options)){
			// no records in DB, use default (const) values to save plugin config:
			$settings = array(
				'wpfla_use_gravatar' => self::WPFLA_USE_GRAVATAR,
				'wpfla_use_js' => self::WPFLA_USE_JS,
				'wpfla_avatar_set' => self::WPFLA_AVATAR_SET,
				'wpfla_letter_index' => self::WPFLA_LETTER_INDEX,
				'wpfla_file_format' => self::WPFLA_IMAGES_FORMAT,
				'wpfla_round_avatars' => self::WPFLA_ROUND_AVATARS,
				'wpfla_unknown_image' => self::WPFLA_IMAGE_UNKNOWN
			);
			add_option('wpfla_settings', $settings);
		} else {
			// there are records in DB for our plugin, let's check if some of them are not empty:
			$change_values = FALSE; // do not update settings by default...
			if ($options['wpfla_avatar_set'] === ''){
				$options['wpfla_avatar_set'] = self::WPFLA_AVATAR_SET;
				$change_values = TRUE;
			}
			if ($options['wpfla_letter_index'] === ''){
				$options['wpfla_letter_index'] = self::WPFLA_LETTER_INDEX;
				$change_values = TRUE;
			}
			if ($options['wpfla_file_format'] === ''){
				$options['wpfla_file_format'] = self::WPFLA_IMAGES_FORMAT;
				$change_values = TRUE;
			}
			if ($options['wpfla_unknown_image'] === ''){
				$options['wpfla_unknown_image'] = self::WPFLA_IMAGE_UNKNOWN;
				$change_values = TRUE;
			}
			if (empty($options['wpfla_use_gravatar'])){
				$options['wpfla_use_gravatar'] = FALSE;
				$change_values = TRUE;
			}
			if (empty($options['wpfla_use_js'])){
				$options['wpfla_use_js'] = FALSE;
				$change_values = TRUE;
			}
			if (empty($options['wpfla_round_avatars'])){
				$options['wpfla_round_avatars'] = FALSE;
				$change_values = TRUE;
			}
			if ($change_values === TRUE){
				$settings['wpfla_use_gravatar'] = $options['wpfla_use_gravatar'];
				$settings['wpfla_use_js'] = $options['wpfla_use_js'];
				$settings['wpfla_avatar_set'] = $options['wpfla_avatar_set'];
				$settings['wpfla_letter_index'] = $options['wpfla_letter_index'];
				$settings['wpfla_file_format'] = $options['wpfla_file_format'];
				$settings['wpfla_round_avatars'] = $options['wpfla_round_avatars'];
				$settings['wpfla_unknown_image'] = $options['wpfla_unknown_image'];
				update_option('wpfla_settings', $settings);
			}
			// and then assign them to our class properties
			$this->use_gravatar = $options['wpfla_use_gravatar'];
			$this->use_js = $options['wpfla_use_js'];
			$this->avatar_set = $options['wpfla_avatar_set'];
			$this->letter_index = $options['wpfla_letter_index'];
			$this->images_format = $options['wpfla_file_format'];
			$this->round_avatars = $options['wpfla_round_avatars'];
			$this->image_unknown = $options['wpfla_unknown_image'];
		}

	}



	public function plugin_activate(){ // plugin activation event

		$php = self::MINIMUM_PHP;
		$wp = self::MINIMUM_WP;

		// check PHP and WP compatibility:
		global $wp_version;
		if (version_compare(PHP_VERSION, $php, '<'))
			$flag = 'PHP';
		else if	(version_compare($wp_version, $wp, '<'))
			$flag = 'WordPress';

		if (!empty($flag)){
			$version = 'PHP' == $flag ? $php : $wp;
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('<p><strong>' . self::PLUGIN_NAME . '</strong> plugin requires ' . $flag . ' version ' . $version . ' or greater.</p>', 'Plugin Activation Error',  array('response' => 200, 'back_link' => TRUE));
		}

		// backup current active default avatar:
		$current_avatar = get_option('avatar_default');
		update_option('avatar_default_wpfla_backup', $current_avatar);

		// set first letter avatar as main avatar when activating the plugin:
		$avatar_file = plugins_url(self::WPFLA_IMAGES_PATH . '/wp-first-letter-avatar.png', __FILE__);
		update_option('avatar_default' , $avatar_file); // set the new avatar to be the default

	}



	public function plugin_deactivate(){ // plugin deactivation event

		// restore previous default avatar:
		$plugin_option_value = plugins_url(self::WPFLA_IMAGES_PATH . '/wp-first-letter-avatar.png', __FILE__);
		$option_name = 'avatar_default_wpfla_backup';
		$option_value = get_option($option_name);
		if (!empty($option_value) && $option_value != $plugin_option_value){
			update_option('avatar_default' , $option_value);
		}

	}



	public function add_discussion_page_avatar($avatar_defaults){

		// add new avatar to Settings > Discussion page
		$avatar_file = plugins_url(self::WPFLA_IMAGES_PATH . '/wp-first-letter-avatar.png', __FILE__);
		$avatar_defaults[$avatar_file] = self::PLUGIN_NAME;
		return $avatar_defaults;

	}



	public function wpfla_add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=wp_first_letter_avatar">'.__('Settings', 'default').'</a>';
		array_unshift($links, $settings_link);
		return $links;

	}



	public function wpfla_add_scripts(){

		// add main CSS file:
		wp_enqueue_style('prefix-style', plugins_url('css/style.css', __FILE__));

		// add main JS file, only when JS is used:
		if ($this->use_js == TRUE){
			$js_variables = array(
				'img_data_attribute' => 'data-wpfla-gravatar',
				'ajaxurl' => admin_url('admin-ajax.php')
			);
			wp_enqueue_script('wpfla-script-handle', plugins_url('js/script.js', __FILE__), array('jquery'));
			wp_localize_script('wpfla-script-handle', 'wpfla_vars_data', $js_variables);
		}

	}



	public function ajax_gravatar_exists(){

		$gravatar_uri = $_POST['gravatar_uri'];
		$gravatar_exists = $this->gravatar_exists_uri($gravatar_uri);

		if ($gravatar_exists == TRUE){
			echo '1';
		} else {
			echo '0';
		}

		exit;

	}



	private function set_avatar($name, $email, $size, $alt = ''){

		if (empty($name)){ // if, for some reason, there is no name, use email instead
			$name = $email;
		} else if (empty($email)){ // and if no email, use user/guest name
			$email = $name;
		}

		// first check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE && $this->use_js == FALSE){
			// gravatar used as default option, now check whether user's gravatar is set:
			if ($this->gravatar_exists($email)){
				// gravatar is set, output the gravatar img
				$avatar_output = $this->output_gravatar_img($email, $size, $alt);
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
			}
		} else if ($this->use_gravatar == TRUE && $this->use_js == TRUE){
			// gravatar with JS is used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt, $email);
		} else {
			// gravatar is not used:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
		}

		return $avatar_output;

	}



	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default, $alt = ''){

		// create two main variables:
		$name = '';
		$email = '';

		// check if it's a comment:
		global $comment;
		if (empty($comment)){
			$comment_id = NULL;
		} else {
			$comment_id = get_comment_ID();
		}

		if ($comment_id === NULL){ // if it's not a regular comment, use $id_or_email to get more data

			if (is_numeric($id_or_email)){ // if id_or_email represents user id, get user by id
				$id = (int) $id_or_email;
				$user = get_user_by('id', $id);
			} else if (is_object($id_or_email)){ // if id_or_email represents an object
				if (!empty($id_or_email->user_id)){  // if there we can get user_id from the object, get user by id
					$id = (int) $id_or_email->user_id;
					$user = get_user_by('id', $id);
				}
			} else { // id_or_email is not user_id and is not an object, then it must be an email
				$user = get_user_by('email', $id_or_email);
			}

			if ($user && is_object($user)){ // if commenter is a registered user...
				$name = $user->data->display_name;
				$email = $user->data->user_email;
			} else { // if commenter is not a registered user, we have to try various fallbacks
				$post_id = get_the_ID();
				if ($post_id !== NULL){ // if this actually is a post...
					$post_data = array('name' => '', 'email' => '');
					// first we try for bbPress:
					$post_data['name'] = get_post_meta($post_id, '_bbp_anonymous_name', TRUE);
					$post_data['email'] = get_post_meta($post_id, '_bbp_anonymous_email', TRUE);
					if (!empty($post_data)){ // we have some post data...
						$name = $post_data['name'];
						$email = $post_data['email'];
					}
				} else { // nothing else to do, assign email from id_or_email to email and later use it as name
					if (!empty($id_or_email)){
						$email = $id_or_email;
					}
				}
			}

		} else { // if it's a standard comment, use basic comment functions to retrive info

			$name = get_comment_author();
			$email = get_comment_author_email();

		}

		$avatar_output = $this->set_avatar($name, $email, $size, $alt = '');

		return $avatar_output;

	}



	public function set_userbar_avatar($avatar, $id_or_email, $size = '96', $default, $alt = ''){ // only size and alt arguments are used

		// get user information:
		global $current_user;
		get_currentuserinfo();
		$name = $current_user->display_name;
		$email = $current_user->user_email;

		$avatar_output = $this->set_avatar($name, $email, $size, $alt);

		return $avatar_output;

	}



	private function output_img($avatar_uri, $size, $alt = '', $gravatar_uri = ''){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		$extra_img_tags = '';
		if (!empty($gravatar_uri)){
			$extra_img_tags .= "data-wpfla-gravatar='{$gravatar_uri}'";
		}
		if ($this->round_avatars == TRUE){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img alt='{$alt}' src='{$avatar_uri}' {$extra_img_tags} class='avatar avatar-{$size} photo wpfla {$extra_img_class}' height='{$size}' width='{$size}'  />";

		// return the complete <img> tag:
		return $output_data;

	}



	private function choose_custom_avatar($comment_author, $size, $alt = '', $email = ''){

		// get picture filename (and lowercase it) from commenter name:
		$file_name = substr($comment_author, $this->letter_index, 1); // get one letter counting from letter_index
		$file_name = strtolower($file_name); // lowercase it...
		// if, for some reason, the result is empty, set file_name to default unknown image:
		if (empty($file_name)){
			$file_name = $this->image_unknown;
		}

		// create array with allowed character range (in this case it is a-z range):
		$allowed_chars = range('a', 'z');
		// check if the file name meets the requirement; if it doesn't - set it to unknown
		if (!in_array($file_name, $allowed_chars)){
			$file_name = $this->image_unknown;
		}

		// detect most appropriate size based on WP avatar size:
		if ($size <= 48) $custom_avatar_size = '48';
		else if ($size > 48 && $size <= 96) $custom_avatar_size = '96';
		else if ($size > 96 && $size <= 128) $custom_avatar_size = '128';
		else if ($size > 128 && $size <= 256) $custom_avatar_size = '256';
		else $custom_avatar_size = '512';

		// create file path - avatar_path variable will look something like this:
		// http://yourblog.com/wp-content/plugins/wp-first-letter-avatar/images/default/96/k.png):
		$avatar_uri =
			plugins_url() . '/'
			. dirname(plugin_basename(__FILE__)) . '/'
			. self::WPFLA_IMAGES_PATH . '/'
			. $this->avatar_set . '/'
			. $custom_avatar_size . '/'
			. $file_name . '.'
			. $this->images_format;

		$gravatar_uri = '';

		if (!empty($email)){
			$gravatar_uri .= $this->generate_gravatar_uri($email, $size);
		}

		// output the final HTML img code:
		return $this->output_img($avatar_uri, $size, $alt, $gravatar_uri);

	}



	private function generate_gravatar_uri($email, $size){

		// email to gravatar url:
		$avatar_uri = self::WPFLA_GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($email)));
		$avatar_uri .= "?s={$size}&d=mm&r=g";

		return $avatar_uri;

	}



	private function output_gravatar_img($comment_email, $size, $alt = ''){

		// output gravatar:
		$avatar_uri = $this->generate_gravatar_uri($comment_email, $size);
		return $this->output_img($avatar_uri, $size, $alt);

	}



	private function gravatar_exists($email){

		/*  Check if there is gravatar assigned to this email
		    returns bool: true if gravatar is assigned, false if it is not
		    function partially borrowed from http://codex.wordpress.org/Using_Gravatars - thanks! */

		$hash = md5(strtolower(trim($email))); // email md5 hash used by gravatar system
		$uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
		$response = wp_remote_head($uri); // response from gravatar server

		if (is_wp_error($response)){ // caused error?
			$data = 'error';
		} else {
			$data = $response['response']['code']; // no error, assign response code to data
		}

		if ($data == '200'){ // response code is 200, gravatar exists, return true
			return TRUE;
		} else { // response code is not 200, gravatar doesn't exist, return false
			return FALSE;
		}

	}



	private function gravatar_exists_uri($uri){

		/*  Check if there is gravatar assigned to this gravatar url
		    returns bool: true if gravatar is assigned, false if it is not
		    function partially borrowed from http://codex.wordpress.org/Using_Gravatars - thanks! */


		// first check whether it is a gravatar URL; if not, return FALSE
		if (stripos($uri, 'gravatar.com/avatar') === FALSE){
			return FALSE;
		}

		// strip all GET parameters:
		$uri = strtok($uri, '?');
		$uri .=  '?d=404';
		$response = wp_remote_head($uri); // response from gravatar server

		if (is_wp_error($response)){ // caused error?
			$data = 'error';
		} else {
			$data = $response['response']['code']; // no error, assign response code to data
		}

		if ($data == '200'){ // response code is 200, gravatar exists, return true
			return TRUE;
		} else { // response code is not 200, gravatar doesn't exist, return false
			return FALSE;
		}

	}

}


// create WP_First_Letter_Avatar object:
$wp_first_letter_avatar = new WP_First_Letter_Avatar();


// require back-end of the plugin
require_once 'wp-first-letter-avatar-config.php';
