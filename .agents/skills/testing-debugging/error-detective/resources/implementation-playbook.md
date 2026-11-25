# Error Detective Implementation Playbook

This playbook provides actionable patterns, regex utilities, and diagnostic procedures for investigating errors, stack traces, and anomalies across Next.js, React, Node.js, and TypeScript applications.

---

## 1. Diagnostic Triage Checklist

When encountering an error:
1. **Identify the Boundary:**
   - Client-side error (hydration mismatch, browser runtime exception, network failure).
   - Server-side error (Server Component rendering, Server Action, API Route, database failure).
   - Build-time / Compile error (TypeScript error, Turbopack bundle failure, missing dependency).
2. **Isolate the Origin:**
   - Inspect the first application frame in the stack trace (ignore `node_modules` internal frames).
   - Trace backwards from the symptom to the state or input change that triggered it.
3. **Check Recent Changes:**
   - Correlate timestamps of the failure with recent code modifications, package updates, or migrations.
4. **Reproduce & Verify:**
   - Formulate a hypothesis based on log data.
   - Test the specific edge case with minimal reproducer before applying fixes.

---

## 2. Common Next.js 16 & React 19 Error Signatures

### A. Async Request APIs Access Errors
* **Symptom:** `Error: Route "...": \`params\` should be awaited before using its properties.`
  or `Error: \`cookies\` was called without \`await\`.`
* **Root Cause:** Next.js 16 strictly removes synchronous access to `params`, `searchParams`, `cookies()`, and `headers()`.
* **Fix:**
  ```tsx
  // Bad (Legacy Next 14)
  export default function Page({ params }: { params: { id: string } }) {
    return <div>{params.id}</div>;
  }

  // Good (Next.js 16)
  export default async function Page(props: { params: Promise<{ id: string }> }) {
    const { id } = await props.params;
    return <div>{id}</div>;
  }
  ```

### B. React Hydration Mismatch
* **Symptom:** `Hydration failed because the server-rendered HTML didn't match the client.`
* **Root Cause:**
  - Rendering dates, random numbers (`Math.random()`), or browser-only APIs (`window`, `localStorage`) on initial server render.
  - Invalid HTML hierarchy (e.g. `<p>` nesting `<div>`, `<tr>` outside `<tbody>`).
* **Detection Regex:**
  ```regex
  (Hydration failed|did not match|Text content does not match server-rendered HTML)
  ```
* **Fix:**
  - Defer client-only values with `useEffect` or `useSyncExternalStore`.
  - Fix invalid DOM element nesting.

### C. Server Actions / Turbopack Boundary Violations
* **Symptom:** `Module not found: Can't resolve '...' in client component.`
* **Root Cause:** Client Component attempting to import server-only packages (e.g. `fs`, database drivers).
* **Fix:**
  - Ensure server functions are in dedicated `"use server"` files.
  - Use `server-only` import guards to catch leaks at compile-time:
    ```bash
    bun add server-only
    ```
    ```ts
    import 'server-only';
    ```

---

## 3. Log Parsing & Regex Utilities

### Extract Error Types and Locations
* Match TypeScript compile errors:
  ```regex
  ^([a-zA-Z0-9_\-\.\/]+)\((\d+),(\d+)\):\s+error\s+(TS\d+):\s+(.*)$
  ```
* Match Stack Trace Frames:
  ```regex
  ^\s*at\s+(?:(?<fn>[\w\.\<\>]+)\s+\()?(?<file>[^\:\)]+)\:(?<line>\d+)\:(?<col>\d+)\)?
  ```
* Match HTTP 5xx Server Logs:
  ```regex
  (?:POST|GET|PUT|DELETE)\s+[^\s]+\s+5\d{2}\s+(?:\d+ms)
  ```

---

## 4. Root Cause Hypothesis Framework

When documenting or reporting an error:

```markdown
### Incident / Bug Analysis
- **Observed Behavior:** <What failed and what the user/client experienced>
- **Error Signature:** `<Exact error message and topmost relevant frame>`
- **Root Cause Hypothesis:** <Why did this occur based on code and state analysis>
- **Evidence:** <Log excerpts, line numbers, or variable values>
- **Immediate Resolution:** <Code change to resolve the defect>
- **Prevention Strategy:** <Type check, automated test, or validation guardrail>
```
