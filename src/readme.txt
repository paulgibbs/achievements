=== Achievements for WordPress ===
Contributors: DJPaul
Tags: achievements, badges, challenges, gaming, points, rewards
Requires at least: 3.8
Tested up to: 3.8.20
Stable tag: 3.5.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P3K7Z7NHWZ5CL&lc=GB&item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&currency_code=GBP&bn=PP%2dDon

Achievements gamifies your WordPress site with challenges, badges, and points.

== Description ==

Achievements gamifies your WordPress site with challenges, badges, and points. Badges and points are the funnest way to reward and encourage members of your community to participate. Leaderboards and rankings bring friendly competition to your community.

Simply by activating Achievements, any standard WordPress theme is suddenly capable of having achievements and tracking user progress; everything works out of the box. Achievements integrates seamlessly with your existing WordPress theme.

This plugin supports many of your favourite WordPress plugins, including [bbPress](http://wordpress.org/plugins/bbpress/), [BuddyPress](http://wordpress.org/plugins/buddypress/), [BuddyStream](http://wordpress.org/plugins/buddystream/), [coursewa.re](http://wordpress.org/plugins/buddypress-courseware/), [Invite Anyone](http://wordpress.org/plugins/invite-anyone/), [WP e-Commerce](http://wordpress.org/plugins/wp-e-commerce/), and [WP-PostRatings](http://wordpress.org/plugins/wp-postratings/). For example, have you ever wanted to give your community members points when they contribute to a discussion, or buy items from your store? Now you can.

For information, support, and developer documentation, visit [achievementsapp.com](http://achievementsapp.com/).

[vimeo http://vimeo.com/56058144]

== Changelog ==
= 3.5.1 =
* Maintenance release. Plugin now requires WordPress 3.8+.
* The unlocked achievement check (the heartbeat) now happens much more quickly.
* Very small UI tweaks in the admin to reflect WordPress' new appearance.

= 3.5 =
* NEW FEATURE: Live Notifications!
* Updates to the "achievement unlocked" notification template.
* General performance improvements.

= 3.4.1 =
* Fix leaderboard behaviour with negative karma point totals

= 3.4 =
* NEW FEATURE: Leaderboards!
* NEW FEATURE: Private achievements!
* NEW FEATURE: Support for the [WP-PostRatings](http://wordpress.org/plugins/wp-postratings/) plugin!
* Improve performance on WordPress multisite.
* Improve compatibility with BuddyPress 1.8.1, includes pagination fixes.
* Improve behaviour of the unlocked achievement pop-up; now less annoying.
* Fix problems with the WP-CLI commands.

= 3.3.1 =
* Improves compatibility with [BuddyPress 1.8](http://buddypress.org/2013/07/buddypress-1-8-di-fara/).

= 3.3 =
* NEW FEATURE: "Featured Achievement" widget!
* NEW FEATURE: "Photo Grid" widget!
* NEW FEATURE: Achievements can now be put into categories!
* NEW FEATURE: WP-CLI commands added for server admins/developers!
* Improvements to the achievements list in the WordPress admin; achievement images are now shown, and the table is easier to read.
* Tweaked dpa_has_progress() to make it easier for other developers to make advanced customisations.
* Add new dpa_achievement_image() template function for developers; use this like you would use the_post_thumbnail().
* Updated author credits on the Supported Plugins admin screen for bbPress.
* Props to Mike Bronner for working on the admin UI tweaks, API improvements, and category support.

= 3.2.3 =
* Fix bug where anonymous users weren't seeing the single achievement template load correctly.
* Fix incorrect links on the "you unlocked an achievement!" popup in certain multisite configurations when the plugin has been activated network-wide.
* Fix PHP Notices when the plugin is deactivated.
* Fix custom capabilities not being set up on a new site (in multisite only).
* Clear a user's pending unlock notifications if an admin un-rewards an achievement that the user has a pending notification for.

= 3.2.2 =
* Fix incorrect BuddyPress Activity timestamps.
* Fix problems with events and links when the plugin is activated network-wide on multisite.
* Improve compatibility with BuddyPress 1.7.

= 3.2.1 =
* Fix error when accessing Supported Plugins screen while BuddyPress is active

= 3.2 =
* NEW FEATURE: Full integration with BuddyPress User Profiles and the Activity stream!
* Localisation improvements and fixes; mo files are now loaded from wp-content/languages/plugins/achievements/dpa-??_??.mo

= 3.1 =
* NEW FEATURE: Achievement Redemption -- let your users unlock achievements by entering a code!
* Added RTL CSS.
* Added shortcodes for the user's achievement list and unlock notification templates.
* Fix blog post achievements being attributed to wrong author for draft posts.
* Fixes for some pagination problems.
* The unlock notification popup now closes when you click outside its box.

= 3.0 =
* Massive new version; everything's new.
* Achievements 3 no longer requires BuddyPress.
* Visit [achievementsapp.com](http://achievementsapp.com/) for more information.

= Pre-3.0 =
* The historical release history has been moved to a better place.

== Installation ==
1. Install via WordPress Plugins administration page.
1. Activate the plugin.

To get started, in your WordPress admin area, explore the "Achievements" menu in the main navigation.

Note: in multisite, activating the plugin "network-wide" will give your entire network one shared instance of achievements. If you want each site to run their own achievements and have them be totally seperate, activate the plugin on individual sites.

== Frequently Asked Questions ==
For information, support, and developer documentation, visit [achievementsapp.com](http://achievementsapp.com/).

== Upgrade Notice ==
= 3.5 =
* NEW FEATURE: Live Notifications!
* Updates to the "achievement unlocked" notification template.
* General performance improvements.

= 3.4.1 =
* Fix leaderboard behaviour with negative karma point totals

= 3.4 =
* NEW FEATURE: Leaderboards!
* NEW FEATURE: Private achievements!
* NEW FEATURE: Support for the [WP-PostRatings](http://wordpress.org/plugins/wp-postratings/) plugin!
* Improve performance on WordPress multisite.
* Improve compatibility with BuddyPress 1.8.1, includes pagination fixes.
* Improve behaviour of the unlocked achievement pop-up; now less annoying.
* Fix problems with the WP-CLI commands.

= 3.3.1 =
* Improves compatibility with [BuddyPress 1.8](http://buddypress.org/2013/07/buddypress-1-8-di-fara/).

= 3.3 =
* NEW FEATURE: "Featured Achievement" widget!
* NEW FEATURE: "Photo Grid" widget!
* NEW FEATURE: Achievements can now be put into categories!
* NEW FEATURE: WP-CLI commands added for server admins/developers!

= 3.2.3 =
* Fix bug where anonymous users weren't seeing the single achievement template load correctly.
* Fix incorrect links on the "you unlocked an achievement!" popup in certain multisite configurations when the plugin has been activated network-wide.
* Fix PHP Notices when the plugin is deactivated.
* Fix custom capabilities not being set up on a new site (in multisite only).
* Clear a user's pending unlock notifications if an admin un-rewards an achievement that the user has a pending notification for.

= 3.2.2 =
* Fix incorrect BuddyPress Activity timestamps.
* Fix problems with events and links when the plugin is activated network-wide on multisite.
* Improve compatibility with BuddyPress 1.7.

= 3.2.1 =
* Fixes error when accessing Supported Plugins screen while BuddyPress is active

= 3.2 =
* NEW FEATURE: Full integration with BuddyPress User Profiles and the Activity Stream!

= 3.1 =
* NEW FEATURE: Achievement Redemption -- let your users unlock achievements by entering a code!
* Much more; see http://achievementsapp.com/updates/ for details

= 3.0 =
* IMPORTANT: Read http://achievementsapp.com/upgrading-from-old-versions/ before upgrading.

== Screenshots ==

1. Achievement notification
2. List of user's unlocked achievements
3. List of all available achievements
4. Admin management screen showing a list of all achievements
5. Crop of part of the "Supported Plugins" admin screen
6. Crop of another part of the "Support Plugins" admin screen

== License ==
"Achievements"
Copyright (C) 2009-13 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

== Acknowledgements ==
Achievements is distributed under GPLv3, but we've used some components from other sources:

* /admin/images/select_arrow.gif, and some CSS styling, taken from "Formalize CSS" <http://formalize.me/>, under the MIT License (<http://www.opensource.org/licenses/mit-license.php>).
* Some CSS styling taken from "Twitter Bootstrap" <http://twitter.github.com/bootstrap/> under the Apache License, Version 2.0 (<http://www.apache.org/licenses/LICENSE-2.0.html>).
* Uses the jquery-tablesorter-min.js library from "tablesorter" <http://tablesorter.com/> under the MIT License (<http://www.opensource.org/licenses/mit-license.php>).
* Uses socialite-min.js from "Socialite" <https://github.com/dbushell/Socialite/> under the MIT License (<http://www.opensource.org/licenses/mit-license.php>).
* Uses the "Chosen" jQuery library <https://github.com/harvesthq/chosen> under the MIT License (<http://harvesthq.github.com/chosen/>).
