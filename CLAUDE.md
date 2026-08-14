# Cozumel Homes Companion Website

## Overview
WordPress theme for cozumelhomes.net — companion site to the Cozumel Manager Mac app.
Specs and plans live in the separate `Cozumel_App_Final` repo under
`docs/superpowers/specs/` and `docs/superpowers/plans/`; progress ledger for the
current plan is `.superpowers/sdd/progress.md` in this repo.

## Local Development
- Local by Flywheel, site name `cozumel-homes`, theme symlinked into
  `~/Local Sites/cozumel-homes/app/public/wp-content/themes/cozumel-homes`
- Mail catcher is called **Mailpit** in this Local version (renamed from Mailhog) —
  under the site's "Tools" tab in the Local app
- Local's own UI is Electron-based and doesn't expose a usable accessibility tree —
  ask for a screenshot rather than trying `osascript`/System Events on it

## Theme Architecture
- GeneratePress child theme (`Template: generatepress` in `style.css`) — no child
  `header.php`, so the parent theme's own header and "Primary Menu" nav location
  render automatically. Don't add nav-rendering code here; just assign a menu to
  the "Primary Menu" location in Appearance → Menus.
- `rental-property` and `forsale-property` are custom post types with **archive
  templates only** (`archive-rental-property.php`, `archive-forsale-property.php`),
  registered with rewrite slugs `rentals` / `for-sale` (`inc/post-types.php`). These
  routes work automatically once permalinks flush — they are NOT WordPress Pages.
  Only **Contact** needs an actual Page (no CPT backs it).
- Custom fields (`inc/meta-fields.php`) hold structured data (neighborhood, address,
  base_rate, max_guests, bedrooms, bathrooms, etc). There's no separate field for
  amenities/house rules or pricing policy notes (e.g. weekly discount, monthly rate)
  — those go directly in the post's main content body alongside the description,
  since `single-rental-property.php` just renders `the_content()`.
- `mac_id` is meant to be sync-managed (Plan B, not yet built) — when creating
  property posts manually in wp-admin before that daemon exists, set `mac_id` to the
  exact app-side id anyway, so the future sync recognizes the post instead of
  creating a duplicate.

## Content Notes
- Per-property copy tone: Nah Ha 101 is the one luxury property; Cool Caribbean
  Views and Casa Bohemia are medium-budget — keep their copy warm but not
  luxury-coded (see progress ledger and `[[website_copy_voice]]` memory in
  `Cozumel_App_Final`'s auto-memory for full drafted copy).
- Pricing structure for all rentals: 7+ night stays (under a month) get 10% off the
  nightly rate; full-month stays use a separate flat monthly rate with electricity
  billed separately by guest consumption.

## Web Design & Quality Standards
- **Rendering**: staying WordPress (server-renders on request, no headless/React
  layer). Use page caching (Hostinger LiteSpeed cache, purge on content change) as
  the ISR-equivalent. RSC/SSR/ISR as React concepts don't apply here — don't add
  a JS framework layer without a concrete need that WP can't meet.
- **Core Web Vitals targets**: LCP < 2.5s, INP < 200ms, CLS < 0.1. Preload the hero
  image, set `width`/`height` or `aspect-ratio` on every image/video so nothing
  shifts on load, defer non-critical JS.
- **Accessibility**: WCAG 2.2 AA — contrast ≥ 4.5:1 body text, visible focus states,
  24×24px minimum touch targets, no drag-only interactions without an alternative.
- **Security**: OWASP Top 10 baseline — WP core/plugin/theme auto-updates,
  least-privilege DB user, HTTPS everywhere, disable XML-RPC if unused, sanitize
  custom form input, don't expose WP version.
- **SEO / AEO**: schema.org markup for lodging listings (price, location, amenities),
  one H1 per page, descriptive alt text on every property photo, concise
  direct-answer content near the top of each page for AI-search crawlers, FAQ
  schema, consistent name/address/phone across pages.
- **Typography & hierarchy**: defined type scale, consistent spacing tokens, max 2
  typefaces, 45–75 character line length, heading hierarchy matches semantic HTML
  (`nav`/`main`/`article` over generic divs).
- **Cross-browser**: latest 2 versions of Chrome/Safari/Firefox + mobile Safari; no
  unprefixed experimental CSS without a fallback.
- **Audience & landing goal**: US/Canada travelers comparison-shopping against
  Airbnb/VRBO, mobile-heavy. Nah Ha 101 (luxury) — CTA is direct inquiry, pitch is
  better-than-Airbnb rate plus personal service. Casa Bohemia / Cool Caribbean
  Views (mid-budget) — CTA is the same inquiry action, but price and the 7-night
  discount need to surface fast. Every page should make it clear within ~5 seconds
  that it's a real, bookable Cozumel property, its price range, and how to contact.

## Production Deployment (VPS)
- Live at `https://cozumelhomes.net`, Hostinger VPS (Ubuntu 24.04, WordOps stack:
  nginx + PHP-FPM + MySQL), theme at
  `/var/www/cozumelhomes.net/htdocs/wp-content/themes/cozumel-homes`. SSH via
  `~/.ssh/id_ed25519_cozumel_vps`, user `deploy`.
- **No CI/CD — deploys are manual `scp` + `ssh` per file**, e.g.:
  ```bash
  scp -i ~/.ssh/id_ed25519_cozumel_vps theme/cozumel-homes/<file> deploy@2.25.104.105:/tmp/<file>
  ssh -i ~/.ssh/id_ed25519_cozumel_vps deploy@2.25.104.105 \
    "sudo cp /tmp/<file> /var/www/cozumelhomes.net/htdocs/wp-content/themes/cozumel-homes/<file> && \
     sudo chown www-data:www-data /var/www/.../<file> && php -l /var/www/.../<file>"
  ```
  Local dev's theme directory is a symlink into this git repo (`~/Local
  Sites/cozumel-homes/app/public/wp-content/themes/cozumel-homes` →
  `theme/cozumel-homes`), so committing here does NOT automatically update
  production — always deploy explicitly after pushing, or production silently
  drifts from what's in git (happened 2026-08-14, see commit `be53f69`).
- Outbound mail (`wp_mail()`) is routed through Google Workspace SMTP via a
  `phpmailer_init` hook in `functions.php` — the VPS has no local MTA, so PHP's
  default `mail()` always fails silently. Credentials
  (`COZUMEL_SMTP_USER`/`COZUMEL_SMTP_PASS`) are constants in `wp-config.php` on
  both local dev and production, never committed (git-ignored).

## SwiftUI-adjacent WordPress gotcha
- `get_permalink()` with no argument depends on WordPress's global `$post`,
  which any `WP_Query` loop mutates via `the_post()`. `wp_reset_postdata()`
  only restores it if the *main* query has at least one post — it's a silent
  no-op otherwise (e.g. front page with zero blog posts). Code that calls
  `get_permalink()` after a custom loop without knowing the main query state
  can resolve to the wrong post entirely. `cozumel_render_inquiry_form()`'s
  redirect hit this on the homepage (`inc/inquiry-form.php`) — fixed by
  checking `is_front_page()` explicitly instead of trusting ambient `$post`.

## Out of Scope (per project preference)
- Avoid WordPress plugins where a reasonable custom alternative exists (past
  experience: plugins are a common source of bloat/security risk). MotoPress and
  Contact Form 7 were both originally specced and should be reconsidered for custom
  alternatives before installing.
