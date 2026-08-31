=== Easy Dashboard ===
Contributors: Milmor
Tags: dashboard, easy, clean, tiny, panel
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 7.1
Version: 2.0
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Replace the default WordPress dashboard with a cleaner welcome page focused on shortcuts, branding, and faster access to content.

== Description ==

Easy Dashboard replaces the standard WordPress dashboard with a focused welcome screen that is easier to use for administrators, editors, clients, and content teams.

Instead of widgets and clutter, users land on a clean dashboard with large menu tiles, a personalized greeting, faster access to content sections, and a layout that follows the current user's role and capabilities.

Easy Dashboard is especially useful for websites with custom post types, editorial workflows, or client users who need a simpler and more guided admin experience.

Features include:

* Replaces the default Dashboard screen with a custom welcome page.
* Shows only the menu sections the current user can actually access.
* Displays large tiles for posts, pages, media, custom post types, and admin sections.
* Adds quick actions for content areas such as opening the list view or creating new content.
* Supports custom post types automatically.
* Includes a branded header with site logo, greeting, and optional tagline.
* Includes an admin-only inline settings panel directly in the welcome page.
* Lets administrators choose built-in color schemes or a custom two-color gradient.
* Updates the header preview in real time while choosing colors.
* Shows a pending updates notice for administrators when WordPress, plugins, or themes need updates.
* Includes a quick button to open the WordPress command search.
* Keeps a one-click link back to the classic WordPress dashboard.

Easy Dashboard aims to stay visually consistent with WordPress while making the first admin screen more useful and easier to understand.

== Frequently Asked Questions ==

= Who can change the dashboard settings? =

Only administrators or users with the `manage_options` capability can edit the welcome page settings.

= Does it work with custom post types? =

Yes. Easy Dashboard automatically shows custom post type menu items when the current user can access them.

= Can I customize the colors? =

Yes. Administrators can choose one of the preset schemes or define a custom two-color gradient directly from the welcome page.

= Can I still use the classic dashboard? =

Yes. The first tile of the second row opens the standard WordPress dashboard with all its widgets. Developers can also disable the redirect entirely with the `easy_dashboard_should_redirect` filter.

= Does it respect user permissions? =

Yes. Tiles and quick actions are rendered according to the current user's WordPress capabilities.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/easy-dashboard` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Open the WordPress admin area. The default Dashboard screen will redirect to Easy Dashboard.
4. If you are an administrator, use the settings icon in the welcome header to customize tagline and colors.

== Screenshots ==

1. The Easy Dashboard welcome page with the branded header and content tiles.
2. Content tiles with quick actions for posts, pages, media and custom post types.
3. The inline settings panel with preset and custom color schemes.


== Changelog ==

= 2.0.1 2026-08-31 =
* Reworked the welcome page layout and branding header.
* Moved dashboard customization into an inline admin-only panel.
* Added preset and custom color schemes with live preview.
* Added pending updates notice for administrators.
* Added quick access button for WordPress command search.
* Improved content tiles with quick actions for common tasks.
* Added a tile linking to the classic dashboard and the `easy_dashboard_should_redirect` filter.
* Moved styles and scripts to separate asset files.
* Tiles now reuse the translated WordPress menu labels, counters excluded.

= 1.7 2026-05-13 =
* Tested for WP 7.0

= 1.6 2025-09-08 =
* Improved code and performance

= 1.5 2025-06-09 =
* Improved code and performance
* Released Github repository

= 1.4 20250526 =
* Improved code and performance
* New design and settings screen

= 1.2.2 20230217 =
* Compatibility check
* Minor improvements

= 1.2.1 20220217 =
* Removed user profile link and logout (duplicated of WP admin bar)
* Compatibility check
* Minor improvements

= 1.2 20200501 =
* Compatibility check
* Minor improvements

= 1.1 17.07.2015 =
* Added option to customize tagline
* Minor changes
* ReadMe changes

= 1.0 26.02.2015 =
* First release
