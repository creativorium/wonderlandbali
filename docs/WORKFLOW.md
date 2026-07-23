# Git Workflow — branch per component

We build the site **component by component**, and each step gets its **own branch** so work
stays isolated and easy to track/review.

## Remote

```
origin  https://github.com/creativorium/wonderlandbali.git
```

## Branch naming

| Kind of work | Branch |
|---|---|
| A new component/section | `feat/section-<name>` (e.g. `feat/section-hero`) |
| Theme/infra change | `chore/<thing>` (e.g. `chore/vite-config`) |
| Fixes | `fix/<thing>` |

## Per-component loop

```bash
# start from an up-to-date main
git checkout main
git pull            # once the remote exists

# branch for the component
git checkout -b feat/section-<name>

# ...build the block (plugin/src/blocks/<name>/...), npm run build, test in Local...

git add -A
git commit -m "feat(<name>): add <name> section"
git push -u origin feat/section-<name>
# open a PR on GitHub, review, merge into main
```

Keep `main` always working. Merge a component only once it looks right in Local against the
real site.

## What is / isn't committed

- **Committed:** all source (`theme/`, `plugin/src`, PHP, config, docs).
- **Ignored:** `node_modules/`, `theme/build/`, `plugin/build/` (regenerated with
  `npm run build`), and `*.local.md` scratch notes.

> Because build output is gitignored, any deploy/CI must run `npm ci && npm run build`.
> See [DEPLOYMENT.md](DEPLOYMENT.md).

## First push (one-time)

```bash
git init
git add -A
git commit -m "chore: scaffold theme + blocks plugin + Vite build"
git branch -M main
git remote add origin https://github.com/creativorium/wonderlandbali.git
git push -u origin main
```
