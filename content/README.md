# Page content sources

Composed Gutenberg block markup for each rebuilt page (`content/pages/<slug>.html`).
These are the **authoring source** — the same markup is stored in the WordPress DB as the
page's `post_content`. Once a page is edited live in Gutenberg, the DB becomes the source of
truth and these files may drift (they're a record of the initial build).

Apply a file to its page with WP-CLI (see README → WP-CLI):

```bash
wp post meta update <id> _elementor_edit_mode ""   # disable Elementor on the page
wp post update <id> content/pages/<slug>.html      # set post_content
```

| Page | Slug | Post ID |
|---|---|---|
| Home | home | 4318 |
| About Us | about-us | 1637 |
| Portfolio | portfolio | 1336 |
| Contact | contact | 1334 |
| Make a Request | request | 1337 |
| Wedding Planning & Styling | weddings-planning-styling | 699 |
| Event Planning & Styling | events-planning-styling | 752 |
| Indian Weddings | indian-weddings | 848 |
| Decoration | decoration | 903 |
| Elopement | elopement | 1501 |
| Portugal | portugal | 1534 |
| Italy | italy | 1548 |
