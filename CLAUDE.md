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

## Out of Scope (per project preference)
- Avoid WordPress plugins where a reasonable custom alternative exists (past
  experience: plugins are a common source of bloat/security risk). MotoPress and
  Contact Form 7 were both originally specced and should be reconsidered for custom
  alternatives before installing.
