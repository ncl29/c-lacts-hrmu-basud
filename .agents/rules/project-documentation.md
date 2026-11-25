---
trigger: always_on
---

# Project Documentation Rule

Always treat our project documentation as the source of truth. Before planning, designing, or writing code, check it for anything relevant to the task. The panel's feedback has the highest priority.

## Source Files (in `.agents/docs/`)

| File | Purpose |
| --- | --- |
| `proposals-and-specs/*.pdf` | Approved HRU Basud project proposal: scope, objectives, features, and constraints |
| `defense-feedback.md` | Panel feedback from defense / milestone reviews. **Highest priority** |
| `_suggestions.md` | Additional suggestions, domain observations, and improvements |
| `diagrams/DFD_0.png`, `diagrams/DFD1.png` | Data flow diagrams (context level and level 1) |
| `diagrams/ERD.png` | Entity relationship diagram (database structure) |
| `diagrams/SysArch.png` | System architecture diagram |
| `AGENTS.md` | General agent instructions and policy |

## Priority Order

When sources conflict, follow this order:

1. `defense-feedback.md` (panel feedback)
2. The project proposal PDF
3. `_suggestions.md`
4. Diagrams (DFD, ERD, SysArch)
5. Other rules in `.agents/rules/`

If something you are asked to do contradicts the panel feedback or the proposal, **stop and tell the user** instead of silently picking one.

## Required Behavior

1. **Read `defense-feedback.md` first** for any feature, module, schema, or UI work. Apply every item that relates to the task.
2. **Stay within the proposal's scope.** Do not add features, modules, or user roles that are not in the proposal unless the user asks for them.
3. **Match the diagrams.** Entities, fields, relationships, and data flows in code must be consistent with `ERD.png`, `DFD_0.png`, and `DFD1.png`. Module boundaries and tech choices must follow `SysArch.png`.
4. **Check `_suggestions.md`** for related improvements and mention them when relevant.
5. **Cite your sources.** When a decision comes from the documentation, say which file it came from (e.g. "Per defense-feedback.md, ...").
6. **Flag gaps and mismatches.** If the code and the documentation disagree, or the docs are unclear, point it out and ask before proceeding.
7. **Keep docs in sync.** If a requested change means a diagram or document is out of date (e.g. a new table not in the ERD), tell the user so they can update it.

## Before Finishing a Task

- [ ] Does this address the relevant panel feedback?
- [ ] Is it within the proposal's scope?
- [ ] Is it consistent with the ERD, DFDs, and system architecture?
- [ ] Did I note any conflicts or documentation updates needed?
