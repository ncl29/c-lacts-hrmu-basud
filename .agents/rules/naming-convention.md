---
trigger: always_on
---

# Naming conventions

Follow the common TypeScript/React conventions (Airbnb and Google style guides, React docs). Keep names descriptive and consistent.

## Files and folders
- Folders and files use `kebab-case`: `revenue-chart.tsx`, `franchise-form.tsx`, `components/dashboard/`. This matches shadcn/ui and Next.js.
- Hooks: `use-franchises.ts`. Types: `franchise.types.ts`. Utilities: `format-date.ts`.
- Route files follow the framework (`page.tsx`, `layout.tsx`).

## Code
- Components, interfaces, types, and enums: `PascalCase` (`RevenueChart`, `LeaveApplication`, `EmployeeRecord`).
- Variables, functions, props, and hooks: `camelCase`. Hooks start with `use` (`useFranchises`).
- Constants that never change: `UPPER_SNAKE_CASE` (`MAX_PASSENGERS`, `DEFAULT_FRANCHISE_VALIDITY_YEARS`).
- Booleans start with `is`, `has`, `can`, or `should` (`isLoading`, `hasError`, `isRenewed`).
- Event handler props start with `on` (`onSubmit`). The functions that handle them start with `handle` (`handleSubmit`).
- Do not prefix interfaces with `I` or suffix types with `Type`.

## Names
- A name says what the thing is or does. Use full words (`LeaveApplication`, not `fApp`).
- Functions are verbs (`getEmployeeProfile`, `calculateLeaveCredits`). Components and variables are nouns.
- Use one word per concept across the project. Pick `employee` or `personnel`, not both; pick `supervisor` or `manager`, not both.
- No single-letter names except short loop indexes. No abbreviations unless they are widely known (`id`, `url`, `dtr`, `spms`, `csc`).

## Database and API
- Database tables and columns: `snake_case` (`employees`, `leave_applications`, `attendance_logs`, `employee_id`, `created_at`).
- In Drizzle ORM schema definitions: Define schema property keys in `camelCase` and map them to `snake_case` column names (e.g. `employeeNumber: text("employee_number")`, `departmentId: text("department_id")`, `createdAt: timestamp("created_at")`). This provides type-safe `camelCase` across TypeScript components without runtime mapping boilerplate.
- API routes: lowercase, plural nouns (`/api/employees`, `/api/leave-applications`, `/api/attendance`). Do not put verbs in the URL.
- Convert `snake_case` to `camelCase` at the API / Server Action boundary, not inside UI components.

## Git
- Branches: `feat/leave-ledger-sync`, `fix/dtr-tardiness-calc`.
- Commit messages follow Conventional Commits: `feat: add inspection checklist field`.

## Before creating a new name
- Check existing names for the same concept. Match what the project already uses.

