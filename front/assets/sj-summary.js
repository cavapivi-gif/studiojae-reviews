/**
 * SJ Reviews — Widget Page Avis
 * Gestion : filtres pills, tri, "Voir plus" cards, troncature texte
 */
;(function () {
  'use strict'

  /* ── Constantes ──────────────────────────────────────────────────────── */
  const TRUNCATE_LEN = 220  // caractères avant troncature

  /* ── Init au chargement ──────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', initAll)
  // Compatibilité Elementor (réinitialise après rendu éditeur)
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_summary.default', initAll)
  }

  function initAll() {
    document.querySelectorAll('.sj-summary').forEach(initWidget)
  }

  /* ── Initialisation par widget ───────────────────────────────────────── */
  function initWidget(widget) {
    const uid      = widget.id
    const initial  = parseInt(widget.dataset.initial, 10) || 5
    const reviews  = widget.querySelector('.sj-summary__reviews')
    const filters  = widget.querySelector('.sj-summary__filters')
    const loadBtn  = widget.querySelector('.sj-summary__load-btn')
    const loadMore = widget.querySelector('.sj-summary__loadmore')

    if (!reviews) return

    // État des filtres actifs
    const active = { rating: null, period: null, language: null, sort: 'recent' }

    /* ── Troncature des textes ─────────────────────────────────────────── */
    reviews.querySelectorAll('.sj-card__text').forEach(p => {
      const full  = p.dataset.full || p.textContent.trim()
      const short = full.length > TRUNCATE_LEN
        ? full.slice(0, TRUNCATE_LEN).trimEnd() + '…'
        : full
      p.textContent = short
      p.dataset.short = short
      p.dataset.full  = full
    })

    /* ── Boutons "Voir plus" par card ──────────────────────────────────── */
    reviews.querySelectorAll('.sj-card__more').forEach(btn => {
      btn.addEventListener('click', function () {
        const p       = this.closest('.sj-card__body').querySelector('.sj-card__text')
        const expanded = this.getAttribute('aria-expanded') === 'true'
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

    /* ── Pills de filtres ──────────────────────────────────────────────── */
    if (filters) {
      filters.querySelectorAll('.sj-filters__pill').forEach(pill => {
        pill.addEventListener('click', function () {
          const type = this.dataset.filter
          const val  = this.dataset.value

          // Toggle : si déjà actif → désactive
          if (active[type] === val) {
            active[type] = null
            this.setAttribute('aria-pressed', 'false')
            this.classList.remove('is-active')
          } else {
            // Désactive tous les pills du même groupe
            filters.querySelectorAll(`.sj-filters__pill[data-filter="${type}"]`).forEach(p => {
              p.setAttribute('aria-pressed', 'false')
              p.classList.remove('is-active')
            })
            active[type] = val
            this.setAttribute('aria-pressed', 'true')
            this.classList.add('is-active')
          }
          applyFilters()
        })
      })

      /* Tri ──────────────────────────────────────────────────────────── */
      const sortSel = filters.querySelector('[data-filter="sort"]')
      if (sortSel) {
        sortSel.addEventListener('change', function () {
          active.sort = this.value
          applyFilters()
        })
      }

      /* Reset ────────────────────────────────────────────────────────── */
      const resetBtn = filters.querySelector('.sj-filters__reset')
      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          active.rating = active.period = active.language = null
          active.sort   = 'recent'
          filters.querySelectorAll('.sj-filters__pill').forEach(p => {
            p.setAttribute('aria-pressed', 'false')
            p.classList.remove('is-active')
          })
          if (sortSel) sortSel.value = 'recent'
          applyFilters()
        })
      }
    }

    /* ── "Voir plus" global (load more) ────────────────────────────────── */
    if (loadBtn) {
      loadBtn.addEventListener('click', function () {
        reviews.querySelectorAll('.sj-card--overflow').forEach(c => {
          c.classList.remove('sj-card--overflow')
        })
        if (loadMore) loadMore.hidden = true
      })
    }

    /* ── Application des filtres ───────────────────────────────────────── */
    function applyFilters() {
      const cards = Array.from(reviews.querySelectorAll('.sj-card'))

      // Filtrage
      cards.forEach(card => {
        const pass =
          (!active.rating   || card.dataset.rating   === active.rating)   &&
          (!active.period   || card.dataset.period   === active.period)   &&
          (!active.language || card.dataset.language === active.language)
        card.classList.toggle('sj-card--hidden', !pass)
      })

      // Tri
      const visible = cards.filter(c => !c.classList.contains('sj-card--hidden'))
      sortCards(visible)

      // Ré-applique overflow si aucun filtre actif
      const anyActive = active.rating || active.period || active.language
      if (!anyActive) {
        cards.forEach((c, i) => {
          if (i >= initial) c.classList.add('sj-card--overflow')
          else              c.classList.remove('sj-card--overflow')
        })
        if (loadMore) loadMore.hidden = false
      } else {
        cards.forEach(c => c.classList.remove('sj-card--overflow'))
        if (loadMore) loadMore.hidden = true
      }

      // Badge compteur filtres actifs
      updateFilterBadge()
    }

    function sortCards(cards) {
      const parent = reviews
      const allCards = Array.from(reviews.querySelectorAll('.sj-card'))

      allCards.sort((a, b) => {
        if (active.sort === 'rating_desc') {
          return parseInt(b.dataset.rating, 10) - parseInt(a.dataset.rating, 10)
        }
        if (active.sort === 'rating_asc') {
          return parseInt(a.dataset.rating, 10) - parseInt(b.dataset.rating, 10)
        }
        // recent (par data-date desc)
        return new Date(b.dataset.date) - new Date(a.dataset.date)
      })

      allCards.forEach(c => parent.appendChild(c))
    }

    function updateFilterBadge() {
      const activeBar = filters?.querySelector('.sj-filters__active')
      if (!activeBar) return
      const count = [active.rating, active.period, active.language].filter(Boolean).length
      const countEl = activeBar.querySelector('.sj-filters__active-count')
      if (count > 0) {
        activeBar.hidden = false
        if (countEl) countEl.textContent = `(${count})`
      } else {
        activeBar.hidden = true
      }
    }
  }

})()
