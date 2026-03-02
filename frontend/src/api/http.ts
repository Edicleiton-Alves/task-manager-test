const API_URL = ''

type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'DELETE'

export async function api<T>(
  path: string,
  options: { method?: HttpMethod; body?: unknown; signal?: AbortSignal } = {},
): Promise<T> {
  const res = await fetch(`${API_URL}${path}`, {
    method: options.method ?? 'GET',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
    signal: options.signal,
  })

  // tenta extrair erro como JSON quando possível
  if (!res.ok) {
    const contentType = res.headers.get('content-type') || ''
    const isJson = contentType.includes('application/json')

    const body = isJson
      ? await res.json().catch(() => null)
      : await res.text().catch(() => '')

    const message =
      (body && (body.message || body.error)) ||
      (typeof body === 'string' && body.slice(0, 200)) ||
      res.statusText

    throw new Error(`API ${res.status}: ${message}`)
  }

  if (res.status === 204) return undefined as T

  // evita "Unexpected token <" se vier HTML
  const contentType = res.headers.get('content-type') || ''
  if (!contentType.includes('application/json')) {
    const text = await res.text().catch(() => '')
    throw new Error(`Resposta não-JSON: ${text.slice(0, 200)}`)
  }

  return (await res.json()) as T
}