=== Remove Query Strings From Static Resources ===
Contributors: yourwpexpert
Tags: remove, query, strings, static, resources, pingdom, gtmetrix, yslow, pagespeed
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Remove query strings from static resources like CSS & JS files.

== Description ==
This plugin will remove any query strings from static resources like CSS & JS files, and will improve your speed scores in services like PageSpeed, YSlow, Pingdoom and GTmetrix.

Resources with a “?” or “&” in the URL are not cached by some proxy caching servers, and moving the query string and encode the parameters into the URL will increase your WordPress site performance grade significant.

If you're using a WordPress cache plugin, don't forget to empty your cache before testing your site performance after activating the plugin.

Feel free to contact us if you have any questions about the plugin: [Your WP Expert](http://www.yourwpexpert.com/contact/)

== Installation ==
1. Upload the `remove-query-strings-from-static-resources` folder to the `/wp-content/plugins/` directory

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. That's it!

== Changelog ==
= 1.0 =

* First release