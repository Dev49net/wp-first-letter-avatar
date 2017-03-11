=== WP First Letter Avatar ===
Plugin Name: WP First Letter Avatar
Version: 2.2.8
Plugin URI: http://dev49.net
Contributors: Dev49.net, DanielAGW
Tags: avatars, comments, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar, initial avatar
Requires at least: 4.6
Tested up to: 4.7
Stable tag: trunk
Author: Dev49.net
Author URI: http://dev49.net
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Set custom avatars for users with no Gravatar. The avatar will be the first (or any other) letter of user's name on a colorful background.

== Description ==

WP First Letter Avatar **sets custom avatars for users without Gravatar**. The avatar will be a first letter of the user's name. You can also configure the plugin to use any other letter to set custom avatar.

WP First Letter Avatar includes a set of **beautiful, colorful letter avatars** in many sizes. Optimal size will be chosen by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is needed... **PSD template** for avatar is also included.

You can also create your own avatar set by creating new directory next to *'default'* folder and following the naming convention from *'default'*. 

By default, custom avatar will be set only to users without Gravatars, but you can change that in settings and not use Gravatar at all.

WP First Letter Avatar helps you **bring more colors** into your blog. Plus, your readers will be more **willing to comment on your posts**, since they can actually relate to these avatars much better than to Mystery Person.

All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are **incredibly light and ultra-high quality**.

WP First Letter Avatar is also available [on GitHub](https://github.com/Dev49net/wp-first-letter-avatar).

= Compatibility with other plugins =
WP First Letter Avatar is fully compatible with [bbPress](https://bbpress.org/) and [wpDiscuz](http://www.gvectors.com/wpdiscuz/). For [BuddyPress](https://buddypress.org/) compatibility please use my other plugin - [BuddyPress First Letter Avatar](https://wordpress.org/plugins/buddypress-first-letter-avatar/).

= Requirements =
WP First Letter Avatar requires at least PHP 5.4. It **does not work properly** on PHP 5.3.x and earlier.

== Installation ==

= From WordPress dashboard =

1. Go to *'Plugins > Add New'*.
2. Search for *'WP First Letter Avatar'*.
3. Activate *'WP First Letter Avatar'* in *'Plugins'* page.
4. Plugin works right out of the box. For additional configuration, go to *'Settings > WP First Letter Avatar'*.

= Manual installation =

Extract the zip file and drop the contents in *'wp-content/plugins/'* directory of your WordPress installation, then activate the Plugin from *'Plugins'* page.

== Frequently Asked Questions ==

= Plugin does not work, what should I do? =

There may be some conflict with this plugin and some other plugins you are using. If WP First Letter Avatar is overriding your other avatar plugins, please go to plugin settings and change Filter Priority value to a lower value - for example 9, or even -1. If other plugins are overriding WP First Letter Avatar images, try increasing the value to 11 or 9999. Experimenting with these values should give you some results. Filter priority value basically specifies the order that avatar filters are executed in. Setting it to a high value will cause WP First Letter Avatar to execute after other plugins, whereas setting it to a low value will execute WP First Letter Avatar before other plugins.

= Can I change custom avatars? =

Absolutely! Just create new directory in 'images' directory, call it, for example 'my_avatar_set' and change the avatar set in settings. Make sure to follow the directory and filename convention. 
NOTE: Your custom avatars WILL BE DELETED after updating the plugin! Make backup! 

= Can I set custom avatars based on last (or any other) character in user's name? =
Of course! This can be done in plugin settings.

= I don't want to use Gravatar at all. Can I disable it? =
Yes! By default, WP First Letter Avatar sets custom avatar only to users without Gravatar, but in plugin settings you can disable it and use custom avatar for everybody.

= Is WP First Letter Avatar compatible with other plugins? =
WP First Letter Avatar has fully tested compatibilty with bbPress and wpDiscuz. For BuddyPress compatibility, please use my other plugin - [BuddyPress First Letter Avatar](https://wordpress.org/plugins/buddypress-first-letter-avatar/).

= Can avatars be round, like in Google+? =
Yes - just go to plugin settings and click Round avatars.

== Screenshots ==

1. This shows three comments with first letter avatars (these commenters don't have their Gravatars) and one with standard Gravatar.
2. Two comments with custom first letter avatars.
3. Set of alphabet avatars in WP First Letter Avatar.
4. Very simple settings page for WP First Letter Avatar. You can decide which character should be used to specify avatar, turn off Gravatar, use custom avatar sets, use rounded avatars etc.

== Changelog ==

= 2.2.8 =
* Added 2 new avatar sets (opensans and roboto) - thanks flector!

= 2.2.7 =
* Added option to pass additional arguments to get_avatar() - thanks dpsjorge! (for developers only)

= 2.2.6.1 =
* Fixed get_currentuserinfo() function deprecated notice

= 2.2.6 =
* Fixed undeclared variable notice
* Fixed mbstring extension error

= 2.2.5 =
* Added Polish translation
* Fixed problem with bbPress avatars

= 2.2.4 =
* Added fallback for Polish letters (thanks Micha�!)
* Plugin prepared for translations (contributors are welcome!)

= 2.2.3 =
* Fixed possible PHP error on activation due to anonymous function used

= 2.2.2 =
* Added support for Arabic letters (huge thanks to **@AmiNimA**)
* Added latest wpDiscuz compatibility
* Fixed possible PHP error

= 2.2.1 =
* Fixed problem with filter priority value

= 2.2 =
* Added support for numbers
* Added support for Cyrillic script (huge thanks to **@collex**)
* WordPress 4.4 ready
* Small fix: changed description of filter priority value in settings (thanks to **@yolandal**)

= 2.1.1 =
* Improved coding style (resulting in possibly slightly better performance)

= 2.1 =
* Redesigned Gravatar/first letter avatar choice mechanism (faster and more reliable performance)

= 2.0.1 =
* Fixed possible problem with verifying Gravatars

= 2.0 =
* WordPress 4.3 ready (fully tested)
* Fixed fatal error on PHP 5.3 (now plugin simply won't activate on PHP lower than 5.4)

= 1.2.8 =
* Greatly improved security of AJAX requests
* Added new feature - filter priority (only for advanced users)
* Fixed possible compatibilty issues with other plugins by adding prefix to couple of global JS variables
* Fixed weird error some users experienced (avatars displaying as letter A for every user)
* Asynchronous JavaScript Gravatar verification now as default option for new plugin users
* No longer need to activate plugin on Settings > Discussion page (it was causing problems)
* Changed plugin author from myself to my brand - Dev49.net :-)

= 1.2.7 =
* Fixed couple of minor issues
* Improved JavaScript Gravatar loading
* Added new default avatar in Settings > Discussion page
* Plugin options removed from database after uninstalling (no DB leftovers after uninstalling)
* Added protection disallowing activating plugin on PHP < 5.4 and WP < 4.0

= 1.2.6 =
* PHP 5.4.x or later REQUIRED: PHP 5.3.x is no longer supported by PHP team, if you are still using it - update immediately
* Added asynchronous Gravatar loading for faster page rendering (needs to be activated in plugin Settings)
* Added auto-check to see if one or more options in plugin Settings are not empty
* Fixed standard avatars replacement on Discussion page in Settings
* Couple of minor fixes

= 1.2.5 =
* Fixed common PHP warning
* Fixed avatar presentation of logged-in users in their userbars

= 1.2.4 =
* Fixed couple of small technical issues

= 1.2.3 =
* Improved avatar appearance on top admin/user bar
* Added full compatibility with bbPress plugin

= 1.2.2 =
* Fixed conflicts with some comment systems (such as wpDiscuz)

= 1.2.1 =
* Avatar is now in the right position in dashboard (in previous versions it used to be in bottom left corner instead of upper right corner)
* Optimized database readings (for plugin settings)

= 1.2 =
* Added round avatars option - you can turn it on in plugin settings

= 1.1 =
* Fixed PHP "Missing argument" error

= 1.0 =
* First WP First Letter Avatar release

== Upgrade Notice ==

= 2.2.8 =
Added two new avatar sets - check it out (roboto and opensans)!

= 2.2.7 =
Added new feature for developers, update not necessary.

= 2.2.6.1 =
* Removed deprecated function - update recommended if you are using WP 4.5.x

= 2.2.6 =
Fixed minor issues, updated recommended.

= 2.2.5 =
Fixed problem with bbPress and possibly other plugins - update recommended.

= 2.2.4 =
Added fallback for Polish letters - update not necessary.

= 2.2.3 =
Fixed possible PHP error on activation. Update not necessary.

= 2.2.2 =
Added support for Arabic letters. Update not necessary.

= 2.2.1 =
Fixed filter priority issue. Update strongly recommended.

= 2.2 =
Added support for numbers and Cyrillic script. Update not necessary.

= 2.1.1 =
Slightly improved performance. Update not necessary.

= 2.1 =
Improved performance and reliability. Update recommended.

= 2.0.1 =
Fixed possible Gravatar incompatibility. Update recommended.

= 1.2.8 =
Fixed couple of issues, added new features. Update recommended.

= 1.2.7 =
Fixed couple of issues, added new features. Update recommended.

= 1.2.5 =
This version fixes annoying PHP warning. Update recommended.

= 1.2.4 =
This version fixes couple of small technical issues. No need to update unless you have experienced any problems with the plugin.

= 1.2.3 =
This version introduces full compatibility with bbPress and fixes some issues with avatars on user/admin bar. Update recommended.

= 1.2.2 =
This version fixes conflicts with some comment systems (such as wpDiscuz) and slightly improves plugin performance. Update recommended.

= 1.2.1 =
This version fixes avatar placement in user dashboard and improves database reads - update as soon as possible.

= 1.2 =
Added new feature (rounded avatars, can be turned on in plugin settings). Update not necessary.

= 1.1 =
This version fixes a PHP "Missing argument" error - upgrade as soon as possible.

= 1.0 =
First WP First Letter Avatar release.