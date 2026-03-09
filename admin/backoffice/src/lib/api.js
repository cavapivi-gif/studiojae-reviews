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
  dashboard: async () => (await req('/dashboard')).json(),

  /** Liste des avis avec filtres */
  reviews: async ({ page = 1, perPage = 20, search = '', rating = 0, source = '', lieu_id = '', orderby = 'date', order = 'DESC' } = {}) => {
    const p = new URLSearchParams({ page, per_page: perPage, search, rating, source, lieu_id, orderby, order })
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

  /** Statut de la sync (polling) */
  syncGoogleStatus: async (id) =>
    (await req(`/lieux/${id}/sync-status`)).json(),

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
}
