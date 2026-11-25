---
trigger: always_on
---

# Component structure

- Split each page into section-level components. Navbar, sidebar, dashboard charts, stat cards, data tables, forms, and filters each get their own component.
- Page files (`page.tsx` or route files) only compose components and fetch data. Keep them under about 100 lines and put no large JSX blocks in them.
- Page-specific sections go in `components/<page-name>/` (for example `components/dashboard/RevenueChart.tsx`).
- Shared components go in `components/shared/` and layout pieces in `components/layout/`. Keep shadcn primitives in `components/ui/` unchanged.
- One component per file, named in PascalCase after what it shows.
- Pass data and handlers as props. Do not hardcode page-specific data inside a reusable component.
- Extract a component when a section is longer than about 50 lines, is used more than once, or has its own state or data.
- Before creating a component, check whether a similar one exists. Reuse or extend it instead of duplicating.
- Do not create wrapper components that only pass props through.
- Move shared types to a `types` file and data-fetching logic to hooks, not into the JSX.

## DataGrid / DGV Standards
- Always reuse the shared `DataGrid` component (`components/shared/data-grid.tsx`) for table and data grid views.
- **Clickable Row Actions Required**: Every row / item in a DGV must be interactive and clickable.
- When an item or row in a DGV is clicked or activated with Enter/Space, it must present an Action Options modal (`rowActions`) allowing the user to choose an action (such as "View Details / Profile", "Update Information / Renewal", "Delete", etc.).
- Provide `rowActions`, `rowActionTitle`, and `rowActionSubtitle` props to `<DataGrid>` on all DGVs.
- Each `rowAction` must have:
  - `label`: Imperative action name (e.g. "View Employee Profile", "View Service Record")
  - `description`: Clear subtext summarizing the action outcome
  - `icon`: Relevant icon (prioritize Phosphor icons, e.g. `Eye`, `Trash`; fallback to Lucide if unavailable in Phosphor, e.g. `FileCheck`)
  - `onClick`: Callback receiving the row item
  - `variant`: (optional, e.g. `"destructive"` for delete actions)
- In individual table cells with direct action buttons or links, always include `e.stopPropagation()` so clicking the specific button executes immediately without opening the row-level Action Options modal.
- Always include pagination (`pageSize`, `pageSizeOptions`) and informative empty states on every DGV.
