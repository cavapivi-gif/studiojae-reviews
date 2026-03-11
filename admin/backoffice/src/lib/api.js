const base  = () => window.sjReviews?.rest_url ?? '/wp-json/sj-reviews/v1'
const nonce = () => window.sjReviews?.nonce ?? ''

async function req(path, options = {}) {
  const res = await fetch(`${base()}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce':   nonce(),
      ...options.headers,
    },
    ...options,
  })
  if (!res.ok) {
    const err = await res.json().catch(() => ({ message: res.statusText }))
    throw new Error(err.message ?? 'Erreur réseau')
  }
  return res
}

export const api = {
  /** Dashboard stats */
  dashboard: async (period = 'all', source = '', lieu_id = '', from_date = '', to_date = '') => {
    const p = new URLSearchParams({ period, source, lieu_id, from_date, to_date })
    return (await req(`/dashboard?${p}`)).json()
  },

  /** Dashboard time-series trends */
  dashboardTrends: async (period = 'all', source = '', lieu_id = '', from_date = '', to_date = '') => {
    const p = new URLSearchParams({ period, source, lieu_id, from_date, to_date })
    return (await req(`/dashboard/trends?${p}`)).json()
  },

  /** Dashboard season comparison */
  dashboardCompare: async (season1, year1, season2, year2) => {
    const p = new URLSearchParams({ season1, year1, season2, year2 })
    return (await req(`/dashboard/compare?${p}`)).json()
  },

  /** Dashboard custom date range comparison */
  dashboardCompareRange: async (from1, to1, from2, to2) => {
    const p = new URLSearchParams({ from1, to1, from2, to2 })
    return (await req(`/dashboard/compare-range?${p}`)).json()
  },

  /** Liste des avis avec filtres */
  reviews: async ({ page = 1, perPage = 20, search = '', rating = 0, source = '', lieu_id = '', orderby = 'date', order = 'DESC', email = '' } = {}) => {
    const p = new URLSearchParams({ page, per_page: perPage, search, rating, source, lieu_id, orderby, order, email })
    const res = await req(`/reviews?${p}`)
    const data = await res.json()
    return {
      items:      data,
      total:      parseInt(res.headers.get('X-WP-Total') ?? '0', 10),
      totalPages: parseInt(res.headers.get('X-WP-TotalPages') ?? '1', 10),
    }
  },

  /** Détail avis */
  review: async (id) => (await req(`/reviews/${id}`)).json(),

  /** Créer avis */
  createReview: async (body) =>
    (await req('/reviews', { method: 'POST', body: JSON.stringify(body) })).json(),

  /** Modifier avis */
  updateReview: async (id, body) =>
    (await req(`/reviews/${id}`, { method: 'PUT', body: JSON.stringify(body) })).json(),

  /** Supprimer avis */
  deleteReview: async (id) =>
    (await req(`/reviews/${id}`, { method: 'DELETE' })).json(),

  /** Liste des lieux */
  lieux: async () => (await req('/lieux')).json(),

  /** Créer lieu */
  createLieu: async (body) =>
    (await req('/lieux', { method: 'POST', body: JSON.stringify(body) })).json(),

  /** Modifier lieu */
  updateLieu: async (id, body) =>
    (await req(`/lieux/${id}`, { method: 'PUT', body: JSON.stringify(body) })).json(),

  /** Supprimer lieu */
  deleteLieu: async (id) =>
    (await req(`/lieux/${id}`, { method: 'DELETE' })).json(),

  /** Sync Google Places pour un lieu (démarre un job en arrière-plan) */
  syncGoogle: async (id) =>
    (await req(`/lieux/${id}/sync-google`, { method: 'POST' })).json(),

  /** Sync Trustpilot pour un lieu */
  syncTrustpilot: async (id) =>
    (await req(`/lieux/${id}/sync-trustpilot`, { method: 'POST' })).json(),

  /** Sync TripAdvisor pour un lieu */
  syncTripadvisor: async (id) =>
    (await req(`/lieux/${id}/sync-tripadvisor`, { method: 'POST' })).json(),

  /** Tester clé API Trustpilot */
  testTrustpilotKey: async (key) =>
    (await req('/settings/test-trustpilot-key', { method: 'POST', body: JSON.stringify({ key }) })).json(),

  /** Tester clé API TripAdvisor */
  testTripadvisorKey: async (key) =>
    (await req('/settings/test-tripadvisor-key', { method: 'POST', body: JSON.stringify({ key }) })).json(),

  /** Flush all dashboard caches */
  flushCache: async () =>
    (await req('/flush-cache', { method: 'POST' })).json(),

  /** Export CSV */
  exportCsv: async ({ source = '', lieu_id = '', rating = 0 } = {}) => {
    const p = new URLSearchParams({ source, lieu_id, rating })
    return (await req(`/export?${p}`)).json()
  },

  /** Réglages */
  settings: async () => (await req('/settings')).json(),

  /** Enregistrer réglages */
  saveSettings: async (body) =>
    (await req('/settings', { method: 'POST', body: JSON.stringify(body) })).json(),

  /** Tester la clé API Google Maps */
  testGoogleKey: async (key) =>
    (await req('/settings/test-google-key', { method: 'POST', body: JSON.stringify({ key }) })).json(),

  /** Post types publics disponibles pour la liaison */
  postTypes: async () => (await req('/post-types')).json(),

  /** Posts des types liés (pour le sélecteur de liaison) */
  linkedPosts: async (postType = '') => {
    const qs = postType ? `?post_type=${encodeURIComponent(postType)}` : ''
    return (await req(`/linked-posts${qs}`)).json()
  },

  /** Post-matches pour mapping produits */
  importPostMatches: async ({ search = '', post_type = '' } = {}) => {
    const p = new URLSearchParams({ search, post_type })
    return (await req(`/import/post-matches?${p}`)).json()
  },

  /** Aperçu import CSV */
  importPreview: async (body) =>
    (await req('/import/preview', { method: 'POST', body: JSON.stringify(body) })).json(),

  /** Exécuter import CSV */
  importExecute: async (body) =>
    (await req('/import/execute', { method: 'POST', body: JSON.stringify(body) })).json(),

  /** AI — generate summary for a lieu */
  aiGenerateSummary: async (lieuId = 'all') =>
    (await req('/ai/generate-summary', { method: 'POST', body: JSON.stringify({ lieu_id: lieuId }) })).json(),

  /** Email digest — send test */
  emailDigestTest: async () =>
    (await req('/email-digest/test', { method: 'POST' })).json(),
}
