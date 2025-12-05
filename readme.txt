=== Persistent Filters ===
Contributors: oveas
Donate link:
Tags: filters, admin, posts, woocommerce, persistence
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Preserves admin list filters for Posts, Pages, and WooCommerce Products. Filters like search terms, ordering and statuses are remembered per user.

== Description ==

Persistent Filters is a lightweight WordPress plugin that preserves admin list filters for Posts, Pages and WooCommerce Products. Once installed end activated, the plugin remembers filter selections like a search string, ordering, product status etc. Filters will be reapplied when you return to the corresponding list screen so you don't have to redo all filtering.

Filters are reapplied automatically when you return to the corresponding list screen so you don't have to redo your filtering.  
All filters are stored **per user per post type** (currently, `post`, `page`, and `product` are supported).  
When a filter is stored for a specific post type, a **Reset Filters** button is added next to the admin list filter options.

== Features ==

* Persist filters for Posts, Pages, and WooCommerce Products in admin lists.
* Remembers filter types like date, categories, search terms and ordering.
* Per-listing persistence (each post type maintains its own stored filters).
* Lightweight with minimal performance impact.
* For unknown post types, search string and ordering are stored.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/persistent-filters` or install via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen.

== Frequently Asked Questions ==

= Does this affect front-end queries? =
No, the plugin only saves and reapplies admin list screen query parameters.

= Is persistence shared between users? =
No, all filters are stored per user.

= When I reset my filters, do I lose filters for all post types? =
No, only the filters for the post type currently listed will be reset.

= I just installed the plugin, but I don't see the Reset Filters button. =
Correct, that button is only shown when you actually used a filter for the current post type.

== Changelog ==

= 1.0.1 =
* Fixed an incomplete commit to svn

= 1.0.0 =
* Initial release: Posts, Pages, and WooCommerce product filter persistence.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

