/**
 * Module centralisé de gestion des providers d'avis.
 *
 * Remplace SOURCE_LABELS, SOURCE_HEX, SOURCE_COLORS, SOURCE_OPTIONS par des appels
 * au registre dynamique (avec cache localStorage de 5 minutes).
 *
 * Usage :
 *   import { getProviders, getProviderLabel, getProviderColor, getProviderIcon } from '@/lib/providers'
 *   const providers = await getProviders()
 *   const label = getProviderLabel('google')   // synchrone après premier chargement
 */

import { SOURCE_LABELS, SOURCE_HEX } from './constants'
import { api } from './api'

const CACHE_KEY = 'sj_providers_cache'
const CACHE_TTL = 5 * 60 * 1000 // 5 minutes

/** @type {Record<string, object>|null} */
let _memory = null

// Fallback synchrone depuis les constantes statiques (avant le premier fetch)
function buildFallback() {
  const out = {}
  for (const [id, label] of Object.entries(SOURCE_LABELS)) {
    out[id] = {
      id,
      label,
      color: SOURCE_HEX[id] ?? '#9CA3AF',
      icon_type: 'letter',
      icon_value: label.charAt(0).toUpperCase(),
      active: true,
      is_system: true,
    }
  }
  return out
}

/**
 * Charge les providers depuis l'API (avec cache localStorage TTL 5min).
 * @returns {Promise<Record<string, object>>}
 */
export async function getProviders() {
  if (_memory) return _memory

  // Try localStorage cache
  try {
    const raw = localStorage.getItem(CACHE_KEY)
    if (raw) {
      const { ts, data } = JSON.parse(raw)
      if (Date.now() - ts < CACHE_TTL && data) {
        _memory = data
        return _memory
      }
    }
  } catch {}

  // Fetch from REST API
  try {
    const list = await api.providers()
    const map = {}
    for (const p of list) {
      map[p.id] = p
    }
    _memory = map
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify({ ts: Date.now(), data: map }))
    } catch {}
    return _memory
  } catch {
    // Fallback on error
    const fb = buildFallback()
    _memory = fb
    return fb
  }
}

/** Vide le cache providers (appelé après une modification) */
export function flushProvidersCache() {
  _memory = null
  try { localStorage.removeItem(CACHE_KEY) } catch {}
}

// ── Accesseurs synchrones (utilisent le cache mémoire ou le fallback statique) ──

function _cache() {
  return _memory ?? buildFallback()
}

/** Label d'un provider. */
export function getProviderLabel(id) {
  return _cache()[id]?.label ?? SOURCE_LABELS[id] ?? id
}

/** Couleur hex d'un provider. */
export function getProviderColor(id) {
  return _cache()[id]?.color ?? SOURCE_HEX[id] ?? '#9CA3AF'
}

/**
 * Construit le HTML de l'icône d'un provider.
 * Pour les SVG inline, retourne le SVG directement.
 * Pour les autres types, retourne un span lettre coloré.
 *
 * @param {string} id     Provider id
 * @param {number} size   Taille en pixels
 * @returns {string}      HTML string (unsafe — à utiliser avec dangerouslySetInnerHTML)
 */
export function getProviderIconHtml(id, size = 16) {
  const p = _cache()[id]
  if (!p) return letterIcon(id.charAt(0).toUpperCase(), '#9CA3AF', size)

  const { icon_type, icon_value, icon_url, color } = p
  const label = p.label || id

  if (icon_type === 'svg_inline' && icon_value) {
    return `<span class="sj-provider-icon sj-provider-icon--svg" style="display:inline-flex;align-items:center;width:${size}px;height:${size}px" aria-label="${escHtml(label)}">${icon_value}</span>`
  }
  if (icon_type === 'img_url' && (icon_url || icon_value)) {
    return `<img class="sj-provider-icon" src="${escAttr(icon_url || icon_value)}" alt="${escHtml(label)}" width="${size}" height="${size}" loading="lazy">`
  }
  if (icon_type === 'emoji' && icon_value) {
    return `<span class="sj-provider-icon sj-provider-icon--emoji" style="font-size:${size}px;line-height:1" aria-label="${escHtml(label)}">${escHtml(icon_value)}</span>`
  }
  const letter = icon_value || label.charAt(0).toUpperCase()
  return letterIcon(letter, color || '#9CA3AF', size)
}

function letterIcon(letter, color, size) {
  const fontSize = Math.max(8, Math.round(size * 0.55))
  return `<span class="sj-provider-icon sj-provider-icon--letter" style="display:inline-flex;align-items:center;justify-content:center;width:${size}px;height:${size}px;border-radius:50%;background:${escAttr(color)};color:#fff;font-size:${fontSize}px;font-weight:600;line-height:1;font-family:sans-serif" aria-hidden="true">${escHtml(letter)}</span>`
}

/**
 * Retourne la liste des providers actifs sous forme d'options pour un <select>.
 * Compatible avec SOURCE_OPTIONS.
 */
export async function getProviderOptions(allLabel = 'Toutes les sources') {
  const providers = await getProviders()
  const opts = [{ value: '', label: allLabel }]
  for (const [id, p] of Object.entries(providers)) {
    if (p.active) opts.push({ value: id, label: p.label })
  }
  return opts
}

function escHtml(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
}
function escAttr(s) {
  return String(s || '').replace(/"/g, '&quot;')
}
