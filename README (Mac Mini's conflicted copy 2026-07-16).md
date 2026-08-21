# Ocular Acumen Link Manager

## Overview
An Ocular Acumen WordPress plugin for managing categorized, ordered link lists. This plugin is not part of the Vela plugin family.

## Key Features
- Dedicated black, gray, and white Link Manager admin interface
- Destination selectors for custom URLs, pages, posts, and WordPress categories
- Link categories, numeric ordering, and same-tab/new-tab behavior
- Category-specific drag-and-drop link ordering
- `[ko_links]` shortcode with category and title options
- Copy-ready category shortcodes and an Overview placement reference
- Secure WordPress-native editor and useful overview columns

## Requirements
- WordPress 6.0 or later
- PHP 7.4 or later

## Installation
1. Copy the plugin into `/wp-content/plugins/`
2. Activate it from the WordPress admin
3. Open **Link Manager** and add categories and links
4. Test in a staging environment before production rollout

## Usage
Use `[ko_links]` to show all links, or filter and title a list:

`[ko_links category="footer" title="Explore"]`

The category may be a slug or term ID. See `readme.txt` for complete instructions.

## Configuration
- Review plugin settings, filters, actions, and any environment-specific assumptions before deployment
- Keep API keys, account IDs, license keys, and secrets out of version control
- Review the codebase for any site-specific assumptions before deployment.

## Extensibility
This plugin may be extended through normal WordPress customization patterns such as actions, filters, template integration, admin settings, or project-specific wrappers, depending on the implementation.

## Development Notes
- Public-safe repository version
- No live secrets should be stored in code
- Test with your active stack before production release

## License
GPL-2.0-or-later
