=== Footer Link Manager ===
Contributors: ko
Tags: links, link manager, footer links, shortcode, categories
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.4.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create categorized, ordered link lists and place them anywhere WordPress accepts a shortcode.

== Description ==

Footer Link Manager provides a focused administration area for reusable navigation and resource links. Each link can point to a custom URL, page, post, or WordPress category and has an organizational Link Category, display order, and same-tab or new-tab behavior.

Use it for internal footer navigation, resource lists, partner links, or any group of links that should be managed independently of page content.

This is an Ocular Acumen plugin. It is not part of the Vela plugin family.

== Installation ==

1. Upload the `ko-link-manager` folder to `/wp-content/plugins/`, or install its ZIP through Plugins > Add New > Upload Plugin.
2. Activate Footer Link Manager.
3. Open Link Manager in WordPress Admin.
4. Create categories, such as Footer, Resources, or Partners.
5. Add links and assign their display order.
6. Place a `[ko_links]` shortcode where the list should appear.

== Usage ==

Display every link:

`[ko_links]`

Display links from a category using its slug or term ID:

`[ko_links category="footer"]`

`[ko_links category="12"]`

Add a custom heading:

`[ko_links category="footer" title="Explore"]`

When `title` is omitted for a valid category, the category name is used. Links are ordered from the lowest display-order number to the highest, then alphabetically when order values match.

Relative destinations such as `/about/` are treated as internal URLs. Links set to open in a new tab include `rel="noopener"`.

== Frequently Asked Questions ==

= Can I use this for internal footer navigation? =

Yes. Use relative URLs such as `/about/` and leave “Open in a new tab” unchecked for normal page-to-page navigation.

= Does it add noreferrer to internal links? =

No. New-tab links receive `noopener`; `noreferrer` is not added, so internal referral information remains available.

== Changelog ==

= 2.4.4 =
* Renamed the plugin and administration interface to Footer Link Manager.

= 2.4.3 =
* Preserved category-specific drag order when links are created or modified.
* Appended newly assigned links without rebuilding or resetting the saved category sequence.
* Removed stale link IDs from the saved sequence during category membership updates.

= 2.4.2 =
* Clarified the Overview CSS reference with the element-specific `ul.ko-links` selector for theme overrides.

= 2.4.1 =
* Added a front-end CSS selector reference to the Overview usage instructions.

= 2.4.0 =
* Added an Overview usage guide covering category creation, link assignment, drag-and-drop ordering, and shortcode placement.
* Added direct workflow links for new users.

= 2.3.1 =
* Expanded the Destination column and improved long URL wrapping in the Links table.
* Tightened secondary list-table columns to prioritize link destinations.

= 2.3.0 =
* Added category-specific drag-and-drop link ordering to each Link Category edit screen.
* Applied saved category order to category-filtered shortcode output.
* Normalized JSON-escaped quotes in detected shortcodes from theme builders such as Divi.

= 2.2.2 =
* Added a Manage Link Categories button to the Overview dashboard.

= 2.2.1 =
* Expanded placement detection to hidden theme-builder and plugin content types.
* Added active parent and child theme PHP file scanning for template placements.
* Added source labels and safe fallbacks for placements without an editor link.

= 2.2.0 =
* Added copy-ready shortcodes to the Link Categories list and category editor.
* Added an Overview placement reference for shortcodes found in WordPress post-type content.
* Added one-click shortcode copy controls.

= 2.1.0 =
* Added destination selectors for external URLs, pages, posts, and WordPress categories.
* Stored internal destinations by object ID so their links follow future permalink changes.

= 2.0.2 =
* Expanded the Link Manager dashboard to use the full available admin width.

= 2.0.1 =
* Added explicit Overview, All Links, Add New, and Categories navigation.
* Corrected Link Manager menu highlighting on link and category screens.

= 2.0.0 =
* Added the Ocular Acumen branded Link Manager dashboard.
* Added monochrome admin styling and Ocular Acumen assets.
* Added secure nonce, capability, autosave, and post-type checks when saving.
* Added useful URL, target, category, and order columns to the link list.
* Added deterministic ordering and escaped link titles.
* Added `noopener` to new-tab links.
* Improved invalid-category handling and relative URL support.
* Added complete installation and shortcode documentation.

= 1.9 =
* Initial archived release.
