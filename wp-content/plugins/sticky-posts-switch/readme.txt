=== Plugin Name ===
Contributors: markusfroehlich
Donate link: http://bit.ly/2e9Bhcw
Tags: sticky, switch, post, cpt, admin
Requires at least: 4.0
Tested up to: 4.7.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a sticky post switch functionality to the admin list post/custom post type pages.

== Description ==

This plugin adds a new column to the post admin columns that allows you to easily mark a post so that it is sticky.
Sticky Posts is a WordPress feature only for posts, with this plugin you can use this feature also with custom post types.

= Features of sticky posts =

* enables you to use the sticky posts feature with every custom post type
* quick and bulk edit support for custom post types
* selection of the post type (post or custom post type)
* selection of the color of the switch icon
* customized order of the column showing the switch icon
* use only built-in WordPress functions
* the star-icon switch saves the posts in the sticky status immediately with ajax
* optionally, set all translations of a post sticky, supports [WPML](https://wpml.org) and [MultilingualPress](https://wordpress.org/plugins/multilingual-press)

= Languages =

* English
* German

== Installation ==

1. Upload 'sticky-posts-switch' to the '/wp-content/plugins/' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. You will now have the new column "Sticky Post" in the post admin screen.

== Frequently Asked Questions ==

= Where can I config the plugin? =

In the menu, navigate to the item "Settings -> Sticky Posts"

= Why can't I find my own type of content (custom post type) in the list? =

The only types of content displayed are those declared as public.
See [register post type](https://codex.wordpress.org/Function_Reference/register_post_type).

= I found a bug, what shall I do? =

If you have found a bug in my plugin, please send me an email with a short description.
I will fix the bug as soon as possible.

= You like my plugin and you'd like to support me? =

Thank you very much!
In case you want to show how much you appreciate my work, I'd be very grateful if you could give me positive rating with Wordpress-Page and/or donate a small amount to me.

== Screenshots ==

1. The sticky post column at the admin list post page
2. Plugin settings

== Changelog ==

= 1.5 =
* Code and performance optimizations
* Hide the sticky post column column at the post trash page

= 1.4 =
* Bugfixes in the "the_posts" filter for custom post types

= 1.3 =
* Code optimization
* Translation fixes

= 1.2 =
* Readme update
* Translation fixes

= 1.1 =
* Translation fixes
* Add multilingual support for WPML and MultilingualPress

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.3 =
* Add bulk and quick edit support for custom post types
* Add a icon animation during ajax loading
* Add an ajax queue for a better switch experience
* Update the post states immediately after sticky/unsticky a post
* Redesign of the settings page