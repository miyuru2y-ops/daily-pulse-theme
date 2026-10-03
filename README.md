# Daily Pulse — WordPress theme

A lean, fast BBC-style editorial news theme for the Daily Pulse site.
No page builders, no jQuery, no JavaScript at all — one stylesheet and clean PHP templates.

## What this repo is

This repo holds **only the design** (the WordPress theme). Article content lives in
WordPress itself and is published through the WP admin or the REST API importer —
never through this repo.

Change anything here, push to `main`, and the live site updates within seconds
via the webhook sync script on the server.

## Install

1. Copy this folder to `wp-content/themes/daily-pulse` on the server
   (or let the webhook sync script do it — see below).
2. WordPress admin → Appearance → Themes → activate **Daily Pulse**.
3. Make sure these categories exist (slugs matter — the theme looks them up by slug):
   `world`, `technology`, `business`, `entertainment`, `sports`, `health`, `science`.
   The REST API importer creates them automatically on first run.
4. Settings → Permalinks → **Post name** (pretty URLs).
5. Recommended for speed: install **LiteSpeed Cache** (free) and enable page caching
   + CSS minification. The theme ships zero JS, so it caches extremely well.

## Instant GitHub → WordPress design sync (webhook)

On the cPanel server:

1. Edit `github-sync.php` (the deploy script):
   - `'repo'   => 'miyuru2y-ops/daily-pulse-theme'`
   - `'branch' => 'main'`
   - `'target' => '/home/USERNAME/public_html/wp-content/themes/daily-pulse'`
   - `'secret' => '<a long random string>'`
2. Rename it to something random, e.g. `sync-a9f3k2.php`, and place it inside
   `public_html` (it needs a URL for the webhook).
3. GitHub repo → Settings → Webhooks → Add webhook:
   - Payload URL: `https://YOUR-DOMAIN/sync-a9f3k2.php?key=<secret>`
   - Content type: `application/json`
   - Events: **Just the push event**
4. Every push to `main` now updates the theme on the server within seconds.
   (Optional safety net: also run the script from a daily cPanel cron.)

## Publishing articles (content pipeline)

`tools/wp_publish.py` (in the main workspace, not this repo) reads the rewritten
`articles.json` and posts new stories to WordPress via the REST API:

- Creates missing section categories automatically.
- Skips stories already published (matched by slug).
- Skips items dated in the future.
- Writes image URL, source name and source URL into post meta the theme reads.

It needs three environment variables:

```
DP_WP_URL=https://your-domain.com
DP_WP_USER=your-wp-username
DP_WP_APP_PASSWORD=xxxx xxxx xxxx xxxx
```

Create the application password in WordPress admin under
Users → Profile → Application Passwords.

## Theme files

| File | Purpose |
|---|---|
| `style.css` | Theme header + the entire design (one stylesheet) |
| `functions.php` | Setup, helpers (time-ago, cards, reading time), most-read view counter, REST meta registration |
| `header.php` / `footer.php` | Masthead, section nav, footer |
| `front-page.php` | Homepage: lead story, top stories, strip, section grids, most-read rail |
| `single.php` | Article page with source box + related stories |
| `category.php` | Section page: lead story + card grid |
| `archive.php`, `index.php`, `404.php` | Fallbacks |

## Post meta keys

| Key | Used for |
|---|---|
| `dp_image` | Image URL shown on cards and the story hero |
| `dp_source_name` | Original publisher name |
| `dp_source_url` | Link to the original article |
| `dp_slug` | Importer dedupe key |
| `dp_views` | Most-read counter (auto-incremented on article views) |
