# Visual System — Andikadevs-inspired CV Manager

## Reference

- Source: https://andikadevs.space/
- Captured: 2026-10-23, desktop viewport 1440px wide, full-page screenshot
- Scope: visual direction for the authenticated CV-management homepage; application data and CRUD behaviour remain unchanged.

## Observed visual language

- Warm paper background (`#FFF9F1`) with saturated sky blue used for navigation and high-emphasis regions.
- Oversized, tightly-spaced grotesque headings paired with compact mono labels.
- Small section counters, thin rules, pill controls, compact cards, and deliberately strong black borders.
- A restrained palette: cream, sky blue, deep navy/black, muted grey, with orange as a minor accent.
- Content uses large breathing room and editorial rather than conventional admin-dashboard composition.

## Tokens used in this project

```css
--paper: #fff9f1;
--paper-strong: #f5eddf;
--sky: #3f92d7;
--sky-light: #bfe5ff;
--ink: #071421;
--muted: #696b70;
--line: rgba(7, 20, 33, .18);
--accent: #f5a623;
--radius-sm: 10px;
--radius-pill: 999px;
```

Typography uses Bricolage Grotesque for display text, Space Grotesk for UI/body copy, and JetBrains Mono for labels and metadata. The reference reports approximately 136px desktop H1 and 88px H2; this implementation scales those values responsively because the target is an editing application rather than a marketing page.

## Component translation

- Sidebar → fixed blue editorial navigation rail with a white pill brand.
- Profile block → hero card with an oversized name and framed portrait.
- Section titles → numbered editorial headings with a mono eyebrow.
- Data groups → cream/white bordered cards with offset blue shadows.
- Buttons → compact pill controls; blue is primary, green becomes dark ink, destructive remains red.
- Modals → warm-paper panels with black borders and reduced generic dashboard chrome.

## Fidelity notes

The color, font families, section rhythm, borders, and editorial density are extracted from the reference. The exact illustrations, mascot, project imagery, and wave artwork are intentionally not copied. The cloud shapes and grid motif are original CSS approximations that carry the same atmosphere while keeping Amirul's product distinct.
