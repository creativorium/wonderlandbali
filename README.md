# Wonderland Bali — WordPress theme + blocks

The site is a **classic PHP theme** plus a **plugin of native Gutenberg blocks**, built with
**Vite**. Every block is server-rendered from PHP — there is no React on the front end — and
the JavaScript that ships is a single small vanilla bundle.

If you are picking up front-end work, the short version is:

- **Components live in `plugin/src/blocks/<name>/`.** One folder per block.
- **Styles are SCSS**, compiled by Vite into two stylesheets.
- **Never edit `theme/build/` or `plugin/build/`** — they are generated, and gitignored.
- Run `npm run build` after every change, then hard-refresh (the server caches).

---

## 1. What you need installed

| Tool | Version | Why |
|---|---|---|
| [Node.js](https://nodejs.org) | **20 or newer** (22 recommended) | Runs Vite. `node -v` to check. |
| [Local](https://localwp.com) by WP Engine | current | Runs WordPress, PHP and MySQL for you — no XAMPP, no Docker. |
| Git | any | Obviously. |
| A terminal | — | On Windows, **Git Bash** (ships with Git) is easiest. PowerShell also works. |

You do **not** need PHP, MySQL or Composer installed separately — Local bundles all of it.

---

## 2. Clone the repo

```bash
git clone https://github.com/creativorium/wonderlandbali.git
cd wonderlandbali
npm install
npm run build
```

`npm run build` must succeed before the site will have any CSS or JS. If it fails on
esbuild, delete `node_modules` and `package-lock.json` and run `npm install` again.

---

## 3. Create the local WordPress site

1. Open **Local** → **Create a new site**
2. Name it **`wonderlandbali`** (the name sets the URL, so use exactly this)
3. Choose **Preferred** environment (PHP 8.2+, MySQL 8, nginx or Apache — any is fine)
4. Set any admin username/password you like — it is only your machine
5. Start the site. It will be at **https://wonderlandbali.local**

Local uses a self-signed certificate, so the first visit shows a security warning. Click
through it. (In terminal commands, `curl -k` skips the check.)

### Get the database

The site's content lives in the **database**, not in this repo — `content/pages/*.html` is
only an authoring record. Ask for a database export and import it:

**Easiest:** Local → right-click the site → **Open site shell**, then:

```bash
wp db import /path/to/wonderland.sql
wp search-replace 'https://olddomain.com' 'https://wonderlandbali.local' --all-tables
wp cache flush
```

You will also want the `wp-content/uploads` folder — copy it into
`.../wonderlandbali/app/public/wp-content/uploads`. Without it every image 404s.

---

## 4. Link the repo into WordPress (the important bit)

WordPress needs to *see* the theme and plugin folders from this repo. Rather than copying
files back and forth, we point WordPress at the repo using **directory junctions** on
Windows (or **symlinks** on macOS/Linux). Edit here → the site updates immediately.

### Windows

Junctions, **not** symlinks — junctions need no admin rights and WordPress follows them
happily. In **Command Prompt or PowerShell** (Git Bash mangles these paths):

```powershell
$src = "C:\path\to\wonderlandbali"
$wp  = "C:\Users\<you>\Local Sites\wonderlandbali\app\public\wp-content"

cmd /c mklink /J "$wp\themes\wonderland"              "$src\theme"
cmd /c mklink /J "$wp\plugins\wonderland-blocks"      "$src\plugin"
cmd /c mklink /J "$wp\plugins\wonderland-maintenance" "$src\plugin-maintenance"
```

> ⚠️ **To remove a junction, run `cmd /c rmdir "<the-link>"` on the link itself.**
> Never `rmdir /s` into it, and never `rm -rf` it from Git Bash — that deletes the **real
> files in your repo**, not just the link.

### macOS / Linux

```bash
src=~/code/wonderlandbali
wp=~/"Local Sites/wonderlandbali/app/public/wp-content"

ln -s "$src/theme"               "$wp/themes/wonderland"
ln -s "$src/plugin"              "$wp/plugins/wonderland-blocks"
ln -s "$src/plugin-maintenance"  "$wp/plugins/wonderland-maintenance"
```

### Then switch it all on

WordPress admin → **Appearance → Themes** → activate **Wonderland**, and
**Plugins** → activate **Wonderland Blocks** and **Wonderland Maintenance**.

> If pages render completely blank, the blocks plugin is not active. Our blocks are
> server-rendered with `save: () => null`, so the database holds no HTML for them — an
> unregistered block outputs nothing at all.

---

## 5. Day-to-day

```bash
npm run build            # build everything (do this after any change)

npm run watch:frontend   # rebuild block CSS/JS on save  ← most front-end work
npm run watch:theme      # rebuild theme CSS/JS on save   (header, footer, blog, login)
npm run watch:editor     # rebuild the block editor bundle
```

Then **hard-refresh** the browser (`Ctrl/Cmd + Shift + R`). The site runs LiteSpeed Cache;
if a change stubbornly will not appear, purge it from the site shell:

```bash
wp litespeed-purge all
```

### The four build targets

| Command | Source | Output | Loaded on |
|---|---|---|---|
| `build:theme` | `theme/src/main.js` | `theme/build/main.{css,js}` | every page |
| `build:login` | `theme/src/login.js` | `theme/build/login.css` | the login screen |
| `build:frontend` | `plugin/src/frontend.js` | `plugin/build/frontend.{css,js}` | every page (block styles + JS) |
| `build:editor` | `plugin/src/editor.js` | `plugin/build/editor.{js,css}` | wp-admin block editor |

---

## 6. Where the front-end code lives

```
theme/
├─ *.php                  page templates (header, footer, front-page, single, home, 404)
├─ inc/                   analytics, login screen, brochure modal, redirects, asset loading
├─ assets/fonts/          brand .ttf files + fonts.css (kept out of Vite so they stay cacheable)
└─ src/
   ├─ main.js             site-wide JS: off-canvas menu, dataLayer events, brochure dialog
   └─ styles/
      ├─ _tokens.scss     ← colours, fonts, spacing. Start here.
      ├─ _header.scss  _footer.scss  _blog.scss  _whatsapp.scss  _brochure.scss
      └─ main.scss        imports the above

plugin/
├─ inc/                   helpers (responsive images), block registration, forms, media
└─ src/
   ├─ frontend.js         imports every block's style.scss + the shared front-end JS
   ├─ editor.js           imports every block's style.scss + index.js (editor only)
   └─ blocks/<name>/
      ├─ block.json       name, attributes, defaults
      ├─ render.php       the HTML that ships  ← the real component
      ├─ style.scss       its styles
      ├─ edit.jsx         the wp-admin editing UI
      └─ index.js         registers the block in the editor

content/pages/*.html      authoring record of each page's block markup (see §8)
```

### Adding a block

1. Copy an existing folder in `plugin/src/blocks/` — `iw-included` is a simple one.
2. Give it a unique `"name": "wonderland/<slug>"` in `block.json`.
3. Add two lines to `plugin/src/editor.js` and one to `plugin/src/frontend.js`
   (the imports — this is the only wiring step; PHP registration is automatic).
4. `npm run build`.

### House rules worth knowing

- **Flexbox first.** The design overlaps and staggers; grid only for uniform repeats
  (galleries, card rows), CSS multicol for masonry.
- **Overlaps are one negative margin** between siblings, not grid-track tricks.
- **Mobile is boxy** — unwrap async columns and stack.
- **Escape everything** in `render.php`: `esc_url`, `esc_attr`, `esc_html`, `wp_kses_post`.
- **One `<h1>` per page**, headings in order, real `alt` text.
- **Images go through `wonderland_image()`** (`plugin/inc/helpers.php`) so they get
  `srcset`/`sizes` and intrinsic dimensions. Never hand-write `<img src>` for uploads.
- **Blocks prefixed `iw-` belong to `/indian-weddings/` only.** Do not reuse them elsewhere
  and do not bend the shared blocks to serve that page.

---

## 7. Deploying

**Staging** deploys itself: pushing to `main` triggers `.github/workflows/deploy.yml`, which
builds and rsyncs the theme and plugins over SSH. **Page content is not deployed by default** —
it only syncs when you run the workflow manually and tick `sync_content`, so a routine code
deploy can never overwrite something edited in wp-admin.

**Live has no SSH wiring**, so it updates from wp-admin instead:

1. Bump the version in `theme/style.css` (and `functions.php`) or `plugin/wonderland-blocks.php`.
2. Merge to `main`, then run **Actions → Release**. It builds, packages both components as
   `wonderland-<version>.zip` / `wonderland-blocks-<version>.zip`, and publishes a GitHub
   Release. It refuses to reuse a version that already has a release.
3. On the site: **Dashboard → Updates → Check again**, then update Wonderland and Wonderland
   Blocks like any other theme or plugin.

`plugin/inc/updates.php` is what points WordPress at those releases — for both components, so
theme updates stop being offered if the blocks plugin is deactivated.

---

## 8. Page content is in the database

Layouts are composed of block comments stored in `post_content`. We keep a copy of each page
in `content/pages/<slug>.html` as the record of truth. To re-apply one locally, from the
Local site shell:

```bash
wp post update <id> content/pages/<slug>.html
wp litespeed-purge all
```

Find the id with `wp post list --post_type=page --fields=ID,post_name`.

The **Classic Editor** plugin is active, so Gutenberg is switched off in wp-admin — page
content is edited through these files and WP-CLI, not the block editor.

---

## 9. When something looks wrong

| Symptom | Cause |
|---|---|
| No styling at all | `npm run build` not run, or `build/` folders missing on the server |
| Pages completely blank | Wonderland Blocks plugin not active |
| Change not showing | LiteSpeed cache — `wp litespeed-purge all` and hard-refresh |
| Every image 404s | `wp-content/uploads` not copied into the Local site |
| Blocks missing in wp-admin | `plugin/build/editor.js` missing — run `npm run build` |
| `npm run build` fails on esbuild | Delete `node_modules` + `package-lock.json`, reinstall |
| Theme/plugin absent from wp-admin | Junction/symlink is wrong, or points at the wrong folder |

---

## 10. Handy commands

Local gives each site a shell with WP-CLI already wired up — **right-click the site → Open
site shell**. From there:

```bash
wp plugin list                  # what's active
wp post list --post_type=page --fields=ID,post_name
wp litespeed-purge all          # clear the page cache
wp media regenerate             # rebuild image sizes (also converts to WebP)
wp db export backup.sql         # snapshot before anything risky
```
