# Public Launch

## Launch day sequence

1. **T-0294** — Complete [RELEASE_CHECKLIST.md](./RELEASE_CHECKLIST.md)
2. Deploy production stack (`docker-compose.prod.yml`)
3. Run migrations and seed launch content
4. Submit sitemap to Search Console
5. Enable feature toggles (search, AI) via admin panel
6. Smoke test critical paths:
   - `/ar` homepage
   - `/ar/persons` listing
   - Entity detail page
   - `/ar/search`
   - `/ar/cms` (editorial login)
7. Announce launch to editorial team and beta users

## Rollback plan

1. Revert to previous Docker image tag
2. Restore database from latest backup if schema/data changed
3. Disable feature toggles if specific module caused issues
4. Post-mortem within 24 hours

## Post-launch (first 48 hours)

- Monitor error rates and latency
- Watch Search Console for crawl issues
- Editorial team publishes 3+ new articles
- Review AI chat logs for citation quality

## Success criteria

- Site accessible over HTTPS with valid certificates
- No P0 bugs in production
- Editorial workflow operational
- PRD §9.5 release checklist fully signed off
