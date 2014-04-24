=== jQuery Pin It Button For Images ===
Contributors: mrsztuczkens, redearthdesign, brocheafoin, robertark
Donate link: http://bit.ly/Uw2mEP
Tags: pinterest, pin it, button, image, images, pinit, social media, hover, click, photo, photos
Requires at least: 3.3.0
Tested up to: 3.6.1
Stable tag: 1.31
License: GPLv2 or later

Highlights images on hover and adds a Pinterest "Pin It" button over them for easy pinning.

== Description ==
If you're looking for an easy way to pin images in your blog posts and pages, this plugin will help you with that. It highlights images and adds a "Pin it" button over them once the user hovers his mouse over an image. Once the user clicks the "Pin it" button, the plugin shows a pop-up window with the image and a description. Everything is ready for pinning, although the user can alter the description.

The plugin allows you to:

* choose from where the description should be taken (possible options: page title, page description and alt/title tags from the image)
* choose which pictures shouldn't show the "Pin it" button (using classes)
* choose which pictures should show the "Pin it" button (all images, post images, images with certain class(es))
* choose if you want to show the "Pin it" button on home page, single posts, single pages or category pages
* disable showing the button on certain posts and pages (works only on single posts and single pages)
* choose transparency level depending on your needs
* use your own Pinterest button design

Once you activate the plugin, it's ready to go with the typical settings - button appears on all images within the body of your posts/pages that aren't marked with "nopin" or "wp-smiley" classes.

**Translators**

- Spanish (es_ES) -  Andrew Kurtis [WebHostingHub](http://www.webhostinghub.com/)

**Please report any bugs (or feature requests) in the plugin's support forum.**

Check out the new features in the Changelog.

Please note that the plugin doesn't work if the user has Javascript disabled.

If you want to learn more about the plugin, visit its website: http://mrsztuczkens.me/jpibfi/

Please consider donating any spare change to help me work on this plugin more. Donations can be made at: http://bit.ly/Uw2mEP

(This plugin is not related to or endorsed by Pinterest or its affiliates)


== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configuration interface can be found under `Settings - jQuery Pin It Button For Images`. There's also a link to the plugin settings in the "Installed plugins" menu.

== Frequently Asked Questions ==

= Where can I change the plugins settings? =

Configuration interface can be found under `Settings - jQuery Pin It Button For Images`. There's also a link to the plugin settings in the "Installed plugins" menu.

= How do I add the button only to specific images? =
On the plugin settings page, there is a "Enabled classes" setting. Please enter there a class (or classes) that should show the "Pin it" button. Please note that images that don't contain any of the classes added in this setting won't show the "Pin it" button.

= How do I disable the button on specific images? =
Use the "Disabled classes" setting on the settings page - add there specific classes or use the "nopin" class.

= Can I use my own "Pin it" button design? =
 Yes. On the settings page, there's a section named "Custom Pit It button". You need to check the Use custom image checkbox and provide a URL address of the image, image's width and height.

 To upload you own image, you can use **Media Library** on your Wordpress installation or an image hosting service like **Photobucket**. Make sure you provide the proper address, width and height of the image. Otherwise, the button won't be displayed properly or won't be displayed at all.

= Where do I report bugs, improvements and suggestions? =
Please report them in the plugin's support forum on Wordpress.org.

== Screenshots ==

1. Base image in a blog post
2. Highlighted image and "Pin it" button on hover
3. Settings panel
4. Pinterest pop-up window

== Changelog ==

= 1.31 =
* Release 2014-03-13
* Important fix

= 1.30 =
* Release 2014-03-13
* PHP code redesign - plugin is much more extension friendly
* Deleted some of the compatibility-with-older-versions code

= 1.21 =
* Released 2014-02-22
* Fixed one issue from the previous release

= 1.20 =
* Released 2014-02-16
* Major JavaScript code redesign

= 1.17 =
* Released 2013-12-10
* Minor bug fix
* Added Spanish translation

= 1.16 =
* Released 2013-11-21
* Minor bug fix

= 1.15 =
* Released 2013-11-06
* Added 'Image description' option to 'Description source' option

= 1.14 =
* Released 2013-10-17
* Minor bug with linking images to posts fixed
* Plugin now supports Retina displays

= 1.13 =
* Released 2013-10-11
* Few minor code changes
* Plugin is translation-ready

= 1.12 =
* Released 2013-10-01
* One minor bug fixed

= 1.11 =
* Released 2013-08-25
* Two minor bugs fixed

= 1.10 =
* Released 2013-08-21
* Added dynamic mode that allows users to download the image and fixes many issues with the transparency layer
* Removed the ability to add custom css to the Pin It button, but added the ability to change margins

= 1.00 =
* Released 2013-08-09
* Major source code redesign
* Small changes in how the plugin works on client side
* WordPress-style settings panel
* Fixed a little glitch from previous version

= 0.0.99 =
* Released 2013-07-18
* Major changes in source code (mostly JavaScript), but little changes in features (few minor bugs/issues should be fixed)

= 0.9.95 =
* Released 2013-04-28
* Bug fixed: issue with pinning images with hashtags in their title/alt
* New feature: possibility to change the position of the "Pin it" button

= 0.9.9 =
* Released 2013-04-04
* Bug fixed: showing "Pin it" button on categories and archives even though they are unchecked in the settings
* New feature: possibility to set minimum image size that triggers the "Pin it" button to show up
* New feature: option to always link the image to its post/page url, instead of linking to the url the user is currently visiting
* Improvement: you now can set "Site title" as the default description of the pin

= 0.9.5 =
* Released 2013-03-04
* Fixed some issues with image sizing and responsive themes
* Code refactoring
* Added preview in the settings panel
* New feature: adding images using media library

= 0.9.2 =
* Released 2013-02-12
* It now works with jQuery versions older than 1.7

= 0.9.1 =
* Released 2013-02-12
* Bug fixed: resizing images when their dimensions are larger than the container they're in
* Bug fixed: plugin not working when jQuery added multiple times
* Bug fixed: wrong image url when images are lazy-loaded

= 0.9 =
* Released 2013-01-28
* Feature: Ability to use custom Pinterest button design

= 0.8 =
* Released 2013-01-12
* Feature: Ability to choose transparency level depending on one's needs
* Added support for default Wordpress align classes, so the plugin doesn't mess up the positioning of the images on screen (in typical cases)

= 0.7.1 =
* Released 2012-12-20
* Bug related to deleting and quick-editing posts fixed

= 0.7 =
* Released 2012-12-18
* Feature: Ability to show or hide the "Pin it" button on home page, single page, single post and categories (with archives)
* Feature: Ability to disable the "Pin it" button on certain post or page, works only on single post/page view
* Added security checks using Nonces

= 0.5 =
* Released 2012-12-9
* Feature: Pinterest window opens as a pop-up
* Feature: Ability to exclude certain classes from showing the "Pin it" button
* Feature: Ability to include only certain classes that will show the "Pin it" button
* Feature: Image is highlighted once hovered
* Feature: IE7 image highlight fix: using a transparent png instead of background-color

== Upgrade Notice ==

= 1.31 =
Important fix.

= 1.30 =
PHP code redesign.

= 1.21 =
Fixed one issue from the previous release.

= 1.20 =
Just code redesign.

= 1.17 =
Minor bug fix and Spanish translation added.

= 1.16 =
Minor bug fix.

= 1.15 =
Adds 'Image description' option to 'Description source' option.

= 1.14 =
Minor bug fix plus support for Retina displays added.

= 1.13 =
Minor code changes, plus plugin is now translation-ready.

= 1.12 =
One minor bug fix.

= 1.11 =
Two minor bug fixes, that's all.

= 1.10 =
This update is recommended for people who had issues with version 1.00 but version 0.99 worked flawlessly. It adds a new mode that allows users to download images and fixes those issues related to version 1.00.

= 1.00 =
Major source code redesign, new settings panel and fix to a little glitch from previous version of the plugin.

= 0.9.99 =
Major source code changes with almost no changes in terms of features. This version can be considered a "test" one. After fixing bugs (if there are any - please report) version 1.0.0 will be published.

= 0.9.95 =
Minor bug fixed and one new feature (setting the position of the "Pin it" button) added.

= 0.9.9 =
A minor bug fixed and two new features (minimum image size among them) added.

= 0.9.5 =
Few minor bug fixes and tweaks

= 0.9.3 =
Fixed bugs with image sizing and responsive themes

= 0.9.2 =
Small update - plugin now works with jQuery versions older than 1.7.

= 0.9.1 =
Few bugs reported by users got fixed.

= 0.9 =
New feature: using custom Pinterest button design

= 0.8 =
Additional feature and added support for basic image positioning.

= 0.7.1 =
Critical bug fix, please update.

= 0.7 =
Additional features and some security enhancements.

= 0.5 =
First version of the plugin.