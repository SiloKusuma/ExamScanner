const API_BASE = 'https://meong.biz.id/examscan';

export async function apiGet(endpoint) {
  const res = await fetch(`${API_BASE}/${endpoint}`);
  if (!res.ok) throw new Error(`API error: ${res.status}`);
  return res.json();
}

export async function apiPost(endpoint, data) {
  const res = await fetch(`${API_BASE}/${endpoint}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error(`API error: ${res.status}`);
  return res.json();
}

export async function apiUpload(endpoint, formData) {
  const res = await fetch(`${API_BASE}/${endpoint}`, {
    method: 'POST',
    body: formData,
  });
  if (!res.ok) throw new Error(`API error: ${res.status}`);
  return res.json();
}

export { API_BASE };
