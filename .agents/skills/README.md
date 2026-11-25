# Workspace Skills Catalog & Compatibility Architecture

This repository hosts a curated suite of **35 agentic skills** organized into **6 modular categories** under `.agents/skills/`.
Discovery of categorized skill subdirectories is formally registered via [`.agents/skills.json`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/skills.json).

---

## 1. System Precedence & Source of Truth

When executing tasks or resolving conflicting recommendations across skills, adhere strictly to the following authority order (mandated by [`.agents/rules/project-documentation.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/rules/project-documentation.md)):

1. **Panel Defense Feedback** (`.agents/docs/defense-feedback.md`) â€” *Highest Priority*.
2. **Approved Project Proposal** (`.agents/docs/proposals-and-specs/*.pdf`).
3. **Suggestions & Improvements** (`.agents/docs/_suggestions.md`).
4. **Architectural & Schema Diagrams** (`.agents/docs/diagrams/ERD.png`, `DFD_0.png`, `DFD1.png`, `SysArch.png`).
5. **Workspace Rules** (`.agents/rules/*.md`: `ui-design.md`, `form-validation.md`, `naming-convention.md`, `component-structure.md`, `ui-feedback.md`, `file-handling.md`).
6. **Skills & Playbooks** (`.agents/skills/*`).

---

## 2. Technology Stack Compatibility Matrix

All skills operate under the active stack defined in [`package.json`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/package.json) and workspace rules:

| Domain | Technology / Version | Critical Compatibility Constraints |
|---|---|---|
| **Framework** | Next.js 16.4.0 (App Router) | **Mandatory Async Request APIs:** `await params`, `await searchParams`, `await cookies()`, `await headers()`. Synchronous access is removed. Turbopack default. `proxy.ts` replaces `middleware.ts`. |
| **Library** | React 19.3.0 | Server Components default; Client Components explicit (`'use client'`). Compatible with React Compiler, `useActionState`, and Actions. |
| **Runtime & PM** | Bun 1.4.1 | Native script runner (`bun dev`, `bun run build`, `bun add`). |
| **Styling** | Tailwind CSS v4 | CSS-first configuration (`@import "tailwindcss"`, `@theme`). **Strict 4px grid:** all spacing, gaps, sizing, radii, and icons must be divisible by 4px (`p-1`, `p-2`, `p-3`, `p-4`, `p-6`, `p-8`). Zero half-steps or arbitrary values. |
| **UI Design** | Shadcn UI & Base UI (`@base-ui/react`) | Single primary brand color, neutral grays, semantic green/amber/red only. No generic gradients or unstyled card grids. Responsive at 360px, 768px, 1280px with zero horizontal scroll. |
| **ORM & Database** | Drizzle ORM & Drizzle Kit | PostgreSQL / LibSQL type-safe layer. Schema keys in `camelCase`, column names in `snake_case`. Isolated at `server-only` boundary. |
| **Validation** | Zod v4 (`zod: ^4.6.5`) | All validation schemas isolated in `*.schema.ts`. Inferred types via `z.infer`. React Hook Form with `@hookform/resolvers/zod`. Drizzle-Zod parity. |

---

## 3. Skill Categorization (35 Skills across 6 Categories)

```
.agents/skills/
â”œâ”€â”€ agent-context/        (7 skills: Memory discipline, context engineering & orchestration)
â”œâ”€â”€ code-quality/         (8 skills: Idiomatic brevity, clean code, typing, governance & audits)
â”œâ”€â”€ database-data/        (4 skills: Drizzle ORM, schema design & SQL performance tuning)
â”œâ”€â”€ frontend/             (8 skills: Next.js 16, React 19, Tailwind v4, Shadcn & UI anti-slop)
â”œâ”€â”€ security-auth/        (4 skills: Role access matrices, session security & API protection)
â””â”€â”€ testing-debugging/    (4 skills: Systematic debugging, error analysis & web performance)
```

### Category 1: `frontend` (8 Skills)
Focuses on UI architecture, component structure, styling tokens, and Next.js 16 / React 19 application development.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `react-nextjs-development` | **Primary Driver** | Fullstack Next.js 16+ & React 19 workflow, async request params, Turbopack, and Bun execution. |
| `nextjs-best-practices` | Companion Guide | Server vs Client component boundaries, layout composition, and streaming strategies. |
| `nextjs-app-router-patterns` | Specialized Patterns | Parallel routes, intercepting routes, route handlers, and error/loading boundaries. |
| `react-patterns` | Component Design | Composition patterns, compound components, and clean custom hooks. |
| `react-best-practices` | Performance Guard | Vercel performance rules (memoization, server-side caching, rendering efficiency). |
| `shadcn` | Component Library | Accessible headless UI primitives, CLI installation, Base UI & Radix integration. |
| `tailwind-patterns` | Utility & Layout | Tailwind CSS v4 CSS-first theming, responsive tokens, and container queries. |
| `anti-slop-design` | Quality Technologist | Eliminating generic AI design tropes, enforcing tactile hierarchy and clean typography. |

### Category 2: `database-data` (4 Skills)
Focuses on schema modeling, type-safe database queries, migration reliability, and SQL performance for Drizzle ORM.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `drizzle-orm-expert` | **Primary Driver** | Drizzle ORM schemas, relations, type-safe queries, and server-only data boundaries. |
| `drizzle-migration-conflict` | Diagnostic Specialist | Resolving Drizzle Kit migration conflicts, snapshot syncs, and journal merges. |
| `database-design` | Architectural Modeling| Normalization, foreign key integrity, index planning, and `ERD.png` alignment. |
| `sql-optimization-patterns` | Query Tuner | `EXPLAIN ANALYZE` execution plans, composite index tuning, and eliminating N+1 queries. |

### Category 3: `security-auth` (4 Skills)
Focuses on authorization governance, role permissions, session authentication, and API defense.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `access-matrix` | **Governance Driver** | Role-by-module permission matrix aligned with HRU Basud roles (HR Admin, HR Officer, Department Head / Supervisor, Employee). |
| `access-review` | Compliance Auditor | Periodic access certification, segregation of duties, and audit trail validation. |
| `auth-implementation-patterns` | Technical Auth | Secure session tokens, HTTP-only cookie handling, and route protection gates. |
| `api-security-best-practices` | Boundary Defense | Route handler protection, input sanitization, CSRF mitigation, and rate limiting. |

### Category 4: `code-quality` (8 Skills)
Focuses on concise, idiomatic code generation, strict type safety, AI governance, and code maintenance.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `super-code` | **Operational Style** | House style prioritizing correctness, clarity, idiomatic brevity, and minimal token bloat. Streamlined for TypeScript. |
| `clean-code` | Structural Discipline | Uncle Bob's readability principles, intention-revealing naming, and single responsibility. |
| `typescript-expert` | Type Authority | Advanced TypeScript 5+ type inference, generics, strict compiler options, and diagnostics. |
| `zod-validation-expert` | Validation Authority| Zod v4 schemas, React Hook Form integration, and Drizzle-Zod schema parity. |
| `vibe-code-auditor` | Architectural Auditor| Identifying fragile AI generated code, hidden coupling, and prototype-level anti-patterns. |
| `vibe-code-cleanup` | Refactoring Specialist | Safe dead-code removal, unreferenced component pruning, and import consolidation. |
| `yes-md` | AI Governance Engine | 6-layer execution governance: safety gates, evidence verification, and anti-slack. |
| `agents-md` | Instruction Steward | Maintaining and auditing `AGENTS.md` documentation across the repository. |

### Category 5: `testing-debugging` (4 Skills)
Focuses on hypothesis-driven debugging, error log analysis, resilient error handling, and performance profiling.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `systematic-debugging` | **Investigative Workflow** | Scientific debugging: reproduce issue, isolate root cause, test hypotheses before patching. |
| `error-detective` | Log & Trace Analyst | Inspecting terminal outputs, parsing stack traces, and isolating crash points. |
| `error-handling-patterns` | Resilient Architecture | Result types, domain error classes, user-facing error boundaries, and graceful degradation. |
| `web-performance-optimization` | Diagnostic Profiler | Core Web Vitals (LCP, CLS, INP), bundle analysis, and network waterfall optimization. |

### Category 6: `agent-context` (7 Skills)
Focuses on context window optimization, persistent memory discipline, and dynamic skill orchestration.

| Skill | Role | Scope & Complementary Trigger |
|---|---|---|
| `antigravity-skill-orchestrator` | **Meta-Orchestrator** | Evaluates task complexity, selects complementary skills, and enforces simplicity guardrails. |
| `agent-memory-discipline` | Memory Protocol | Standing rules: Recall before acting/deciding, and Save after correcting/deciding. |
| `conversation-memory` | Architecture Model | Tiered memory structures (buffer, short-term session, long-term, and entity memory). |
| `context-fundamentals` | Theory Foundation | Understanding token allocation, prompt caching boundaries, and attention mechanics. |
| `context-optimization` | Budget Optimizer | Prompt caching, observation masking, token budgeting, and dynamic pruning. |
| `context-compression` | Token Compactor | Summarization techniques, structured compaction, and state distillation. |
| `context-degradation` | Diagnostic Monitor | Mitigating lost-in-the-middle effects, attention drift, and prompt fatigue. |

---

## 4. Complementarity & Handoff Protocols

To guarantee that skills complement each other without conflict:

1. **Clean Code vs. Super Code**:
   - `clean-code` defines *what clean code looks like* (meaningful names, small single-purpose functions, high cohesion).
   - `super-code` defines *how to write it efficiently* (minimal surface area, zero unnecessary boilerplate, idiomatic brevity, streamlined for TypeScript).
   - *Synergy:* Clean Code prevents spaghetti; Super Code prevents over-engineered enterprise fluff.

2. **Vibe-Code Auditor vs. Vibe-Code Cleanup vs. YES.md**:
   - `vibe-code-auditor` evaluates architecture and detects fragile prototypes.
   - `vibe-code-cleanup` performs the safe refactor, removing unused files and orphaned imports.
   - `yes-md` supplies the governance guardrails and verification checklist to ensure changes are proven correct before finishing.

3. **Next.js 16 Stack vs. Legacy Patterns**:
   - Whenever an App Router skill suggests synchronous `params` or `middleware.ts`, `react-nextjs-development` and `AGENTS.md` strictly take precedence (mandating `await params` and `proxy.ts`).

4. **Drizzle ORM Sole Authority**:
   - All database schemas and migrations execute exclusively through `drizzle-orm-expert` and `drizzle-migration-conflict` using `bun run drizzle-kit`.

5. **UI Aesthetics vs. Project Design Rules**:
   - `anti-slop-design` conforms strictly to `.agents/rules/ui-design.md`: 4px grid steps (`p-1`, `p-2`, `p-3`, `p-4`, `p-6`, `p-8`), single primary brand color, neutral grays, and complete responsiveness across 360px, 768px, and 1280px.

6. **Systematic Debugging vs. Error Detective**:
   - `error-detective` surfaces the raw facts (log snippets, stack traces, anomalous events).
   - `systematic-debugging` structures the scientific investigation (forming and proving hypotheses before modifying code).
   - `error-handling-patterns` hardens the code so the bug cannot recur silently.

---

## 5. Configuration Registration

Antigravity discovers these 6 categories via [`.agents/skills.json`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/skills.json):

```json
{
  "entries": [
    { "path": ".agents/skills/agent-context" },
    { "path": ".agents/skills/code-quality" },
    { "path": ".agents/skills/database-data" },
    { "path": ".agents/skills/frontend" },
    { "path": ".agents/skills/security-auth" },
    { "path": ".agents/skills/testing-debugging" }
  ]
}
```

