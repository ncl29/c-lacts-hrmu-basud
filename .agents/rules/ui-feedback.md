---
trigger: always_on
---

# UI feedback and surfaces: toast, modal, or page

## Toast (Sonner)
- Use for non-blocking results: success, error, info, and async outcomes (uploads, sync, API responses).
- Use `toast.promise()` for async actions. Keep messages to one plain-language line, with no technical error dumps.
- Never put forms, inputs, or required decisions in a toast.
- Mount `<Toaster />` once in the root layout. One toast per action.

## Choosing a surface for create, edit, and view
Pick the lightest surface that fits the amount of information. Do not default to a modal.

| Content | Surface |
|---|---|
| One field (rename, toggle, quick status change) | Inline edit or popover |
| Small form (about 1-4 simple fields, single step) or a short read-only summary | Modal (`Dialog`) |
| Medium form or details where the user needs the underlying list or page for context | Side sheet / drawer |
| Large form (many fields, sections, file uploads, rich text), multi-step flow, or full detail view | Dedicated page or route |

Rules of thumb:
- If the content scrolls inside the modal, it should probably be a sheet or a page.
- If the user may want to link to it, bookmark it, refresh it, or use the back button, make it a page.
- Multi-step flows belong on a page (or a stepper page), never in a modal.
- Previews and detail views use a modal only when they are short and viewed in isolation.

## Modal (Dialog)
- Use only when the table above says so: small, single-purpose input or a brief view.
- Use `AlertDialog` for destructive or irreversible actions (delete, discard, logout) with explicit confirm and cancel.
- Every modal needs a title, a close button, Esc/overlay dismiss (except destructive AlertDialog), and focus trapping.
- Keep modals small and single-purpose. Never stack them.

## Combining
- Modal or sheet action succeeds: close it, then show a success toast.
- Modal or sheet action fails: keep it open and show an error toast.
- Page-based form succeeds: navigate to the relevant page (list or detail) and show a success toast.
- Page-based form fails: stay on the page and show an error toast.
- Validation errors go inline under the field, never in a toast or modal.

## Never
- Use `alert()`, `confirm()`, or `prompt()`.
- Use a modal just to show a message, or a toast to ask for a decision.
- Put a long or multi-step form in a modal just for consistency.