# Persistent Filters for WordPress

Persistent Filters is a lightweight WordPress plugin that preserves admin list filters for Posts, Pages and some WooCommerce listings (products, orders and coupons). Once installed and activated, the plugin remembers filter selections like a search string, ordering, product status etc.
Filters will be reapplied when you return to the corresponding list screen so you don't have to redo all filtering.

Filters are stored per user per listing. When a filter is stored for a certain listing type, a `Reset Filter` button is added next to the filter options in the listing.


## Features
- Persistent filters for
  - Posts
  - Pages
  - WooCommerce Products, Orders and Coupons
  - Simple History
- Remember multiple filter types like date, categories, search terms and ordering.
- Per-listing persistence (each listing type keeps its own set of filters).
- Lightweight: minimal performance overhead and no external dependencies.
- Option to remove all filters, for all users or only for the current user.

## Installation

- In the Admin menu select **Plugins**
- Click the `Add Plugin` button
- Look for the `Persistent Filters` plugin and click `Install now`
- Activate Persistent Filters in the **Plugins** screen.

## Usage
- Visit Posts → All Posts, Pages → All Pages, Products → All Products or anu other support listing page.
- Configure the filters you need (status, date, search, etc.) and click Filter.
- The plugin will automatically save those filter settings for the current user and post type.
- When you return to that list screen, your filters are reapplied automatically.
- To clear persisted filters for the current screen use the "Reset filters" button added next to the filter controls.

## Security & Privacy
- Filter state is stored in user meta.
- No external network calls. Data stored only on your site.
- Avoid storing sensitive information in filters (search terms are saved).

## Frequently Asked Questions
Q: Does this affect front-end queries?  
A: No, the plugin only saves and reapplies admin list screen query parameters.

Q: Is persistence shared between users?  
A: No, all filters are stored per user.

Q: When I reset my filters, do I lose filters for all post types?
A: No, only the filters for the post type currently listed will be reset.

Q: I just installed and activated this plugin, but I don't see the Reset filter button.
A: Correct, that button is only shown when you actually used a filter for the current post type.

Q: Can I remove all filters at once? =
A: Yes, in the **Plugins** screen in your Admin environment, click the `Reset all filters` link next to the `Deactivate` option. You will get a popup where you can select if you want to remove your own filters only, or for all users.

## Contributing
Contributions, bug reports and feature requests are welcome. Please open an issue or submit a pull request on the plugin repository.

## License
GNU General Public License GPLv2 — see license.txt for details.

## Changelog (short)
- 1.0.0 — Initial release: Posts, Pages and WooCommerce product filter persistence.
- 1.1.0
  - Fix: disabled fallbaclk by default, that could cause infinite loops
  - Added support for WooCommerce Orders
- 1.2.0 
  - Fix: Don't add filters if only quicklinks are used, that also caused infite redirects
  - Added an option to remove all filters
  - Added Dutch translation
- 1.3.0
  - Changed the localization to the officially supported way
  - Added support for WooCommerce coupons and HPOS orders
- 1.3.1
  - Added support for Simple History
