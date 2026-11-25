---
name: context-optimization
description: "Techniques for maximizing effective context capacity, reducing token overhead, and optimizing attention allocation through prompt caching, observation masking, token pruning, dynamic budgeting, and context isolation."
risk: none
source: community
date_added: "2026-09-04"
---

# Context Optimization Strategies

Context optimization is the engineering discipline of extending effective context capacity and maximizing the signal-to-noise ratio within an agent's context window. While context compression handles condensing long conversation histories after they grow, context optimization actively minimizes token waste, prevents context bloating, and optimizes attention allocation from the first turn.

## When to Use
Activate this skill when:
- Designing agent architectures, prompt pipelines, or tool execution loops
- Reducing token consumption, API costs, or inference latency
- Improving retrieval accuracy and instruction adherence across long sessions
- Structuring prompts to maximize prefix caching (KV-cache hits)
- Managing large tool outputs (logs, file diffs, search results) that threaten context budgets
- Designing sub-agent delegation boundaries to isolate context

## Core Concepts

Context optimization operates on five primary pillars:

1. **Prefix Alignment & Prompt Caching**: Structuring prompts so that static, reusable content resides at the exact prefix of the prompt. Modern LLM providers offer dramatic cost and latency reductions (up to 90% cost drop and 80% lower latency) when prompt prefixes match previous requests.
2. **Observation Masking & Truncation**: Replacing bulky, raw tool outputs (which account for over 80% of agent tokens in coding sessions) with concise summaries, targeted line slices, or external disk references.
3. **Token Pruning & Semantic Deduplication**: Actively removing obsolete intermediate turns, superseded file versions, duplicate queries, and repetitive error dumps.
4. **Dynamic Context Budgeting**: Enforcing explicit token allocations for system rules, working memory, message history, tool observations, and response buffers.
5. **Sub-Agent Context Isolation**: Delegating expansive, noisy sub-tasks (e.g. repo search, large file inspection) to ephemeral sub-agents, returning only synthesized outputs to the primary orchestrator.

---

## Detailed Topics

### 1. Prefix Alignment & Prompt Caching

Modern inference engines utilize KV (Key-Value) caching to reuse pre-computed attention states across requests. Any variation in tokens at the beginning of a prompt invalidates the cache for all subsequent tokens.

#### The Static-to-Dynamic Ordering Rule
Structure every prompt strictly from most static to most dynamic:

```
[System Instructions & Identity]       <-- Highly static (Cached across all sessions)
[Project Rules & Schemas]             <-- Static per project (Cached within project)
[Tool Definitions]                    <-- Static per runtime (Cached across calls)
[Retrieved Reference Documents]       <-- Semi-static (Cached across related tasks)
---------------- CACHE BREAK BOUNDARY ----------------
[Conversation History]               <-- Dynamic per session (Progressively cached)
[Latest User Query & Tool Results]   <-- Highly dynamic (Never cached upfront)
```

#### Cache-Friendly Design Invariants
- **Eliminate Dynamic Timestamps in System Prompts**: Do not inject seconds or milliseconds into the system prompt. Inject current timestamps in the dynamic user turn instead.
- **Deterministic Serialization**: Ensure tool definitions, function schemas, and rule lists serialize with deterministic key ordering (e.g., sorted keys) across requests.
- **Cache Checkpointing**: Place explicit prompt cache checkpoints after tool definitions and project rules to maximize cache hit rates.

---

### 2. Observation Masking & Selective Tool Retention

Tool outputs (observations) comprise up to 83.9% of context tokens in autonomous agent trajectories. Storing full raw command logs, entire 2,000-line files, or voluminous web pages directly in the active context window rapidly exhausts attention budgets.

#### Strategies for Observation Management

| Technique | How It Works | Ideal Use Case |
| :--- | :--- | :--- |
| **Windowed Slicing** | Inspect files via `StartLine` and `EndLine` slices rather than whole files. | Reading code, log files, configuration blocks |
| **Observation Masking** | Replace completed tool outputs in conversation history with 1-line reference stubs once digested. | Successful edits, repetitive directory listings |
| **Externalization (Scratchpad)** | Write large data to temporary scratch files or artifacts; keep only file paths in context. | Build logs, test suites, large JSON responses |
| **Relevance Filtering** | Parse output with regex or structured extraction before returning to context. | Search results, database queries, terminal output |

---

### 3. Token Pruning & Working Memory Distillation

As conversations progress, intermediate exploratory turns become noise once decisions are reached.

#### Pruning Candidates
- **Failed Trial Retries**: When an agent tries 3 syntax variations before finding the correct command, prune the failed error traces once the successful solution is confirmed.
- **Superseded File Versions**: When a file is read, modified, and verified, older reads of that same file should be pruned from the active context.
- **Repetitive Confirmations**: Routine tool acknowledgments should be stripped or collapsed to preserve context signal.

---

### 4. Dynamic Context Budgeting

Do not treat the context window as a bottomless bucket. Partition the available token budget into explicit functional zones:

```
Total Context Window Budget (e.g., 100% / 128k - 1M tokens)
├── System Prompt & Standing Rules:  10 - 15% (Hard ceiling)
├── Tool Schemas & Definitions:       5 - 10%
├── Working Memory / Scratchpad:     15 - 20%
├── Dynamic Message History:          35 - 45% (Sliding window / compacted)
└── Reserved Generation Buffer:       15 - 20% (Reserved for model output)
```

When Dynamic Message History reaches its 45% budget allocation, trigger compaction or pruning immediately rather than waiting for physical context saturation.

---

### 5. Context Isolation via Ephemeral Sub-Agents

The most effective context optimization for multi-domain tasks is **spatial isolation**. Instead of stuffing exploration into the main agent's working context:

1. **Spawn an Ephemeral Sub-Agent**: Provide the sub-agent with a dedicated, isolated context window containing only the immediate sub-task instructions.
2. **Execute Exploration**: The sub-agent reads files, runs searches, and parses large logs in its own context.
3. **Return Synthesized Signal**: The sub-agent terminates and returns a concise, 10–20 line distilled report to the parent agent.
4. **Result**: Hundreds of thousands of exploratory tokens are discarded, keeping the parent agent's context clean and hyper-focused.

---

## Practical Guidance & Checklist

### Daily Context Optimization Checklist
- [ ] Are tool schemas ordered deterministically?
- [ ] Are system prompts free of dynamic timestamps or ephemeral session IDs?
- [ ] Are file reads limited to targeted line ranges (`StartLine` / `EndLine`)?
- [ ] Are large command outputs directed to scratch files or filtered with grep?
- [ ] Are completed sub-tasks summarized before starting new phases?
- [ ] Is critical domain guidance placed at the beginning or end of context?

---

## Examples

### Example 1: Anti-Pattern vs. Optimized Prefix Layout

**Anti-Pattern (Busts Prompt Cache Every Turn):**
```markdown
[System Prompt]
Current Time: 2026-10-10 03:01:24.182 UTC  <-- Breaks cache on every second!
Session ID: sess_94829183                 <-- Breaks cache across sessions!

[Rules]
...
```

**Optimized Pattern (Maximizes Cache Hits):**
```markdown
[System Prompt - Static]
You are a software engineering assistant.
[Project Rules - Static]
- Framework: Next.js 16 with Turbopack
- Styling: Tailwind CSS v4 on 4px grid
[Tool Definitions - Static]
...
--- CACHE CHECKPOINT ---
[User Turn - Dynamic]
Timestamp: 2026-10-10 03:01:24
User request: Fix navbar mobile drawer animation.
```

### Example 2: Observation Masking in Trajectories

**Before Optimization (5,000 tokens of raw build log):**
```text
Agent: run_command("bun run build")
Output:
[... 4,800 lines of bundle compilation, module resolutions, chunk maps ...]
Exit code: 0
```

**After Optimization (50 tokens):**
```text
Agent: run_command("bun run build")
Output:
✓ Next.js build completed in 781ms. Static routes generated: / and /dashboard. Exit code: 0.
Full log persisted at: .system_generated/tasks/build-828.log
```

---

## Guidelines

1. **Order by Stability**: Place static content at prompt prefixes to maximize KV-cache reuse.
2. **Never Read Entire Large Files**: Use windowed line reading (`view_file` with ranges) for files > 100 lines.
3. **Externalize Large Artifacts**: Write verbose data to disk and reference filepaths rather than dumping contents into context.
4. **Mask Stale Observations**: Replace historical tool outputs with high-signal summary stubs after they are processed.
5. **Isolate Exploratory Work**: Delegate noisy searches and log inspections to ephemeral sub-agents.
6. **Enforce Budget Envelopes**: Monitor token usage per zone and compact before degradation begins.
7. **Keep System Prompts Lean**: Use progressive disclosure to load specialized domain skills on demand.
8. **Prune Intermediate Failures**: Discard dead-end error loops once a working approach is verified.

---

## Integration

This skill coordinates directly with the context engineering ecosystem:
- **`context-fundamentals`**: Establishes attention mechanics and progressive disclosure theory.
- **`context-degradation`**: Identifies degradation patterns that optimization prevents.
- **`context-compression`**: Handles historical turn condensation when budget envelopes fill.
- **`agent-memory-discipline`**: Provides rules for persisting decisions outside the active context window.

---

## Skill Metadata

**Created**: 2025-12-20  
**Last Updated**: 2026-10-10  
**Author**: Agent Skills for Context Engineering Contributors  
**Version**: 1.1.0  

## Limitations
- Use this skill to optimize context structure, token efficiency, and agent communication.
- Do not prune active error traces that are currently under investigation.
- Always preserve critical invariant rules and constraints during context curation.