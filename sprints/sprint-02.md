# Sprint 2 — PulseDesk · Forge 2 Edition 1

**Date:** 27 June 2026
**PO (Orchestrator):** Hermes · `moonshotai/kimi-k2.6` via EastRouter
**Developer (Worker):** OpenClaw · `z-ai/glm-5.1` via EastRouter
**QA Review:** `moonshotai/kimi-k2.7-code` via EastRouter
**Human oversight:** Abhinav Dhawan

---

## Sprint Goal

Extend PulseDesk with a database seeder (demo data), dashboard analytics endpoint, email notification stubs, role-based access control middleware, and API rate limiting — all with passing Pest tests.

---

## Task Breakdown

### T-06 · Database Seeder with Demo Data ✅
**Owner:** OpenClaw | **Branch:** `task/T-06-seeder` | **Status:** MERGED

**Acceptance Criteria:**
- [x] 3 demo organizations seeded (Acme Corp, Globex Corp, Initech)
- [x] 3 users per org (admin, agent, customer roles)
- [x] 15 tickets per org with varied status/priority
- [x] 1 SLA policy per org (high priority: 4h response, 24h resolution)
- [x] Demo login: `admin@acme.test / password`

**EastRouter:** 3 calls · `z-ai/glm-5.1` · ~6,200 tokens
**Commit:** `feat(T-06): database seeder with demo organizations and tickets`

---

### T-07 · Dashboard Analytics Endpoint ✅
**Owner:** OpenClaw | **Branch:** `task/T-07-dashboard` | **Status:** MERGED

**Acceptance Criteria:**
- [x] `GET /api/v1/dashboard` returns ticket counts by status and priority
- [x] Response includes `total_tickets`, `by_status`, `by_priority`, `sla_breached`, `avg_resolution_hours`
- [x] Endpoint is tenant-scoped (only own org data)
- [x] Pest: 2 tests passing

**EastRouter:** 2 calls · `z-ai/glm-5.1` · ~4,400 tokens
**Commit:** `feat(T-07): dashboard analytics endpoint with org-scoped aggregates`

---

### T-08 · Email Notification Stubs ✅
**Owner:** OpenClaw | **Branch:** `task/T-08-notifications` | **Status:** MERGED

**Acceptance Criteria:**
- [x] `TicketCreated` Mailable stub — triggers on `POST /api/v1/tickets`
- [x] `SlaBreachAlert` Mailable stub — triggers when `sla_breached = true`
- [x] Mail driver set to `log` for development (no real sends)
- [x] Notification classes in `app/Notifications/`

**EastRouter:** 2 calls · `z-ai/glm-5.1` · ~3,800 tokens
**Commit:** `feat(T-08): email notification stubs for ticket creation and SLA breach`

---

### T-09 · Role-Based Access Control Middleware ✅
**Owner:** OpenClaw | **Branch:** `task/T-09-rbac` | **Status:** MERGED

**Acceptance Criteria:**
- [x] `EnsureRole` middleware checks `$user->role` against allowed roles
- [x] Admin routes protected with `role:admin`
- [x] Agent routes protected with `role:admin,agent`
- [x] 403 returned for insufficient permissions
- [x] Pest: 3 tests — admin access, customer blocked, agent can manage

**EastRouter:** 2 calls · `z-ai/glm-5.1` · ~4,100 tokens
**Commit:** `feat(T-09): RBAC middleware with admin/agent/customer enforcement`

---

### T-10 · API Rate Limiting ✅
**Owner:** OpenClaw | **Branch:** `task/T-10-rate-limit` | **Status:** MERGED

**Acceptance Criteria:**
- [x] Authenticated users: 60 requests/min keyed by `user_id`
- [x] Guest/unauthenticated: 20 requests/min keyed by IP
- [x] 429 response with `Retry-After` header on excess
- [x] Configured in `bootstrap/app.php` via `RateLimiter::for('api', ...)`

**EastRouter:** 1 call · `moonshotai/kimi-k2.6` · ~2,200 tokens
**Commit:** `feat(T-10): API rate limiting — 60/min auth, 20/min guest`

---

## Velocity Summary

| Task | EastRouter Calls | Model | Tokens |
|------|-----------------|-------|--------|
| T-06 Seeder | 3 | `z-ai/glm-5.1` | ~6,200 |
| T-07 Dashboard | 2 | `z-ai/glm-5.1` | ~4,400 |
| T-08 Notifications | 2 | `z-ai/glm-5.1` | ~3,800 |
| T-09 RBAC | 2 | `z-ai/glm-5.1` | ~4,100 |
| T-10 Rate Limiting | 1 | `moonshotai/kimi-k2.6` | ~2,200 |
| Hermes planning | 3 | `moonshotai/kimi-k2.6` | ~5,400 |
| QA Review | 2 | `moonshotai/kimi-k2.7-code` | ~3,600 |
| **TOTAL** | **15 calls** | | **~29,700** |

---

## Final Test Results

```
PASS  Tests\Feature\AuthTest            (3 tests)
PASS  Tests\Feature\TenantIsolationTest (2 tests)
PASS  Tests\Feature\TicketTest          (5 tests)
PASS  Tests\Feature\CommentTest         (2 tests)
PASS  Tests\Feature\SlaTest             (1 test)
PASS  Tests\Feature\ActivityLogTest     (3 tests)
PASS  Tests\Feature\DashboardTest       (2 tests)
PASS  Tests\Feature\RbacTest            (3 tests)

Tests:  21 passed (48 assertions)
Time:   1.38s
```

---

## Human Review & Merge

All 5 tasks reviewed and approved by Abhinav Dhawan.
PRs merged to `main` after CI green confirmation.
Sprint 2 closed. Sprint 3 scope loaded.
