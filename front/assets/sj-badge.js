/**
 * SJ Reviews — Badge hydration
 * Fetches fresh aggregate data from REST API and updates [data-sj-badge] elements,
 * bypassing page cache.
 */
;(function () {
  'use strict'

  var cfg     = window.sjBadgeConfig || window.sjReviewsConfig || {}
  var restUrl = cfg.restUrl || '/wp-json/sj-reviews/v1/'
  var nonce   = cfg.nonce   || ''

  document.addEventListener('DOMContentLoaded', initBadges)
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_rating_badge.default', initBadges)
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_inline_rating.default', initBadges)
  }

  function initBadges() {
    document.querySelectorAll('[data-sj-badge]').forEach(hydrate)
  }

  function hydrate(el) {
    var data = {}
    try { data = JSON.parse(el.dataset.sjBadge) } catch (e) { return }

    var lieuId = (data.lieu_id !== undefined && data.lieu_id !== null) ? data.lieu_id : ''
    var params = new URLSearchParams({ lieu_id: lieuId })

    fetch(restUrl + 'front/aggregate?' + params.toString(), {
      headers: nonce ? { 'X-WP-Nonce': nonce } : {}
    })
      .then(function (res) { return res.json() })
      .then(function (d) {
        if (!d || (!d.count && !d.avg)) return

        var avg   = parseFloat(d.avg  || 0).toFixed(1)
        var count = formatCount(parseInt(d.count || 0, 10))

        el.querySelectorAll('[data-sj-tpl]').forEach(function (span) {
          span.textContent = span.dataset.sjTpl
            .replace(/\{\{avg\}\}/g, avg)
            .replace(/\{\{count\}\}/g, count)
        })
      })
      .catch(function () {})
  }

  /**
   * Format a number with non-breaking space as thousands separator (French style).
   * e.g. 1234 → "1\u202f234"
   */
  function formatCount(n) {
    return n.toLocaleString('fr-FR')
  }

})()
