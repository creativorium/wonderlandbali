# Troubleshooting

## Site shows a "Maintenance" / "Coming soon" screen

We checked: there is **no `.maintenance` file** in the WordPress root and no maintenance
drop-in in `wp-content/`, so this is **not** a stuck-update lock. On this site (Elementor +
Pro Elements) the cause is almost certainly a **plugin-level toggle** that carried over from
the live site.

**Most likely: Elementor Maintenance Mode.** You can still log in to `wp-admin` as an admin.

**Fix (UI):**
1. Go to http://wonderlandbali.local/wp-admin
2. **Elementor → Tools → Maintenance Mode**
3. Set **"Choose Mode"** to **Disabled** → Save.

**If it's not Elementor**, check these in turn:
- **Settings → Reading** → make sure no "coming soon" is set; check active plugins for
  *SeedProd*, *WP Maintenance Mode*, *LiteSpeed* (has a maintenance option), *Really Simple SSL*.
- Look for a leftover **`.maintenance`** file in the site root and delete it (recreates on updates).
- **LoginPress** / **Newfold/Bluehost** plugins can also gate the front end.

**Fix (database, if you can't reach the UI):** the Elementor toggle is stored in
`wp_options`. Set it off:
```sql
UPDATE wp_options SET option_value = 'disabled'
WHERE option_name = 'elementor_maintenance_mode_mode';
```
(Run via Local's *Adminer/phpMyAdmin* — Local → the site → **Database → Open Adminer**.)

## Theme/plugin doesn't appear in wp-admin

- Confirm the junctions exist and point to the right source (see SETUP.md step 2).
- The theme needs `style.css` **and** `functions.php` (both present) and the plugin needs its
  header comment in `wonderland-blocks.php` (present).

## Blocks missing / "Wonderland" category empty in the inserter

- Run `npm run build` — the editor needs `plugin/build/editor.js`.
- Hard-refresh the editor (Ctrl+Shift+R) to bust the browser cache.
- Check the browser console for a JS error in `editor.js`.

## Front-end block has no styles

- `plugin/build/frontend.css` must exist (`npm run build:frontend`).
- Confirm the block's `style.scss` is imported in `plugin/src/frontend.js`.

## `npm run build` fails on esbuild / native module

- Delete `node_modules` and `package-lock.json`, then `npm install` again.
- Node 20+ required.

## Junction accidentally points at the wrong place / broke

Remove and recreate (safe — `rmdir` on a junction never touches the source):
```powershell
cmd /c rmdir "C:\Users\Nego\Local Sites\wonderlandbali\app\public\wp-content\themes\wonderland"
```
then recreate per SETUP.md.
