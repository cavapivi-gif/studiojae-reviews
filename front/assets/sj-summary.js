/**
 * SJ Reviews — Widget Page Avis
 * Gestion : filtre modal, tri, "Voir plus" cards, troncature texte, sous-critères
 * AJAX load more + backend search
 */
;(function () {
  'use strict'

  var cfg = window.sjReviewsConfig || {}
  var restUrl = cfg.restUrl || '/wp-json/sj-reviews/v1/'
  var nonce   = cfg.nonce   || ''

  document.addEventListener('DOMContentLoaded', initAll)
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_summary.default', initAll)
  }

  function initAll() {
    document.querySelectorAll('.sj-summary').forEach(initWidget)
  }

  function initWidget(widget) {
    var uid      = widget.id
    var initial  = parseInt(widget.dataset.initial, 10) || 5
    var words    = parseInt(widget.dataset.words, 10) || 40
    var reviews  = widget.querySelector('.sj-summary__reviews')
    var filterBar = widget.querySelector('.sj-summary__filterbar')
    var loadBtn  = widget.querySelector('.sj-summary__load-btn')
    var loadMore = widget.querySelector('.sj-summary__loadmore')
    var modal    = document.getElementById(uid + '-modal')
    var searchInput = widget.querySelector('.sj-search__input')

    if (!reviews) return

    // Read widget params from data attributes for AJAX queries
    var ajaxParams = {
      lieu_id: widget.dataset.lieuId || 'all',
      lieu_ids: widget.dataset.lieuIds || '',
      source_filter: widget.dataset.sourceFilter || ''
    }
    var showCertified = widget.dataset.showCertified === '1'

    // ── AI Summary (lazy load from cache) ──────────────────────────────────
    var aiBlock = document.getElementById(uid + '-ai')
    if (aiBlock) {
      var aiLieuId = aiBlock.dataset.lieuId || 'all'
      var aiTextEl = document.getElementById(uid + '-ai-text')
      fetch(restUrl + 'front/ai-summary?lieu_id=' + encodeURIComponent(aiLieuId), {
        headers: { 'X-WP-Nonce': nonce }
      })
        .then(function (r) { return r.json() })
        .then(function (data) {
          if (data.summary && aiTextEl) {
            aiTextEl.textContent = data.summary
            if (data.generated_at) {
              var meta = document.createElement('p')
              meta.className = 'sj-summary__ai-meta'
              meta.textContent = 'Basé sur ' + (data.review_count || '') + ' avis · Généré le ' + data.generated_at
              aiBlock.appendChild(meta)
            }
          } else if (aiTextEl) {
            aiTextEl.innerHTML = '<span class="sj-summary__ai-loading">Aucun résumé disponible. Générez-le depuis les réglages du plugin.</span>'
          }
        })
        .catch(function () {
          if (aiTextEl) aiTextEl.innerHTML = '<span class="sj-summary__ai-loading">Résumé indisponible.</span>'
        })
    }

    // État des filtres (en cours d'édition dans modal)
    var pending = { rating: null, period: null, language: null, travel: null }
    // État appliqué
    var active  = { rating: null, period: null, language: null, travel: null, sort: 'recent', search: '' }

    // AJAX state
    var currentPage     = 1
    var totalFound      = parseInt(widget.dataset.totalReviews, 10) || 0
    var ajaxLoading     = false
    var searchTimer     = null
    var filtersActive   = false   // true when any filter is applied
    var filteredTotal   = 0       // total matching filtered results
    var filteredPage    = 1       // current page within filtered results

    /* ── Troncature par mots ─────────────────────────────────────────────── */
    function truncateCards(container) {
      container.querySelectorAll('.sj-card__text').forEach(function (p) {
        var full      = p.dataset.full || p.textContent.trim()
        var wordArr   = full.split(/\s+/)
        var shortArr  = wordArr.slice(0, words)
        var short     = wordArr.length > words ? shortArr.join(' ') + '…' : full
        p.textContent   = short
        p.dataset.short = short
        p.dataset.full  = full
        var btn = p.closest('.sj-card__body') && p.closest('.sj-card__body').querySelector('.sj-card__more')
        if (btn) btn.hidden = wordArr.length <= words
      })
    }
    truncateCards(reviews)

    /* ── Boutons "Voir plus" par card ──────────────────────────────────── */
    function bindCardMoreButtons(container) {
      container.querySelectorAll('.sj-card__more').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var p        = this.closest('.sj-card__body').querySelector('.sj-card__text')
          var expanded = this.getAttribute('aria-expanded') === 'true'
          if (expanded) {
            p.textContent = p.dataset.short
            this.setAttribute('aria-expanded', 'false')
            this.innerHTML = 'Voir plus <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
          } else {
            p.textContent = p.dataset.full
            this.setAttribute('aria-expanded', 'true')
            this.innerHTML = 'Réduire <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M11 9l-4-4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
          }
        })
      })
    }
    bindCardMoreButtons(reviews)

    /* ── Tri ──────────────────────────────────────────────────────────────── */
    if (filterBar) {
      var sortSel = filterBar.querySelector('[data-filter="sort"]')
      if (sortSel) {
        sortSel.addEventListener('change', function () {
          active.sort = this.value
          applyFilters()
        })
      }

      // Reset bar button
      var resetBtn = filterBar.querySelector('.sj-filters__reset')
      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          resetAll()
          applyFilters()
        })
      }

      // Recherche — debounced, goes to backend
      if (searchInput) {
        searchInput.addEventListener('input', function () {
          var val = this.value.trim()
          clearTimeout(searchTimer)
          searchTimer = setTimeout(function () {
            active.search = val
            applyFilters()
          }, 350)
        })
      }
    }

    /* ── Modal filtres ───────────────────────────────────────────────────── */
    var trigger = filterBar && filterBar.querySelector('.sj-filter-trigger')

    if (trigger && modal) {
      // Ouvrir modal
      trigger.addEventListener('click', function () {
        Object.assign(pending, { rating: active.rating, period: active.period, language: active.language, travel: active.travel })
        syncModalUI()
        modal.hidden = false
        document.body.classList.add('sj-modal-open')
        this.setAttribute('aria-expanded', 'true')
      })

      // Fermer (overlay + bouton X)
      modal.querySelectorAll('[data-close="modal"]').forEach(function (el) {
        el.addEventListener('click', closeModal)
      })

      // Escape key
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal()
      })

      // Pills dans la modal → toggle pending
      modal.querySelectorAll('.sj-filter-modal__pill, .sj-filter-modal__dot-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var type = this.dataset.filter
          var val  = this.dataset.value
          if (pending[type] === val) {
            pending[type] = null
            this.setAttribute('aria-pressed', 'false')
            this.classList.remove('is-active')
          } else {
            modal.querySelectorAll('[data-filter="' + type + '"]').forEach(function (p) {
              p.setAttribute('aria-pressed', 'false')
              p.classList.remove('is-active')
            })
            pending[type] = val
            this.setAttribute('aria-pressed', 'true')
            this.classList.add('is-active')
          }
        })
      })

      // Appliquer
      var applyBtn = modal.querySelector('.sj-filter-modal__btn-apply')
      if (applyBtn) {
        applyBtn.addEventListener('click', function () {
          Object.assign(active, pending)
          closeModal()
          applyFilters()
        })
      }

      // Réinitialiser (dans la modal)
      var modalResetBtn = modal.querySelector('.sj-filter-modal__btn-reset')
      if (modalResetBtn) {
        modalResetBtn.addEventListener('click', function () {
          pending.rating = pending.period = pending.language = pending.travel = null
          syncModalUI()
        })
      }
    }

    function closeModal() {
      modal.hidden = true
      document.body.classList.remove('sj-modal-open')
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'false')
        trigger.focus()
      }
    }

    // Focus trap inside modal (a11y)
    if (modal) {
      modal.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return
        var focusable = Array.from(modal.querySelectorAll(
          'button:not([hidden]):not([disabled]), [tabindex]:not([tabindex="-1"]), input, select, textarea, a[href]'
        ))
        if (!focusable.length) return
        var first = focusable[0]
        var last  = focusable[focusable.length - 1]
        if (e.shiftKey) {
          if (document.activeElement === first) { e.preventDefault(); last.focus() }
        } else {
          if (document.activeElement === last) { e.preventDefault(); first.focus() }
        }
      })
    }

    function syncModalUI() {
      modal.querySelectorAll('[data-filter]').forEach(function (btn) {
        var type = btn.dataset.filter
        if (!type || type === 'sort') return
        var isActive = pending[type] === btn.dataset.value
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false')
        btn.classList.toggle('is-active', isActive)
      })
    }

    /* ── AJAX Load More ──────────────────────────────────────────────────── */
    if (loadBtn) {
      loadBtn.addEventListener('click', function () {
        if (filtersActive) {
          loadNextFilteredPage()
        } else {
          loadNextPage()
        }
      })
    }

    function loadNextPage() {
      if (ajaxLoading) return
      ajaxLoading = true
      if (loadBtn) loadBtn.disabled = true

      var nextPage = currentPage + 1
      var params = new URLSearchParams({
        page: nextPage,
        per_page: initial,
        sort: active.sort,
        lieu_id: ajaxParams.lieu_id,
        lieu_ids: ajaxParams.lieu_ids,
        source_filter: ajaxParams.source_filter
      })

      fetch(restUrl + 'front/reviews?' + params.toString(), {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {}
      })
        .then(function (res) {
          totalFound = parseInt(res.headers.get('X-WP-Total'), 10) || totalFound
          return res.json()
        })
        .then(function (data) {
          if (!Array.isArray(data) || data.length === 0) {
            if (loadMore) loadMore.hidden = true
            return
          }
          currentPage = nextPage
          data.forEach(function (r) {
            var card = buildCardHtml(r)
            reviews.insertAdjacentHTML('beforeend', card)
          })
          // Initialize new cards
          truncateCards(reviews)
          bindCardMoreButtons(reviews)

          // Update load more visibility
          var totalLoaded = reviews.querySelectorAll('.sj-card').length
          if (totalLoaded >= totalFound) {
            if (loadMore) loadMore.hidden = true
          } else {
            updateLoadMoreCount(totalFound - totalLoaded)
          }
        })
        .catch(function () {})
        .finally(function () {
          ajaxLoading = false
          if (loadBtn) loadBtn.disabled = false
        })
    }

    function updateLoadMoreCount(remaining) {
      if (!loadMore) return
      var countEl = loadMore.querySelector('.sj-summary__load-count')
      if (countEl) countEl.textContent = remaining > 0 ? '(' + remaining + ')' : ''
      loadMore.hidden = remaining <= 0
    }

    /* ── Application des filtres ─────────────────────────────────────────── */
    function applyFilters() {
      var anyFilter = active.rating || active.period || active.language || active.travel || active.search

      if (anyFilter) {
        filtersActive = true
        filteredPage = 1
        fetchFilteredReviews(true) // true = replace all cards (page 1)
      } else {
        filtersActive = false
        // No filter: re-fetch page 1 from server
        showServerCards()
      }

      updateBadges(anyFilter)
    }

    function buildFilterParams(page) {
      var params = new URLSearchParams({
        page: page,
        per_page: initial,
        sort: active.sort,
        lieu_id: ajaxParams.lieu_id,
        lieu_ids: ajaxParams.lieu_ids,
        source_filter: ajaxParams.source_filter
      })
      if (active.rating)   params.set('rating', active.rating)
      if (active.period)   params.set('period', active.period)
      if (active.language) params.set('language', active.language)
      if (active.travel)   params.set('travel', active.travel)
      if (active.search)   params.set('search', active.search)
      return params
    }

    function fetchFilteredReviews(replaceAll) {
      if (ajaxLoading) return
      ajaxLoading = true

      var params = buildFilterParams(replaceAll ? 1 : filteredPage + 1)

      reviews.classList.add('sj-summary__reviews--loading')
      if (loadBtn) loadBtn.disabled = true

      fetch(restUrl + 'front/reviews?' + params.toString(), {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {}
      })
        .then(function (res) {
          filteredTotal = parseInt(res.headers.get('X-WP-Total'), 10) || 0
          return res.json()
        })
        .then(function (data) {
          if (!Array.isArray(data)) return

          if (replaceAll) {
            // Replace all cards (first page of filtered results)
            reviews.innerHTML = ''
            filteredPage = 1
          } else {
            filteredPage++
          }

          data.forEach(function (r) {
            reviews.insertAdjacentHTML('beforeend', buildCardHtml(r))
          })
          truncateCards(reviews)
          bindCardMoreButtons(reviews)

          if (replaceAll && data.length === 0) {
            reviews.innerHTML = '<p class="sj-summary--empty">Aucun avis ne correspond à vos critères.</p>'
            if (loadMore) loadMore.hidden = true
          } else {
            // Show/hide load more based on filtered total
            var totalLoaded = reviews.querySelectorAll('.sj-card').length
            var remaining = filteredTotal - totalLoaded
            if (remaining > 0) {
              updateLoadMoreCount(remaining)
            } else {
              if (loadMore) loadMore.hidden = true
            }
          }
        })
        .catch(function () {})
        .finally(function () {
          reviews.classList.remove('sj-summary__reviews--loading')
          ajaxLoading = false
          if (loadBtn) loadBtn.disabled = false
        })
    }

    function loadNextFilteredPage() {
      fetchFilteredReviews(false) // false = append (next page)
    }

    function showServerCards() {
      // Re-fetch from page 1 (reset to server state)
      if (ajaxLoading) return
      ajaxLoading = true

      var params = new URLSearchParams({
        per_page: initial,
        sort: active.sort,
        lieu_id: ajaxParams.lieu_id,
        lieu_ids: ajaxParams.lieu_ids,
        source_filter: ajaxParams.source_filter
      })

      reviews.classList.add('sj-summary__reviews--loading')

      fetch(restUrl + 'front/reviews?' + params.toString(), {
        headers: nonce ? { 'X-WP-Nonce': nonce } : {}
      })
        .then(function (res) {
          totalFound = parseInt(res.headers.get('X-WP-Total'), 10) || 0
          return res.json()
        })
        .then(function (data) {
          if (!Array.isArray(data)) return
          reviews.innerHTML = ''
          currentPage = 1
          data.forEach(function (r) {
            reviews.insertAdjacentHTML('beforeend', buildCardHtml(r))
          })
          truncateCards(reviews)
          bindCardMoreButtons(reviews)
          var remaining = totalFound - data.length
          updateLoadMoreCount(remaining)
        })
        .catch(function () {})
        .finally(function () {
          reviews.classList.remove('sj-summary__reviews--loading')
          ajaxLoading = false
        })
    }

    // Initialize load more count from server-rendered data
    var serverCardCount = reviews.querySelectorAll('.sj-card').length
    if (totalFound === 0) {
      totalFound = serverCardCount
    }
    // Fix initial count display
    if (loadMore) {
      var remainingInit = totalFound - serverCardCount
      if (remainingInit > 0) {
        updateLoadMoreCount(remainingInit)
      }
    }

    function resetAll() {
      active.rating = active.period = active.language = active.travel = null
      active.search = ''
      pending.rating = pending.period = pending.language = pending.travel = null
      if (searchInput) searchInput.value = ''
      filtersActive = false
    }

    function updateBadges(anyFilter) {
      var count = [active.rating, active.period, active.language, active.travel].filter(Boolean).length
      var badge = trigger && trigger.querySelector('.sj-filter-trigger__badge')
      if (badge) {
        badge.textContent = count > 0 ? count : ''
        badge.hidden = count === 0
      }
      var activeBar = filterBar && filterBar.querySelector('.sj-filters__active')
      if (activeBar) {
        var countEl = activeBar.querySelector('.sj-filters__active-count')
        if (countEl) countEl.textContent = count > 0 ? '(' + count + ')' : ''
        activeBar.hidden = count === 0
      }
    }

    /* ── Build card HTML from JSON ──────────────────────────────────────── */
    function buildCardHtml(r) {
      var avatar = ''
      if (r.avatar) {
        avatar = '<img class="sj-card__avatar sj-card__avatar--img" src="' + escHtml(r.avatar) + '" alt="' + escHtml(r.author) + '" width="36" height="36" loading="lazy">'
      } else {
        var initiale = r.author ? r.author.charAt(0).toUpperCase() : '?'
        var colorIdx = (r.author || '').charCodeAt(0) % 6
        var colors = ['#e0e7ff','#fce7f3','#d1fae5','#fef3c7','#ede9fe','#fee2e2']
        var textColors = ['#4f46e5','#be185d','#059669','#d97706','#7c3aed','#dc2626']
        avatar = '<div class="sj-card__avatar sj-card__avatar--initiale" aria-hidden="true" style="background:' + colors[colorIdx] + ';color:' + textColors[colorIdx] + '">' + escHtml(initiale) + '</div>'
      }

      var certifiedHtml = (showCertified && r.certified) ? '<span class="sj-card__certified">Certifié</span>' : ''

      var bubblesHtml = buildBubblesHtml(r.rating || 0)

      var metaParts = []
      if (r.visit_date) {
        try {
          var d = new Date(r.visit_date)
          metaParts.push(d.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' }))
        } catch (e) { /* ignore */ }
      }
      var travelLabels = { couple: 'Couple', solo: 'Solo', famille: 'Famille', amis: 'Entre amis', affaires: 'Affaires' }
      if (r.travel_type && travelLabels[r.travel_type]) {
        metaParts.push(travelLabels[r.travel_type])
      }
      var metaHtml = metaParts.length ? '<span class="sj-card__meta">' + escHtml(metaParts.join(' · ')) + '</span>' : ''

      var titleHtml = r.avis_title ? '<h3 class="sj-card__title">' + escHtml(r.avis_title) + '</h3>' : ''

      var textHtml = ''
      if (r.text) {
        textHtml = '<div class="sj-card__body"><p class="sj-card__text" data-full="' + escAttr(r.text) + '">' + escHtml(r.text) + '</p>'
        var wc = r.text.split(/\s+/).length
        if (wc > words) {
          textHtml += '<button type="button" class="sj-card__more" aria-expanded="false">Voir plus <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
        }
        textHtml += '</div>'
      }

      var dateHtml = ''
      if (r.date) {
        try {
          var pd = new Date(r.date)
          dateHtml = '<footer class="sj-card__footer"><time class="sj-card__date" datetime="' + escAttr(r.date) + '">Rédigé le ' + pd.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) + '</time></footer>'
        } catch (e) { /* ignore */ }
      }

      var sourceName = r.source && r.source !== 'direct' ? '<span class="sj-card__source-name">' + escHtml(r.source.charAt(0).toUpperCase() + r.source.slice(1)) + '</span>' : ''

      return '<article class="sj-card" data-rating="' + (r.rating || 0) + '" data-language="' + escAttr(r.language || 'fr') + '" data-date="' + escAttr(r.date || '') + '" data-travel="' + escAttr(r.travel_type || '') + '">'
        + '<div class="sj-card__header"><div class="sj-card__author-block">' + avatar
        + '<div class="sj-card__author-info"><span class="sj-card__author-name">' + escHtml(r.author || 'Anonyme') + '</span>' + sourceName + '</div></div>' + certifiedHtml + '</div>'
        + '<div class="sj-card__rating">' + bubblesHtml + metaHtml + '</div>'
        + titleHtml + textHtml + dateHtml
        + '</article>'
    }

    function buildBubblesHtml(rating) {
      if (!rating || rating <= 0) return ''
      var html = '<div class="sj-summary__bubbles sj-summary__bubbles--sm" aria-label="' + rating + ' sur 5">'
      for (var i = 1; i <= 5; i++) {
        var fill = Math.min(1, Math.max(0, rating - (i - 1)))
        var cls = fill >= 0.75 ? 'full' : (fill >= 0.25 ? 'half' : 'empty')
        html += '<span class="sj-summary__bubble sj-summary__bubble--' + cls + '" aria-hidden="true"></span>'
      }
      html += '</div>'
      return html
    }

    function escHtml(s) {
      var div = document.createElement('div')
      div.textContent = s || ''
      return div.innerHTML
    }

    function escAttr(s) {
      return (s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    }
  }

})()
