# Architecture

## The three pieces

1. **Theme (`theme/` → `wp-content/themes/wonderland`)**
   A *classic* PHP theme: `header.php`, `footer.php`, `front-page.php`, `page.php`,
   `single.php`, `index.php`. It provides the site chrome (header/nav/footer) and renders
   page content with `the_content()` — so **Gutenberg drives the layout** of each page.
   Site-wide styles/JS live in `theme/src/` and build to `theme/build/main.css|js`.

2. **Blocks plugin (`plugin/` → `wp-content/plugins/wonderland-blocks`)**
   Every reusable component is a **native Gutenberg block**. Each block is:
   - `block.json` — metadata + `"render": "file:./render.php"`
   - `edit.jsx` — the editor UI (React, editor-only)
   - `render.php` — the **front-end HTML** (server-rendered; no JS shipped)
   - `style.scss` — styles shared by front end + editor preview

3. **Vite build**
   `vite.config.js` defines three targets, chosen with `--mode`:
   | mode | entry | output | purpose |
   |---|---|---|---|
   | `theme` | `theme/src/main.js` | `theme/build/main.js` + `main.css` | site-wide front-end assets |
   | `editor` | `plugin/src/editor.js` | `plugin/build/editor.js` + `editor.css` | block editor UIs |
   | `frontend` | `plugin/src/frontend.js` | `plugin/build/frontend.css` | front-end block styles |

## Why this is fast

- **No React on the front end.** Blocks render to plain HTML via `render.php`. React only
  loads inside `wp-admin`.
- **`@wordpress/*` are externalized** to the `wp.*` globals WordPress already ships — they
  are *not* bundled into `editor.js`, so the editor bundle stays tiny (~2 KB).
- **JSX compiles to `wp.element.createElement`** (no React runtime dependency).
- **Fixed filenames + `filemtime()` cache-busting** — no manifest, no hashed-filename churn.
- Front-end CSS is one small combined file (`frontend.css`) — fewer requests + better gzip
  than per-block files at this scale. (If it grows large, we can split per-block later.)

## Data / render flow

```
Gutenberg editor  ──edit.jsx──▶  block attributes saved to post_content (as a comment, no HTML)
                                          │
Front-end request ────────────────────────▶  render.php reads $attributes ──▶ HTML
theme front-page.php ─ the_content() ─ renders each block via its render.php
```

Because blocks are **dynamic** (`save: () => null`), changing `render.php` updates every
existing page instantly — no "block validation" errors, no re-saving posts.

## Adding a new component (block)

1. Create `plugin/src/blocks/<name>/` with `block.json`, `edit.jsx`, `render.php`, `style.scss`
   (copy `hero/` as a template).
2. Set a unique `"name": "wonderland/<name>"` in `block.json`.
3. Register the UI + styles by adding two lines:
   - `plugin/src/editor.js`: `import './blocks/<name>/style.scss'; import './blocks/<name>/index.js';`
   - `plugin/src/frontend.js`: `import './blocks/<name>/style.scss';`
4. `npm run build` (or keep `npm run watch:editor` / `watch:frontend` running).
5. The block auto-registers in PHP — `inc/registration.php` scans `src/blocks/*` for
   `block.json`. No PHP edit needed.

## Conventions

- BEM-ish class prefix `wl-` for block markup (`.wl-hero`, `.wl-hero__title`).
- Design tokens (colors, fonts, spacing) live in `theme/src/styles/_tokens.scss` as CSS
  custom properties, so blocks and theme share one palette. Refine these against the real
  site as we rebuild.
- Escape all output in `render.php` (`esc_url`, `esc_html`, `wp_kses_post`).
