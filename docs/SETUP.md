# Setup

## Prerequisites

- **Node.js 20+** (tested on v22) and npm.
- **Local** (by WP Engine / Flywheel) with the `wonderlandbali` site at
  `C:\Users\Nego\Local Sites\wonderlandbali`.

## 1. Install & build

```bash
cd "C:\Users\Nego\Documents\Works\Wonderland"
npm install
npm run build
```

## 2. Link theme + plugin into Local (Windows junctions)

We use **directory junctions** (not symlinks) because they work **without admin rights or
Developer Mode** on Windows, and WordPress follows them transparently.

Run in **PowerShell** (from anywhere):

```powershell
$src   = "C:\Users\Nego\Documents\Works\Wonderland"
$wp    = "C:\Users\Nego\Local Sites\wonderlandbali\app\public\wp-content"

# Theme
cmd /c mklink /J "$wp\themes\wonderland"          "$src\theme"
# Plugin
cmd /c mklink /J "$wp\plugins\wonderland-blocks"  "$src\plugin"
```

To verify:

```powershell
Get-Item "$wp\themes\wonderland","$wp\plugins\wonderland-blocks" |
  Select-Object Name,LinkType,Target
```

To remove a junction (does **not** delete the source): `cmd /c rmdir "$wp\themes\wonderland"`.

> ⚠️ Use `rmdir` (or Explorer "Delete" on the junction itself) — never `rmdir /s` into the
> junction, and never `rm -rf` the link from Git Bash, or you risk deleting the *source*
> files it points to.

## 3. Activate in WordPress

- **Theme:** http://wonderlandbali.local/wp-admin → Appearance → Themes → **Wonderland** → Activate.
- **Plugin:** Plugins → **Wonderland Blocks** → Activate.

## 4. Develop

```bash
npm run watch:theme      # rebuild theme CSS/JS on save
npm run watch:editor     # rebuild block editor bundle on save
npm run watch:frontend   # rebuild front-end block CSS on save
```

Refresh the browser after a rebuild. (Live HMR is a possible later enhancement — see
DEPLOYMENT/architecture notes.)

## Notes

- The site DB (`local`, user/pass `root`/`root`) is a full copy of the live site. Its MySQL
  port is assigned by Local at runtime and changes between restarts.
- If the site shows a **maintenance screen**, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md).
