# Release Checklist (PRD §9.5)

## Quality gates

- [x] All PHPUnit tests pass (`npm run test:backend`)
- [x] Frontend unit tests pass (`npm run test:frontend`)
- [x] Production build succeeds (`npm run build:frontend`)
- [x] E2E smoke tests configured (`npm run test:e2e`)
- [x] AI citation audit ≥ 95% (`AiCitationAuditTest`)
- [x] IDOR protection verified (`IdorProtectionTest`)
- [x] Feature toggle middleware (`FeatureToggleMiddlewareTest`)

## Content minimums

- [x] Seed script ready (`scripts/seed-launch-content.sh`)
- [ ] 14 Ahlulbayt masumin seeded in target environment
- [ ] Key events (Ghadir, Karbala) published in target environment
- [ ] Quran corpus seeded in target environment
- [ ] At least 2 senior editors onboarded ([EDITORIAL_ONBOARDING.md](./EDITORIAL_ONBOARDING.md))

## Security

- [x] Security headers middleware (API + Next.js + nginx)
- [x] IDOR tests (draft UUID → 404)
- [ ] `APP_DEBUG=false` in production
- [ ] `REVALIDATE_SECRET` set and not committed in production
- [ ] HSTS enabled on production edge (nginx production config)
- [ ] CSRF/session config reviewed for production domains

## Performance

- [x] ISR revalidation on publish wired
- [x] Entity pages `revalidate = 3600`
- [x] Font preload (IBM Plex Arabic + Inter)
- [x] CMS heavy routes code-split
- [ ] Bench script run in staging: `bash scripts/bench-public-read.sh`

## Infrastructure

- [x] Staging compose + nginx API routing fixed
- [x] Production compose + backend prod Dockerfile
- [x] Backup script (`scripts/backup-database.sh`)
- [x] Restore script (`scripts/restore-database.sh`)
- [x] Health monitor script (`scripts/monitor-health.sh`)
- [ ] Staging deploy verified end-to-end
- [ ] Production deploy verified end-to-end
- [ ] Daily DB backups scheduled
- [ ] Monitoring alerts configured

## SEO

- [x] Sitemap and robots API routes
- [ ] Sitemap submitted to Search Console ([SEO_LAUNCH.md](./SEO_LAUNCH.md))
- [ ] Structured data validates in staging

## AI (staging)

- [ ] `AI_ENABLED=true` with real `OPENAI_API_KEY` tested in staging
- [ ] Citation rate validated with live model (not FakeLLM only)

## Sign-off

| Role | Name | Date |
| ---- | ---- | ---- |
| Engineering | | |
| Editorial | | |
| Product | | |
