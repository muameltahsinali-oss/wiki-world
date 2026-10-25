# Editorial Team Onboarding

## Roles

| Role | Access |
| ---- | ------ |
| Editor | Create/edit drafts in assigned domain |
| Reviewer | Review submissions |
| Senior Editor | Approve, publish, unpublish |
| Admin | Users, settings, audit logs |

## Getting started

1. Admin creates account and assigns role + domain scope (`ahlulbayt`, `quran`, `general`, `books_sources`)
2. Editor logs in at `/ar/cms`
3. Create entity draft → add blocks → add references → submit for review
4. Senior editor reviews queue at `/ar/cms/reviews` → approve → publish

## Training checklist (2 senior editors)

- [ ] Log in and navigate CMS dashboard
- [ ] Create a person draft with Arabic title and summary
- [ ] Add paragraph and heading blocks
- [ ] Attach at least one published reference
- [ ] Submit for review
- [ ] Second editor: approve and publish
- [ ] Verify public page at `/ar/persons/{uuid}`
- [ ] Practice unpublish and archive workflow

## Style guidelines

- Use [DESIGN_SYSTEM.md](./DESIGN_SYSTEM.md) for UI consistency
- Summaries: 1–2 sentences, neutral educational tone
- No unsourced claims; every factual block should link to a reference

## Support

- Technical issues: check audit logs at `/ar/admin/audit`
- Workflow questions: see [EXECUTION_ROADMAP.md](./EXECUTION_ROADMAP.md) Phase 8–10
