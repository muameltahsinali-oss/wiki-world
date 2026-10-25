# SEO Launch Guide

## Pre-launch

1. Ensure all published entities have unique slugs and summaries
2. Verify `sitemap.xml` and `robots.txt` routes respond
3. Confirm JSON-LD structured data on entity pages (publish triggers regeneration)

## Google Search Console

1. Add property for production domain
2. Verify ownership (DNS TXT or HTML file)
3. Submit sitemap: `https://wikiworld.example/sitemap.xml`
4. Request indexing for key landing pages (`/ar`, `/ar/persons`, `/ar/events`)

## Locale handling

- Arabic (`/ar`) is canonical default
- English (`/en`) has `hreflang` alternates on entity pages
- Use `noindex` on CMS and admin routes (already excluded from sitemap)

## Post-submit monitoring

- Coverage report: fix crawl errors within 48h
- Core Web Vitals: target LCP < 2.5s (ISR + font preload)
- Monitor 404s for old slug redirects

## Analytics

- `POST /api/v1/analytics/views` records page views
- Popular entities: `GET /api/v1/{locale}/analytics/popular`
