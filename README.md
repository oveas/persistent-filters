# Persistent Filters for WordPress

Persistent Filters is a lightweight WordPress plugin that preserves admin list filters for Posts, Pages and WooCommerce Products. Once installed end activated, the plugin remembers filter selections like a search string, ordering, product status etc.
Filters will be reapplied when you return to the corresponding list screen so you don't have to redo all filtering.

Filters are stored per user per post type (currently `post`, `page` and `product`). When a filter a stored for a certain post type, a `Reset Filter` button is added next to the filter options in the post listing.


## Features
- Persist filters for Posts, Pages and WooCommerce Products in the admin lists.
- Remember multiple filter types like date, categories, search terms and ordering.
- Per-listing persistence (each post type keeps its own set of filters).
- Lightweight: minimal performance overhead and no external dependencies.
- For unknown post types, search string and ordering are stored..

## Installation
1. Upload the plugin folder to `/wp-content/plugins/persistent-filters` or install via the admin Plugins → Add New → Upload Plugin.
2. Activate the plugin from the Plugins screen.

## Usage
- Visit Posts → All Posts, Pages → All Pages, or Products → All Products.
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

## Contributing
Contributions, bug reports and feature requests are welcome. Please open an issue or submit a pull request on the plugin repository.

## License
GNU General Public License GPLv2 — see license.txt for details.

## Changelog (short)
- 1.0.0 — Initial release: Posts, Pages and WooCommerce product filter persistence.
