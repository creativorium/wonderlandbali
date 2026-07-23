# Tooling Notes — external tools evaluated

## awesome-claude-skills (ComposioHQ)
<https://github.com/ComposioHQ/awesome-claude-skills>

- **What it is:** a *curated directory* of Claude "skills" (reusable instruction packages) —
  not code you install into this project.
- **Relevance here:** useful as a **reference** to browse for workflow helpers (docs, dev
  automation). It has **no effect on the WordPress site's performance**.
- **Verdict:** fine to bookmark; nothing to add to this repo.

## OmniRoute (diegosouzapw)
<https://github.com/diegosouzapw/OmniRoute>

- **What it is:** an AI-provider **gateway/proxy** (Node/TypeScript, Next.js) that routes
  requests across many LLM providers, with fallback and token compression.
- **Relevance here:** **unrelated to WordPress.** It could matter for your *AI tooling costs*,
  not for this rebuild.
- **Caution:** "many free providers, no API keys" proxies carry ToS / quality / privacy
  trade-offs. Vet before routing real work through it.
- **Verdict:** out of scope for this project; not added.

---

## Actually relevant to this project (for reference)

- **Vite** — front-end/editor bundling (in use).
- **@wordpress/scripts** — the "official" block build tool (webpack). We deliberately use Vite
  instead for speed/smaller output; `@wordpress/*` are externalized to `wp.*` globals so we
  don't need it.
- **Local (WP Engine)** — local WordPress environment (in use).
