---
name: antigravity-skill-orchestrator
description: "A meta-skill that understands task requirements, dynamically selects appropriate skills, tracks successful skill combinations using local memory or MCP, and prevents skill overuse for simple tasks."
category: meta
risk: safe
source: community
tags: "[orchestration, meta-skill, agent-memory, task-evaluation]"
date_added: "2026-03-13"
---

# antigravity-skill-orchestrator

## Overview

The `skill-orchestrator` is a meta-skill designed to enhance the AI agent's ability to tackle complex problems. It acts as an intelligent coordinator that first evaluates the complexity of a user's request. Based on that evaluation, it determines if specialized skills are needed. If they are, it selects the right combination of available skills, tracks these combinations using local persistent memory or an MCP memory server for future reference, and guides the agent through the execution process. Crucially, it includes strict guardrails to prevent the unnecessary use of specialized skills for simple tasks that can be solved with baseline capabilities.

## When to Use This Skill

- Use when tackling a complex, multi-step problem that likely requires multiple domains of expertise.
- Use when you are unsure which specific skills are best suited for a given user request, and need to discover or select them.
- Use when the user explicitly asks to "orchestrate", "combine skills", or "use the best tools for the job" on a significant task.
- Use when you want to look up previously successful combinations of skills for a specific type of problem.

## Core Concepts

### Task Evaluation Guardrails
Not every task requires a specialized skill. For straightforward issues (e.g., small CSS fixes, simple script writing, renaming a variable), **DO NOT USE** specialized skills. Over-engineering simple tasks wastes tokens and time. 

Additionally, the orchestrator is strictly forbidden from fabricating non-existent skills. Its purpose is to combine and use existing skills provided in the local `.agents/skills/` directory or curated community ecosystems.

Before invoking any skills, evaluate the task:
1. **Is the task simple/contained?** Solve it directly using ordinary file editing, search, and terminal capabilities available in the current environment.
2. **Is the task complex/multi-domain?** Only then should you proceed to orchestrate skills.

### Skill Selection & Combinations
When a task is deemed complex, identify the necessary domains across the 6 categorized skill suites in `.agents/skills/`:
- **frontend**: `react-nextjs-development`, `nextjs-best-practices`, `nextjs-app-router-patterns`, `react-patterns`, `react-best-practices`, `shadcn`, `tailwind-patterns`, `anti-slop-design`
- **database-data**: `drizzle-orm-expert`, `drizzle-migration-conflict`, `database-design`, `sql-optimization-patterns`
- **security-auth**: `access-matrix`, `access-review`, `api-security-best-practices`, `auth-implementation-patterns`
- **code-quality**: `clean-code`, `super-code`, `typescript-expert`, `zod-validation-expert`, `vibe-code-auditor`, `vibe-code-cleanup`, `yes-md`, `agents-md`
- **testing-debugging**: `systematic-debugging`, `error-detective`, `error-handling-patterns`, `web-performance-optimization`
- **agent-context**: `antigravity-skill-orchestrator`, `agent-memory-discipline`, `conversation-memory`, `context-fundamentals`, `context-optimization`, `context-compression`, `context-degradation`

### Master Skill Catalog
If locally available skills are insufficient for an expansive task, consult the Antigravity master skill catalog at:
`https://raw.githubusercontent.com/sickn33/agentic-awesome-skills/main/CATALOG.md`

### Memory Integration (File-Based or MCP)
To build institutional knowledge across sessions, track successful combinations.
- **Default (File-Based):** Read and append entries in `.agents/skills/agent-context/agent-memory-discipline/memory/skill-combinations.md` using standard file tools (`view_file`, `write_to_file`).
- **MCP Integration (if configured):** If a memory MCP server is active in the environment, use `memory_search` and `memory_write`. If not present, fall back cleanly to file-based notes without failing.

---

## Step-by-Step Guide

### 1. Task Evaluation & Guardrail Check
1. Read the user's request.
2. Ask yourself: *"Can I solve this efficiently with standard file editing and terminal commands?"*
3. If **YES**: Proceed directly without invoking specialized skills. Stop orchestration here.
4. If **NO**: Proceed to Step 2.

### 2. Retrieve Past Combinations
1. Check existing memory notes in `.agents/skills/agent-context/agent-memory-discipline/memory/skill-combinations.md` (or run `memory_search` if an MCP memory server is enabled).
2. If a previously successful combination fits the current task, review the approach and proceed to execution.
3. If no matching combination is found, continue to Step 3.

### 3. Discover and Select Skills
1. Analyze the core requirements (e.g., Next.js 16 App Router UI + database layer).
2. Map requirements to locally verified skills in `.agents/skills/`:
   - Full-stack React/Next.js features: `react-nextjs-development` + `shadcn`
   - Database schemas/queries: `drizzle-orm-expert`
   - Deep debugging & logs: `systematic-debugging` + `error-detective`
   - Quality & governance: `super-code` + `yes-md`
   - Persistent conventions: `agent-memory-discipline`
3. If local skills do not cover an essential third-party service, inspect the master catalog.
4. Select the minimal set of skills needed. **Do not over-select.**

### 4. Apply Skills and Track the Combination
1. Execute the task following the guidelines of the chosen skills.
2. After successful verification, record the combination into `.agents/skills/agent-context/agent-memory-discipline/memory/skill-combinations.md` (or call `memory_write` if MCP is enabled).
   - Format: `[active <YYYY-MM-DD>] Task Domain: <Description>. Skills: <Skill1 + Skill2>. Outcome: <Why it succeeded>.`

---

## Examples

### Example 1: Handling a Simple Task (The Guardrail in Action)
**User Request:** "Change the primary color variable in `globals.css` to slate-900."  
**Action:** The skill orchestrator evaluates the task, identifies it as a single-file styling edit, and bypasses specialized skills to perform a direct edit.

### Example 2: Complex Feature Orchestration
**User Request:** "Implement a new vehicle dispatch dashboard route with server-side filtering, database persistence, and investigate why hydration errors occur on load."  
**Action:**
1. Task is complex.
2. Select skills: `react-nextjs-development` (for Next.js 16 async App Router page), `drizzle-orm-expert` (for DB querying), and `error-detective` (to diagnose the hydration mismatch).
3. Coordinate execution across each domain.
4. Log the successful combination into memory upon completion.

---

## Best Practices

- ✅ **Do:** Always evaluate task complexity *before* activating specialized skills.
- ✅ **Do:** Keep the number of orchestrated skills as small as possible (1–3 max per task).
- ✅ **Do:** Verify that any required packages are installed in `package.json` before applying stack-specific skills.
- ❌ **Don't:** Fabricate or invoke non-existent skill names (always verify against `.agents/skills`).
- ❌ **Don't:** Fail if an MCP memory tool is unavailable; fall back to file-based memory notes.