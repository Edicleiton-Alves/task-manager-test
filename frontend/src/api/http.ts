const API_URL = ''

type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'DELETE'

export async function api<T>(
  path: string,
  options: { method?: HttpMethod; body?: unknown; signal?: AbortSignal } = {},
): Promise<T> {
  const res = await fetch(`${API_URL}${path}`, {
    method: options.method ?? 'GET',
    headers: { 'Content-Type': 'application/json' },
    body: options.body ? JSON.stringify(options.body) : undefined,
    signal: options.signal,
  })

  if (!res.ok) {
    const text = await res.text().catch(() => '')
    throw new Error(`API ${res.status}: ${text || res.statusText}`)
  }

  if (res.status === 204) return undefined as T
  return (await res.json()) as T
}