<?php

/**
 * Plugin Name: WP First Letter Avatar
 * Text Domain: wp-first-letter-avatar
 * Domain Path: /languages/
 * Plugin URI: http://dev49.net
 * Contributors: Dev49.net, DanielAGW
 * Description: Set custom avatars for users with no Gravatar. The avatar will be the first (or any other) letter of the user's name on a colorful background.
 * Version: 2.2.8
 * Author: Dev49.net
 * Author URI: http://dev49.net
 * Tags: avatars, comments, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar, initial avatar
 * Requires at least: 4.6
 * Tested up to: 4.7
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly:
if (!defined('ABSPATH')){
    exit;
}

/**
 * Class WP_First_Letter_Avatar
 */
class WP_First_Letter_Avatar
{
	// Setup:
	const MINIMUM_PHP = '5.4';
	const MINIMUM_WP = '4.6';
	const IMAGES_PATH = 'images'; // avatars root directory
	const GRAVATAR_URL = 'https://secure.gravatar.com/avatar/'; // default url for gravatar
	const PLUGIN_NAME = 'WP First Letter Avatar';

	// Default configuration (this is the default configuration only for the first plugin use):
	const USE_GRAVATAR = true;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars even when gravatar is set
	const AVATAR_SET = 'default'; // directory where avatars are stored
	const LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const IMAGES_FORMAT = 'png';   // file format of the avatars
	const ROUND_AVATARS = false;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning with symbol other than one from a-z range
	const FILTER_PRIORITY = 10;  // plugin filter priority

	// properties duplicating const values (will be changed in constructor after reading config from DB):
    /**
     * @var bool
     */
	private $use_gravatar = self::USE_GRAVATAR;

    /**
     * @var string
     */
	private $avatar_set = self::AVATAR_SET;

    /**
     * @var int
     */
	private $letter_index = self::LETTER_INDEX;

    /**
     * @var string
     */
	private $images_format = self::IMAGES_FORMAT;

    /**
     * @var bool
     */
	private $round_avatars = self::ROUND_AVATARS;

    /**
     * @var string
     */
	private $image_unknown = self::IMAGE_UNKNOWN;

    /**
     * @var int
     */
	private $filter_priority = self::FILTER_PRIORITY;

    /**
     * WP_First_Letter_Avatar constructor.
     */
	public function __construct(){
		
		/* --------------- CONFIGURATION --------------- */

		// get plugin configuration from database:
		$options = get_option('wpfla_settings');
		if (empty($options)){
			// no records in DB, use default (const) values to save plugin config:
			$initial_settings = array(
				'wpfla_use_gravatar' => self::USE_GRAVATAR,
				'wpfla_avatar_set' => self::AVATAR_SET,
				'wpfla_letter_index' => self::LETTER_INDEX,
				'wpfla_file_format' => self::IMAGES_FORMAT,
				'wpfla_round_avatars' => self::ROUND_AVATARS,
				'wpfla_unknown_image' => self::IMAGE_UNKNOWN,
				'wpfla_filter_priority' => self::FILTER_PRIORITY
			);
			add_option('wpfla_settings', $initial_settings);
		} else { // there are records in DB for our plugin
			// assign them to our class properties:
			$this->use_gravatar = (array_key_exists('wpfla_use_gravatar', $options) ? (bool)$options['wpfla_use_gravatar'] : false);
			$this->avatar_set = (array_key_exists('wpfla_avatar_set', $options) ? (string)$options['wpfla_avatar_set'] : self::AVATAR_SET);
			$this->letter_index = (array_key_exists('wpfla_letter_index', $options) ? (int)$options['wpfla_letter_index'] : self::LETTER_INDEX);
			$this->images_format = (array_key_exists('wpfla_file_format', $options) ? (string)$options['wpfla_file_format'] : self::IMAGES_FORMAT);
			$this->round_avatars = (array_key_exists('wpfla_round_avatars', $options) ? (bool)$options['wpfla_round_avatars'] : false);
			$this->image_unknown = (array_key_exists('wpfla_unknown_image', $options) ? (string)$options['wpfla_unknown_image'] : self::IMAGE_UNKNOWN);
			$this->filter_priority = (array_key_exists('wpfla_filter_priority', $options) ? (int)$options['wpfla_filter_priority'] : self::FILTER_PRIORITY);
		}


		/* --------------- WP HOOKS --------------- */

		// add plugins_loaded action to load textdomain:
		add_action('plugins_loaded', array($this, 'plugins_loaded'));

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

		// add plugin activation hook:
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));

		// add stylesheets/scripts:
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority, 6);

        // add filter for wpDiscuz:
		add_filter('wpdiscuz_author_avatar_field', array($this, 'set_wpdiscuz_avatar'), $this->filter_priority, 4);

		// add additional filter for userbar avatar, but only when not in admin:
		if (!is_admin()){
			add_action('admin_bar_menu', array($this, 'admin_bar_menu_action'), 0);
		} else { // when in admin, make sure first letter avatars are not displayed on discussion settings page
			global $pagenow;
			if ($pagenow == 'options-discussion.php'){
				remove_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority);
			}
		}

	}

	/**
	 * Plugins loaded - load text domain
	 */
	public function plugins_loaded(){

		load_plugin_textdomain('wp-first-letter-avatar', FALSE, basename(dirname(__FILE__)) . '/languages/');

	}

	/**
	 * Add scripts and stylesheets
	 */
	public function enqueue_scripts(){

		wp_enqueue_style('wpfla-style-handle', plugins_url('css/style.css', __FILE__));

	}

	/**
	 * This method is called when 'admin_bar_menu' action is called - it is needed to apply another filter just to
	 * filter the avatar in top bar (for logged in users)
	 */
	public function admin_bar_menu_action(){ // change avatar in the userbar at the top

		add_filter('get_avatar', array($this, 'set_userbar_avatar'), $this->filter_priority, 6);

	}

	/**
	 * On plugin activation check WP and PHP version and if requirements are not met, disable the plugin and display error
	 */
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
			$wrong_version_text = sprintf(__('<p>This plugin requires %s version %s or greater.</p>', 'wp-first-letter-avatar'), $flag, $version);
			$wrong_version_message_title = __('Plugin Activation Error', 'wp-first-letter-avatar');
			wp_die($wrong_version_text, $wrong_version_message_title, array('response' => 200, 'back_link' => true));
		}

	}

	/**
	 * Add Settings link to Plugins section
	 *
	 * @param array $links
     *
     * @return array
	 */
	public function add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=wp_first_letter_avatar">'.__('Settings', 'wp-first-letter-avatar').'</a>';
		array_unshift($links, $settings_link);

		return $links;

	}

	/**
     * This is method is used to filter wpDiscuz parameter - it feeds $comment object to get_avatar() function
     * (more on line 102 in wpdiscuz/templates/comment/class.WpdiscuzWalker.php)
     *
     * @param $author_avatar_field
     * @param $comment
     * @param $user
     * @param $profile_url
     *
     * @return WP_Comment
     */
	public function set_wpdiscuz_avatar($author_avatar_field, $comment, $user, $profile_url){

        // that's all we need - instead of user ID or guest email supplied in
        // $author_avatar_field, we just need to return the $comment object
		return $comment;

	}

	/**
	 * This is the main method used for generating avatars. It returns full HTML <img /> tag.
     *
     * @param string $name
     * @param string $email
     * @param string $size
     * @param string $alt
     * @param array $args
     *
     * @return string
	 */
	private function set_avatar($name, $email, $size, $alt = '', $args = array()){

		if (empty($name)){ // if, for some reason, there is no name, use email instead
			$name = $email;
		} else if (empty($email)){ // and if no email, use user/guest name
			$email = $name;
		}

		// first check whether Gravatar should be used at all:
		if ($this->use_gravatar == true){
			$gravatar_uri = $this->generate_gravatar_uri($email, $size);
			$first_letter_uri = $this->generate_first_letter_uri($name, $size);
			$avatar_uri = $gravatar_uri . '&default=' . urlencode($first_letter_uri);
		} else {
			// gravatar is not used:
			$first_letter_uri = $this->generate_first_letter_uri($name, $size);
			$avatar_uri = $first_letter_uri;
		}

		$avatar_img_output = $this->generate_avatar_img_tag($avatar_uri, $size, $alt, $args); // get final <img /> tag for the avatar/gravatar

		return $avatar_img_output;

	}

	/**
	 * This filters every WordPress avatar call and return full HTML <img /> tag
     *
     * @param string $avatar
     * @param WP_Comment|string $id_or_email
     * @param string $size
     * @param string $default
     * @param string $alt
     * @param array $args
     *
     * @return string
	 */
	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = '', $args = array()){

		// create two main variables:
		$name = '';
		$email = '';
		$user = null; // we will try to assign User object to this


		if (is_object($id_or_email)){ // id_or_email can actually be also a comment object, so let's check it first
			if (!empty($id_or_email->comment_ID)){
				$comment_id = $id_or_email->comment_ID; // it is a comment object and we can take the ID
			} else {
				$comment_id = null;
			}
		} else {
			$comment_id = null;
		}

		if ($comment_id === null){ // if it's not a regular comment, use $id_or_email to get more data

			if (is_numeric($id_or_email)){ // if id_or_email represents user id, get user by id
				$id = (int) $id_or_email;
				$user = get_user_by('id', $id);
			} else if (is_object($id_or_email)){ // if id_or_email represents an object
				if (!empty($id_or_email->user_id)){  // if we can get user_id from the object, get user by id
					$id = (int) $id_or_email->user_id;
					$user = get_user_by('id', $id);
				}
			}

			if (!empty($user) && is_object($user)){ // if commenter is a registered user...
				$name = $user->data->display_name;
				$email = $user->data->user_email;
			} else if (is_string($id_or_email)){ // if string was supplied
				if (!filter_var($id_or_email, FILTER_VALIDATE_EMAIL)){ // if it is NOT email, it must be a username
					$name = $id_or_email;
				} else { // it must be email
					$email = $id_or_email;
					$user = get_user_by('email', $email);
				}
			} else { // if commenter is not a registered user, we have to try various fallbacks
				$post_id = get_the_ID();
				if ($post_id !== null){ // if this actually is a post...
					$post_data = array('name' => '', 'email' => '');
					// first we try for bbPress:
					$post_data['name'] = get_post_meta($post_id, '_bbp_anonymous_name', true);
					$post_data['email'] = get_post_meta($post_id, '_bbp_anonymous_email', true);
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

		} else { // if it's a standard comment, use basic comment properties and/or functions to retrieve info

			$comment = $id_or_email;

			if (!empty($comment->comment_author)){
				$name = $comment->comment_author;
			} else {
				$name = get_comment_author();
			}

			if (!empty($comment->comment_author_email)){
				$email = $comment->comment_author_email;
			} else {
				$email = get_comment_author_email();
			}

		}

		if (empty($name) && !empty($user) && is_object($user)){ // if we do not have the name, but we have user object
			$name = $user->display_name;
		}

		if (empty($email) && !empty($user) && is_object($user)){ // if we do not have the email, but we have user object
			$email = $user->user_email;
		}

		$avatar_output = $this->set_avatar($name, $email, $size, $alt, $args);

		return $avatar_output;

	}

	/**
	 * This method is used to filter the avatar displayed in upper bar (displayed only for logged in users)
     *
     * @param string $avatar
     * @param WP_Comment|string $id_or_email
     * @param string $size
     * @param string $default
     * @param string $alt
     * @param array $args
     *
     * @return string
	 */
	public function set_userbar_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = '', $args = array()){

		// get user information:
		$current_user = wp_get_current_user();
		$name = $current_user->display_name;
		$email = $current_user->user_email;

		// use obtained data to return full HTML <img> tag
		$avatar_output = $this->set_avatar($name, $email, $size, $alt, $args);

		return $avatar_output;

	}

	/**
	 * Generate full HTML <img /> tag with avatar URL, size, CSS classes etc.
     *
     * @param string $avatar_uri
     * @param string $size
     * @param string $alt
     * @param array $args
     *
     * @return string
	 */
	private function generate_avatar_img_tag($avatar_uri, $size, $alt = '', $args = array()){

		// Default classes
		$css_classes = 'avatar avatar-' . $size . ' photo';
		
		// Append plugin class
		$css_classes .= ' wpfla';
		
		// prepare extra classes for <img> tag depending on plugin settings:
		if ($this->round_avatars == true){
			$css_classes .= ' round-avatars';
		}
		
		// Append extra classes
		if (array_key_exists('class', $args)) {
			if (is_array($args['class'])) {
				$css_classes .= ' ' . implode(' ', $args['class']);
			} else {
				$css_classes .= ' ' . $args['class'];
			}
		}

		$output_data = "<img alt='{$alt}' src='{$avatar_uri}' class='{$css_classes}' width='{$size}' height='{$size}' />";

		// return the complete <img> tag:
		return $output_data;

	}

	/**
	 * This method generates full URL for letter avatar (for example http://yourblog.com/wp-content/plugins/wp-first-letter-avatar/images/default/96/k.png),
	 * according to the $name and $size provided
     *
     * @param string $name
     * @param string $size
     *
     * @return string
	 */
	private function generate_first_letter_uri($name, $size){

		// get picture filename (and lowercase it) from commenter name:
		if (empty($name)){  // if, for some reason, the name is empty, set file_name to default unknown image

			$file_name = $this->image_unknown;

		} else { // name is not empty, so we can proceed

			$file_name = substr($name, $this->letter_index, 1); // get one letter counting from letter_index
			$file_name = strtolower($file_name); // lowercase it...

			if (extension_loaded('mbstring')){ // check if mbstring is loaded to allow multibyte string operations
				$file_name_mb = mb_substr($name, $this->letter_index, 1); // repeat, this time with multibyte functions
				$file_name_mb = mb_strtolower($file_name_mb); // and again...
			} else { // mbstring is not loaded - we're not going to worry about it, just use the original string
				$file_name_mb = $file_name;
			}

			// couple of exceptions:
			if ($file_name_mb == 'ą'){
				$file_name = 'a';
				$file_name_mb = 'a';
			} else if ($file_name_mb == 'ć'){
				$file_name = 'c';
				$file_name_mb = 'c';
			} else if ($file_name_mb == 'ę'){
				$file_name = 'e';
				$file_name_mb = 'e';
			} else if ($file_name_mb == 'ń'){
				$file_name = 'n';
				$file_name_mb = 'n';
			} else if ($file_name_mb == 'ó'){
				$file_name = 'o';
				$file_name_mb = 'o';
			} else if ($file_name_mb == 'ś'){
				$file_name = 's';
				$file_name_mb = 's';
			} else if ($file_name_mb == 'ż' || $file_name_mb == 'ź'){
				$file_name = 'z';
				$file_name_mb = 'z';
			}

			// create arrays with allowed character ranges:
			$allowed_numbers = range(0, 9);
			foreach ($allowed_numbers as $number){ // cast each item to string (strict param of in_array requires same type)
				$allowed_numbers[$number] = (string)$number;
			}
			$allowed_letters_latin = range('a', 'z');
			$allowed_letters_cyrillic = range('а', 'ё');
			$allowed_letters_arabic = range('آ', 'ی');
			// check if the file name meets the requirement; if it doesn't - set it to unknown
			$charset_flag = ''; // this will be used to determine whether we are using latin chars, cyrillic chars, arabic chars or numbers
			// check whther we are using latin/cyrillic/numbers and set the flag, so we can later act appropriately:
			if (in_array($file_name, $allowed_numbers, true)){
				$charset_flag = 'number';
			} else if (in_array($file_name, $allowed_letters_latin, true)){
				$charset_flag = 'latin';
			} else if (in_array($file_name, $allowed_letters_cyrillic, true)){
				$charset_flag = 'cyrillic';
			} else if (in_array($file_name, $allowed_letters_arabic, true)){
				$charset_flag = 'arabic';
			} else { // for some reason none of the charsets is appropriate
				$file_name = $this->image_unknown; // set it to uknknown
			}

			if (!empty($charset_flag)){ // if charset_flag is not empty, i.e. flag has been set to latin, number or cyrillic...
				switch ($charset_flag){ // run through various options to determine the actual filename for the letter avatar
					case 'number':
						$file_name = 'number_' . $file_name;
						break;
					case 'latin':
						$file_name = 'latin_' . $file_name;
						break;
					case 'cyrillic':
						$temp_array = unpack('V', iconv('UTF-8', 'UCS-4LE', $file_name_mb)); // beautiful one-liner by @bobince from SO - http://stackoverflow.com/a/27444149/4848918
						$unicode_code_point = $temp_array[1];
						$file_name = 'cyrillic_' . $unicode_code_point;
						break;
					case 'arabic':
						$temp_array = unpack('V', iconv('UTF-8', 'UCS-4LE', $file_name_mb));
						$unicode_code_point = $temp_array[1];
						$file_name = 'arabic_' . $unicode_code_point;
						break;
					default:
						$file_name = $this->image_unknown; // set it to uknknown
						break;
				}
			}

		}

		// detect most appropriate size based on WP avatar size:
		if ($size <= 48) $custom_avatar_size = '48';
		else if ($size > 48 && $size <= 96) $custom_avatar_size = '96';
		else if ($size > 96 && $size <= 128) $custom_avatar_size = '128';
		else if ($size > 128 && $size <= 256) $custom_avatar_size = '256';
		else $custom_avatar_size = '512';

		// create file path - $avatar_uri variable will look something like this:
		// http://yourblog.com/wp-content/plugins/wp-first-letter-avatar/images/default/96/k.png):
		$avatar_uri =
			plugins_url() . '/'
			. dirname(plugin_basename(__FILE__)) . '/'
			. self::IMAGES_PATH . '/'
			. $this->avatar_set . '/'
			. $custom_avatar_size . '/'
			. $file_name . '.'
			. $this->images_format;

		// return the final first letter image url:
		return $avatar_uri;

	}

	/**
	 * This method generates full URL for Gravatar, according to the $email and $size provided
     *
     * @param string $email
     * @param string $size
     *
     * @return string
	 */
	private function generate_gravatar_uri($email, $size = '96'){

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ // if email not correct
			$email = ''; // set it to empty string
		}

		// email to gravatar url:
		$avatar_uri = self::GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($email)));
		$avatar_uri .= "?s={$size}&r=g";

		return $avatar_uri;

	}
}

// create WP_First_Letter_Avatar object:
$wp_first_letter_avatar = new WP_First_Letter_Avatar();

// require back-end of the plugin
if (is_admin() && !defined('DOING_AJAX')){
	require_once 'wp-first-letter-avatar-config.php';
	// create WP_First_Letter_Avatar_Config object:
	$wp_first_letter_avatar_config = new WP_First_Letter_Avatar_Config();
}
