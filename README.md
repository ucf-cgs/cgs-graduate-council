# UCF Graduate Council Theme

A standalone WordPress theme for the UCF Graduate Council website. The code started as a customization of WordPress Twenty Sixteen and now includes council-specific templates and admin tools for meetings, committee membership, posted files, policies, and archives.

![Graduate Council theme screenshot](screenshot.png)

## Requirements

- WordPress 4.4 or newer. The compatibility check in `inc/back-compat.php` enforces this minimum.
- Advanced Custom Fields with the Repeater and Options Page APIs, normally provided by ACF Pro. The homepage and footer call these APIs directly.
- The site's ACF field groups and existing WordPress content. This repository does not contain ACF field-group exports.

The repository tracks its browser assets, including Moment.js, Pikaday, and Genericons. A normal installation does not need a build step.

## Installation

Clone the theme into the WordPress themes directory:

```sh
cd wp-content/themes
git clone https://github.com/ucf-cgs/cgs-graduate-council.git
```

Activate **Graduate Council** from **Appearance > Themes**, or use WP-CLI:

```sh
wp theme activate cgs-graduate-council
```

On a fresh WordPress installation, activate ACF before opening the public site. Then restore or create the site's field groups, pages, menus, settings, and content. Saving **Settings > Permalinks** once will refresh the rewrite rules for the custom post types.

## Site configuration

The theme adds **Settings > Council Site Settings** for the current academic year, the available year list, and the college list. It also registers `Primary Menu` and `Social Links Menu` locations plus the `Sidebar`, `Content Bottom 1`, and `Content Bottom 2` widget areas.

Assign one of the following templates when creating the corresponding WordPress pages:

| Page template | File |
| --- | --- |
| Council Homepage | `page_council-homepage.php` |
| Meetings Schedule | `page_meetings.php` |
| Meetings Archive | `page_meetings-archive.php` |
| Members Archive | `page_members-archive.php` |
| Minutes Directory | `page_minutes.php` |
| Archives | `page_archives.php` |

The ACF-powered **Graduate Site Settings** options page stores footer details and other site-wide fields. Homepage-specific ACF fields include the topic tracker, policy feedback links, subscription links, and content shown after the meeting schedule.

## Content types

The active theme code registers these custom post types:

| Admin label | Post type | Purpose |
| --- | --- | --- |
| Meetings | `gs_meetings` | Meeting dates, times, locations, deadlines, agendas, and minutes |
| Members | `gs_member` | Council members and committee serving years |
| File posting | `gs_file` | Agendas, minutes, reports, forms, policies, and related files |

`post_types/agenda.php` and `post_types/minutes.php` contain older post-type implementations, but `functions.php` does not load them.

## Project layout

- `functions.php` sets up the theme, loads active post types, and registers front-end assets.
- `post_types/` contains the council settings and custom content models.
- `page_*.php` contains the council's custom page templates.
- `template-parts/` and `inc/` contain the inherited Twenty Sixteen template helpers.
- `style.css` contains the theme metadata and primary stylesheet.
- `js/` contains the public and WordPress admin scripts.
- `vendor/` contains the checked-in Moment.js and Pikaday dependencies.

## Legacy dependency management

The `bower.json` file records the original front-end dependency setup and installs packages into `vendor/`. Those files already live in the repository, so do not run Bower during routine installation. Use `bower install` only when intentionally updating the vendored dependencies, and review the generated changes before committing them.

## Testing changes

This project has no automated test suite. Before deploying a change, check the affected page templates on a WordPress site with representative council data. For admin changes, create or edit the related meeting, member, or file post and confirm that its metadata saves correctly. Also check browser errors, keyboard navigation, responsive layouts, and the public archive pages.

## License

The theme header in `style.css` declares the GNU General Public License v2 or later. Bundled third-party assets retain their own license files.
