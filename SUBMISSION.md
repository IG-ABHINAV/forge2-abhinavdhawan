# Submission checklist -- Forge 2 / Edition 1 (PulseDesk)

Tick each and point to the in-repo path. Everything must be committed in THIS repo.

- [x] Repo is public, named `forge2-abhinavdhawan`
- [x] README has exact run steps; `php artisan migrate --seed` works from a fresh clone -> [README.md](file:///f:/FORGE%202/forge2-pulsedesk/README.md)
- [x] Backend = Laravel 11 + MySQL -> [backend/](file:///f:/FORGE%202/forge2-pulsedesk/backend) ; Frontend = React 19 + Vite + Tailwind -> [frontend/](file:///f:/FORGE%202/forge2-pulsedesk/frontend)
- [x] Multi-tenancy: Org A cannot see Org B data (tenant derived from auth session) -> [EnsureTenantScope.php](file:///f:/FORGE%202/forge2-pulsedesk/backend/app/Http/Middleware/EnsureTenantScope.php)
- [x] Hermes config committed -> [hermes-config.yaml](file:///f:/FORGE%202/forge2-pulsedesk/agents/hermes/hermes-config.yaml)
- [x] OpenClaw config committed -> [openclaw.json](file:///f:/FORGE%202/forge2-pulsedesk/agents/openclaw/openclaw.json)
- [x] agent-log.md shows the real human->Hermes->OpenClaw loop -> [agent-log.md](file:///f:/FORGE%202/forge2-pulsedesk/agent-log.md)
- [x] sprints/ has >= 2 sprint docs -> [sprints/](file:///f:/FORGE%202/forge2-pulsedesk/sprints)
- [x] Slack proof in slack-export/ (export) or slack-export/screenshots/ (per channel) -> [slack-export/screenshots/](file:///f:/FORGE%202/forge2-pulsedesk/slack-export/screenshots)
- [x] App / agents-running / CI screenshots in evidence/screenshots/ -> [evidence/screenshots/](file:///f:/FORGE%202/forge2-pulsedesk/evidence/screenshots)
- [x] .github/workflows/ci.yml present + a green run on the Actions tab -> [.github/workflows/ci.yml](file:///f:/FORGE%202/forge2-pulsedesk/.github/workflows/ci.yml)
- [x] PRs merged by ME (human); commit authors are the agents -> [Git History](file:///f:/FORGE%202/forge2-pulsedesk/scratch/git_history.ps1)
- [x] All model calls went through EastRouter -> [agent-log.md](file:///f:/FORGE%202/forge2-pulsedesk/agent-log.md)
- [x] Models used: `z-ai/glm-5.1` (OpenClaw coding), `moonshotai/kimi-k2.6` (Hermes planning), `moonshotai/kimi-k2.7-code` (QA analysis)
- [x] Sprints run: 33 sprints
