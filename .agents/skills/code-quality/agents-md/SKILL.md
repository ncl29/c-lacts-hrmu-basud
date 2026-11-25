---
name: agents-md
description: Create, revise, or audit AGENTS.md files from repository evidence, verified commands, and correctly scoped instructions without overwriting maintainer intent.
category: development
risk: safe
source: community
date_added: "2026-03-06"
---

# Maintain AGENTS.md from Repository Evidence

## Overview

Create or improve agent instructions that help a coding agent change the repository correctly without rediscovering its workflow. Base every repository-specific command, path, and rule on evidence in the current checkout.

Prefer a focused diff over a wholesale rewrite. Follow the repository's own needs and maintainer intent.

## When to Use

- The user asks to create, update, shorten, or audit `AGENTS.md`.
- A monorepo or subfolder needs root instructions plus narrower directory-level overrides.
- Existing agent instructions contain stale commands, duplicated policy, or unsupported claims.
- Reconciling instructions across tools (`AGENTS.md`, `GEMINI.md`, etc.).

## How It Works

### 1. Preserve Existing Intent & Tool-Managed Blocks

Before writing, read every instruction file that applies to the target path:
- **Preserve Tool-Managed Blocks:** Never delete or corrupt automated tool blocks, such as:
  ```markdown
  <!-- BEGIN:nextjs-agent-rules -->
  ...
  <!-- END:nextjs-agent-rules -->
  ```
  managed by `next dev`. These must be preserved to prevent git thrashing.
- **Respect Subtree Policies:** If a directory specifies a local policy (e.g. `.agents/project_docs/AGENTS.md` specifying draft status and directing suggestions to `_suggestions.md`), always honor that boundary.
- **Preserve Accurate Maintainer Rules:** Improve an existing `AGENTS.md` in place with minimal, high-signal diffs.

### 2. Build a Bounded Evidence Map

Inspect only enough of the repository to establish how work is actually done:
1. Read manifests (`package.json`, lockfiles like `bun.lock`) to determine the exact runtime and package manager (e.g., Bun, pnpm, npm).
2. Check build configs (`next.config.ts`, `tsconfig.json`) and scripts.
3. Check CI workflows and existing code patterns.
4. Distinguish verified commands from unverified assumptions.

### 3. Choose the Instruction Scope

- **Root `AGENTS.md`:** Repository-wide guidance, core stack rules, build/test commands, and tool boundaries.
- **Nested `AGENTS.md`:** Scoped policies for specific subdirectories (e.g. `docs/`, `project_docs/`, or distinct packages). Do not duplicate root rules into nested files; only include what differs.

### 4. Write High-Signal Guidance

Include only what actively guides agent decisions:
- **Repository Map:** Key directories and boundaries.
- **Commands:** Exact commands with proper tool flags (e.g., `bun dev`, `bun run build`, `bun run lint`).
- **Safety Boundaries:** Production data, destructive commands, or protected directories.
- **Style & Code Rules:** Version-specific framework constraints (e.g., Next.js 16 async `params`, Server Components).

Write direct, testable statements:
```markdown
- Run commands from repository root with Bun: `bun dev` for Turbopack dev server, `bun run build` for production check.
```

### 5. Validate Before Handoff

1. Re-read each modified `AGENTS.md` completely.
2. Confirm all paths, files, and scripts referenced exist.
3. Verify that changes do not conflict with parent or child instruction files.

---

## Example: Verified Root AGENTS.md

When evidence confirms Bun and Next.js 16:

```markdown
# Agent Instructions

## Commands
- Package manager: Bun (`bun@1.4.1`). Run all commands from repository root.
- Install: `bun install`
- Dev server: `bun dev` (runs Next.js with Turbopack)
- Build: `bun run build`
- Lint: `bun run lint`

## Architecture & Code Conventions
- Framework: Next.js 16 (App Router) + React 19 + Tailwind CSS v4.
- Route parameters (`params`, `searchParams`) and dynamic request functions (`cookies()`, `headers()`) must always be awaited asynchronously.
- Respect subtree policies (e.g. `.agents/project_docs/AGENTS.md`).
```

---

## Best Practices

- ✅ **Do:** Base every command on project manifests and lockfiles.
- ✅ **Do:** Make focused diffs rather than reformatting the entire file.
- ✅ **Do:** Keep instructions concise, pragmatic, and easy to scan.
- ❌ **Don't:** Overwrite automated framework blocks (`next dev` blocks).
- ❌ **Don't:** Assume commands or dependencies that are not in the repository.