# Partner portal design

The partner portal (partners.repostit.io) uses the Repostit design system, the same one as repostit.io and the webapp:
white ground, near-black type, Geist, black buttons, violet only as an accent.

- Tokens live in `tailwind.config.js` (colors, radii, shadows) and `resources/css/app.css` (shared `rp-*` components:
  header, buttons, fields, cards, pills). The Tailwind gray scale is remapped onto the system's neutrals, so existing
  `gray-*` classes land on design-system colors. Use the semantic names (`ink`, `line`, `brand-text`, ...) in new markup.
- Build the stylesheet with `npm ci && npm run build:css`. The Docker image rebuilds it from the views, so a missing
  local build never ships stale CSS. `public/assets/css/app.css` is committed for local PHP runs.
- Geist (`public/assets/fonts`), the plane mark (`public/assets/brand`), favicons and the Workflows screen loop
  (`public/assets/media`) are copied from hax98/repostit-site. Alpine is pinned and self-hosted in `public/assets/js/vendor`.
- Rules worth keeping: never violet buttons or gradients, borders instead of shadows, sentence case, no exclamation
  marks, 48px fields with a visible label, 44px minimum touch targets, and a 2px brand focus outline.
