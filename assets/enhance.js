/* ===== CMS Enhancements ===== */
(function () {
    'use strict';

    /* ---- Toast ---- */
    function toastBox() {
        var box = document.getElementById('cms-toasts');
        if (!box) { box = document.createElement('div'); box.id = 'cms-toasts'; document.body.appendChild(box); }
        return box;
    }
    window.cmsToast = function (message, type) {
        type = type || 'info';
        var t = document.createElement('div');
        t.className = 'cms-toast ' + type;
        var ico = type === 'success' ? '\u2713' : (type === 'error' ? '!' : 'i');
        t.innerHTML = '<span class="t-ico">' + ico + '</span><span></span>';
        t.lastChild.textContent = message;
        toastBox().appendChild(t);
        setTimeout(function () {
            t.classList.add('out');
            setTimeout(function () { t.remove(); }, 320);
        }, 3500);
    };

    /* ---- Confirm popup ---- */
    window.cmsConfirm = function (title, text, onYes) {
        var bg = document.createElement('div');
        bg.className = 'cms-modal-bg';
        bg.innerHTML = '<div class="cms-modal"><div class="m-ico">!</div><h4></h4><p></p>' +
            '<div class="m-btns"><button type="button" class="m-cancel">Cancel</button>' +
            '<button type="button" class="m-ok">Yes, continue</button></div></div>';
        bg.querySelector('h4').textContent = title;
        bg.querySelector('p').textContent = text;
        function close() { bg.remove(); }
        bg.querySelector('.m-cancel').onclick = close;
        bg.querySelector('.m-ok').onclick = function () { close(); onYes(); };
        bg.addEventListener('click', function (e) { if (e.target === bg) close(); });
        document.body.appendChild(bg);
        bg.querySelector('.m-ok').focus();
    };

    document.addEventListener('DOMContentLoaded', function () {

        /* Turn the existing success / error text messages into toasts */
        document.querySelectorAll('.success-msg, .error-msg').forEach(function (el) {
            if (el.hidden || !el.textContent.trim()) return;
            window.cmsToast(el.textContent.trim(), el.classList.contains('success-msg') ? 'success' : 'error');
            el.style.display = 'none';
        });

        /* Count-up for dashboard / report numbers (home page already has its own) */
        document.querySelectorAll('.kpi-container .kpi-card h3').forEach(function (el) {
            var target = parseInt(el.textContent, 10);
            if (!isNaN(target)) el.dataset.final = String(target);
            if (isNaN(target) || target === 0) return;
            var start = null, dur = 900;
            el.textContent = '0';
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        /* Delete / confirm links: use the nice popup instead of the browser box */
        document.querySelectorAll('a[onclick*="confirm("]').forEach(function (a) {
            var m = a.getAttribute('onclick').match(/confirm\((['"])(.*?)\1\)/);
            var question = m ? m[2] : 'Are you sure?';
            a.removeAttribute('onclick');
            a.addEventListener('click', function (e) {
                e.preventDefault();
                window.cmsConfirm('Please confirm', question, function () { window.location.href = a.href; });
            });
        });

        /* Page fade-out when clicking an internal link */
        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
            if (a.target && a.target !== '_self') return;
            if (a.hasAttribute('download')) return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || /^(mailto:|tel:|javascript:)/i.test(href)) return;
            if (a.origin !== window.location.origin) return;
            e.preventDefault();
            document.body.classList.add('cms-leaving');
            setTimeout(function () { window.location.href = a.href; }, 180);
        });
    });

    /* Back button: make sure the page is visible again */
    window.addEventListener('pageshow', function () { document.body.classList.remove('cms-leaving'); });
})();

/* =====================================================================
   ROUND 2
   ===================================================================== */
(function () {
    'use strict';

    function store(key, val) {
        try { if (val === undefined) return localStorage.getItem(key); localStorage.setItem(key, val); } catch (e) { return null; }
    }

    /* ---------- Dark mode: apply as early as possible ---------- */
    var darkOn = store('cms-dark') === '1';
    if (darkOn) document.documentElement.classList.add('cms-dark');

    var MOON = '<svg viewBox="0 0 24 24" width="20" height="20" style="vertical-align:middle" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>';
    var SUN = '<svg viewBox="0 0 24 24" width="20" height="20" style="vertical-align:middle" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>';

    function lum(rgb) {
        var m = rgb.match(/[\d.]+/g); if (!m) return 1;
        return (0.2126 * m[0] + 0.7152 * m[1] + 0.0722 * m[2]) / 255;
    }
    function isDarkBlock(el) {
        var cs = getComputedStyle(el);
        var bg = cs.backgroundColor, m = bg.match(/[\d.]+/g);
        if (m && (m.length < 4 || parseFloat(m[3]) > 0.6) && lum(bg) < 0.32) return true;
        var bi = cs.backgroundImage;
        if (bi && bi.indexOf('gradient') > -1) {
            var c = bi.match(/rgba?\([^)]+\)/);
            if (c && lum(c[0]) < 0.4) return true;
        }
        return false;
    }
    function markDarkBlocks(root) {
        var kids = root.children;
        for (var i = 0; i < kids.length; i++) {
            var el = kids[i];
            if (/^(SCRIPT|STYLE|LINK|IMG|CANVAS|SVG|OPTION)$/i.test(el.tagName)) continue;
            if (el.id === 'cms-dark-btn') continue;
            var r = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
            if (isDarkBlock(el) && !el.classList.contains('cms-keep')) {
                var tag = el.tagName;
                /* only whole bars / cards / buttons, not tiny text spans */
                if (!r || r.width > 60 || r.height > 24 || /^(BUTTON|A|TH)$/.test(tag)) {
                    el.classList.add('cms-keep');
                    continue; /* its children are already restored by the second invert */
                }
            }
            markDarkBlocks(el);
        }
    }
    function applyDark(on) {
        document.documentElement.classList.toggle('cms-dark', on);
        var btn = document.getElementById('cms-dark-btn');
        if (btn) btn.innerHTML = on ? SUN : MOON;
        document.querySelectorAll('.cms-keep').forEach(function (e) { e.classList.remove('cms-keep'); });
        if (on) markDarkBlocks(document.body);
        store('cms-dark', on ? '1' : '0');
    }

    document.addEventListener('DOMContentLoaded', function () {

        /* ---------- Loading bar ---------- */
        var bar = document.createElement('div');
        bar.id = 'cms-progress';
        document.body.appendChild(bar);
        function runBar() { bar.className = ''; void bar.offsetWidth; bar.className = 'run'; }
        window.addEventListener('pageshow', function () { bar.className = 'done'; });
        runBar(); setTimeout(function () { bar.className = 'done'; }, 120);
        /* start the bar after the fade-out class is added by the first script */
        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (a && a.origin === window.location.origin && !a.target && a.getAttribute('href').charAt(0) !== '#') setTimeout(runBar, 0);
        });

        /* ---------- Sticky table header ---------- */
        document.querySelectorAll('table th').forEach(function (th) {
            var c = getComputedStyle(th).backgroundColor, m = c.match(/[\d.]+/g);
            if (m && (m.length < 4 || parseFloat(m[3]) > 0.9)) th.classList.add('cms-stick');
        });

        /* ---------- Status icons ---------- */
        var map = [
            [/^(closed|resolved|completed|verified|active|pass|accepted)$/i, '\u2713'],
            [/^(overdue)$/i, '!'],
            [/^(assigned|in progress|under investigation|action required|for resolution|evidence gathering|findings prepared|reassigned)$/i, '\u21BB'],
            [/^(submitted|for assignment|pending)$/i, '\u25CF'],
            [/^(inactive)$/i, '\u25CB']
        ];
        document.querySelectorAll('.status-pill, .status-badge, .done-badge').forEach(function (el) {
            if (el.querySelector('.cms-sico')) return;
            var txt = el.textContent.trim();
            for (var i = 0; i < map.length; i++) {
                if (map[i][0].test(txt)) {
                    var s = document.createElement('span');
                    s.className = 'cms-sico'; s.textContent = map[i][1];
                    el.insertBefore(s, el.firstChild);
                    if (/^overdue$/i.test(txt)) el.classList.add('cms-overdue');
                    break;
                }
            }
        });

        /* ---------- Empty state for tables with no rows ---------- */
        document.querySelectorAll('table').forEach(function (t) {
            var rows = Array.prototype.filter.call(t.querySelectorAll('tr'), function (r) { return !r.querySelector('th'); });
            if (rows.length > 0) return;
            var card = t.closest('.table-card, .card, .page-wrap') || t.parentElement;
            if (card && card.querySelector('.empty, .empty-note, .cms-empty')) return;
            var d = document.createElement('div');
            d.className = 'cms-empty';
            d.innerHTML = '<svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/></svg>' +
                '<h4>No records found</h4><p>There is nothing to show here yet.</p>';
            (t.closest('.table-scroll') || t).insertAdjacentElement('afterend', d);
        });

        /* ---------- Dark mode button ---------- */
        var btn = document.createElement('button');
        btn.id = 'cms-dark-btn'; btn.type = 'button'; btn.title = 'Dark / Light mode';
        btn.innerHTML = darkOn ? SUN : MOON;
        btn.addEventListener('click', function () {
            applyDark(!document.documentElement.classList.contains('cms-dark'));
        });
        document.body.appendChild(btn);
        if (darkOn) applyDark(true);
    });
})();

/* ===== ROUND 3: phone helpers ===== */
(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var small = window.matchMedia('(max-width: 700px)').matches;
        if (!small) return;

        /* "Swipe" hint above tables that are wider than the screen */
        setTimeout(function () {
            document.querySelectorAll('table').forEach(function (t) {
                var holder = t.closest('.table-scroll');
                var scroller = t.scrollWidth > t.clientWidth + 4 ? t : (holder && holder.scrollWidth > holder.clientWidth + 4 ? holder : null);
                if (!scroller) return;
                var anchor = holder || t;
                if (anchor.previousElementSibling && anchor.previousElementSibling.classList.contains('cms-swipe')) return;
                var hint = document.createElement('div');
                hint.className = 'cms-swipe';
                hint.textContent = '\u2190 Swipe to see more \u2192';
                anchor.parentNode.insertBefore(hint, anchor);
                scroller.addEventListener('scroll', function () { hint.remove(); }, { once: true });
            });
        }, 600);

        /* Complaint timeline: scroll so the current step is in view */
        setTimeout(function () {
            var bar = document.getElementById('progress');
            if (!bar) return;
            var cur = bar.querySelector('.p.now') || bar.querySelector('.p.done:last-of-type');
            if (cur) bar.scrollLeft = Math.max(0, cur.offsetLeft - bar.clientWidth / 2 + cur.clientWidth / 2);
        }, 700);
    });
})();

/* =====================================================================
   ROUND 4: animations and effects
   ===================================================================== */
(function () {
    'use strict';
    var mq = function (q) { return window.matchMedia && window.matchMedia(q).matches; };
    var reduce = mq('(prefers-reduced-motion: reduce)');
    var fine = mq('(hover: hover) and (pointer: fine)');
    var small = mq('(max-width: 700px)');

    function ready(fn) {
        if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn);
    }

    /* ---------- Soft animated background ---------- */
    function background() {
        if (reduce) return;
        var b = document.body;
        if (getComputedStyle(b).backgroundImage !== 'none' || /reg-page|track-page/.test(b.className)) return;
        if (document.getElementById('cms-bg')) return;
        var bg = document.createElement('div');
        bg.id = 'cms-bg'; bg.innerHTML = '<i></i><i></i><i></i>';
        b.appendChild(bg);
    }

    /* ---------- Spotlight on cards (mouse only) ---------- */
    function spotlight() {
        if (!fine || reduce) return;
        document.querySelectorAll('.kpi-card, .quick-btn, .chart-card, .panel-card, .report-card').forEach(function (el) {
            el.classList.add('cms-spot');
            el.addEventListener('pointermove', function (e) {
                var r = el.getBoundingClientRect();
                el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                el.style.setProperty('--my', (e.clientY - r.top) + 'px');
            }, { passive: true });
        });
    }

    /* ---------- Reveal on scroll (only things that have no animation yet) ---------- */
    function reveal() {
        if (reduce || !('IntersectionObserver' in window)) return;
        var sel = '.panel-card, .report-card, .detail-item, .recent-list li, .form-card, tbody tr';
        var counters = new Map();
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll(sel).forEach(function (el) {
            if (el.closest('.side-panel, .cms-modal')) return;
            if (el.offsetParent === null) return;
            if (getComputedStyle(el).animationName !== 'none') return;
            var n = counters.get(el.parentNode) || 0;
            counters.set(el.parentNode, n + 1);
            el.style.setProperty('--d', Math.min(n, 8) * 60 + 'ms');
            el.classList.add('cms-rv');
            io.observe(el);
        });
        /* safety: never leave anything hidden */
        setTimeout(function () {
            document.querySelectorAll('.cms-rv:not(.in)').forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('in');
            });
        }, 1500);
    }

    /* ---------- Ripple on buttons ---------- */
    function ripple() {
        if (reduce) return;
        var sel = '.btn-primary, .btn-search, .btn-clear, .submit-btn, .login-btn, .go-btn, .quick-btn, .chip, a.act, .panel-group-title, .home-link, button[type="submit"], .back-pill';
        document.addEventListener('pointerdown', function (e) {
            var host = e.target.closest ? e.target.closest(sel) : null;
            if (!host || host.id === 'cms-dark-btn') return;
            if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
            host.classList.add('cms-rip-host');
            var r = host.getBoundingClientRect();
            var size = Math.max(r.width, r.height) * 2.2;
            var m = getComputedStyle(host).backgroundColor.match(/[\d.]+/g) || [255, 255, 255, 0];
            var light = (m.length < 4 || parseFloat(m[3]) > 0.3) ? (0.2126 * m[0] + 0.7152 * m[1] + 0.0722 * m[2]) / 255 > 0.6 : true;
            var s = document.createElement('span');
            s.className = 'cms-ripple';
            s.style.width = s.style.height = size + 'px';
            s.style.left = (e.clientX - r.left - size / 2) + 'px';
            s.style.top = (e.clientY - r.top - size / 2) + 'px';
            s.style.background = light ? 'rgba(47,111,237,.22)' : 'rgba(255,255,255,.4)';
            host.appendChild(s);
            setTimeout(function () { s.remove(); }, 700);
        }, { passive: true });
    }

    /* ---------- Forms: shake invalid fields, spinner on submit ---------- */
    function forms() {
        document.addEventListener('invalid', function (e) {
            var el = e.target;
            if (!el.classList) return;
            el.classList.remove('cms-shake'); void el.offsetWidth; el.classList.add('cms-shake');
            setTimeout(function () { el.classList.remove('cms-shake'); }, 500);
        }, true);
        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (e.defaultPrevented || !f || f.tagName !== 'FORM') return;
            if (f.checkValidity && !f.checkValidity()) return;
            var b = f.querySelector('button[type="submit"], input[type="submit"]');
            if (!b || b.classList.contains('cms-loading') || b.querySelector('.spin, .spinner')) return;
            b.classList.add('cms-loading');
            setTimeout(function () { b.classList.remove('cms-loading'); }, 8000);
        });
        window.addEventListener('pageshow', function () {
            document.querySelectorAll('.cms-loading').forEach(function (b) { b.classList.remove('cms-loading'); });
        });
    }

    /* ---------- Typing effect on the welcome message ---------- */
    function typing() {
        if (reduce) return;
        var h = document.querySelector('.hero-text h2');
        if (!h) return;
        var full = h.textContent;
        if (!full.trim()) return;
        h.setAttribute('aria-label', full);
        h.textContent = '';
        var txt = document.createTextNode('');
        var caret = document.createElement('span');
        caret.className = 'cms-caret';
        h.appendChild(txt); h.appendChild(caret);
        function start() {
            var i = 0;
            (function step() {
                i++; txt.nodeValue = full.slice(0, i);
                if (i < full.length) setTimeout(step, 40);
                else setTimeout(function () { caret.remove(); }, 1500);
            })();
        }
        var waited = 0;
        (function wait() {
            var ov = document.getElementById('splashOverlay');
            var done = true;
            if (ov && document.body.contains(ov)) {
                var cs = getComputedStyle(ov);
                done = ov.classList.contains('hide-splash') || cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0';
            }
            if (done || waited > 9000) start(); else { waited += 250; setTimeout(wait, 250); }
        })();
    }

    /* ---------- Confetti + animated check after saving ---------- */
    function confetti() {
        var c = document.createElement('canvas');
        c.id = 'cms-confetti';
        var dpr = window.devicePixelRatio || 1, w = window.innerWidth, h = window.innerHeight;
        c.width = w * dpr; c.height = h * dpr;
        c.style.width = w + 'px'; c.style.height = h + 'px';
        document.body.appendChild(c);
        var g = c.getContext('2d'); g.scale(dpr, dpr);
        var colors = ['#2f6fed', '#60a5fa', '#0a2540', '#16a34a', '#f59e0b', '#93c5fd', '#ef4444'];
        var n = small ? 70 : 130, ps = [];
        for (var i = 0; i < n; i++) {
            var a = -Math.PI / 2 + (Math.random() - 0.5) * 2.2, sp = 7 + Math.random() * 10;
            ps.push({
                x: w / 2, y: h * 0.42, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp, s: 5 + Math.random() * 6,
                r: Math.random() * 6, vr: (Math.random() - 0.5) * 0.4, c: colors[i % colors.length]
            });
        }
        var t0 = null;
        (function frame(ts) {
            if (!t0) t0 = ts;
            var t = ts - t0;
            g.clearRect(0, 0, w, h);
            ps.forEach(function (p) {
                p.vx *= 0.985; p.vy = p.vy * 0.985 + 0.32; p.x += p.vx; p.y += p.vy; p.r += p.vr;
                g.save(); g.translate(p.x, p.y); g.rotate(p.r);
                g.globalAlpha = Math.max(0, 1 - t / 2600);
                g.fillStyle = p.c; g.fillRect(-p.s / 2, -p.s / 3, p.s, p.s * 0.6);
                g.restore();
            });
            if (t < 2600) requestAnimationFrame(frame); else c.remove();
        })(performance.now());
    }
    window.cmsCelebrate = function (message) {
        if (reduce) { if (window.cmsToast) window.cmsToast(message || 'Saved successfully!', 'success'); return; }
        var wrap = document.createElement('div');
        wrap.className = 'cms-check-wrap';
        wrap.innerHTML = '<div class="cms-check-box"><svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="46"/><path d="M30 52l14 14 27-30"/></svg><p></p></div>';
        wrap.querySelector('p').textContent = message || 'Saved successfully!';
        document.body.appendChild(wrap);
        confetti();
        setTimeout(function () { wrap.classList.add('out'); setTimeout(function () { wrap.remove(); }, 420); }, 1900);
    };
    function celebrateOnSuccess() {
        var q = new URLSearchParams(window.location.search);
        if (q.get('status') !== 'success') return;
        var key = 'cms-celebrated', last = null;
        try { last = sessionStorage.getItem(key); sessionStorage.setItem(key, window.location.href); } catch (e) { }
        if (last === window.location.href) return;
        setTimeout(function () { window.cmsCelebrate('Saved successfully!'); }, 350);
    }

    /* ---------- Charts: replay a smooth animation when they scroll into view ---------- */
    function charts() {
        if (!window.Chart || !window.Chart.instances) return;
        try { window.Chart.defaults.font.family = "'Poppins', sans-serif"; } catch (e) { }
        Object.keys(window.Chart.instances).forEach(function (k) {
            var ch = window.Chart.instances[k], played = false;
            function play() {
                if (played) return; played = true;
                try { ch.options.animation = { duration: 1300, easing: 'easeOutQuart' }; ch.reset(); ch.update(); } catch (e) { }
            }
            if (reduce) return;
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (es) { if (es[0].isIntersecting) { play(); io.disconnect(); } }, { threshold: 0.35 });
                io.observe(ch.canvas);
            } else play();
        });
    }

    /* ---------- Dashboard: resolution rate ring ---------- */
    function ring() {
        var grid = document.querySelector('.chart-grid');
        var cards = document.querySelectorAll('.kpi-container .kpi-card');
        if (!grid || !cards.length || document.querySelector('.cms-ring-card')) return;
        var vals = {};
        cards.forEach(function (c) {
            var p = c.querySelector('p'), h = c.querySelector('h3');
            if (!p || !h) return;
            var n = parseInt(h.dataset.final !== undefined ? h.dataset.final : h.textContent, 10);
            vals[p.textContent.trim().toLowerCase()] = isNaN(n) ? 0 : n;
        });
        var total = vals['total complaints'];
        if (!total) return;
        var done = (vals['resolved'] || 0) + (vals['closed'] || 0);
        var pct = Math.round(done * 100 / total);
        var card = document.createElement('div');
        card.className = 'chart-card cms-ring-card';
        card.innerHTML = '<h3>Resolution Rate</h3><div class="cms-ring"><svg viewBox="0 0 120 120"><defs><linearGradient id="cmsRg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2f6fed"/><stop offset="1" stop-color="#16a34a"/></linearGradient></defs>' +
            '<circle class="trk" cx="60" cy="60" r="50"/><circle class="bar" cx="60" cy="60" r="50" transform="rotate(-90 60 60)"/></svg>' +
            '<div class="pct"><b>0%</b><span>resolved or closed</span></div></div><p class="cms-ring-note"></p>';
        card.querySelector('.cms-ring-note').textContent = done + ' of ' + total + ' complaints';
        grid.appendChild(card);
        var box = card.querySelector('.cms-ring'), label = card.querySelector('.pct b');
        box.style.setProperty('--off', (314.16 * (1 - pct / 100)).toFixed(2));
        function go() {
            box.classList.add('go');
            var t0 = null;
            (function step(ts) {
                if (!t0) t0 = ts;
                var p = Math.min((ts - t0) / 1400, 1);
                label.textContent = Math.round(pct * (1 - Math.pow(1 - p, 3))) + '%';
                if (p < 1) requestAnimationFrame(step);
            })(performance.now());
        }
        if (reduce || !('IntersectionObserver' in window)) { label.textContent = pct + '%'; box.classList.add('go'); return; }
        var io = new IntersectionObserver(function (es) { if (es[0].isIntersecting) { go(); io.disconnect(); } }, { threshold: 0.4 });
        io.observe(card);
    }

    ready(function () {
        background(); spotlight(); ripple(); forms(); typing(); celebrateOnSuccess(); ring();
        /* wait one tick so other scripts have set up their own animations */
        setTimeout(reveal, 50);
        if (document.readyState === 'complete') charts(); else window.addEventListener('load', charts);
    });
})();
