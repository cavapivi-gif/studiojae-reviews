/**
 * SJ Reviews — Social Proof Toast
 *
 * Fetches the most recent review and displays a non-intrusive toast.
 * Shows once per session (sessionStorage), dismissible, auto-hides after 8s.
 */
(function () {
    'use strict';

    const cfg = window.sjToastConfig || {};
    if (!cfg.restUrl) return;

    const STORAGE_KEY = 'sj_toast_shown';
    const DISPLAY_DURATION = 8000; // auto-hide after 8s

    // Only show once per session
    if (sessionStorage.getItem(STORAGE_KEY)) return;

    const toast     = document.getElementById('sj-social-proof-toast');
    const avatarEl  = document.getElementById('sj-toast-avatar');
    const textEl    = document.getElementById('sj-toast-text');
    const metaEl    = document.getElementById('sj-toast-meta');
    const starsEl   = document.getElementById('sj-toast-stars');

    if (!toast || !avatarEl || !textEl) return;

    // Close button
    const closeBtn = toast.querySelector('.sj-toast__close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            hide();
        });
    }

    function show() {
        toast.removeAttribute('hidden');
        // Force reflow for animation
        toast.offsetHeight;
        toast.classList.add('sj-toast--visible');
        sessionStorage.setItem(STORAGE_KEY, '1');

        // Auto-hide
        setTimeout(hide, DISPLAY_DURATION);
    }

    function hide() {
        toast.classList.remove('sj-toast--visible');
        setTimeout(function () {
            toast.setAttribute('hidden', '');
        }, 400);
    }

    function starsHtml(rating) {
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += i <= rating ? '★' : '☆';
        }
        return html;
    }

    // Relative time in French
    function relativeTime(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)   return 'à l\'instant';
        if (diff < 3600) return 'il y a ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400) return 'il y a ' + Math.floor(diff / 3600) + 'h';
        if (diff < 172800) return 'hier';
        return 'il y a ' + Math.floor(diff / 86400) + ' jours';
    }

    // Avatar colors
    var colors = [
        { bg: '#e0e7ff', color: '#4f46e5' },
        { bg: '#fce7f3', color: '#be185d' },
        { bg: '#d1fae5', color: '#059669' },
        { bg: '#fef3c7', color: '#d97706' },
        { bg: '#ede9fe', color: '#7c3aed' },
    ];

    // Fetch most recent reviews (last 48h, 4★+)
    var url = cfg.restUrl + 'front/reviews?per_page=5&sort=recent';

    fetch(url, {
        headers: { 'X-WP-Nonce': cfg.nonce || '' },
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var reviews = data.items || data;
            if (!Array.isArray(reviews) || reviews.length === 0) return;

            // Find first 4★+ review that is < 48h old
            var review = null;
            for (var ri = 0; ri < reviews.length; ri++) {
                var r = reviews[ri];
                var age = Date.now() - new Date(r.date).getTime();
                if (r.rating >= 4 && age < 48 * 3600 * 1000) {
                    review = r;
                    break;
                }
            }
            if (!review) return;

            // Populate
            var name = review.author || 'Client';
            var initial = name.charAt(0).toUpperCase();
            var colorIdx = name.charCodeAt(0) % colors.length;
            var ac = colors[colorIdx];

            if (review.avatar) {
                avatarEl.innerHTML = '<img src="' + review.avatar + '" alt="' + name + '" width="36" height="36" loading="lazy">';
            } else {
                avatarEl.style.background = ac.bg;
                avatarEl.style.color = ac.color;
                avatarEl.textContent = initial;
            }

            textEl.textContent = name + ' a laissé un avis';
            metaEl.textContent = relativeTime(review.date) + (review.source ? ' · ' + review.source : '');
            starsEl.innerHTML = starsHtml(review.rating);

            // Make toast clickable if reviews URL configured
            if (cfg.reviewsUrl) {
                toast.style.cursor = 'pointer';
                toast.addEventListener('click', function (e) {
                    if (e.target.closest('.sj-toast__close')) return;
                    window.location.href = cfg.reviewsUrl;
                });
            }

            // Show after configured delay
            setTimeout(show, cfg.delay || 5000);
        })
        .catch(function () {
            // Silent fail — don't break the page
        });
})();
