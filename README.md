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

### Attributes

#### [entase_story]
- **markup2html** - Auto parse Entase markup description. Don't set or set to ``no``. Default: ``yes``.

#### [entase_productions]
- **limit** - Set items limit. ``0`` means no limit. Default: ``0``.
- **fields** - Item fields to include. Comma separated list. Possible options: ``post_title``, ``post_content``, ``post_feature_image``, ``post_tags``, ``entase_title``, ``entase_story``, ``entase_photo_poster``, ``entase_photo_og``, ``multisource_image``.
- **filter_categories** - Filter by category IDs. Comma separated list. 
- **filter_tags** - Filter by tag IDs. Comma separated list.
- **filter_current_categories** - Filter by the same categories of the current query object. Default: ``no``. Options: ``yes`` | ``no``.
- **filter_current_tags** - Filter by the same tags of the current query object. Default: ``no``. Options: ``yes`` | ``no``.
- **multisource_image** - Provide the sources for ``multisource_image`` field. Comma separated list. Default: ``""``. Options: ``post_feature_image``, ``entase_photo_poster``, ``entase_photo_og``.

#### [entase_events]
- **limit** - Set items limit. ``0`` means no limit. Default: ``0``.
- **fields** - Item fields to include. Comma separated list. Possible options: ``production_post_title``, ``production_post_content``, ``production_post_feature_image``, ``entase_title``, ``entase_story``, ``entase_photo_poster``, ``entase_photo_og``, ``post_title``, ``post_content``, ``post_feature_image``, ``entase_dateStart``, ``entase_dateonly``, ``entase_timeonly``, ``entase_book``, ``entase_location_countryCode``, ``entase_location_countryName``, ``entase_location_cityName``, ``entase_location_postCode``, ``entase_location_address``, ``entase_location_placeName``, ``entase_location_lat``, ``entase_location_lng``
- **filter_status** - Filter by event status. Comma separated list. Default: ``1``. Options: ``1``, ``2``, ``3``, ``4``, ``5``.
- **filter_productions** - Filter by production Entase IDs. Comma separated list. 
- **filter_current_production** - Filter by the same production of the current query object. Default: ``no``. Options: ``yes`` | ``no``.
- **allow_qs_production** - Filter by production Entase ID provided in Query String with arg ``?produciton=......``. Default: ``no``. Options: ``yes`` | ``no``.
- **allow_qs_date** - Filter by date provided in Query String with arg ``?date=start-end``. Date format: ``YYYYMMDD``. Example: ``20220901-2022-09-30``. Default: ``no``. Options: ``yes`` | ``no``.
- **targeturl** - Click action when item is clicked. Default: ``book``. Options: ``book`` | ``production``.
- **booklabel** - Label of the book button. Default: ``Book``.
- **dateformat** - Date format. PHP compatible.
- **timeformat** - Time format. PHP comaptible.
- **contentchars** - Apply limit for ``post_content``, ``production_post_content``, ``entase_story`` fields. Default: ``200``

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
