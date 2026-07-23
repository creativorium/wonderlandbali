# Deployment & Auto-Deploy (PLAN / NOTES ONLY)

> Nothing here is wired up yet — this is the plan for when we're ready to auto-deploy.
> The site currently lives on **Bluehost** (WordPress). The rebuilt `theme/` + `plugin/`
> are what we deploy; the WordPress core, DB, and uploads stay on the host.

## What actually needs to ship

Only two directories go to the server:

- `theme/`  → `wp-content/themes/wonderland`
- `plugin/` → `wp-content/plugins/wonderland-blocks`

…**with `build/` compiled**. Since `build/` is gitignored, the deploy must run the Vite
build (`npm ci && npm run build`) before/while shipping, then upload everything **except**
`node_modules/` and `src/` (source isn't needed on the server, though shipping it is harmless).

## Option A — GitHub Actions → SFTP/FTP to Bluehost (simplest, recommended first)

Bluehost gives SFTP/FTP credentials. A workflow on push to `main`:

```yaml
# .github/workflows/deploy.yml   (draft — do not enable until secrets are set)
name: Deploy
on:
  push:
    branches: [ main ]
jobs:
  build-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: 22, cache: npm }
      - run: npm ci
      - run: npm run build
      - name: Upload theme
        uses: SamKirkland/FTP-Deploy-Action@v4
        with:
          server:   ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USER }}
          password: ${{ secrets.FTP_PASS }}
          protocol: ftps
          local-dir: ./theme/
          server-dir: /public_html/wp-content/themes/wonderland/
      - name: Upload plugin
        uses: SamKirkland/FTP-Deploy-Action@v4
        with:
          server:   ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USER }}
          password: ${{ secrets.FTP_PASS }}
          protocol: ftps
          local-dir: ./plugin/
          server-dir: /public_html/wp-content/plugins/wonderland-blocks/
```

Secrets to add in GitHub repo settings → *Secrets and variables → Actions*:
`FTP_HOST`, `FTP_USER`, `FTP_PASS`. Confirm the real `server-dir` paths from Bluehost
(often `/public_html/...`, sometimes `/home/<user>/public_html/...`).

The FTP-Deploy action syncs only changed files (keeps a `.ftp-deploy-sync-state.json`), so
it's incremental.

## Option B — Git-based deploy

If Bluehost SSH is available: bare repo + `post-receive` hook that checks out to the
`wp-content` paths and runs the build on the server (needs Node on the host — often not
available on shared hosting, so Option A is usually simpler).

## Pre-launch checklist (later)

- [ ] Confirm Bluehost FTP/SSH access + exact paths.
- [ ] Decide staging vs. production (a Bluehost staging site first is safest).
- [ ] Migrate the pages we've rebuilt (Gutenberg content) — export/import or rebuild on host.
- [ ] Deactivate Elementor / Pro Elements only **after** all pages are rebuilt in Gutenberg.
- [ ] Turn off Elementor Maintenance Mode on production when going live.
- [ ] Verify caching (LiteSpeed) + purge on deploy.

## Decision needed before we build this

- Deploy to **production directly**, or set up a **Bluehost staging** site first? (Staging
  recommended.)
- FTP/FTPS or SSH available on the plan?
