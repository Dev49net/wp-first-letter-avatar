<?php

/*
	PHP file containing whole back-end of the WP First Letter Avatar plugin.
	WP First Letter Avatar prefix - 'wpfla'
*/


class WP_First_Letter_Avatar_Config {


	public function __construct(){

		add_action( 'admin_menu', array($this, 'wpfla_add_admin_menu' ));
		add_action( 'admin_init', array($this, 'wpfla_settings_init' ));

	}



	public function wpfla_add_admin_menu(  ) {

		add_options_page( 'WP First Letter Avatar', 'WP First Letter Avatar', 'manage_options', 'wp_first_letter_avatar', array($this, 'wpfla_options_page') );

	}



	public function wpfla_settings_init(  ) {

		register_setting( 'pluginPage', 'wpfla_settings' );

		 add_settings_section(
			'wpfla_pluginPage_section',
			'Plugin configuration',
			array($this, 'wpfla_settings_section_callback'),
			'pluginPage'
		);

		add_settings_field(
			'wpfla_letter_index',
			'Letter index<br/>Default: 0',
			array($this, 'wpfla_letter_index_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

		add_settings_field(
			'wpfla_file_format',
			'File format<br/>Default: png',
			array($this, 'wpfla_file_format_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

		add_settings_field(
			'wpfla_unknown_image',
			'Unknown image name<br/>Default: mystery',
			array($this, 'wpfla_unknown_image_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

		add_settings_field(
			'wpfla_avatar_set',
			'Avatar set<br/>Default: default',
			array($this, 'wpfla_avatar_set_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

		add_settings_field(
			'wpfla_use_gravatar',
			'Use Gravatar<br/>Default: check',
			array($this, 'wpfla_use_gravatar_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

		add_settings_field(
			'wpfla_round_avatars',
			'Round avatars<br/>Default: uncheck',
			array($this, 'wpfla_round_avatars_render'),
			'pluginPage',
			'wpfla_pluginPage_section'
		);

	}



	public function wpfla_letter_index_render(){

		$options = get_option('wpfla_settings');
		?>
		<input style="width:40px;" type='text' name='wpfla_settings[wpfla_letter_index]' value='<?php echo $options['wpfla_letter_index']; ?>' />
	<?php

	}



	public function wpfla_file_format_render(){

		$options = get_option('wpfla_settings');
		?>
		<input style="width: 100px;" type='text' name='wpfla_settings[wpfla_file_format]' value='<?php echo $options['wpfla_file_format']; ?>' />
	<?php

	}



	public function wpfla_unknown_image_render(){

		$options = get_option('wpfla_settings');
		?>
		<input type='text' name='wpfla_settings[wpfla_unknown_image]' value='<?php echo $options['wpfla_unknown_image']; ?>' />
	<?php

	}



	public function wpfla_avatar_set_render(){

		$options = get_option('wpfla_settings');
		?>
		<input type='text' name='wpfla_settings[wpfla_avatar_set]' value='<?php echo $options['wpfla_avatar_set']; ?>' />
	<?php

	}



	public function wpfla_use_gravatar_render(){

		$options = get_option('wpfla_settings');
		?>
		<input type='checkbox' name='wpfla_settings[wpfla_use_gravatar]' <?php checked( $options['wpfla_use_gravatar'], 1 ); ?> value='1' />
	<?php

	}



	public function wpfla_round_avatars_render(){

		$options = get_option('wpfla_settings');
		?>
		<input type='checkbox' name='wpfla_settings[wpfla_round_avatars]' <?php checked( $options['wpfla_round_avatars'], 1 ); ?> value='1' />
	<?php

	}



	public function wpfla_settings_section_callback(){

		// leaving this in case I want to add something here in future...

	}



	public function wpfla_options_page(  ) {

		?>
		<form action='options.php' method='post'>

			<h2>WP First Letter Avatar</h2>

			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button();
			?>

			<hr />

			<h3>Fields description:</h3>
			<p>
				<strong>Letter index</strong><br />
				<span style="text-decoration: underline">0</span>: use first letter for the avatar; <span style="text-decoration: underline">1</span>: use second letter; <span style="text-decoration: underline">-1</span>: use last letter, etc.
			</p>
			<p>
				<strong>File format</strong><br />
				File format of your avatars, for example <span style="text-decoration: underline">png</span> or <span style="text-decoration: underline">jpg</span>.
			</p>
			<p>
				<strong>Unknown image name</strong><br />
				Name of the file used for unknown usernames (without extension).
			</p>
			<p>
				<strong>Avatar set</strong><br />
				Directory where your avatars are stored.
			</p>
			<p>
				<strong>Use Gravatar</strong><br />
				<span style="text-decoration: underline">Check</span>: use Gravatar when available; <span style="text-decoration: underline">Uncheck</span>: always use custom avatars.
			</p>
			<p>
				<strong>Round avatars</strong><br />
				<span style="text-decoration: underline">Check</span>: use rounded avatars; <span style="text-decoration: underline">Uncheck</span>: use standard avatars.
			</p>
			<p>In case of any problems, use the default values.</p>

			<hr />

			<p style="text-align: right; margin-right:30px">WP First Letter Avatar was created by Daniel Wroblewski</p>

		</form>
	<?php

	}

}



// create WP_First_Letter_Avatar_Config object:
$first_letter_avatar_config = new WP_First_Letter_Avatar_Config();
