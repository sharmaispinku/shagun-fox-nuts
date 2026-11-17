/* ==========================================================================
   Shagun Enterprises — interactions
   ========================================================================== */
(function () {
    'use strict';

    var doc = document;

    /* ------------------------------------------------------------ header */
    var header = doc.getElementById('siteHeader');
    var toTop = doc.getElementById('toTop');

    function onScroll() {
        var y = window.scrollY || window.pageYOffset;
        if (header) header.classList.toggle('is-scrolled', y > 8);
        if (toTop) toTop.classList.toggle('is-visible', y > 600);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (toTop) {
        toTop.addEventListener('click', function (ev) {
            ev.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* -------------------------------------------------------- mobile nav */
    var toggle = doc.getElementById('menuToggle');
    var nav = doc.getElementById('primaryNav');

    function closeNav() {
        if (!nav || !toggle) return;
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        nav.addEventListener('click', function (ev) {
            if (ev.target.closest('a')) closeNav();
        });
        doc.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') closeNav();
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) closeNav();
        });
    }

    /* --------------------------------------------------- scroll reveals */
    var revealItems = doc.querySelectorAll('.reveal');
    if (revealItems.length && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        revealItems.forEach(function (el) { io.observe(el); });
    } else {
        revealItems.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* -------------------------------------------------------------- faq */
    doc.querySelectorAll('.faq-q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panel = doc.getElementById(btn.getAttribute('aria-controls'));
            var open = btn.getAttribute('aria-expanded') === 'true';

            // Close siblings inside the same list for an accordion feel.
            var list = btn.closest('.faq-list');
            if (list) {
                list.querySelectorAll('.faq-q').forEach(function (other) {
                    if (other !== btn && other.getAttribute('aria-expanded') === 'true') {
                        other.setAttribute('aria-expanded', 'false');
                        var p = doc.getElementById(other.getAttribute('aria-controls'));
                        if (p) p.style.maxHeight = null;
                    }
                });
            }

            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            if (panel) panel.style.maxHeight = open ? null : panel.scrollHeight + 'px';
        });
    });

    /* ------------------------------------------------------------ toast */
    var toastTimer;
    function toast(message, isError) {
        var el = doc.querySelector('.toast');
        if (!el) return;
        el.textContent = message;
        el.classList.toggle('is-error', !!isError);
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.remove('is-visible'); }, 4200);
    }
    window.shagunToast = toast;

    /* --------------------------------------------------- estimate widget */
    var qtyInput = doc.getElementById('quantity_kg');
    var gradeSelect = doc.getElementById('grade');
    var estimateValue = doc.getElementById('estimateValue');
    var estimateSub = doc.getElementById('estimateSub');

    function money(n) {
        return '₹' + Math.round(n).toLocaleString('en-IN');
    }

    function updateEstimate() {
        if (!estimateValue || !gradeSelect || !qtyInput) return;
        var opt = gradeSelect.options[gradeSelect.selectedIndex];
        var rate = parseFloat(opt ? opt.getAttribute('data-rate') : '0') || 0;
        var qty = parseFloat(qtyInput.value) || 0;

        if (!rate || !qty) {
            estimateValue.textContent = '—';
            if (estimateSub) estimateSub.textContent = 'Enter quantity to see an estimate';
            return;
        }
        var total = rate * qty;
        estimateValue.textContent = money(total);
        if (estimateSub) {
            estimateSub.textContent = qty + ' kg × ' + money(rate) + '/kg · indicative, before GST & freight';
        }
    }

    if (gradeSelect && qtyInput) {
        gradeSelect.addEventListener('change', updateEstimate);
        qtyInput.addEventListener('input', updateEstimate);
        updateEstimate();
    }

    /* --------------------------------------------------- order form ajax */
    var form = doc.getElementById('orderForm');
    if (form) {
        var submitBtn = form.querySelector('button[type="submit"]');
        var formMsg = doc.getElementById('formMsg');

        function showErrors(errors) {
            form.querySelectorAll('.field').forEach(function (f) { f.classList.remove('has-error'); });
            form.querySelectorAll('.field-error').forEach(function (n) { n.remove(); });

            Object.keys(errors || {}).forEach(function (name) {
                var input = form.querySelector('[name="' + name.replace(/"/g, '\\"') + '"]');
                if (!input) return;
                var field = input.closest('.field') || input.parentElement;
                field.classList.add('has-error');
                var small = doc.createElement('div');
                small.className = 'field-error';
                small.textContent = errors[name];
                field.appendChild(small);
            });

            if (errors && Object.keys(errors).length) {
                var first = form.querySelector('.has-error input, .has-error select, .has-error textarea');
                if (first) first.focus();
            }
        }

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            if (formMsg) formMsg.innerHTML = '';
            showErrors({});
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.label = submitBtn.innerHTML;
                submitBtn.innerHTML = 'Sending…';
            }

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        showErrors(data.errors || {});
                        if (data.message && formMsg) {
                            formMsg.innerHTML = '<div class="alert alert--error">' + data.message + '</div>';
                        }
                        return;
                    }

                    var ref = data.reference || '';
                    var waText = 'Hello Shagun Enterprises, I just submitted request ' + ref
                        + ' for ' + (data.quantity || '') + ' kg ' + (data.grade || 'makhana') + '.';
                    var waUrl = 'https://wa.me/' + (data.whatsapp || '') + '?text=' + encodeURIComponent(waText);

                    if (formMsg) {
                        formMsg.innerHTML =
                            '<div class="alert alert--success" role="status">'
                            + '<strong>Request received.</strong> Your reference is <strong>' + ref + '</strong>. '
                            + 'Our grading desk will revert within one working day.'
                            + '<div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap">'
                            + '<a class="btn btn-primary btn-sm" href="' + waUrl + '" target="_blank" rel="noopener">Continue on WhatsApp</a>'
                            + '<a class="btn btn-ghost btn-sm" href="/track?ref=' + encodeURIComponent(ref) + '">Track this request</a>'
                            + '</div></div>';
                        formMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    form.reset();
                    updateEstimate();
                    toast('Request ' + ref + ' submitted');
                })
                .catch(function () {
                    if (formMsg) {
                        formMsg.innerHTML = '<div class="alert alert--error">Could not reach the server. Please try again, or WhatsApp us on ' + (form.dataset.phone || '') + '.</div>';
                    }
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.label || 'Send request';
                    }
                });
        });
    }

    /* ------------------------------------------------------ phone masks */
    doc.querySelectorAll('input[data-phone-mask]').forEach(function (input) {
        input.addEventListener('input', function () {
            var digits = input.value.replace(/\D/g, '').slice(0, 10);
            input.value = digits.replace(/(\d{5})(\d{0,5})/, function (_, a, b) {
                return b ? a + ' ' + b : a;
            });
        });
    });

    /* ------------------------------------------------------ type chips */
    var typeField = doc.getElementById('typeField');
    if (typeField) {
        doc.querySelectorAll('input[name="type_choice"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (radio.checked) typeField.value = radio.value;
            });
        });
    }

    /* --------------------------------------------------------- qty nudges */
    doc.querySelectorAll('[data-qty-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = doc.getElementById(btn.getAttribute('data-qty-step'));
            if (!target) return;
            var step = parseFloat(btn.getAttribute('data-step') || '25');
            var value = (parseFloat(target.value) || 0) + (btn.getAttribute('data-dir') === 'up' ? step : -step);
            target.value = Math.max(0, Math.round(value));
            target.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
})();