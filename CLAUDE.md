# CLAUDE.md

Behavioral and engineering guidelines for AI coding assistants working on this PHP landing page + admin system project.

This project prioritizes:
- simplicity
- maintainability
- predictable architecture
- fast loading
- SEO
- conversion
- security
- minimal JavaScript
- stable CRUD behavior

Tradeoff:
Prefer practical and simple solutions over complex scalable architecture.

---

# 1. Think Before Coding

Do not assume requirements.

Before implementing:
- clarify unclear behavior
- state assumptions explicitly
- prefer the simplest working approach
- avoid adding features not requested
- present tradeoffs when multiple approaches exist

Never silently introduce:
- frameworks
- architecture changes
- abstractions
- speculative features

If something is unclear:
- stop
- explain uncertainty
- ask instead of guessing

---

# 2. Simplicity First

This is a landing page + admin system project, not an enterprise application framework.

Prefer:
- simple PHP rendering
- straightforward request flow
- lightweight JavaScript
- explicit logic
- predictable structure

Avoid:
- overengineering
- unnecessary abstractions
- premature optimization
- generic systems with single usage
- architecture for hypothetical future requirements

Always ask:
"Can this be simpler?"

If a solution can be implemented clearly with:
- PHP
- Tailwind
- vanilla JavaScript

prefer that over additional tooling.

---

# 3. Server-Rendered First

Prefer server-rendered HTML.

Rules:
- Render content in PHP whenever possible.
- Avoid client-side rendering unless necessary.
- Avoid SPA-like architecture.
- Avoid moving business logic into frontend JavaScript.

Prefer:
- PHP templates
- server-rendered SEO content
- simple form submissions
- predictable page rendering

Avoid:
- API-first architecture unless required
- rendering critical content with JavaScript
- frontend-driven routing

---

# 4. Minimal JavaScript

JavaScript should enhance UX, not power the entire application.

Use JavaScript only for:
- modals
- tabs
- dropdowns
- sliders
- AJAX forms when useful
- lightweight interactivity

Prefer:
- vanilla JavaScript
- event delegation
- small isolated scripts

Avoid:
- frontend frameworks unless requested
- reactive architecture
- complex state systems
- large JS bundles
- unnecessary dependencies

---

# 5. TailwindCSS Rules

Use Tailwind consistently and predictably.

Rules:
- Follow existing spacing scale.
- Reuse utility patterns.
- Prefer utility classes over custom CSS.
- Keep responsive behavior consistent.

Avoid:
- inline styles
- excessive custom CSS
- inconsistent spacing
- deeply duplicated utility chains

Prefer:
- readable utility grouping
- reusable layout patterns
- simple responsive structure

---

# 6. PHP Rules

Keep PHP straightforward and maintainable.

Prefer:
- simple includes/templates
- readable procedural flow
- explicit logic
- predictable naming

Avoid:
- unnecessary OOP abstraction
- custom framework behavior
- magic helpers
- deeply nested architecture
- dynamic resolution systems

Business logic should:
- remain readable
- remain traceable
- not be spread across excessive layers

---

# 7. File Structure Discipline

Do not create unnecessary files or layers.

Before creating:
- helper
- utility
- class
- abstraction
- service
- component system

Ask:
"Will this actually be reused?"

Prefer:
- local logic
- simple partials
- flat structure when practical

Avoid:
- enterprise-style folder structures
- deeply nested directories
- abstraction layers for one-time use

---

# 8. SEO First

SEO is a core requirement.

Rules:
- Important content must exist in server-rendered HTML.
- Use semantic HTML.
- Maintain proper heading hierarchy.
- Use descriptive metadata.
- Optimize image alt text.

Prefer:
- fast first paint
- crawlable structure
- lightweight pages

Avoid:
- JS-rendered SEO content
- excessive third-party scripts
- blocking assets

---

# 9. Performance First

Performance matters more than architectural purity.

Rules:
- Minimize JavaScript usage.
- Minimize CSS bloat.
- Avoid unnecessary libraries.
- Optimize images.
- Lazy load when appropriate.

Prefer:
- lightweight DOM updates
- small scripts
- simple rendering logic

Avoid:
- animation-heavy interfaces
- expensive DOM operations
- unnecessary re-renders
- oversized frontend dependencies

---

# 10. Conversion-Focused UI

UI exists to support usability and conversion.

Prioritize:
- readability
- clear CTA
- mobile usability
- loading speed
- obvious navigation
- clear forms

Avoid:
- flashy UI
- unnecessary animation
- distracting interactions
- clever but confusing UX

Consistency matters more than uniqueness.

---

# 11. Form Handling

Forms must be reliable and predictable.

Rules:
- Validate on frontend and backend.
- Never trust frontend validation alone.
- Handle success/error states clearly.
- Sanitize all user input.

Prefer:
- simple form flows
- explicit validation rules
- readable error messages

Avoid:
- fragile frontend-only validation
- hidden validation behavior
- overly dynamic form systems

---

# 12. Security Awareness

Security is mandatory.

Rules:
- Escape output properly.
- Sanitize all inputs.
- Validate uploaded files.
- Never expose secrets.
- Use prepared statements.
- Never trust request data directly.

Avoid:
- SQL injection risk
- unsafe HTML rendering
- unrestricted uploads
- exposing stack traces publicly

Prefer:
- explicit validation
- predictable sanitization
- secure defaults

---

# 13. Surgical Changes

Only change what is necessary.

Do not:
- reformat unrelated files
- rewrite working architecture
- rename unrelated variables
- reorganize structure unnecessarily

Every changed line should relate directly to the request.

If unrelated issues are discovered:
- mention them separately
- do not fix unless requested

---

# 14. No Fake Confidence

Do not pretend something was verified if it was not.

Never claim:
- "fully tested"
- "SEO optimized"
- "production ready"
- "secure"

unless actually verified.

If unsure:
- say so explicitly
- ask instead of guessing

Do not invent:
- framework APIs
- plugin behavior
- server configuration assumptions

---

# 15. Communication Style

Be concise and technical.

Avoid:
- filler
- motivational language
- unnecessary apologies
- overexplaining obvious code

Prefer:
- direct reasoning
- practical tradeoffs
- implementation-focused responses

When suggesting changes:
- explain WHY
- explain IMPACT
- explain TRADEOFFS

---

# 16. Definition of Done

A task is done when:
- requirements are satisfied
- SEO is preserved
- performance is preserved
- changes are minimal
- code remains maintainable
- forms work correctly
- security basics are respected
- no unnecessary complexity was introduced

Prefer practical solutions over perfect architecture.

---

# 17. Admin System Architecture

The admin system should prioritize:
- predictability
- maintainability
- data integrity
- security
- simple CRUD flows

Avoid:
- unnecessary architecture complexity
- hidden magic behavior
- tightly coupled modules
- speculative backend systems

Prefer:
- straightforward request flow
- explicit validation
- readable business logic
- clear CRUD structure

---

# 18. CRUD Rules

CRUD operations should be explicit and easy to trace.

Rules:
- Validate all input.
- Sanitize all user data.
- Keep create/update/delete logic readable.
- Avoid hidden mutations.

Prefer:
- explicit field mapping
- predictable queries
- readable request handling
- clear success/error responses

Avoid:
- mass assignment without validation
- dynamic CRUD generators
- overly generic repository patterns

---

# 19. Authentication & Session Rules

Authentication logic must remain simple and secure.

Rules:
- Verify authentication on protected routes.
- Regenerate sessions after login.
- Validate permissions server-side.
- Never trust frontend permissions alone.

Avoid:
- exposing admin endpoints publicly
- storing sensitive logic in frontend code
- trusting hidden form inputs

Prefer:
- explicit permission checks
- predictable session flow
- readable auth handling

---

# 20. Database Rules

Database access must be predictable and secure.

Rules:
- Always use prepared statements.
- Avoid raw SQL concatenation.
- Keep queries readable.
- Optimize only when necessary.

Prefer:
- explicit SQL
- clear joins
- readable conditions

Avoid:
- hidden query builders
- unnecessary abstraction layers
- dynamic SQL generation for simple queries

Never trust request input directly in SQL.

---

# 21. Admin UI Rules

Admin interfaces should prioritize clarity and efficiency.

Prefer:
- readable tables
- consistent forms
- obvious actions
- confirmation before destructive actions
- stable layouts

Avoid:
- flashy animation
- hidden actions
- inconsistent spacing
- creative but confusing UI

Admin systems optimize for productivity, not visual experimentation.

---

# 22. Form Validation Rules

Validation must exist on both:
- frontend
- backend

Rules:
- Never trust client-side validation alone.
- Validate required fields explicitly.
- Return clear validation errors.
- Validate uploads carefully.

Avoid:
- silent validation failures
- inconsistent validation rules
- hidden validation side effects

Prefer:
- predictable validation flow
- readable error handling
- explicit rules

---

# 23. File Upload Rules

File uploads must be validated carefully.

Rules:
- Validate MIME types.
- Validate file extensions.
- Validate file size.
- Rename uploaded files safely.
- Never trust original filenames.

Avoid:
- unrestricted uploads
- executable uploads
- direct executable upload paths

Prefer:
- generated filenames
- organized upload directories
- predictable storage structure

---

# 24. Logging & Debugging Rules

Errors should be traceable and understandable.

Prefer:
- meaningful error messages
- structured logging
- readable debug flow

Avoid:
- silent catch blocks
- swallowed exceptions
- dumping raw errors to users

Production users should never see stack traces.

---

# 25. Admin Performance Rules

Admin systems should remain responsive with large datasets.

Rules:
- Use pagination for large tables.
- Avoid loading unnecessary records.
- Avoid N+1 query patterns.
- Avoid rendering huge datasets at once.

Prefer:
- server-side filtering
- indexed queries
- lightweight rendering

Avoid:
- loading full datasets unnecessarily
- repeated heavy queries
- rendering massive DOM trees

---

# 26. Route & URL Rules

Routes should remain predictable and readable.

Prefer:
- explicit route naming
- resource-oriented structure
- simple URL patterns

Avoid:
- deeply nested routes
- unclear endpoint naming
- magic route generation

URLs should be easy to understand and debug.

---

# 27. CMS Content Rules

CMS content should remain SEO-friendly and manageable.

Rules:
- Preserve semantic HTML structure.
- Support editable SEO metadata.
- Prevent content from breaking layouts.
- Sanitize rich text carefully.

Prefer:
- predictable content structure
- explicit content blocks
- manageable editing experience

Avoid:
- unsafe HTML rendering
- unrestricted embedded scripts
- overly flexible content systems

---

# 28. Deployment & Environment Rules

Environment configuration must remain explicit.

Rules:
- Never hardcode credentials.
- Use environment variables.
- Separate development and production config.
- Keep deployment predictable.

Avoid:
- environment-specific business logic
- hidden deployment assumptions
- secrets committed to repositories

---

# 29. Dependency Rules

Minimize dependencies.

Rules:
- Prefer existing project dependencies.
- Avoid adding libraries for trivial problems.
- Prefer native PHP/browser capabilities first.

Before adding a dependency:
- confirm existing tools cannot solve it
- confirm long-term value

Avoid:
- overlapping libraries
- unnecessary frontend plugins
- heavy packages for simple functionality

---

# 30. Final Checklist Before Completion

Before finalizing work, verify:

- Requirements are satisfied
- Scope stayed minimal
- Existing architecture respected
- SEO remains intact
- Performance remains reasonable
- Security basics are respected
- Validation exists where needed
- No duplicated logic introduced
- No unused files/imports remain
- Forms and CRUD flows remain functional
- Upload handling remains safe
- Diff remains review-friendly

If unsure:
- say so explicitly
- ask instead of guessing