# Entase plugin for Wordpress
![GitHub release (latest by date)](https://img.shields.io/badge/php-%3E%3D7.4-blue)
![GitHub release (latest by date)](https://img.shields.io/badge/wp-6.0.2-green)
![GitHub release (latest by date)](https://img.shields.io/badge/license-GPL-blue)
![GitHub release (latest by date)](https://img.shields.io/badge/elementor%20integrated-93003c)

Official repository.

## How it works
This plugin creates two post types - ``Production`` and ``Event``. Both are imported & synced from Entase. You can however add an additional custom functionality by hooking to the wordpress hooks as usual. The plugin functionality should not conflict with any existing post types named ``Production`` and/or ``Event``. However some integrations might have unexpected result.

This plugin is integrated with Elementor, but it is **NOT REQUIRED**. It also works as a standalone solution with shortcodes.

## Setup
1. Install and activate the plugin as usual.
2. Retrieve your public and secret API keys from Entase and add them inside ``Dashboard -> Settigs -> Entase``
3. Import your productions by going to ``Dashboard -> Productions`` and click ``Import from Entase`` button.
4. Import your events by going to ``Dashboard -> Events`` and click ``Import from Entase`` button.

## Shortcodes
- [entase_title]
- [entase_story]
- [entase_id]
- [entase_productionid]
- [entase_link]
- [entase_book]
- [entase_photo_poster]
- [entase_photo_og]
- [entase_productions]
- [entase_events]

## Elementor
For your conviniance the shortcode functionality is wrapped inside an Elementor widgets and active tags.

### Widgets
- Productions widget - customizable productions grid
- Events widget - customizable events grid

### Active Tags
- Production title
- Production story
- Production photo poster
- Production photo og

## Autosync
You can turn on auto sync by going to ``Dashboard -> Settigs -> Entase`` and enable ``Auto sync periodically (cron)`` option.

**Sync method**<br>
The sync functions are pulling new data by querieng Entase API in scheduled intervals of time.

**Sync schedule:**
- Every 10 minutes - Upcoming events update.
- Every 15 minutes - New events import.
- Every 25 minutes - New productions import.

**Performace:**<br>
Note that the cron jobs hooks with the ``WP-Cron`` schedule which is not a real cron job. If possible it's recommended to move the WP cron execution on a real cron job execution which will speed up the entire website.

_For more information please [read here](https://developer.wordpress.org/plugins/cron/)._
