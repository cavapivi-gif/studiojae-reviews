/**
 * SJ Reviews — Badge hydration
 * Fetches fresh aggregate data from REST API and updates [data-sj-badge] elements,
 * bypassing page cache.
 *
 * Optimisation : les requêtes sont dédupliquées par clé lieu_id via un cache de
 * promesses en mémoire — N badges sur la même page pour le même lieu = 1 seul fetch.
 */
;(function () {
  'use strict'

  var cfg     = window.sjBadgeConfig || window.sjReviewsConfig || {}
  var restUrl = cfg.restUrl || '/wp-json/sj-reviews/v1/'
  var nonce   = cfg.nonce   || ''

  /** Cache de promesses fetch par clé de paramètres (évite les doubles requêtes) */
  var fetchCache = {}

  document.addEventListener('DOMContentLoaded', initBadges)
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_rating_badge.default', initBadges)
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_inline_rating.default', initBadges)
  }

  function initBadges() {
    document.querySelectorAll('[data-sj-badge]').forEach(hydrate)
  }

  /**
   * Retourne la promesse de fetch pour une clé donnée, en la créant une seule fois.
   * @param {string} cacheKey  Clé unique représentant les paramètres de la requête
   * @param {URLSearchParams} params
   * @returns {Promise<object|null>}
   */
  function getAggregate(cacheKey, params) {
    if (!fetchCache[cacheKey]) {
      fetchCache[cacheKey] = fetch(restUrl + 'front/aggregate?' + params.toString(), {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {}
      })
        .then(function (res) { return res.json() })
        .catch(function () { return null })
    }
    return fetchCache[cacheKey]
  }

  function hydrate(el) {
    var data = {}
    try { data = JSON.parse(el.dataset.sjBadge) } catch (e) { return }

    // lieu_id peut être un array quand la page a plusieurs lieux liés (multi-select meta box).
    var lieuId       = (data.lieu_id !== undefined && data.lieu_id !== null) ? data.lieu_id : ''
    // source_filter : tableau ou chaîne vide — doit correspondre au render PHP
    var sourceFilter = Array.isArray(data.source_filter) ? data.source_filter.join(',') : ''

    var params, cacheKey
    if (Array.isArray(lieuId)) {
      var joined = lieuId.join(',')
      params   = new URLSearchParams({ lieu_id: 'all', lieu_ids: joined, source_filter: sourceFilter })
      cacheKey = 'all:' + joined + '|' + sourceFilter
    } else {
      params   = new URLSearchParams({ lieu_id: lieuId, source_filter: sourceFilter })
      cacheKey = String(lieuId) + '|' + sourceFilter
    }

    getAggregate(cacheKey, params).then(function (d) {
      if (!d || (!d.count && !d.avg)) return

      var avg   = parseFloat(d.avg  || 0).toFixed(1)
      var count = formatCount(parseInt(d.count || 0, 10))

      el.querySelectorAll('[data-sj-tpl]').forEach(function (span) {
        span.textContent = span.dataset.sjTpl
          .replace(/\{\{avg\}\}/g, avg)
          .replace(/\{\{count\}\}/g, count)
      })
    })
  }

  /**
   * Format a number with non-breaking space as thousands separator (French style).
   * e.g. 1234 → "1\u202f234"
   */
  function formatCount(n) {
    return n.toLocaleString('fr-FR')
  }

})()
