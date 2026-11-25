---
trigger: always_on
---

# UI design

- Keep screens minimal. Reveal secondary fields and actions only when needed (after a selection, in a dialog, or in a menu).
- Reuse existing shared components and styles before creating new ones. Keep spacing, buttons, labels, tables, and forms consistent across pages.
- Colors: one primary, neutral grays, and semantic red/green/amber only where they carry meaning. Use theme tokens or CSS variables, never hardcoded colors, gradients, or extra accents.

## Icons

- Prioritize Phosphor icons (`@phosphor-icons/react`). Only fall back to Lucide (the shadcn default) when Phosphor has no suitable icon.
- Do not mix icon sets within one component. If a Lucide icon is needed, use it only where no Phosphor equivalent exists.
- Use one weight across the app (default `regular`). Icon sizes follow the 4px grid (16, 20, 24).
- Icons inherit color from the parent (`currentColor`). Never hardcode icon colors.

## Sizing (4px grid)

- All spacing and sizing (padding, margin, gap, width, height, radius, icons) must be divisible by 4px.
- Tailwind: whole steps only (`p-1`, `p-2`, `p-3`, `p-4`, `p-6`, `p-8`). No half steps (`p-1.5`, `p-2.5`) and no arbitrary values (`w-[250px]`) unless divisible by 4 and no standard class fits.
- Exempt: font sizes, line heights, and 1px hairline borders.

## Data tables (DGV)

Tables show a scannable summary, not the full record. Less information per row, no horizontal scrolling.

### Columns
- Show only what the user needs to identify and scan a row: a name or identifier, the date, the status, and at most one or two key values (such as amount or owner).
- Aim for 3-5 columns on desktop and 2-3 on mobile. If a sixth column feels necessary, question whether it belongs.
- Do not show internal IDs, audit fields (created by, updated at), long text, notes, or rarely used fields in the table. Put them in the detail view (surface chosen per the UI feedback rule) or in an expandable row.
- Combine related values into one cell instead of adding a column (for example, name with email in smaller muted text below, or amount with currency).
- Optional columns go behind a column visibility toggle and are hidden by default.

### Dates
- Show one date per row unless the time itself matters. Use a short, consistent format across the app (for example, `Oct 17, 2026`).
- Show time or full timestamps only in the detail view or a tooltip.

### Avoiding horizontal scroll
- Columns fit the container: use `w-full` with `table-fixed` or flexible column widths. No fixed pixel widths.
- Truncate long text with ellipsis (`truncate`, `max-w-*`) and show the full value in a tooltip. Do not let cells grow the table.
- Avoid wrapping to many lines. Keep rows to one or two lines.
- On mobile, hide non-essential columns with responsive classes (`hidden md:table-cell`) or switch each row to a stacked card showing the same key fields.

### Rows and actions
- Make the row clickable to open the detail view. Do not add a button for it.
- Put secondary row actions (edit, duplicate, delete) in a single actions menu (kebab) at the end of the row. Do not show several icon buttons per row.
- Sort and filter only on columns that are useful to sort or filter. Paginate or virtualize long lists.

### Last resort
- Use `overflow-x-auto` inside the table container only when the user truly needs many columns side by side (for example, a data comparison or export-style view). Keep the first column sticky so rows stay identifiable. This is the exception, not the default.

## Responsive

- Mobile-first: base classes for small screens, then `sm:`, `md:`, `lg:`. No custom media queries.
- Must work at 360px, 768px, and 1280px with no horizontal page scroll.
- Use flex/grid, `max-w-*`, and `w-full`. No fixed pixel widths on containers.
- Tables follow the Data tables rules above and avoid scrolling. Wide charts scroll inside `overflow-x-auto`.
- Navbar collapses into a menu (shadcn `Sheet`) on mobile. Never hide important actions on mobile; move them into a menu or dialog.
- Tap targets are at least 40px high on mobile.