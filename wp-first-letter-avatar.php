<?php
/**
 * Plugin Name: WP First Letter Avatar
 * Plugin URI: https://github.com/DanielAGW/wp-first-letter-avatar
 * Contributors: DanielAGW
 * Description: Set custom avatars for users with no Gravatar. The avatar will be a first (or any other) letter of the users's name, just like in Discourse.
 * Version: 1.2.1
 * Author: Daniel Wroblewski
 * Author URI: https://github.com/DanielAGW
 * Tags: avatars, comments, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar
 * Requires at least: 3.0.1
 * Tested up to: 4.1.1
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



class WP_First_Letter_Avatar {

	// Setup (these values always stay the same):
	const IMAGES_PATH = 'images'; // avatars root directory
	const GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';    // default url for gravatar - we're using HTTPS to avoid annoying warnings

	// Default configuration (this is the default configuration only for the first plugin usage):
	const USE_GRAVATAR = TRUE;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars even when gravatar is set
	const AVATAR_SET = 'default'; // directory where avatars are stored
	const LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const IMAGES_FORMAT = 'png';   // file format of the avatars
	const ROUND_AVATARS = FALSE;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning
										// with symbol other than one from a-z range
	// variables duplicating const values (will be changed in constructor after reading config from DB):
	private $use_gravatar = self::USE_GRAVATAR;
	private $avatar_set = self::AVATAR_SET;
	private $letter_index = self::LETTER_INDEX;
	private $images_format = self::IMAGES_FORMAT;
	private $round_avatars = self::ROUND_AVATARS;
	private $image_unknown = self::IMAGE_UNKNOWN;



	public function __construct(){

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wpfla_add_settings_link'));

		// add stylesheets/scripts:
		add_action('wp_enqueue_scripts', array($this, 'wpfla_add_scripts'));

		// add filter to get_avatar but only when not in admin panel:
		if (!is_admin()){
			add_filter('get_avatar', array($this, 'set_avatar'), 10, 4);
		}

		// get plugin configuration from database:
		$options = get_option('wpfla_settings');
		if (empty($options)){

			// no records in DB, use default (const) values to save plugin config:
			$settings = array(
				'wpfla_use_gravatar' => self::USE_GRAVATAR,
				'wpfla_avatar_set' => self::AVATAR_SET,
				'wpfla_letter_index' => self::LETTER_INDEX,
				'wpfla_file_format' => self::IMAGES_FORMAT,
				'wpfla_round_avatars' => self::ROUND_AVATARS,
				'wpfla_unknown_image' => self::IMAGE_UNKNOWN
			);
			add_option('wpfla_settings', $settings);

		} else {

			// there are records in DB for our plugin, let's assign them to our variables:
			$this->use_gravatar = $options['wpfla_use_gravatar'];
			$this->avatar_set = $options['wpfla_avatar_set'];
			$this->letter_index = $options['wpfla_letter_index'];
			$this->images_format = $options['wpfla_file_format'];
			$this->round_avatars = $options['wpfla_round_avatars'];
			$this->image_unknown = $options['wpfla_unknown_image'];

		}

	}



	public function wpfla_add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=wp_first_letter_avatar">'.__("Settings", "default").'</a>';
		array_unshift($links, $settings_link);
		return $links;

	}



	public function wpfla_add_scripts(){

		// add main CSS file:
		wp_enqueue_style('prefix-style', plugins_url('css/style.css', __FILE__) );

	}



	public function set_avatar($avatar, $id_or_email, $size, $default, $alt = ''){

		// create array with needed avatar parameters for easier passing to next method:
		$avatar_params = array(
			'avatar' => $avatar,
			'id_or_email' => $id_or_email,
			'size' => $size,
			'alt' => $alt
		);

		// First check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE){
			// Gravatar used as default option, now check whether user's gravatar is set:
			$user_email = $this->get_email($id_or_email);
			if ($this->gravatar_exists($user_email)){
				// gravatar is set, output the gravatar img
				$avatar_output = $this->output_gravatar_img($user_email, $size, $alt);
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($avatar_params);
			}
		} else {
			// Gravatar is not used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($avatar_params);
		}

		return $avatar_output;

	}



	private function output_img($avatar, $size, $alt){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		if ($this->round_avatars == TRUE){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo wpfla {$extra_img_class}' height='{$size}' width='{$size}' />";

		// echo the <img> tag:
		return $output_data;

	}



	private function choose_custom_avatar($avatar_params){

		// extract parameters to separate variables for convenience:
		$id_or_email = $avatar_params['id_or_email'];
		$avatar = $avatar_params['avatar'];
		$size = $avatar_params['size'];
		$alt = $avatar_params['alt'];

		// lower-cased file name based on the letter retrieved from the name:
		$file_name = strtolower($this->get_letter($id_or_email));
		if ($file_name === FALSE){
			// if name returned false, set file name to default unknown image
			$file_name = $this->image_unknown;
		}

		// create array with allowed character range (in this case it is a-z range):
		$allowed_chars = range('a', 'z');
		// check if the file name meets the requirement; if it doesn't - set it to unknown
		if (!in_array($file_name, $allowed_chars)) {
			$file_name = $this->image_unknown;
		}

		// detect most appropriate size based on WP avatar size:
		if ($size <= 48) $custom_avatar_size = '48';
		else if ($size > 48 && $size <= 96) $custom_avatar_size = '96';
		else if ($size > 96 && $size <= 128) $custom_avatar_size = '128';
		else if ($size > 128 && $size <= 256) $custom_avatar_size = '256';
		else $custom_avatar_size = '512';

		// add slashes for convenience (these vars will be used to create path to custom avatar)
		$custom_avatar_size .= '/';
		$avatar_set = '/' . $this->avatar_set . '/';
		$images_format = '.' . $this->images_format; // add dot before file extension

		// get main plugin directory and add leading and trailing slashes:
		$plugin_directory = '/' . dirname(plugin_basename(__FILE__)) . '/';
		// avatar var will look like this: http://yourblog.com/wp-content/plugins/wp-first-letter-avatar/images/default/96/k.png
		$avatar = plugins_url() . $plugin_directory . self::IMAGES_PATH . $avatar_set .  $custom_avatar_size . $file_name . $images_format;

		// output the final HTML img code:
		return $this->output_img($avatar, $size, $alt);

	}



	private function output_gravatar_img($email, $size, $alt){

		// email to gravatar url:
		$avatar = self::GRAVATAR_URL;
		$avatar .= md5(strtolower(trim($email)));
		$avatar .= "?s=$size&d=mm&r=g";

		// output gravatar:
		return $this->output_img($avatar, $size, $alt);

	}



	private function get_email($id_or_email){

		/* retrieve and return email from passed parameter - it can be user id (int/string), email (string) or comment object
		   borrowed from wp-includes/pluggable.php */

		$email = '';
		if (is_numeric($id_or_email)){
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ($user)
				$email = $user->user_email;
		} elseif (is_object($id_or_email)){
			$allowed_comment_types = apply_filters('get_avatar_comment_types', array('comment'));
			if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array) $allowed_comment_types))
				return FALSE;
			if (!empty($id_or_email->user_id)){
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ($user)
					$email = $user->user_email;
			} elseif (!empty($id_or_email->comment_author_email)){
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		return $email;

	}



	private function get_letter($id_or_email){

		/* retrieve and return letter from passed parameter
		   return FALSE if letter cannot be retrieved */

		$name = '';
		if (is_numeric($id_or_email)){
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ($user)
				$name = $user->display_name;
		} elseif (is_object($id_or_email)){
			$allowed_comment_types = apply_filters('get_avatar_comment_types', array('comment'));
			if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array) $allowed_comment_types))
				return FALSE;
			if (!empty($id_or_email->user_id)){
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ($user)
					$name = $user->display_name;
			} elseif (!empty($id_or_email->comment_author)){
				$name = $id_or_email->comment_author;
			}
		} else {
			return FALSE;
		}

		// get specified letter from the name var and return it:
		$letter = substr($name, $this->letter_index, 1);
		return $letter;

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
			return true;
		} else { // response code is not 200, gravatar doesn't exist, return false
			return false;
		}

	}

}


// create WP_First_Letter_Avatar object:
$first_letter_avatar = new WP_First_Letter_Avatar();


// require back-end of the plugin
require_once 'wp-first-letter-avatar-config.php';
