=== Integral MailChimp ===
Contributors: Jamie Currie, Varr Willis
Donate Link: http://integralwp.com
Author URI: http://integralwp.com
Plugin URI: http://integralwp.com/plugins/complete-mailchimp-plugin-for-wordpress/
Tags: MailChimp, email marketing, email lists, segments, merge tags
Requires at least: 3.9
Tested up to: 4.0
Stable tag: 1.10.10
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Fully Integrated MailChimp Plugin for WordPress




== Description ==

Design, target & send emails directly from WP using MailChimp templates! Supports user sync, signup forms, stats & more.




= Plugin Features =

MailChimp provides best-in-class email communication tools. WordPress provides best-in-class content management tools. Integral MailChimp for WordPress provides the only tool to let them work seamlessly together.

What can it do? What can't it do is a better question!

*   Seamlessly Sync Your Subscriber Data Between WordPress and MailChimp. Not just default fields like names and email addresses, but any information you’re collecting. From zip codes to purchase histories, you can build smart lists for targeted group sending that maximize the effectiveness of your email campaigns, all of which are updated automatically. Batch sync'ing lets you update entire lists with the push of a button.

*   Email Signup Widget. Add as many sign-up forms as you like, with support for interest groups and the ability to capture any custom merge fields you're using. Optionally create new WordPress user accounts or simply pass the information on to MailChimp.

*   Design And Send Campaigns Directly From WordPress. The Integral Email plugin brings all of the tools of MailChimp’s email editor to the WordPress dashboard. Choose from dozens of MailChimp’s high quality email templates; add your own images and text via our visual editor (similar to the MailChimp designer); send previews to specific addresses or lists; and schedule and send emails, all from your own WordPress site.

*   In-Depth Analytics At Your Fingertips. See open rates, click-throughs, bounces and more. No more firing off messages blindly and hoping they have an impact. Now you can monitor every send and learn in real time what is and isn’t effective.

*   Send To The Right People At The Right Time. MailChimp’s segmented sending is a powerful tool that lets you optimize the odds your messages will lead to action. Our built-in segmenting support lets you take advantage of this to target emails based on virtually anything you might track, from interest groups to recent user activity.

*   Automatic Webhook Registration. Sending data from WordPress to MailChimp is great, but what happens when a subscriber clicks the update preferences link in your email and makes changes directly to their MailChimp profile? Our plugin automatically creates Webhooks -- callback actions -- that keep data sync'd in both directions.

*   Developer Friendly & Readily Extensible. We have provided numerous hooks and filters, plus thorough documentation and code example, so you can quickly and easily add new functionality. Let's say you use a custom registration form and collect users' favorite NFL team. With just a couple of lines of code in your functions.php file you can make that field available as a sync field that you can drag-and-drop enable/disable just like any other. Need help getting something to work? We're here to help!

*   Superior Support. Have questions? We offer support forums, tutorials and how-to videos. Plus, as an Integral customer your queries will be answered directly by our developers -- no call centers asking you to reboot your router!

*   Money Back Guarantee. We’re confident that we’ve created a tool that will create value for our customers. How can we be so sure? Because we’re using it ourselves on client sites that process millions of dollars a year in transactions. But if, for any reason, you decide it’s not for you, we’ll gladly issue a full refund within 30 days of purchase.




== Installation ==

1.  Upload your plugin folder to the '/wp-content/plugins' directory.
1.  Activate the plugin through the 'Plugins' menu in WordPress.
1.  Follow the Setup Wizard in the Dashboard to complete the installation




== Frequently Asked Questions ==

FAQ's coming soon!




== Changelog ==

= 1.10.10 =
* Fix: Small fix for the admin menus

= 1.10.9 =
* Tweak: Modified how menu items are being generated to support compatibility with 3rd party menu plugins

= 1.10.8 =
* Tweak: Major performance improvement

= 1.10.7 =
* Fix: Implemented improved batch syncing for better performance and faster syncing

= 1.10.6 =
* Fix: Small fix for localization support

= 1.10.5 =
* Fix: Small fix for the subscribe widget

= 1.10.4 =
* New: Localization support for German, Spanish, French, Italian, Norwegian, and Russian

= 1.10.3 =
* Tweak: Improved support for multisite installs
* New: Added support for placing subscribe widget form inside a modal popup window

= 1.10.2 =
* Fix: Small fix to support batch user syncing
* Tweak: Switched from cURL to the builtin WP HTTP mechanism for fetching API data

= 1.10.1 =
*   Fix: Issue with WYSIWYG link creator breaking Campaign Editor page
*   New: Added hook for unsubscribing members
*   New: Added filter for changing unsubscribe options
*   Tweak: Enabled use of minified css and js files 

= 1.10 =
*   Fix: Corrected use of set_site_transient() for system caching
*   Fix: Disabled user subscribing and updating if no email provided
*   Fix: Fixed issue with user templates populating with gallery templates
*   Tweak: Added new error response regarding SSL Peer Verification if the API Key validation fails

= 1.9 =
*   Fix: Issue with the New Email Campaign page not operating correctly (failed JS file load)

= 1.8 =
*   Fix: Issue where merge tag values were not getting synced
*   Fix: Unchecked checkboxes not being correctly updated in admin options
*   Fix: Removed unnecessary loading of bootstrap and bootstrap-datepicker styles on front end
*   Fix: Removed permission check that would prevent users not allowed to access the plugin from accessing other admin settings
*   New: Added filter so users set the required permissions for the plugin in their functions.php file
*   New: Added ability to conditionally load sign up form themes -- implemented but no themes included yet

= 1.7 =
*   Fix: Changed permissions level to edit_others_posts. Means plugin requires user role to be at least editor. Previously required admin role.
*   Fix: Issue with incorrectly processing batch syncing on List Management page
*   Fix: Tinymce forced_root_block and keep_styles values so that multi-paragraph text updates format correctly

= 1.6 =
*   Tweak: Updated the minimum WordPress version to 3.9

= 1.5 = 
*   Fix: Issue with excessive calls for List Groupings 
*   Fix: Issue with creating campaign when no segment selected 
*   Fix: Modal dialog not clearing if multiple windows are opened consecutively on Admin Options page 
*   Fix: Clearfix style issue in Firefox 
*   Tweak: Disabled the "Insert Image From URL" option in the Media Uploader 
*   Tweak: Forced new-email.js to load only on the campaign editor page 

= 1.4 = 
*   Fix: Issue with duplicate notifications after user updating
*   Tweak: Improved functionality of Plugin Updater

= 1.3 = 
*   Fix: Issue with certain templates showing up as blank in the Campaign Editor
*   New: Added new Debug Log page once Debugging is turned on in Admin Options

= 1.2 =
*   Fix: Issue with segment sending
*   Fix: Issue with Webhook updates not applying in WordPress
*   Fix: Issue with image dimensions being sent in campaign
*   New: Added new "Setup" section in Admin Options with buttons for clearing API Data and Registering Webhooks
*   New: Added improved security for registering Webhooks

= 1.0 =
*   First release




== Upgrade Notice ==

= 1.10.10 =
* Fix: Small fix for the admin menus

= 1.10.9 =
* Tweak: Improved compatibility with 3rd party admin menu plugins

= 1.10.8 =
* Tweak: Major performance improvement

= 1.10.7 =
* Implemented improved batch syncing for better performance and faster syncing

= 1.10.6 =
* Small fix for localization support

= 1.10.5 =
* Small fix for the subscribe widget

= 1.10.4 =
Added localization support for German, Spanish, French, Italian, Norwegian, and Russian

= 1.10.3 =
Additional support for multisite and new modal window support for subscription form

= 1.10.2 =
Important fix for those getting API Key activation failures

= 1.10.1 =
Important fix for the Campaign Editor's WYSIWYG link tool and some new hooks and filters for unsubscribing users

= 1.10 =
Important fix to support multisite installations

= 1.9 =
Important fix for creating New Email Campaigns

= 1.8 =
Important fix for merge tag syncing, fixed styles loading unnecessarily on front end. Added a filter for setting the required permission level for the plugin. Added sign-up form theme bundles support

= 1.7 =
Improvements for user permissions, batch syncing and email editing

= 1.6 =
Updated the minimum WordPress version to 3.9

= 1.5 =
Several minor bug fixes and performance improvements

= 1.4 =
Update manually to ensure automatic updates on future releases

= 1.3 =
Added a debug log & ticket submission form for easy support

= 1.2 =
Several fixes to segments, webhooks and templates as well as some general security fixes.

= 1.0 =
This is the initial release.


