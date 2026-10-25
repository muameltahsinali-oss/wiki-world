import { describe, expect, it } from 'vitest';

import { parseSseChunk } from './ai-streaming';

describe('parseSseChunk', () => {
  it('parses delta events', () => {
    expect(parseSseChunk('event: delta\ndata: {"text":"Hello"}')).toEqual([
      { type: 'delta', text: 'Hello' },
    ]);
  });

  it('parses citation events', () => {
    expect(
      parseSseChunk(
        'event: citation\ndata: {"entity_uuid":"u1","entity_type":"event","title":"Ghadir"}',
      ),
    ).toEqual([
      {
        type: 'citation',
        citation: {
          entity_uuid: 'u1',
          entity_type: 'event',
          title: 'Ghadir',
          excerpt: null,
          source_uuid: null,
          confidence: undefined,
        },
      },
    ]);
  });

  it('parses done events', () => {
    expect(parseSseChunk('event: done\ndata: {"conversation_uuid":"c1"}')).toEqual([
      {
        type: 'done',
        conversation_uuid: 'c1',
        message_uuid: undefined,
        disclaimer: undefined,
        related_entities: undefined,
      },
    ]);
  });
});
