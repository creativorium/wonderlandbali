# Wonderland Bali — WordPress Rebuild

Rebuild of [wonderlandbali.com](https://wonderlandbali.com) (currently WordPress +
Elementor on Bluehost) into a **fast, lightweight** WordPress site:

- **Classic PHP theme** (`theme/`) for structure + templates.
- **Native Gutenberg blocks** (`plugin/`) for every content component, **server-rendered**
  via `render.php` (zero React on the front end).
- **Vite** for building editor + front-end assets (fast, minified, tiny output).
- Layout is composed in the **Gutenberg editor**, component by component, matching the
  real site.

## Repo layout

```
Wonderland/
├─ theme/                 → junctioned into wp-content/themes/wonderland
│  ├─ *.php               classic templates (header, footer, front-page, …)
│  ├─ inc/                setup + asset enqueue
│  ├─ src/                Vite source (SCSS/JS)
│  └─ build/             (generated) main.css / main.js
├─ plugin/                → junctioned into wp-content/plugins/wonderland-blocks
│  ├─ wonderland-blocks.php
│  ├─ inc/registration.php
│  ├─ src/
│  │  ├─ editor.js        editor bundle entry (registers block UIs)
│  │  ├─ frontend.js      front-end style entry
│  │  └─ blocks/<name>/   one folder per component (block.json, edit.jsx, render.php, style.scss)
│  └─ build/             (generated) editor.js/css, frontend.css
├─ vite.config.js         three build targets: theme | editor | frontend
├─ package.json
└─ docs/                  architecture, setup, workflow, deployment, troubleshooting
```

## Quick start

```bash
npm install
npm run build        # build all three targets
npm run watch:theme  # or watch:editor / watch:frontend while developing
```

The `theme/` and `plugin/` folders are **junctioned** into the Local WordPress site at
`C:\Users\Nego\Local Sites\wonderlandbali`, so a rebuild here updates the site immediately.

- **Local URL:** http://wonderlandbali.local
- Activate **Theme:** Appearance → Themes → *Wonderland*
- Activate **Plugin:** Plugins → *Wonderland Blocks*

See [`docs/SETUP.md`](docs/SETUP.md) to (re)create the junctions, and
[`docs/WORKFLOW.md`](docs/WORKFLOW.md) for the branch-per-component git flow.

## Docs

| Doc | What |
|---|---|
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | How theme + blocks + Vite fit together, and how to add a block |
| [SETUP.md](docs/SETUP.md) | Fresh-machine setup, junctions, Local |
| [WORKFLOW.md](docs/WORKFLOW.md) | Git branch-per-component workflow + remote |
| [DEPLOYMENT.md](docs/DEPLOYMENT.md) | Auto-deploy plan (notes only for now) |
| [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | Maintenance-mode fix & common issues |
| [TOOLING-NOTES.md](docs/TOOLING-NOTES.md) | Notes on external tools evaluated |
