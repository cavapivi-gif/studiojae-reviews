/**
 * SJ Reviews — Widget Page Avis
 * Gestion : filtre modal, tri, "Voir plus" cards, troncature texte, sous-critères
 */
;(function () {
  'use strict'

  document.addEventListener('DOMContentLoaded', initAll)
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj_summary.default', initAll)
  }

  function initAll() {
    document.querySelectorAll('.sj-summary').forEach(initWidget)
  }

  function initWidget(widget) {
    const uid      = widget.id
    const initial  = parseInt(widget.dataset.initial, 10) || 5
    const words    = parseInt(widget.dataset.words, 10) || 40
    const reviews  = widget.querySelector('.sj-summary__reviews')
    const filterBar = widget.querySelector('.sj-summary__filterbar')
    const loadBtn  = widget.querySelector('.sj-summary__load-btn')
    const loadMore = widget.querySelector('.sj-summary__loadmore')
    const modal    = document.getElementById(uid + '-modal')

    if (!reviews) return

    // État des filtres (en cours d'édition dans modal)
    const pending = { rating: null, period: null, language: null, travel: null }
    // État appliqué
    const active  = { rating: null, period: null, language: null, travel: null, sort: 'recent' }

    /* ── Troncature par mots ─────────────────────────────────────────────── */
    reviews.querySelectorAll('.sj-card__text').forEach(p => {
      const full      = p.dataset.full || p.textContent.trim()
      const wordArr   = full.split(/\s+/)
      const shortArr  = wordArr.slice(0, words)
      const short     = wordArr.length > words ? shortArr.join(' ') + '…' : full
      p.textContent   = short
      p.dataset.short = short
      p.dataset.full  = full
      // Montrer/cacher le bouton Voir plus
      const btn = p.closest('.sj-card__body')?.querySelector('.sj-card__more')
      if (btn) btn.hidden = wordArr.length <= words
    })

    /* ── Boutons "Voir plus" par card ──────────────────────────────────── */
    reviews.querySelectorAll('.sj-card__more').forEach(btn => {
      btn.addEventListener('click', function () {
        const p        = this.closest('.sj-card__body').querySelector('.sj-card__text')
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

    /* ── Tri ──────────────────────────────────────────────────────────────── */
    if (filterBar) {
      const sortSel = filterBar.querySelector('[data-filter="sort"]')
      if (sortSel) {
        sortSel.addEventListener('change', function () {
          active.sort = this.value
          applyFilters()
        })
      }

      // Reset bar button
      const resetBtn = filterBar.querySelector('.sj-filters__reset')
      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          resetAll()
          applyFilters()
        })
      }
    }

    /* ── Modal filtres ───────────────────────────────────────────────────── */
    const trigger = filterBar?.querySelector('.sj-filter-trigger')

    if (trigger && modal) {
      // Ouvrir modal
      trigger.addEventListener('click', function () {
        // Copie l'état actif dans pending
        Object.assign(pending, { rating: active.rating, period: active.period, language: active.language, travel: active.travel })
        syncModalUI()
        modal.hidden = false
        document.body.classList.add('sj-modal-open')
        this.setAttribute('aria-expanded', 'true')
      })

      // Fermer (overlay + bouton X)
      modal.querySelectorAll('[data-close="modal"]').forEach(el => {
        el.addEventListener('click', closeModal)
      })

      // Escape key
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal()
      })

      // Pills dans la modal → toggle pending
      modal.querySelectorAll('.sj-filter-modal__pill, .sj-filter-modal__dot-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const type = this.dataset.filter
          const val  = this.dataset.value
          if (pending[type] === val) {
            pending[type] = null
            this.setAttribute('aria-pressed', 'false')
            this.classList.remove('is-active')
          } else {
            modal.querySelectorAll(`[data-filter="${type}"]`).forEach(p => {
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
      modal.querySelector('.sj-filter-modal__btn-apply')?.addEventListener('click', function () {
        Object.assign(active, pending)
        closeModal()
        applyFilters()
      })

      // Réinitialiser (dans la modal)
      modal.querySelector('.sj-filter-modal__btn-reset')?.addEventListener('click', function () {
        pending.rating = pending.period = pending.language = pending.travel = null
        syncModalUI()
      })
    }

    function closeModal() {
      modal.hidden = true
      document.body.classList.remove('sj-modal-open')
      trigger?.setAttribute('aria-expanded', 'false')
    }

    function syncModalUI() {
      modal.querySelectorAll('[data-filter]').forEach(btn => {
        const type = btn.dataset.filter
        if (!type || type === 'sort') return
        const isActive = pending[type] === btn.dataset.value
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false')
        btn.classList.toggle('is-active', isActive)
      })
    }

    /* ── "Voir plus" global (load more) ──────────────────────────────────── */
    let allLoaded = false
    if (loadBtn) {
      loadBtn.addEventListener('click', function () {
        reviews.querySelectorAll('.sj-card--overflow').forEach(c => c.classList.remove('sj-card--overflow'))
        if (loadMore) loadMore.hidden = true
        allLoaded = true
      })
    }

    /* ── Application des filtres ─────────────────────────────────────────── */
    function applyFilters() {
      const cards = Array.from(reviews.querySelectorAll('.sj-card'))

      cards.forEach(card => {
        const pass =
          (!active.rating   || card.dataset.rating   === active.rating)   &&
          (!active.period   || card.dataset.period   === active.period)   &&
          (!active.language || card.dataset.language === active.language) &&
          (!active.travel   || card.dataset.travel   === active.travel)
        card.classList.toggle('sj-card--hidden', !pass)
      })

      sortCards()

      const anyFilter = active.rating || active.period || active.language || active.travel

      if (!anyFilter && !allLoaded) {
        cards.forEach((c, i) => {
          if (c.classList.contains('sj-card--hidden')) return
          // Compte les visibles pour déterminer overflow
        })
        // Ré-applique overflow sur base des cards non-cachées
        let visibleCount = 0
        cards.forEach(c => {
          if (c.classList.contains('sj-card--hidden')) {
            c.classList.remove('sj-card--overflow')
            return
          }
          visibleCount++
          c.classList.toggle('sj-card--overflow', visibleCount > initial)
        })
        const overflowCount = cards.filter(c => c.classList.contains('sj-card--overflow')).length
        if (loadMore) {
          const countEl = loadMore.querySelector('.sj-summary__load-count')
          if (countEl) countEl.textContent = overflowCount > 0 ? `(${overflowCount})` : ''
          loadMore.hidden = overflowCount === 0
        }
      } else {
        cards.forEach(c => c.classList.remove('sj-card--overflow'))
        if (loadMore && anyFilter) loadMore.hidden = true
      }

      updateBadges(anyFilter)
    }

    function sortCards() {
      const allCards = Array.from(reviews.querySelectorAll('.sj-card'))
      allCards.sort((a, b) => {
        if (active.sort === 'rating_desc') return parseInt(b.dataset.rating, 10) - parseInt(a.dataset.rating, 10)
        if (active.sort === 'rating_asc')  return parseInt(a.dataset.rating, 10) - parseInt(b.dataset.rating, 10)
        return new Date(b.dataset.date) - new Date(a.dataset.date)
      })
      allCards.forEach(c => reviews.appendChild(c))
    }

    function resetAll() {
      active.rating = active.period = active.language = active.travel = null
      pending.rating = pending.period = pending.language = pending.travel = null
      allLoaded = false
    }

    function updateBadges(anyFilter) {
      const count = [active.rating, active.period, active.language, active.travel].filter(Boolean).length
      // Badge bouton Filtrer
      const badge = trigger?.querySelector('.sj-filter-trigger__badge')
      if (badge) {
        badge.textContent = count > 0 ? count : ''
        badge.hidden = count === 0
      }
      // Barre réinitialiser
      const activeBar = filterBar?.querySelector('.sj-filters__active')
      if (activeBar) {
        const countEl = activeBar.querySelector('.sj-filters__active-count')
        if (countEl) countEl.textContent = count > 0 ? `(${count})` : ''
        activeBar.hidden = count === 0
      }
    }
  }

})()
