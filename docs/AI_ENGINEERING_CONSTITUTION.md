# AI Engineering Constitution

# ويكي العالمية — Wiki World

**Document Version:** 1.0  
**Status:** Authoritative — Mandatory for All AI Coding Assistants  
**Date:** July 7, 2026  
**Classification:** Internal — Engineering Governance  
**Document Type:** AI Development Rulebook & Engineering Constitution  
**Authority Level:** Second only to [PROJECT_CONSTITUTION.md](./PROJECT_CONSTITUTION.md) (when published)

---

## Document Control

| Field | Value |
|-------|-------|
| Product Name (Arabic) | ويكي العالمية |
| Product Name (English) | Wiki World |
| Document Owner | Chief Software Architect / AI Engineering Lead |
| Applies To | Cursor, Claude, ChatGPT, GitHub Copilot, Codex, Windsurf, and all future AI coding assistants |
| Governing References | [PRD.md](./PRD.md), [KNOWLEDGE_DOMAIN_BLUEPRINT.md](./KNOWLEDGE_DOMAIN_BLUEPRINT.md), [DATABASE_DESIGN.md](./DATABASE_DESIGN.md), [BACKEND_ARCHITECTURE.md](./BACKEND_ARCHITECTURE.md), [API_SPECIFICATION.md](./API_SPECIFICATION.md) |

### Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-07-07 | Engineering Architecture | Initial AI engineering constitution |

### Enforcement Statement

Every line of AI-generated code, configuration, migration, test, or documentation **must comply** with this document and all governing references. When rules conflict, resolve in this order:

1. **Project Constitution** (when published)
2. **PRD** (product requirements and non-functional requirements)
3. **Knowledge Domain Blueprint** (domain model)
4. **Database Design** (persistence)
5. **Backend Architecture** (application structure)
6. **API Specification** (contracts)
7. **This document** (engineering process and quality gates)

If a requirement is missing from documentation, **stop and ask**. Do not guess.

---

## Table of Contents

1. [Engineering Philosophy](#1-engineering-philosophy)
2. [General AI Rules](#2-general-ai-rules)
3. [Documentation Rules](#3-documentation-rules)
4. [Code Quality Rules](#4-code-quality-rules)
5. [Folder Rules](#5-folder-rules)
6. [Component Rules](#6-component-rules)
7. [Backend Rules](#7-backend-rules)
8. [Frontend Rules](#8-frontend-rules)
9. [API Rules](#9-api-rules)
10. [Database Rules](#10-database-rules)
11. [Security Rules](#11-security-rules)
12. [Performance Rules](#12-performance-rules)
13. [Localization Rules](#13-localization-rules)
14. [Accessibility Rules](#14-accessibility-rules)
15. [Design Rules](#15-design-rules)
16. [AI Assistant Behavior Rules](#16-ai-assistant-behavior-rules)
17. [Git Rules](#17-git-rules)
18. [Testing Rules](#18-testing-rules)
19. [Refactoring Rules](#19-refactoring-rules)
20. [Code Review Checklist](#20-code-review-checklist)
21. [Definition of Done](#21-definition-of-done)
22. [Engineering Principles](#22-engineering-principles)
- [Appendix A: Wiki World Stack Reference](#appendix-a-wiki-world-stack-reference)
- [Appendix B: Naming Convention Registry](#appendix-b-naming-convention-registry)
- [Appendix C: Forbidden Patterns](#appendix-c-forbidden-patterns)
- [Appendix D: Pre-Implementation Checklist](#appendix-d-pre-implementation-checklist)
- [Appendix E: Decision Log Template](#appendix-e-decision-log-template)

---

# 1. Engineering Philosophy

Wiki World is a **mission-critical Islamic knowledge platform**. Engineering decisions affect scholarly trust, accessibility for Arabic-primary users, and long-term maintainability across dozens of future modules. AI assistants must operate as **senior engineers**, not code generators.

## 1.1 Core Mindset

| Principle | Meaning for AI |
|-----------|----------------|
| **Quality over speed** | Do not ship shortcuts to close a task. Incomplete work must be flagged, not hidden. |
| **Architecture before implementation** | Read governing docs before writing code. Design fits existing modules. |
| **Consistency over cleverness** | Prefer boring, readable patterns that match the codebase. |
| **Reuse before creating** | Search for existing services, components, validators, and DTOs first. |
| **Maintainability first** | The next engineer (human or AI) must understand the change in 6 months. |
| **Readable code** | Code is read more than written. Optimize for comprehension. |
| **Future-proof design** | Entity-first, modular, locale-aware. No one-off hacks that block Hadith, Fiqh, or future modules. |

## 1.2 System Thinking

Wiki World is not a collection of pages. It is:

- An **Entity-First Knowledge Graph** (persons, events, sources, verses — not documents)
- A **publishing pipeline** (draft → review → publish — editor-only)
- A **multilingual platform** (Arabic primary, English secondary, unlimited future locales)
- A **RAG-constrained AI layer** (citations mandatory, no fatwa authority)
- A **modular monolith** (Laravel backend, clear bounded contexts)

AI must think in **systems, aggregates, workflows, and contracts** — not in isolated files.

## 1.3 Product Constraints (Non-Negotiable)

| Constraint | Source | Engineering Implication |
|------------|--------|-------------------------|
| Editor-only publishing | PRD | No public write APIs for content |
| Corpus safety | PRD, API Spec | Draft entities never leak to public/search/AI retrieval |
| Source integrity | PRD, Blueprint | Publish blocked without minimum sourced references |
| Arabic-first | PRD | RTL, typography, and locale routing are first-class |
| Ja'fari editorial scope | PRD | Do not generalize sectarian content assumptions |
| Nonprofit / waqf model | PRD | No payment, subscription, or ads infrastructure unless specified |
| AI is not authority | PRD | Disclaimers, citations, refusal paths required |

## 1.4 Technical Stack (Authoritative)

| Layer | Stack |
|-------|-------|
| Backend | Laravel 12, PHP 8.4+ |
| Database | PostgreSQL 16+ |
| Cache / Queue / Rate limits | Redis 7+ |
| Search | Meilisearch (future), PostgreSQL FTS (fallback) |
| AI | OpenAI via swappable adapter |
| Storage | S3-compatible object storage |
| Infrastructure | Docker Compose (local), containerized deploy |
| API | REST `/api/v1/`, JSON, UUID public IDs |

Frontend stack will be defined in a dedicated frontend architecture document. Until then, follow PRD UX requirements (RTL, responsive, WCAG 2.1 AA, Core Web Vitals) and API Specification contracts.

---

# 2. General AI Rules

## 2.1 Absolute Prohibitions

AI **must never**:

| # | Prohibition |
|---|-------------|
| G-01 | Break module boundaries or layer boundaries defined in Backend Architecture |
| G-02 | Duplicate business logic across handlers, services, or components |
| G-03 | Duplicate UI components that already exist |
| G-04 | Create unnecessary files (helpers, utils, wrappers) for one-time operations |
| G-05 | Ignore or contradict governing documentation |
| G-06 | Invent API endpoints, request shapes, or response fields not in API Specification |
| G-07 | Invent database tables, columns, or relationships not in Database Design |
| G-08 | Invent entity types, relationship types, or workflow states not in Blueprint/Database Design |
| G-09 | Violate naming conventions (Appendix B) |
| G-10 | Ignore design system tokens when a design system exists |
| G-11 | Skip authorization checks on mutating operations |
| G-12 | Guess product requirements, acceptance criteria, or user intent |
| G-13 | Expose internal integer database IDs in API responses |
| G-14 | Return draft/unpublished content on public surfaces |
| G-15 | Hardcode user-facing strings (violates NFR-I18N-001) |
| G-16 | Commit secrets, API keys, or credentials |
| G-17 | Leave `console.log`, `dd()`, `dump()`, `var_dump()`, or debug breakpoints |
| G-18 | Leave `TODO`, `FIXME`, `HACK` without explicit user approval |
| G-19 | Make unrelated drive-by changes while implementing a feature |
| G-20 | Remove or weaken tests to make builds pass |

## 2.2 Mandatory Behaviors

AI **must always**:

| # | Requirement |
|---|-------------|
| M-01 | Read relevant governing documents before implementation |
| M-02 | Search the codebase for existing patterns before creating new abstractions |
| M-03 | State assumptions explicitly when documentation is ambiguous |
| M-04 | Ask the user when requirements are missing or contradictory |
| M-05 | Keep changes scoped to the requested task |
| M-06 | Preserve backward compatibility unless explicitly told otherwise |
| M-07 | Run lint and tests after substantive changes |
| M-08 | Update documentation when behavior or architecture changes |

## 2.3 When Information Is Missing

```
IF requirement not in PRD
AND not inferable from existing architecture
AND not a standard engineering best practice
THEN ask the user before implementing
```

**Do not** fill gaps with assumptions about:
- New entity types or modules
- New user roles or permissions
- New API endpoints
- New payment/auth providers
- Editorial policy changes
- AI behavior beyond RAG + citations

## 2.4 Conflict Resolution

If the user request contradicts governing documents:

1. **Stop**
2. **Cite** the conflicting rule (document + section)
3. **Propose** a compliant alternative
4. **Wait** for explicit approval before violating architecture

---

# 3. Documentation Rules

Documentation is part of the product (NFR-MAINT-004). Undocumented architecture changes are defects.

## 3.1 When to Update Documentation

| Change Type | Documents to Update |
|-------------|---------------------|
| New API endpoint or contract change | API_SPECIFICATION.md + changelog note |
| New table or schema change | DATABASE_DESIGN.md + migration notes |
| New module or bounded context | BACKEND_ARCHITECTURE.md |
| New entity/relationship type | KNOWLEDGE_DOMAIN_BLUEPRINT.md |
| New product behavior | PRD.md (or flag for product owner) |
| Engineering process change | This document |
| Reversible implementation decision | Appendix E decision log entry |

## 3.2 What Not to Document

| Avoid | Reason |
|-------|--------|
| Obvious code comments (`// increment counter`) | Noise |
| Duplicate API docs in random README files | Single source of truth |
| Implementation details in architecture docs | Architecture docs stay conceptual |
| Unapproved markdown files the user did not request | Scope control |

## 3.3 Code Comments

Comments are justified when they explain:

- Non-obvious business rules (e.g., why publish requires minimum sources)
- Workarounds with ticket/reference
- Security-sensitive constraints
- Performance trade-offs

Comments are **not** justified for:

- Describing what code obviously does
- Commenting out dead code (delete it)
- Leaving notes to future self (`// fix later`)

## 3.4 Decision Records

Significant architectural decisions require a decision log entry:

| Field | Content |
|-------|---------|
| ID | `ENG-D{NNN}` |
| Date | ISO date |
| Context | Problem being solved |
| Decision | What was chosen |
| Alternatives | What was rejected |
| Consequences | Trade-offs |

Store in Appendix E of this document or a linked `docs/decisions/` file when the project matures.

---

# 4. Code Quality Rules

## 4.1 Required Qualities

| Quality | Standard |
|---------|----------|
| Readable | Clear names; functions do one thing; max ~40 lines per function (guideline) |
| Simple | Minimum abstraction needed; no premature patterns |
| Modular | Single responsibility; bounded context alignment |
| Reusable | Shared logic in services/components, not copy-pasted |
| Strongly typed | PHP typed properties/returns; TypeScript strict mode on frontend |
| Self-explanatory | Names over comments |
| Production ready | Error handling, validation, auth, logging — no "prototype" code in main branch |

## 4.2 Prohibited Patterns

| Pattern | Replace With |
|---------|--------------|
| Magic numbers/strings | Named constants, enums, config |
| Hardcoded UI text | Locale files (`ar.json`, `en.json`) |
| Duplicated queries | Repository methods, query scopes, shared query objects |
| God classes / 500-line files | Split by responsibility |
| Deep nesting (>3 levels) | Early returns, extracted methods |
| Dead code | Delete |
| Unused imports/variables | Remove |
| Temporary hacks | Proper fix or documented decision with approval |
| Quick fixes that skip tests | Complete fix with tests |

## 4.3 Function and Component Size

| Unit | Guideline | Action if Exceeded |
|------|-----------|-------------------|
| Function | ≤ 40 lines | Extract helpers |
| Class | ≤ 300 lines | Split by responsibility |
| React component | ≤ 150 lines | Extract sub-components |
| Controller action | ≤ 15 lines | Delegate to application handler |

Controllers and route handlers **orchestrate only** — no business logic.

## 4.4 Error Handling

| Layer | Responsibility |
|-------|----------------|
| Domain | Throw domain exceptions for rule violations |
| Application | Catch, translate to API error codes |
| API | Return standard error envelope (API Spec §16) |
| Frontend | Display localized user messages; never expose stack traces |

Never swallow exceptions silently.

---

# 5. Folder Rules

## 5.1 No Random Folders

AI must not create ad-hoc directories such as:

- `helpers/`, `misc/`, `common2/`, `temp/`, `old/`
- Feature files dumped in project root
- Cross-module `shared/` without architectural approval

## 5.2 Backend Structure (Laravel Modular Monolith)

Follow Backend Architecture module boundaries:

```
app/
├── Modules/
│   ├── Authentication/
│   ├── Users/
│   ├── KnowledgeCore/
│   ├── Articles/
│   ├── Quran/
│   ├── Ahlulbayt/
│   ├── Publishing/
│   ├── Search/
│   ├── AI/
│   └── ...
│       ├── Domain/
│       │   ├── Entities/
│       │   ├── Services/
│       │   ├── Policies/
│       │   └── Events/
│       ├── Application/
│       │   ├── Commands/
│       │   ├── Queries/
│       │   ├── Handlers/
│       │   └── DTOs/
│       ├── Infrastructure/
│       │   ├── Persistence/
│       │   └── Adapters/
│       └── Http/
│           ├── Controllers/
│           ├── Requests/
│           └── Resources/
```

| Rule | Detail |
|------|--------|
| F-01 | Domain layer does not import Infrastructure or Http |
| F-02 | Modules communicate via public interfaces, not direct model access across boundaries |
| F-03 | Knowledge Core is central — type-specific modules extend, not bypass |
| F-04 | Tests mirror module structure: `tests/Unit/Modules/{Module}/`, `tests/Feature/` |

## 5.3 Frontend Structure (When Implemented)

```
src/
├── app/                    # Routes (locale-aware)
├── components/
│   ├── ui/                 # Design system primitives
│   ├── entities/           # Entity-type display components
│   ├── layout/
│   └── cms/
├── lib/                    # API client, utils (no business logic duplication)
├── hooks/
├── i18n/
│   ├── ar/
│   └── en/
└── styles/
```

| Rule | Detail |
|------|--------|
| F-05 | Feature-first within `components/` — not type-first sprawl |
| F-06 | CMS components separated from public components |
| F-07 | API client centralized — no raw `fetch` scattered in components |

## 5.4 Concern Separation

| Never Mix | Keep Separate |
|-----------|---------------|
| Public website + CMS admin routes | Different layouts, auth guards |
| API controllers + domain logic | Handler delegation |
| Database migrations + business logic | Migrations are schema only |
| Tests + production config | `.env.testing`, test doubles |

---

# 6. Component Rules

## 6.1 Before Creating Any Component

```
1. SEARCH existing components (ui/, entities/, layout/)
2. If match exists → REUSE
3. If partial match → EXTEND via props/composition
4. If no match → CREATE with design system tokens
5. DOCUMENT props interface
```

## 6.2 Component Requirements

Every UI component must support:

| Requirement | Standard |
|-------------|----------|
| Reusability | Configurable via props; no hardcoded entity-specific data |
| Composability | Accept `children`; avoid monolithic components |
| Accessibility | Semantic HTML, ARIA where needed, keyboard operable |
| Localization | No hardcoded strings; RTL-safe layout |
| Dark mode | Use design tokens; no hardcoded colors |
| Responsive | Mobile-first; 320px minimum (PRD US-V-003) |

## 6.3 Entity Display Components

Wiki World has repeating entity patterns. Reuse:

| Pattern | Examples |
|---------|----------|
| Entity card | Search results, related entities, bookmarks |
| Entity header | Title, summary, infobox, actions |
| Infobox | Person dates, event dates, source metadata |
| Citation block | Source references with reliability tier |
| Content blocks | Paragraph, heading, quote, list, image, entity link |
| Timeline item | Chronological event row |

Do not create `PersonCard`, `EventCard`, `ArticleCard` with duplicate layout logic — use `EntityCard` with type-specific infobox slots.

## 6.4 CMS Components

| Component | Notes |
|-----------|-------|
| Block editor | Matches block types in Blueprint and API Spec |
| Entity picker | Uses `/editorial/search/entities` |
| Source picker | Uses editorial source search |
| Workflow actions | Maps to API workflow endpoints exactly |
| Publish checklist | Reads `/editorial/entities/{uuid}/publish-readiness` |

---

# 7. Backend Rules

## 7.1 Architectural Patterns (Mandatory)

| Pattern | Application |
|---------|-------------|
| **DDD** | Bounded contexts per Backend Architecture modules |
| **Clean Architecture** | Domain → Application → Infrastructure → Http |
| **SOLID** | Single responsibility; depend on interfaces at boundaries |
| **CQRS (pragmatic)** | Separate command/query handlers where beneficial |
| **Repository** | At infrastructure boundary for complex aggregates only |
| **Domain Events** | Publish, unpublish, role change → async side effects |

## 7.2 Layer Responsibilities

| Layer | Allowed | Forbidden |
|-------|---------|-----------|
| **Http/Controllers** | Request validation, auth check, dispatch handler, return resource | Business logic, DB queries, workflow rules |
| **Application/Handlers** | Orchestration, transactions, authorization, DTO mapping | HTTP concerns, raw SQL |
| **Domain/Services** | Business rules, policies, invariants | Framework imports, request objects |
| **Infrastructure** | Eloquent, Redis, S3, Meilisearch, OpenAI adapters | Business rule decisions |

## 7.3 Module Boundaries

| Rule | Example |
|------|---------|
| Quran module does not write person profiles directly | Goes through KnowledgeCore |
| AI module reads published corpus only | `workflow_state = published` filter mandatory |
| Search index sync is async | Queue job after publish — not in publish transaction |
| Analytics writes are fire-and-forget | Separate from entity reads |

## 7.4 Validation

| Layer | Validates |
|-------|-----------|
| Form Request / Input DTO | Types, formats, required fields |
| Domain Policy | Business rules (e.g., relationship type allowed) |
| PublishIntegrityValidator | Source minimum, slug uniqueness, checklist |

## 7.5 Authorization

Every command and query handler:

```
1. Resolve authenticated user (or guest context)
2. Call AuthorizationService / Policy
3. If denied → throw Forbidden (403) — never silent empty results for protected resources
4. Proceed with domain logic
```

| Surface | Check |
|---------|-------|
| Public read | Published corpus only |
| Editorial | Role + domain scope |
| Admin | Role + MFA for admin operations |
| Account | User owns resource |

## 7.6 Transactions and Side Effects

| Operation | Transaction | Async |
|-----------|-------------|-------|
| Save draft | Yes — entity aggregate | No |
| Publish | Yes — entity + workflow + version | Search index, sitemap, cache bust via queue |
| AI query | No full transaction | Conversation save separate |
| Media upload | Metadata yes | Image processing via queue |

## 7.7 Events and Queues

| Event | Listeners (examples) |
|-------|---------------------|
| EntityPublished | ReindexSearch, BustEntityCache, RegenerateSitemap, RegenerateStructuredData |
| EntityUnpublished | RemoveFromIndex, BustCache |
| UserRegistered | SendVerificationEmail |

Never perform heavy I/O synchronously in request cycle when queue is appropriate.

## 7.8 Prohibited Backend Patterns

| Forbidden | Reason |
|-----------|--------|
| Business logic in controllers | Untestable, duplicated |
| Direct cross-module Eloquent access | Breaks boundaries |
| Raw SQL in controllers | Belongs in infrastructure |
| `$user->isAdmin` in random places | Use AuthorizationService |
| N+1 queries | Use eager loading, query optimization |
| Synchronous OpenAI in publish flow | Latency, failure coupling |
| Skipping audit log on publish/role change | NFR-SEC-005 violation |

---

# 8. Frontend Rules

## 8.1 Rendering Strategy

| Prefer | When |
|--------|------|
| Server Components / SSR | Public entity pages, lists, SEO-critical content |
| Client Components | Interactivity: search autocomplete, AI chat, bookmarks, CMS editor |
| Static generation / ISR | Rarely changing indexes (surah list) where appropriate |

## 8.2 Data Fetching

| Rule | Detail |
|------|--------|
| API Spec is contract | Use typed API client matching response envelopes |
| Locale in path | `/ar/persons/...` — not query param alone |
| No invented endpoints | See API Specification Appendix C |
| Error envelope handling | Parse `error.code`, display localized message |
| Cache headers | Respect ETag on public GET |

## 8.3 State Management

| Avoid | Prefer |
|-------|--------|
| Global state for server data | Server fetch + cache (SWR/React Query pattern) |
| Client state for URL state | URL search params for filters, pagination |
| Deep prop drilling (>3 levels) | Composition, context for theme/locale only |
| Unnecessary re-renders | Memoization where measured, not by default |

## 8.4 Performance (PRD NFR-PERF)

| Target | Approach |
|--------|----------|
| LCP < 2.5s | SSR/SSG, image optimization, font subsetting |
| INP < 200ms | Minimize client JS, defer non-critical |
| CLS < 0.1 | Explicit image dimensions, reserved space |
| TTFB < 600ms | CDN, API caching |

## 8.5 SEO (Frontend Responsibility)

| Requirement | Implementation |
|-------------|----------------|
| hreflang | From `seo.hreflang` in API response |
| JSON-LD | Inject `seo.structured_data` |
| Canonical | Use `seo.canonical_url` |
| Meta tags | `meta_title`, `meta_description` from API |

## 8.6 CMS Frontend

| Rule | Detail |
|------|--------|
| Separate auth context | Editorial token/session |
| Autosave | Debounced PATCH with idempotency key |
| Optimistic UI | Only where rollback is safe |
| Preview | Use `/editorial/entities/{uuid}/preview` — not public URL guessing |

---

# 9. API Rules

## 9.1 Contract Authority

[API_SPECIFICATION.md](./API_SPECIFICATION.md) is the **single API contract**. AI must not:

- Add endpoints without updating the spec
- Change response shapes without version consideration
- Return raw Eloquent models
- Use camelCase in JSON (snake_case only)

## 9.2 Response Standards

| Type | Shape |
|------|-------|
| Single resource | `{ "data": { ... }, "meta": { "request_id": ... } }` |
| Collection | `{ "data": [ ... ], "meta": { "pagination": ... } }` |
| Error | `{ "error": { "code", "message", "details" }, "meta": { "request_id" } }` |

## 9.3 Status Codes

| Code | Use |
|------|-----|
| 200 | Successful GET/PATCH |
| 201 | Successful POST create |
| 204 | Successful DELETE |
| 400 | Validation failure |
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Not found OR not published (public) |
| 409 | Conflict (workflow, slug) |
| 422 | Business rule violation |
| 429 | Rate limit |
| 503 | Maintenance / AI unavailable |

## 9.4 Public API Corpus Safety

```
Public GET → only workflow_state = published AND deleted_at IS NULL
Draft UUID on public API → 404 (not 403)
```

This applies to: entity endpoints, search, AI retrieval, sitemap, related content.

## 9.5 Versioning

- All routes under `/api/v1/`
- Additive changes only within v1
- Breaking changes require v2 + 12-month deprecation

---

# 10. Database Rules

## 10.1 Schema Authority

[DATABASE_DESIGN.md](./DATABASE_DESIGN.md) defines 82 tables. AI must not:

- Create tables without updating Database Design
- Add columns without migration + documentation
- Denormalize without documented performance justification
- Store duplicate data that violates single source of truth

## 10.2 Entity-First Storage

| Concept | Storage |
|---------|---------|
| Identity | `entities` table (uuid, type, workflow_state) |
| Type-specific data | Profile extension tables (`person_profiles`, etc.) |
| Localized content | `entity_localizations` |
| Relationships | `entity_relationships` with typed codes |
| Citations | `entity_references` |

Never store persons/events as JSON blobs in a generic table.

## 10.3 Integrity Rules

| Rule | Detail |
|------|--------|
| Foreign keys | Enforced at database level |
| Soft deletes | `deleted_at` on entities; respect in all queries |
| UUID public IDs | `entities.uuid` — never expose `entities.id` |
| Quran text | Immutable — apparatus tables versioned separately |
| Workflow states | Enum from Database Design — no ad-hoc states |

## 10.4 Migrations

| Rule | Detail |
|------|--------|
| One concern per migration | Not mixed unrelated changes |
| Reversible | `down()` method required |
| No data destruction without strategy | Column drops require migration plan |
| Index additions | Follow Database Design index strategy |
| Seed data | Quran corpus via seeders — not editorial API |

## 10.5 Query Rules

| Prefer | Avoid |
|--------|-------|
| Eloquent with scopes | Raw SQL in application layer |
| `published()` scope on public queries | Manual `where` scattered |
| Eager loading | N+1 |
| Pagination | Unbounded `SELECT *` |

---

# 11. Security Rules

## 11.1 Input Validation

- Validate **all** input at API boundary (Form Requests)
- Validate file uploads: MIME, size, extension
- Sanitize HTML block content (whitelist tags per API Spec)
- Sanitize AI user prompts (length limits, injection mitigation per NFR-SEC-008)

## 11.2 Authorization

| Rule | Detail |
|------|--------|
| Server-side only | Never trust client role claims |
| RBAC on every mutation | NFR-SEC-003 |
| Domain scope for editors | `quran`, `ahlulbayt`, `general`, `books_sources` |
| MFA for Admin | NFR-SEC-004 |
| IDOR prevention | UUID lookup + policy check |

## 11.3 Secrets

| Never | Always |
|-------|--------|
| Commit `.env` values | Use `.env.example` with placeholders |
| Log tokens/passwords | Redact sensitive fields in logs |
| Expose stack traces in production | Generic error messages |

## 11.4 OWASP Mitigations (NFR-SEC-002)

| Risk | Mitigation |
|------|------------|
| Injection | Parameterized queries, validated input |
| XSS | Output encoding, CSP, sanitized HTML |
| CSRF | Sanctum CSRF for SPA, SameSite cookies |
| Broken access control | Policies on all handlers |
| Security misconfiguration | Secure headers per API Spec §18 |
| File upload abuse | Type/size validation, S3 private bucket for drafts |

## 11.5 Rate Limiting

Implement per API Specification:

| Endpoint | Limit |
|----------|-------|
| Public read | 120/min/IP |
| AI chat | 10/hr anon, 50/hr registered |
| Auth | 5/15min/email |
| Editorial write | 60/min/user |

---

# 12. Performance Rules

## 12.1 Hierarchy of Optimization

```
1. Correctness and security
2. Pagination and bounded queries
3. Caching (Redis, HTTP, CDN)
4. Async processing (queues)
5. Index optimization
6. Micro-optimizations (last resort)
```

## 12.2 Caching (Backend Architecture §15)

| Key Pattern | TTL |
|-------------|-----|
| `entity:{uuid}:{locale}` | 15 min |
| `search:{hash}` | 5 min |
| `seo:structured:{uuid}:{locale}` | 1 hour |

Cache bust via domain events — not manual TTL guessing.

## 12.3 Pagination

| Endpoint Type | Strategy |
|---------------|----------|
| Standard lists | Offset (`page`, `per_page`, max 100) |
| Timeline, history | Cursor pagination |
| Search | Offset with `took_ms` in meta |

Never return unbounded collections.

## 12.4 Background Jobs

Use queues for:

- Search index sync
- Image variant generation
- Sitemap regeneration
- AI embedding generation (future)
- Email notifications
- Analytics aggregation

## 12.5 Frontend Performance

| Technique | Apply |
|-----------|-------|
| Code splitting | Per route, per module |
| Lazy loading | Below-fold images, heavy CMS tools |
| Streaming | AI chat SSE per API Spec |
| Chunk processing | Large CSV imports (future) |

---

# 13. Localization Rules

## 13.1 No Hardcoded Strings (NFR-I18N-001)

| Context | Location |
|---------|----------|
| UI labels | `i18n/ar.json`, `i18n/en.json` |
| API error messages | Laravel lang files, keyed by `error.code` |
| Email templates | Localized blade/templates per locale |
| Content | `entity_localizations` — not UI files |

## 13.2 Locale Priority

| Locale | Role |
|--------|------|
| `ar` | Primary — default, canonical SEO |
| `en` | Secondary — full support |
| Future | Add via configuration, not code fork (NFR-I18N-008) |

## 13.3 RTL/LTR (NFR-I18N-002)

| Requirement | Implementation |
|-------------|----------------|
| Layout mirroring | `dir` attribute on root; logical CSS properties |
| Icons | Mirror directional icons (arrows, chevrons) |
| Typography | Arabic font stack distinct from Latin |
| Mixed content | `dir="auto"` on user-generated spans |

## 13.4 Formatting (NFR-I18N-003/004)

| Type | Arabic (`ar`) | English (`en`) |
|------|---------------|----------------|
| Numbers | Eastern Arabic numerals | Western numerals |
| Dates | Hijri primary where applicable | Gregorian |
| Pluralization | ICU message format |

## 13.5 Content Fallback (NFR-I18N-005)

- Missing translation → 404 or explicit `meta.fallback` (API Spec §13.4)
- Never silently show wrong locale without indicator
- UI fallback strings ≠ content fallback

## 13.6 Locale Routing (NFR-I18N-006)

- URL prefix: `/ar/...`, `/en/...`
- API path: `/api/v1/ar/...`
- No forced redirect without user control

---

# 14. Accessibility Rules

Wiki World targets **WCAG 2.1 Level AA** (NFR-A11Y-001).

## 14.1 Requirements

| Rule | Standard |
|------|----------|
| Keyboard navigation | All interactive elements operable (NFR-A11Y-002) |
| Screen readers | ARIA landmarks, labels, live regions for AI streaming (NFR-A11Y-003) |
| Color contrast | 4.5:1 text, 3:1 UI components (NFR-A11Y-004) |
| RTL screen readers | Correct `lang` attribute (NFR-A11Y-005) |
| Focus indicators | Visible focus ring (NFR-A11Y-006) |
| Reduced motion | `prefers-reduced-motion` respected (NFR-A11Y-007) |
| Alt text | 100% published images (NFR-A11Y-008) |

## 14.2 Implementation Checklist

| Element | Requirement |
|---------|-------------|
| Buttons | `<button>` or `role="button"` with keyboard support |
| Links | Descriptive text — not "click here" |
| Images | `alt` required; decorative → `alt=""` |
| Forms | `<label>` associated with inputs |
| Modals | Focus trap, Escape to close, return focus |
| AI chat | `aria-live="polite"` for streaming responses |
| Skip links | Skip to main content |
| Headings | Logical hierarchy (h1 → h2 → h3) |

## 14.3 CMS Accessibility

Editors may rely on assistive technology. CMS must be keyboard-navigable and screen-reader compatible — not only the public site.

---

# 15. Design Rules

## 15.1 Design System Authority

When a design system document exists, it overrides ad-hoc styling. Until then:

| Token | Rule |
|-------|------|
| Spacing | 4px base scale (4, 8, 12, 16, 24, 32, 48, 64) |
| Typography | Arabic and Latin font stacks defined once |
| Colors | Semantic tokens (`--color-text-primary`, not `#333`) |
| Icons | Single icon library |
| Animation | Subtle; respect reduced motion |

## 15.2 Prohibited

| Never | Why |
|-------|-----|
| Invent new colors per component | Inconsistent brand |
| Inline styles for layout | Unmaintainable |
| Random border-radius values | Use token scale |
| One-off animations | Use shared motion tokens |
| Clone third-party UI blindly | Must match Wiki World design language |

## 15.3 Wiki World UX Principles (PRD)

| Principle | Application |
|-----------|-------------|
| Knowledge First | Content is hero; chrome is minimal |
| Reading experience | Comfortable line length, spacing, typography |
| Trust signals | Source citations visible, reliability tiers |
| Calm interface | No aggressive popups, ads, or dark patterns |

## 15.4 Responsive Breakpoints

| Breakpoint | Target |
|------------|--------|
| 320px+ | Mobile (minimum supported) |
| 768px+ | Tablet |
| 1024px+ | Desktop |
| 1440px+ | Wide desktop |

Touch targets: minimum 44×44px on mobile (PRD).

---

# 16. AI Assistant Behavior Rules

This section governs **how AI coding assistants work**, distinct from the product's AI feature.

## 16.1 Session Start Protocol

Before writing code:

```
□ Read task description
□ Identify affected modules
□ Read relevant sections of governing docs
□ Search codebase for existing implementations
□ List assumptions
□ Ask if requirements are incomplete
```

## 16.2 During Implementation

| Do | Don't |
|----|-------|
| Make minimal, focused changes | Refactor unrelated code |
| Match existing naming and patterns | Introduce new patterns without justification |
| Explain trade-offs for significant decisions | Make silent architectural changes |
| Run tests and lint | Skip verification |
| Update docs when contracts change | Leave docs stale |

## 16.3 Hallucination Prevention

| Domain | Verify Against |
|--------|----------------|
| API endpoints | API_SPECIFICATION.md Appendix C |
| Database tables | DATABASE_DESIGN.md Appendix A |
| Entity types | KNOWLEDGE_DOMAIN_BLUEPRINT.md |
| Permissions | API Spec §4 + Backend Architecture §7 |
| NFRs | PRD Section 7 |

If not documented, **ask** — do not invent.

## 16.4 Product AI Feature (RAG Assistant)

When implementing the product's AI feature:

| Rule | Detail |
|------|--------|
| Corpus-only retrieval | Published entities only |
| Citations mandatory | Every factual claim linked to source/entity UUID |
| Refusal path | Fatwa requests, personal rulings → polite refusal |
| Disclaimer | Visible, per PRD US-V-053 |
| Rate limits | Per API Spec |
| Flagging | Editorial review queue for problematic responses |

## 16.5 Significant Change Protocol

When introducing changes that affect architecture:

1. State the problem
2. Cite current architecture
3. Propose change with trade-offs
4. Wait for approval
5. Update governing docs
6. Implement

---

# 17. Git Rules

## 17.1 Commits

| Rule | Detail |
|------|--------|
| Atomic commits | One logical change per commit |
| Message format | `type(scope): description` — e.g., `feat(quran): add verse cross-reference endpoint` |
| Types | `feat`, `fix`, `refactor`, `test`, `docs`, `chore` |
| No unrelated changes | Don't mix formatting with feature |
| No secrets | Pre-commit check |

## 17.2 Branches

| Branch | Purpose |
|--------|---------|
| `main` | Production-ready |
| `feature/{name}` | New features |
| `fix/{name}` | Bug fixes |
| `docs/{name}` | Documentation only |

## 17.3 Pull Requests

| Rule | Detail |
|------|--------|
| Focused | Single feature or fix |
| Description | What, why, how to test |
| Linked requirements | Reference PRD US-* or FR-* when applicable |
| No drive-by refactors | Separate PR |

## 17.4 AI-Specific Git Rules

- Do not commit unless user explicitly requests
- Do not force push to main
- Do not amend commits not created in current session
- Run `git status` and `git diff` before commit when requested

---

# 18. Testing Rules

## 18.1 Test Pyramid

| Level | Scope | Required |
|-------|-------|----------|
| Unit | Domain services, policies, value objects | Yes — for business logic |
| Feature | API endpoints, auth, workflow | Yes — for all endpoints |
| Integration | Search, AI gateway, queue jobs | When external systems involved |
| Architecture | Layer boundaries, policy coverage | Backend Architecture §19.6 |
| E2E | Critical user journeys | Public read, login, CMS publish |

## 18.2 Coverage Requirements

| Area | Minimum |
|------|---------|
| Domain policies | 100% of rules |
| API endpoints | Happy path + auth failure + validation failure |
| Publish workflow | All state transitions |
| Corpus safety | Draft never in public/search/AI tests |
| Permissions | Each role tested on restricted endpoints |

## 18.3 Test Quality

| Rule | Detail |
|------|--------|
| Deterministic | No flaky tests |
| Isolated | Database transactions, factories |
| Readable | Arrange-Act-Assert pattern |
| Named | `test_editor_cannot_publish_without_senior_role` |
| No logic in tests | Avoid conditionals in test bodies |

## 18.4 Regression Protection

- Never delete failing tests to green the build
- Add test for every bug fix
- Run full suite before marking task complete

## 18.5 AI/Search Tests (Backend Architecture §19.8–19.9)

| Test | Assertion |
|------|-----------|
| IDOR | Draft UUID returns 404 on public API |
| Privilege escalation | Editor cannot access admin |
| AI corpus boundary | Draft entities excluded from retrieval |
| Citation validation | Response citations resolve to published entities |
| Rate limits | 429 after threshold |

---

# 19. Refactoring Rules

## 19.1 Purpose

Refactoring improves structure **without changing behavior**.

## 19.2 Allowed

| Change | Condition |
|--------|-----------|
| Extract method/class | Tests pass |
| Rename for clarity | Update all references |
| Remove duplication | No behavior change |
| Improve types | Stricter typing |
| Move to correct module | Update imports, tests |

## 19.3 Forbidden Without Approval

| Change | Reason |
|--------|--------|
| API response shape change | Contract break |
| Database schema change | Requires migration strategy |
| Workflow state change | Editorial process impact |
| Full rewrite | Risk > benefit |
| Framework upgrade | Separate initiative |

## 19.4 Refactoring Process

```
1. Ensure tests exist (write if missing)
2. Make incremental changes
3. Run tests after each step
4. No mixed refactor + feature in same commit
```

---

# 20. Code Review Checklist

AI must verify **every item** before marking a task complete.

## 20.1 Architecture

| # | Check |
|---|-------|
| ☐ | Module boundaries respected |
| ☐ | Domain layer free of infrastructure imports |
| ☐ | No business logic in controllers |
| ☐ | Entity-first patterns followed |
| ☐ | Publishing workflow uses state machine |

## 20.2 Documentation

| # | Check |
|---|-------|
| ☐ | Governing docs updated if contracts changed |
| ☐ | Decision log entry for significant choices |
| ☐ | API Spec updated for endpoint changes |
| ☐ | Database Design updated for schema changes |

## 20.3 Code Quality

| # | Check |
|---|-------|
| ☐ | Naming follows Appendix B |
| ☐ | No magic values |
| ☐ | No duplicated logic |
| ☐ | No dead code |
| ☐ | No unused imports/variables |
| ☐ | Functions/components within size guidelines |

## 20.4 Localization & Accessibility

| # | Check |
|---|-------|
| ☐ | No hardcoded user-facing strings |
| ☐ | RTL/LTR tested |
| ☐ | Keyboard navigation works |
| ☐ | ARIA labels on interactive elements |
| ☐ | Color contrast acceptable |
| ☐ | `prefers-reduced-motion` respected |

## 20.5 Security & Performance

| # | Check |
|---|-------|
| ☐ | Input validated |
| ☐ | Authorization on all mutations |
| ☐ | No secrets in code |
| ☐ | Pagination on lists |
| ☐ | No N+1 queries |
| ☐ | Heavy work queued |

## 20.6 Testing & Build

| # | Check |
|---|-------|
| ☐ | Unit tests added/updated |
| ☐ | Feature tests for API changes |
| ☐ | All tests pass |
| ☐ | Lint passes |
| ☐ | Build succeeds |

## 20.7 Cleanup

| # | Check |
|---|-------|
| ☐ | No `console.log` / `dd()` / `dump()` |
| ☐ | No debugging artifacts |
| ☐ | No unapproved TODOs |
| ☐ | No unrelated file changes |

---

# 21. Definition of Done

A feature is **complete** only when **all** criteria are met:

## 21.1 Requirements

| # | Criterion |
|---|-----------|
| 1 | Satisfies specified PRD user story / functional requirement |
| 2 | Acceptance criteria verifiable |
| 3 | Edge cases handled (empty state, error state, permission denied) |

## 21.2 Architecture

| # | Criterion |
|---|-----------|
| 4 | Aligns with Backend Architecture module boundaries |
| 5 | Entity-first / API contracts respected |
| 6 | No undocumented architectural decisions |

## 21.3 Quality

| # | Criterion |
|---|-----------|
| 7 | Tests written and passing |
| 8 | Lint passing |
| 9 | No known bugs |
| 10 | Code review checklist (§20) complete |

## 21.4 User Experience

| # | Criterion |
|---|-----------|
| 11 | Responsive (320px minimum) |
| 12 | Accessible (WCAG 2.1 AA) |
| 13 | Localized (ar + en where applicable) |
| 14 | RTL/LTR correct |

## 21.5 Production Readiness

| # | Criterion |
|---|-----------|
| 15 | Secure (auth, validation, no leaks) |
| 16 | Performant (pagination, caching considered) |
| 17 | Observable (logging, request IDs) |
| 18 | Documentation updated |

## 21.6 Explicitly Not Done

| State | Meaning |
|-------|---------|
| "Works on my machine" | Not done |
| "Tests skipped" | Not done |
| "Will add tests later" | Not done |
| "TODO: fix edge case" | Not done |
| "Needs design review" | Not done — flag to user |

---

# 22. Engineering Principles

Every AI agent must internalize these principles before writing a single line of code:

| # | Principle |
|---|-----------|
| P-01 | **Build once, scale forever.** Design for 1M entities and 10+ modules from day one. |
| P-02 | **Architecture before implementation.** Read the docs. Search the codebase. Then code. |
| P-03 | **Reuse before create.** Existing service, component, or pattern wins. |
| P-04 | **Knowledge before assumptions.** Governing docs > training data > guesses. |
| P-05 | **Consistency beats cleverness.** Match the codebase, not the latest trend. |
| P-06 | **Documentation is part of the product.** Undocumented changes are incomplete work. |
| P-07 | **If unsure, ask.** A question costs minutes. A wrong assumption costs days. |
| P-08 | **Think in systems, not pages.** Entities, workflows, relationships — not screens. |
| P-09 | **Every decision should make the next feature easier.** Favor extensibility. |
| P-10 | **Corpus safety is sacred.** Published only on public surfaces. Always. |
| P-11 | **Arabic is not a translation afterthought.** RTL and Arabic typography are primary. |
| P-12 | **Trust is earned in sources.** Citations, reliability tiers, editorial workflow. |
| P-13 | **Security is not optional.** Auth on every mutation. Validate every input. |
| P-14 | **Performance is a feature.** Paginate, cache, queue — in that order. |
| P-15 | **Tests prove it works.** No tests = not done. |
| P-16 | **Small, focused changes.** One feature. One PR. One responsibility. |
| P-17 | **Leave the codebase better.** Fix what you touch — within scope. |
| P-18 | **AI assists, humans govern.** AI coding agents implement; architecture docs govern. |

---

# Appendix A: Wiki World Stack Reference

| Component | Technology | Version |
|-----------|------------|---------|
| Language (backend) | PHP | 8.4+ |
| Framework | Laravel | 12 |
| Database | PostgreSQL | 16+ |
| Cache/Queue | Redis | 7+ |
| Search | Meilisearch | Future |
| AI Provider | OpenAI | Via adapter |
| Object Storage | S3-compatible | — |
| Containerization | Docker | Compose (local) |
| API Auth | Laravel Sanctum | Session + token |
| API Format | JSON REST | v1 |

---

# Appendix B: Naming Convention Registry

| Context | Convention | Example |
|---------|------------|---------|
| Database tables | plural snake_case | `entity_relationships` |
| Database columns | snake_case | `workflow_state`, `published_at` |
| PHP classes | PascalCase | `PublishEntityHandler` |
| PHP methods | camelCase | `publishEntity()` |
| PHP variables | camelCase | `$entityUuid` |
| API URL paths | kebab-case, plural | `/saved-searches` |
| API JSON fields | snake_case | `published_at` |
| API enums | snake_case strings | `"in_review"` |
| Public IDs | UUID v7 | `550e8400-e29b-41d4-a716-446655440000` |
| Permission codes | dot notation | `entity.publish` |
| Relationship types | snake_case | `participated_in` |
| Entity type codes | snake_case | `person`, `tafsir_profile` |
| Editor domains | snake_case | `books_sources` |
| Cache keys | colon separated | `entity:{uuid}:{locale}` |
| Queue names | dot separated | `search.reindex` |
| Config keys | dot notation | `wikiworld.ai.rate_limit` |
| React components | PascalCase | `EntityCard` |
| React hooks | camelCase with `use` | `useBookmarks` |
| CSS variables | kebab-case | `--color-text-primary` |
| i18n keys | dot notation | `entity.person.born` |
| Git branches | kebab-case | `feature/quran-cross-refs` |
| Test methods | snake_case | `test_editor_cannot_publish` |

---

# Appendix C: Forbidden Patterns

| Pattern | Why Forbidden | Alternative |
|---------|---------------|-------------|
| `$table->id()` only for entities | Internal ID leak risk | `uuid` column + public exposure |
| `User::isAdmin()` in controllers | Inconsistent auth | `AuthorizationService` |
| `Entity::all()` on public API | Unbounded, leaks drafts | Scoped `published()` query |
| Raw `DB::select()` in handlers | Bypasses domain | Repository |
| `sleep()` for retries | Blocks workers | Queue with backoff |
| `file_get_contents($url)` | SSRF risk | Validated HTTP client |
| Storing AI responses without citations | Trust violation | `AIResponseDTO` with citations |
| `innerHTML` without sanitization | XSS | Sanitizer library |
| Global `$forcePublish` flags | Bypasses workflow | Proper state machine |
| Committing `.env` | Security breach | `.env.example` only |
| Creating `utils.php` catch-all | Unmaintainable | Module-specific services |
| Duplicating API validation in frontend only | Bypassable | Server-side always required |

---

# Appendix D: Pre-Implementation Checklist

Copy and complete before any non-trivial implementation:

```
## Task: _______________________________________________

### Context
- [ ] Read PRD relevant section: _______________________
- [ ] Read Blueprint section: __________________________
- [ ] Read API Spec endpoints: _________________________
- [ ] Read Database Design tables: _____________________
- [ ] Read Backend Architecture module: ________________

### Codebase
- [ ] Searched for existing implementation: ____________
- [ ] Identified module: _______________________________
- [ ] Identified patterns to follow: ___________________

### Requirements
- [ ] Acceptance criteria clear: Yes / No → Ask
- [ ] Assumptions documented: __________________________
- [ ] Out of scope items noted: _________________________

### Implementation Plan
1. ____________________________________________________
2. ____________________________________________________
3. ____________________________________________________

### Tests Planned
- [ ] Unit: ____________________________________________
- [ ] Feature: _________________________________________

### Docs to Update
- [ ] __________________________________________________
```

---

# Appendix E: Decision Log Template

| ID | Date | Context | Decision | Alternatives Rejected | Consequences |
|----|------|---------|----------|----------------------|--------------|
| ENG-D001 | 2026-07-07 | AI engineering governance | Created AI Engineering Constitution as mandatory rulebook for all AI coding assistants | Ad-hoc per-tool rules | Single enforceable standard; referenced by Cursor rules and CI |

---

**End of Document**

*ويكي العالمية — Wiki World AI Engineering Constitution v1.0*  
*Governing references: [PRD.md](./PRD.md), [KNOWLEDGE_DOMAIN_BLUEPRINT.md](./KNOWLEDGE_DOMAIN_BLUEPRINT.md), [DATABASE_DESIGN.md](./DATABASE_DESIGN.md), [BACKEND_ARCHITECTURE.md](./BACKEND_ARCHITECTURE.md), [API_SPECIFICATION.md](./API_SPECIFICATION.md)*
