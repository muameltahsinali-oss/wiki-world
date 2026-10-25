# OpenAI Staging Test

Verify AI chat with a live OpenAI key before public launch.

## Prerequisites

```env
AI_ENABLED=true
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o
```

Enable via admin feature toggle `ai_enabled` or ensure DB toggle matches env.

## Steps

1. Deploy staging stack ([STAGING.md](./STAGING.md))
2. Seed corpus: `bash scripts/seed-launch-content.sh`
3. Enable search: `SEARCH_ENABLED=true`
4. POST chat:

```bash
curl -s -X POST "$API_BASE/ai/chat" \
  -H "Content-Type: application/json" \
  -d '{"message":"ما أهمية غدير خم؟","locale":"ar"}' | jq .
```

5. Confirm:
   - `data.refused` is false for educational questions
   - `data.citations` is non-empty
   - Answer includes educational disclaimer
6. Run citation audit locally with real provider (optional extended test)

## Rollback

Set `ai_enabled` toggle to false in admin or `AI_ENABLED=false` in env.
