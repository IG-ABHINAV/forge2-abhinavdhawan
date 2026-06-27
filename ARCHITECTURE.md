# Architecture — PulseDesk

## Overview

PulseDesk is a **multi-tenant support-desk SaaS** built on Laravel 11 (backend) + React 19 (frontend). Every record is strictly scoped to an `organization_id` derived from the authenticated user's session — never from a client-supplied header or query param.

---

## Multi-Tenancy Approach

**Mechanism:** Eloquent Global Scope + `auth:sanctum` middleware

Every tenanted model (`Ticket`, `Comment`, `SlaPolicy`, `ActivityLog`) has a `GlobalScope` applied via `EnsureTenantScope` middleware and model boot methods:

```php
// EnsureTenantScope middleware (runs on every authenticated API request)
// Stores auth()->user()->organization_id in app('tenant_id')

// In Ticket model:
protected static function booted(): void
{
    static::addGlobalScope('tenant', function (Builder $builder) {
        if ($id = app('tenant_id')) {
            $builder->where('organization_id', $id);
        }
    });
}
```

The `organization_id` is **always taken from `$request->user()->organization_id`** — never from the request body or URL. This makes cross-tenant data access structurally impossible.

---

## Data Model

```
organizations
  id, name, slug, plan, domain, created_at, updated_at

users
  id, name, email, password, organization_id (FK), role (admin|agent|customer)
  email_verified_at, remember_token, created_at, updated_at

tickets
  id, organization_id (FK), user_id (FK → requester), assignee_id (FK → users),
  title, description, status (open|in_progress|resolved|closed),
  priority (low|medium|high|urgent), sla_due_at, sla_breached (bool),
  created_at, updated_at

comments
  id, ticket_id (FK), user_id (FK), body, is_internal (bool), created_at, updated_at

sla_policies
  id, organization_id (FK), name, priority, response_hours, resolution_hours,
  created_at, updated_at

activity_logs
  id, organization_id (FK), user_id (FK), entity_type, entity_id,
  action, payload (JSON), created_at
```

---

## API Routes

| Method | Path | Auth | Role | Notes |
|--------|------|------|------|-------|
| POST | `/api/v1/register` | — | — | Creates org + admin user, returns Sanctum token |
| POST | `/api/v1/login` | — | — | Returns Sanctum token |
| POST | `/api/v1/logout` | ✓ | any | Revokes current token |
| GET | `/api/v1/me` | ✓ | any | Returns authenticated user + org |
| GET | `/api/v1/dashboard` | ✓ | any | Ticket counts by status/priority, SLA stats |
| GET | `/api/v1/tickets` | ✓ | any | Tenant-scoped, filterable by status/priority |
| POST | `/api/v1/tickets` | ✓ | any | Creates ticket in authenticated user's org |
| GET | `/api/v1/tickets/{id}` | ✓ | tenant | 403 if ticket belongs to another org |
| PATCH | `/api/v1/tickets/{id}` | ✓ | agent/admin | Update status, priority, assignee |
| DELETE | `/api/v1/tickets/{id}` | ✓ | admin | Soft delete |
| GET | `/api/v1/tickets/{id}/comments` | ✓ | tenant | Lists comments (internal filtered by role) |
| POST | `/api/v1/tickets/{id}/comments` | ✓ | tenant | Adds reply or internal note |

---

## Key Architectural Decisions

1. **SQLite for local dev / CI, MySQL for production** — `.env.example` ships with MySQL config for judges running fresh; CI uses SQLite for zero-dependency test runs.

2. **Laravel Sanctum token auth** — stateless API tokens, no session cookies. Token stored in `localStorage` on frontend, sent as `Authorization: Bearer <token>` header.

3. **Role-based access via `EnsureRole` middleware** — three roles: `admin`, `agent`, `customer`. Middleware registered as `role` alias in `bootstrap/app.php`.

4. **SLA breach detection** — `sla_due_at` set on ticket creation based on org's `SlaPolicy` for the given priority. `sla_breached` flag computed as `Carbon::now()->gt($ticket->sla_due_at)`.

5. **All model calls via EastRouter** — `https://api.eastrouter.com/v1`. Models: `z-ai/glm-5.1` (coding), `moonshotai/kimi-k2.6` (planning), `moonshotai/kimi-k2.7-code` (QA).

6. **CORS** — `config/cors.php` allows all origins (`*`) for local development. Sanctum stateful domains not used (token auth only).

7. **Frontend API base URL** — configured via `VITE_API_URL` env var. Falls back to `http://127.0.0.1:8000` in development.

---

## Tenant Isolation Test

```php
// TenantIsolationTest.php — cross-tenant access returns 403
$orgA = Organization::factory()->create();
$userA = User::factory()->for($orgA)->create(['role' => 'admin']);

$orgB = Organization::factory()->create();
$ticketB = Ticket::factory()->for($orgB)->create();

$this->actingAs($userA)
     ->getJson("/api/v1/tickets/{$ticketB->id}")
     ->assertStatus(403);  // ✅ Tenant boundary enforced
```
