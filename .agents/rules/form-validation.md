---
trigger: always_on
---

# Form validation

## Validation
- Use Zod for all form and input validation. Do not write manual validation logic or use another validation library.
- Define one schema per form in a separate file (`employee.schema.ts`). Get the TypeScript type from it with `z.infer`. Do not write the type by hand.
- For database-backed entities: Connect the form schema to Drizzle tables using `drizzle-zod` (e.g. `createInsertSchema(employees)`) or align field constraints directly. This guarantees client forms, server actions, and database schemas share a single source of truth.
- Use React Hook Form with `zodResolver` and the shadcn `Form` components (`FormField`, `FormItem`, `FormLabel`, `FormControl`, `FormMessage`).
- Reuse the same schema on the server (API route or server action). Never trust client-side validation alone.
- Write clear, specific error messages in the schema, such as "Enter a valid employee ID". Do not leave Zod's default messages.
- Validate on blur first, then on every change after an error appears (`mode: "onTouched"`). Validate everything on submit.
- On a failed submit, focus the first invalid field.

## Error design
- An invalid field turns red: red border (`border-destructive`) and a red focus ring (`ring-destructive`).
- The error message sits directly below the field in small red text (`text-sm text-destructive`), with a 4px gap. Use `FormMessage`, which does this by default.
- The label also turns red when the field is invalid.
- Show one message per field, and show nothing before the user has touched the field.
- Mark required fields with a red asterisk after the label. Do not rely on color alone: always show the message text, and set `aria-invalid` and `aria-describedby` (shadcn `FormControl` does this).
- Add an error icon inside or beside the message only if it helps readability, using a size from the 4px grid (16px).
- For server errors (for example, "Employee ID already exists"), use `form.setError` to show them on the matching field. Show general errors in a destructive `Alert` at the top of the form.
- Disable the submit button while submitting. Do not disable it just because the form is invalid, because users then can't see what is wrong.
- Use the same error style on every form in the project.
