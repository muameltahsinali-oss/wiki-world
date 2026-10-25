import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 50 },
    { duration: '1m', target: 200 },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

const API_BASE = __ENV.API_BASE || 'http://127.0.0.1:8000/api/v1';
const LOCALE = __ENV.LOCALE || 'ar';

export default function () {
  const list = http.get(`${API_BASE}/${LOCALE}/persons?per_page=10`);
  check(list, { 'list 200': (r) => r.status === 200 });

  if (list.status === 200) {
    const body = list.json();
    const uuid = body?.data?.[0]?.uuid;
    if (uuid) {
      const detail = http.get(`${API_BASE}/${LOCALE}/persons/${uuid}`);
      check(detail, { 'detail 200': (r) => r.status === 200 });
    }
  }

  sleep(0.1);
}
