---
name: react-nextjs-development
description: "React 19 and Next.js 16+ application development with App Router, Turbopack, Server Components, Async Request APIs, TypeScript, Tailwind CSS v4, and modern full-stack patterns."
category: development
risk: safe
source: personal
date_added: "2026-10-09"
---

# React 19 & Next.js 16 Development Workflow

## Overview

Specialized workflow and patterns for developing high-performance applications with **Next.js 16+**, **React 19+**, **TypeScript 5+**, and **Tailwind CSS v4**.

> [!IMPORTANT]
> **Next.js 16 Breaking Conventions:**
> 1. **Async Request APIs are mandatory:** Synchronous access to `params`, `searchParams`, `cookies()`, and `headers()` has been completely removed.
> 2. **Turbopack is the default:** Used out-of-the-box for `next dev` and `next build`.
> 3. **`proxy.ts` replaces `middleware.ts`:** Middleware file conventions have migrated to `proxy.ts` running in the Node.js runtime.
> 4. **Modern Caching & Tags:** Use `updateTag` for immediate read-your-writes inside Server Actions, `revalidateTag(tag, profile)` with a cache profile, and `cacheComponents: true`.

---

## Technology Stack

| Category | Technology & Version | Description |
|---|---|---|
| **Framework** | Next.js 16.4+ (App Router) | Turbopack default, Async Request APIs, Cache Components |
| **Library** | React 19.3+ | React Compiler ready, `useActionState`, Actions, View Transitions |
| **Language** | TypeScript 5+ | Strict type inference with Next.js type helpers |
| **Styling** | Tailwind CSS v4 & Turbopack | Modern CSS-first config (`@import "tailwindcss"`, `@theme`) |
| **UI Components** | Shadcn UI & Base UI (`@base-ui/react`) | Headless, accessible primitives with CVA (`class-variance-authority`) |
| **Runtime / Package Manager** | Bun 1.4+ | Fast installation, native script execution (`bun dev`, `bun run build`) |

---

## 1. App Router & Routing Patterns

### Async Route Parameters (`PageProps` & `LayoutProps`)
In Next.js 16, route parameters (`params`) and search parameters (`searchParams`) are strictly **Promises** and must be awaited.

```tsx
// app/vehicles/[id]/page.tsx
interface PageProps {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}

export default async function VehiclePage(props: PageProps) {
  const { id } = await props.params;
  const search = await props.searchParams;
  const tab = typeof search.tab === "string" ? search.tab : "overview";

  return (
    <main className="p-6 space-y-4">
      <h1 className="text-2xl font-bold tracking-tight">Vehicle Details: {id}</h1>
      <p className="text-muted-foreground">Active Tab: {tab}</p>
    </main>
  );
}
```

### Route Handlers (`RouteContext`)
In `route.ts`, the context parameters are also asynchronous:

```ts
// app/api/vehicles/[id]/route.ts
import { NextResponse, type NextRequest } from "next/server";

export async function GET(
  request: NextRequest,
  context: { params: Promise<{ id: string }> }
) {
  const { id } = await context.params;
  return NextResponse.json({ id, status: "active" });
}
```

### Proxy Boundary (`proxy.ts`)
`middleware.ts` is deprecated in Next.js 16 in favor of `proxy.ts`:

```ts
// proxy.ts (root of project)
import { NextResponse, type NextRequest } from "next/server";

export function proxy(request: NextRequest) {
  const authHeader = request.headers.get("authorization");
  if (!authHeader && request.nextUrl.pathname.startsWith("/api/protected")) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }
  return NextResponse.next();
}

export const config = {
  matcher: ["/api/protected/:path*", "/admin/:path*"],
};
```

---

## 2. Server vs. Client Boundary Architecture

### Default to React Server Components (RSC)
- Fetch data directly in Server Components where possible.
- Keep Client Components at the leaves of the component tree to minimize the client bundle size.
- Use `"use client"` only for components requiring browser APIs, state hooks (`useState`), or event handlers (`onClick`, `onChange`).

### Clean Composition Pattern
Pass server-rendered components as `children` into Client Components to preserve server-side streaming:

```tsx
// components/dashboard-shell.tsx (Client Component)
"use client";

import { useState } from "react";

export function DashboardShell({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  return (
    <div className="flex min-h-screen">
      <aside className={sidebarOpen ? "w-64" : "w-16"}>
        <button onClick={() => setSidebarOpen(!sidebarOpen)}>Toggle</button>
      </aside>
      <section className="flex-1">{children}</section>
    </div>
  );
}
```

---

## 3. Data Mutations & Server Actions

### Next.js 16 Mutation Semantics (`updateTag` & `refresh`)
When mutating data in Server Actions, use `updateTag` for instant read-your-writes updates:

```ts
// app/actions/vehicles.ts
"use server";

import { updateTag, refresh } from "next/cache";

export async function updateVehicleStatus(formData: FormData) {
  const vehicleId = formData.get("vehicleId") as string;
  const status = formData.get("status") as string;

  // Perform database write operation here...
  
  // Immediately invalidate and refresh cache for this entity
  updateTag(`vehicle-${vehicleId}`);
  
  // Refresh client router if needed
  refresh();
  
  return { success: true };
}
```

### Form Mutations with React 19 `useActionState`
Use React 19's native `useActionState` for managing pending states, errors, and form actions cleanly:

```tsx
// components/vehicle-status-form.tsx
"use client";

import { useActionState } from "react";
import { updateVehicleStatus } from "@/app/actions/vehicles";

export function VehicleStatusForm({ vehicleId }: { vehicleId: string }) {
  const [state, formAction, isPending] = useActionState(
    async (prevState: any, formData: FormData) => {
      try {
        return await updateVehicleStatus(formData);
      } catch (err: any) {
        return { error: err.message || "Failed to update" };
      }
    },
    null
  );

  return (
    <form action={formAction} className="space-y-4">
      <input type="hidden" name="vehicleId" value={vehicleId} />
      <select name="status" disabled={isPending} className="border p-2 rounded">
        <option value="available">Available</option>
        <option value="maintenance">Maintenance</option>
        <option value="dispatched">Dispatched</option>
      </select>
      <button
        type="submit"
        disabled={isPending}
        className="px-4 py-2 bg-primary text-primary-foreground rounded disabled:opacity-50"
      >
        {isPending ? "Updating..." : "Save Status"}
      </button>
      {state?.error && <p className="text-destructive text-sm">{state.error}</p>}
    </form>
  );
}
```

---

## 4. Styling & Design Tokens (Tailwind CSS v4)

Tailwind CSS v4 uses a modern CSS-first configuration rather than `tailwind.config.js`:
- Core theme variables are declared directly in `@theme` blocks inside CSS.
- Use `cn()` helper (`clsx` + `twMerge` or `class-variance-authority`) for dynamic class merging.
- Ensure all interactive elements have rich visual affordances (hover, active, focus-visible states).

---

## 5. Development & Verification Workflow

1. **Package Management:** Run dependencies and scripts with Bun:
   ```bash
   bun dev     # Starts Next 16 Turbopack dev server
   bun run build # Turbopack production build check
   bun run lint  # ESLint check
   ```
2. **Quality Gates:**
   - [ ] No synchronous access to `params`, `searchParams`, `cookies()`, or `headers()`.
   - [ ] No Client Component importing server-only code.
   - [ ] Proper error handling and pending states in forms using React 19 Actions.
   - [ ] Zero TypeScript compile errors (`bun x tsc --noEmit`).