WP First Letter Avatar
==============

A WordPress plugin to set custom avatars for users with no Gravatar. The avatar will be a first (or any other) letter of 
the users's name.

## Purpose

This plugin was inspired by avatars used on some [Discourse forums](http://www.discourse.org/). What it does is, by 
default, check if commenter has a Gravatar assigned to his email address; if he has, Gravatar is displayed, if not - custom
avatar is used. Custom avatar consists of commenter's name first letter and colorful background. 
WP First Letter Avatar helps you bring more colors into your blog. Plus, your readers will be more willing to comment
on your posts since they can actually relate to these avatars much better than to Monsters or Mystery Man.
WP First Letter Avatar includes a set of beautiful, colorful letter avatars in many sizes. Optimal size will be chosen 
by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is
needed... PSD template for avatar is also included - it's a simple Stag Sans Thin letter. 
Plugin is configurable - you can disable Gravatar, choose different letter, use custom sets of avatars etc.
All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are incredibly light and ultra-high 
quality.

## Installation

You can download a
[zip from GitHub](https://github.com/DanielAGW/wp-first-letter-avatar/archive/master.zip) and upload it using the WordPress
plugin uploader or manually unzip it and place in ```wp-content/plugins/```. 

## Configuration

Configuration is very simple. Here are configuration options available in options:
**Letter index**
0: use first letter for the avatar; 1: use second letter; -1: use last letter, etc.
**File format**
File format of your avatars, for example png or jpg.
**Unknown image name**
Name of the file used for unknown usernames.
**Avatar set**
Directory where avatars are stored.
**Use Gravatar**
Check: use Gravatar when available; Uncheck: always use custom avatars.

## Issues
If you notice any errors or have an idea for improving the plugin, please
[open an issue](https://github.com/DanielAGW/wp-first-letter-avatar/issues?state=open).