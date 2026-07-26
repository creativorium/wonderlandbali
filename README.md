# Wonderland Bali — WordPress Rebuild

Rebuild of [wonderlandbali.com](https://wonderlandbali.com) (WordPress + Elementor on
Bluehost) into a **fast, lightweight** WordPress site:

- **Classic PHP theme** (`theme/`) for structure, header/footer + templates.
- **Native Gutenberg blocks** (`plugin/`) for every content component, **server-rendered**
  via `render.php` (zero React on the front end).
- **Vite** builds the editor + front-end assets (fast, minified, tiny output).
- Layout is composed in **Gutenberg**, component by component, matching the real site.

> This single file is the whole project doc: [Layout](#repo-layout) · [Quick start](#quick-start)
> · [Architecture](#architecture) · [Adding a block](#adding-a-new-component-block) · [Blocks built](#homepage-blocks-built)
> · [Local setup / junctions](#local-setup) · [WP-CLI](#wp-cli-against-the-local-site) · [Git workflow](#git-workflow)
> · [Deployment](#deployment--auto-deploy) · [Troubleshooting](#troubleshooting) · [Tooling notes](#tooling-notes)

---

## Repo layout

```
Wonderland/
├─ theme/                 → junctioned into wp-content/themes/wonderland
│  ├─ *.php               classic templates (header, footer, front-page, page, single, index)
│  ├─ inc/                setup + asset enqueue
│  ├─ assets/             fonts (Analogue Modern, Gill Sans) + logo.svg
│  ├─ src/                Vite source (SCSS/JS: tokens, header, footer, base)
│  └─ build/             (generated) main.css / main.js
├─ plugin/                → junctioned into wp-content/plugins/wonderland-blocks
│  ├─ wonderland-blocks.php
│  ├─ inc/registration.php
│  ├─ src/
│  │  ├─ editor.js        editor bundle entry (registers block UIs)
│  │  ├─ frontend.js      front-end style + slideshow entry
│  │  └─ blocks/<name>/   one folder per component (block.json, edit.jsx, render.php, style.scss, index.js)
│  └─ build/             (generated) editor.js/css, frontend.js/css
├─ vite.config.js         three build targets: theme | editor | frontend
├─ package.json
└─ README.md              ← this file
```

## Quick start

```bash
npm install
npm run build          # build all three targets (theme, editor, frontend)
npm run watch:theme    # or watch:editor / watch:frontend while developing
```

- **Local URL:** https://wonderlandbali.local (self-signed cert — click through the warning)
- **Theme:** *Wonderland* (active). **Plugin:** *Wonderland Blocks* (active).
- Hard-refresh (Ctrl+Shift+R) after a rebuild — LiteSpeed caches.

---

## Architecture

**Three pieces:**

1. **Theme** (`theme/` → `wp-content/themes/wonderland`) — classic PHP theme. Provides the
   site chrome (transparent overlay header + menu overlay, taupe footer) and renders page
   content with `the_content()`, so **Gutenberg drives page layout**. Site-wide CSS/JS build
   to `theme/build/main.css|js`. Fonts ship as a static `assets/fonts/fonts.css`.
2. **Blocks plugin** (`plugin/` → `wp-content/plugins/wonderland-blocks`) — every reusable
   component is a **native block**: `block.json` (metadata + `"render": "file:./render.php"`),
   `edit.jsx` (editor UI), `render.php` (front-end HTML), `style.scss`, `index.js`.
   Blocks auto-register — `inc/registration.php` scans `src/blocks/*` for `block.json`.
3. **Vite build** — `vite.config.js` has three targets chosen with `--mode`:

   | mode | entry | output | purpose |
   |---|---|---|---|
   | `theme` | `theme/src/main.js` | `theme/build/main.js` + `main.css` | site-wide front-end |
   | `editor` | `plugin/src/editor.js` | `plugin/build/editor.js` + `editor.css` | block editor UIs |
   | `frontend` | `plugin/src/frontend.js` | `plugin/build/frontend.js` + `frontend.css` | front-end block CSS + slideshow JS |

**Why it's fast:** no React on the front end (blocks render to plain HTML via `render.php`);
`@wordpress/*` externalized to the `wp.*` globals WordPress ships (editor bundle stays tiny);
JSX compiles to `wp.element.createElement` (no React runtime); fixed filenames +
`filemtime()` cache-busting (no manifest); fonts kept as separate cacheable files
(`assetsInlineLimit: 0` — Vite lib mode otherwise base64-inlines them).

**Design tokens** live in `theme/src/styles/_tokens.scss` as CSS custom properties
(palette `#E3DEDA` greige / `#C5B7B3` taupe / `#EFA58F` coral / `#E2B9C9` pink / `#000`;
fonts Analogue Modern + Gill Sans) so theme and blocks share one system. Blocks use `wl-`
class prefix and reference the tokens with fallbacks.

### Adding a new component (block)

1. Create `plugin/src/blocks/<name>/` with `block.json`, `edit.jsx`, `render.php`,
   `style.scss`, `index.js` (copy an existing block as a template).
2. Set a unique `"name": "wonderland/<name>"` in `block.json`.
3. Add two import lines to `plugin/src/editor.js`
   (`import './blocks/<name>/style.scss'; import './blocks/<name>/index.js';`) and one to
   `plugin/src/frontend.js` (`import './blocks/<name>/style.scss';`).
4. `npm run build`. The block auto-registers in PHP — no PHP edit needed.
5. Escape all output in `render.php` (`esc_url`, `esc_html`, `wp_kses_post`).

### Homepage blocks built

Front page (`/`, page ID 4318) is composed of these blocks, in order:

| Block | Section |
|---|---|
| `wonderland/hero` | Full-screen 8-image Ken-Burns slideshow, vertical label, headline, ghost CTA, award badge |
| `wonderland/intro` | About / Our Story — image cluster + display title + story + Learn More |
| `wonderland/divider` | Full-width crossfading image band |
| `wonderland/services` | "Our Services" + 5 service cards (repeater) |
| `wonderland/cta` (plain) | Download the Wonderland Brochure |
| `wonderland/cta` (quote) | Alice/Cheshire quote + Make a Request |
| `wonderland/portfolio` | "Portfolio" + 27-image gallery + View More |
| `wonderland/follow` | Follow Us + Instagram feed (shortcode) + partner logos |

The hero + divider share one slideshow driver: any `[data-slideshow]` element cycles the
`is-active` class across its `.js-slide` children (`plugin/src/frontend.js`).

---

## Local setup

Prereqs: **Node 20+**, and **Local** (WP Engine) with the site at
`C:\Users\Nego\Local Sites\wonderlandbali`.

### Link theme + plugin into Local (Windows junctions)

We use **directory junctions** (not symlinks) because they work **without admin rights or
Developer Mode** and WordPress follows them transparently. In PowerShell:

```powershell
$src = "C:\Users\Nego\Documents\Works\Wonderland"
$wp  = "C:\Users\Nego\Local Sites\wonderlandbali\app\public\wp-content"
cmd /c mklink /J "$wp\themes\wonderland"         "$src\theme"
cmd /c mklink /J "$wp\plugins\wonderland-blocks" "$src\plugin"
```

Verify: `Get-Item "$wp\themes\wonderland","$wp\plugins\wonderland-blocks" | Select Name,LinkType,Target`.
Remove a junction (never touches the source): `cmd /c rmdir "$wp\themes\wonderland"`.

> ⚠️ Use `rmdir` on the junction itself — never `rmdir /s` into it or `rm -rf` the link from
> Git Bash, or you risk deleting the **source** files.

## WP-CLI against the Local site

Local's PHP has no default `php.ini` (mysqli off) and `DB_HOST` is `localhost`, but Local
sets `mysqli.default_port` to the site's MySQL port. Run WP-CLI with Local's PHP, enable
mysqli, and override the port. **The MySQL port is dynamic per Local restart** — read it from
`%APPDATA%\Local\sites.json` (site `wonderlandbali`, `services.mysql.ports.MYSQL`).

```bash
PHP="…/lightning-services/php-8.2.27+1/bin/win64/php.exe"
EXT="…/lightning-services/php-8.2.27+1/bin/win64/ext"
WP="C:/Users/Nego/Local Sites/wonderlandbali/app/public"
"$PHP" -d extension_dir="$EXT" -d extension=mysqli -d mysqli.default_port=<PORT> \
  wp-cli.phar --path="$WP" <command>      # harmless "mysqlnd" load warning
```

Direct MySQL client also works: `mysql --ssl-mode=DISABLED -h127.0.0.1 -P<PORT> -uroot -proot local`.

**WordPress state so far:** old "Home" (ID 11) slug → `home-old`; new **Home (ID 4318)** is
the front page and holds the block-built homepage. Elementor header/footer templates
(560/1342/2918/549) are **unpublished** so the native theme chrome renders — republish to
restore. Main Menu is assigned to the theme's `primary` location. **Classic Editor** is
active → the block editor is off in admin; edit block content via WP-CLI or deactivate
Classic Editor.

---

## Git workflow

Build **component by component**, each on its **own branch** (`feat/section-<name>`), merged
into `main` with `--no-ff`. Remote: `https://github.com/creativorium/wonderlandbali.git`.

```bash
git checkout main
git checkout -b feat/section-<name>
# …build block, npm run build, test in Local…
git add -A && git commit -m "feat(<name>): …"
git checkout main && git merge --no-ff feat/section-<name>
```

**Committed:** all source (`theme/`, `plugin/src`, PHP, config, this README).
**Ignored:** `node_modules/`, `theme/build/`, `plugin/build/` (regenerate with `npm run build`),
`*.local.md` scratch notes. Because build output is gitignored, any deploy/CI must run
`npm ci && npm run build`.

First push (one-time): `git remote add origin <url>` then `git push -u origin main`.

---

## Deployment & Auto-Deploy

> **Not wired up yet — plan only.** Only two dirs ship: `theme/` → `wp-content/themes/wonderland`
> and `plugin/` → `wp-content/plugins/wonderland-blocks`, **with `build/` compiled**
> (`npm ci && npm run build`), uploading everything except `node_modules/`.

**Option A — GitHub Actions → SFTP/FTP to Bluehost (recommended first).** On push to `main`,
build then upload with `SamKirkland/FTP-Deploy-Action@v4` (incremental). Secrets:
`FTP_HOST`, `FTP_USER`, `FTP_PASS`. Confirm the real `server-dir` (often
`/public_html/wp-content/...`). Draft:

```yaml
# .github/workflows/deploy.yml  (draft — enable once secrets + paths confirmed)
name: Deploy
on: { push: { branches: [ main ] } }
jobs:
  build-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: 22, cache: npm }
      - run: npm ci
      - run: npm run build
      - uses: SamKirkland/FTP-Deploy-Action@v4
        with: { server: "${{ secrets.FTP_HOST }}", username: "${{ secrets.FTP_USER }}", password: "${{ secrets.FTP_PASS }}", protocol: ftps, local-dir: ./theme/,  server-dir: /public_html/wp-content/themes/wonderland/ }
      - uses: SamKirkland/FTP-Deploy-Action@v4
        with: { server: "${{ secrets.FTP_HOST }}", username: "${{ secrets.FTP_USER }}", password: "${{ secrets.FTP_PASS }}", protocol: ftps, local-dir: ./plugin/, server-dir: /public_html/wp-content/plugins/wonderland-blocks/ }
```

**Option B — Git-based** (needs Bluehost SSH + Node): bare repo + `post-receive` hook that
checks out to the `wp-content` paths and builds on the server.

**Pre-launch checklist:** confirm Bluehost FTP/SSH + paths · prefer a **staging** site first ·
migrate rebuilt Gutenberg pages · deactivate Elementor/Pro Elements only **after** all pages
are rebuilt · turn off any coming-soon/maintenance on production · verify LiteSpeed + purge on deploy.

---

## Troubleshooting

**Site shows a "Maintenance"/"Coming soon" screen.** No `.maintenance` file → it's a plugin
toggle, usually **Elementor → Tools → Maintenance Mode** (you can still reach `wp-admin`).
Set it to Disabled. Or in the DB:
`UPDATE wp_options SET option_value='disabled' WHERE option_name='elementor_maintenance_mode_mode';`.

**Theme/plugin missing in wp-admin.** Check the junctions (see [Local setup](#local-setup)).
Theme needs `style.css` + `functions.php`; plugin needs its header in `wonderland-blocks.php`.

**Block editor won't show blocks / "Wonderland" category empty.** Run `npm run build`
(editor needs `plugin/build/editor.js`); hard-refresh the editor. Note **Classic Editor** is
active site-wide — deactivate it to edit pages in Gutenberg.

**Front-end block has no styles/fonts.** `plugin/build/frontend.css` must exist; the block's
`style.scss` must be imported in `plugin/src/frontend.js`. Fonts load from the active theme's
`assets/fonts/fonts.css` — the Wonderland theme must be active.

**HTTPS cert warning / curl fails.** Local uses a self-signed cert and forces HTTPS (Really
Simple SSL). Click through in the browser; use `curl -k https://wonderlandbali.local/`. Use
root-relative asset URLs in block content to avoid mixed content.

**`npm run build` fails on esbuild.** Delete `node_modules` + `package-lock.json`, reinstall. Node 20+.

**Elementor header/footer came back.** Its templates were unpublished to let the native
chrome render; republishing them (560/1342/2918/549) re-enables the Elementor versions.

---

## Tooling notes

- **awesome-claude-skills** (ComposioHQ) — a curated *directory* of Claude skills, not code to
  install. Useful as a reference; no effect on site performance.
- **OmniRoute** — an AI-provider gateway/proxy; unrelated to WordPress. Out of scope here.
- **Vite** — used for bundling instead of `@wordpress/scripts` (webpack) for speed/smaller
  output; `@wordpress/*` are externalized to `wp.*` globals.
- **Local (WP Engine)** — local WordPress environment.

Local-only scratch notes live in `REFERENCE.local.md` (gitignored via `*.local.md`).
