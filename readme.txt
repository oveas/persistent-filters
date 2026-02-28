=== Persistent Filters ===
Contributors: oveas
Donate link:
Tags: filters, admin, posts, woocommerce, persistence
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Preserves admin list filters for Posts, Pages, and WooCommerce Products. Filters like search terms, ordering and statuses are remembered per user.

== Description ==

Persistent Filters is a lightweight WordPress plugin that preserves admin list filters for Posts, Pages and WooCommerce listings (products, orders and coupons). Once installed and activated, the plugin remembers filter selections like a search string, ordering, product status etc. Filters will be reapplied when you return to the corresponding list screen so you don't have to redo all filtering.

Filters are reapplied automatically when you return to the corresponding list screen so you don't have to redo your filtering.  
All filters are stored **per user per listing**. When a filter is stored for a specific listing, a **Reset Filters** button is added next to the admin list filter options.

== Features ==

* Persistent filters for Posts, Pages and WooCommerce Products, Orders and Coupons in the admin lists.
* Remembers filter types like date, categories, search terms and ordering.
* Per-listing persistence (each post type maintains its own stored filters).
* Lightweight with minimal performance impact.
* Option to remove all filters, for all users of only for the current user.

== Installation ==

1. In the Admin menu select **Plugins**
2. Click the `Add Plugin` button
3. Look for the `Persistent Filters` plugin and click `Install now`
4. Activate Persistent Filters in the **Plugins** screen.

== Frequently Asked Questions ==

= Does this affect front-end queries? =
No, the plugin only saves and reapplies admin list screen query parameters.

= Is persistence shared between users? =
No, all filters are stored per user.

= When I reset my filters, do I lose filters for all post types? =
No, only the filters for the post type currently listed will be reset.

= I just installed the plugin, but I don't see the Reset Filters button. =
Correct, that button is only shown when you actually used a filter for the current post type.

= Can I remove all filters at once? =
Yes, in the **Plugins** screen in your Admin environment, click the `Reset all filters` link next to the `Deactivate` options. You will get a popup where you can select if you want to remove your own  filters only, or for all users.

== Changelog ==

= 1.3.0 =
* Changed the localization to the officially supported way
* Added support for WooCommerce coupons and HPOS orders

= 1.2.0 =
* Fix: Don't add filters if only quicklinks are used, that also caused infite redirects
* Added an option to remove all filters
* Added Dutch translation

= 1.1.0 =
* Fix: disabled fallbaclk by default, that could cause infinite loops
* Added support for WooCommerce Orders

= 1.0.1 =
* Fixed an incomplete commit to svn

= 1.0.0 =
* Initial release: Posts, Pages, and WooCommerce product filter persistence.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

