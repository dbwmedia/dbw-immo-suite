(function () {
    'use strict';

    var modal = document.getElementById('dbw-contact-modal');
    if (!modal) return;

    var form       = modal.querySelector('#dbw-contact-form');
    var steps      = modal.querySelectorAll('.dbw-modal__step');
    var progressBar = modal.querySelector('.dbw-modal__progress-bar');
    var btnClose   = modal.querySelector('.dbw-modal__close');
    var submitBtn  = form.querySelector('button[type="submit"]');
    var stickyBar  = document.querySelector('.dbw-sticky-cta-bar');
    var originalText = submitBtn.textContent.trim();
    var currentStep = 1;

    // --- Step Navigation ---
    function goToStep(step) {
        currentStep = step;
        steps.forEach(function (s) {
            var sStep = s.dataset.step;
            s.classList.remove('is-active', 'is-exit-left', 'is-enter-right');

            if (sStep === String(step) || sStep === step) {
                s.classList.add('is-active');
                // Focus first input on step 2
                if (step === 2) {
                    var firstInput = s.querySelector('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"])');
                    if (firstInput) setTimeout(function() { firstInput.focus(); }, 350);
                }
            }
        });

        // Update progress bar
        if (progressBar) {
            if (step === 1) progressBar.dataset.step = '1';
            else if (step === 2) progressBar.dataset.step = '2';
            else progressBar.dataset.step = '3';
        }
    }

    // --- Intent Selection (auto-advance to step 2) ---
    var intents = modal.querySelectorAll('.dbw-intent');
    intents.forEach(function (intent) {
        intent.addEventListener('click', function () {
            var radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            // Visual feedback
            intents.forEach(function (i) { i.classList.remove('is-selected'); });
            this.classList.add('is-selected');

            // Show context fields for this intent
            var val = radio.value;
            modal.querySelectorAll('[data-context]').forEach(function (el) {
                el.hidden = el.dataset.context !== val;
            });

            // Auto-advance after brief delay
            setTimeout(function () { goToStep(2); }, 300);
        });
    });

    // --- Back Button ---
    modal.querySelectorAll('[data-goto-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            goToStep(parseInt(this.dataset.gotoStep));
        });
    });

    // --- Open ---
    document.querySelectorAll('[data-dbw-open-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Reset to step 1
            form.reset();
            intents.forEach(function (i) { i.classList.remove('is-selected'); });
            modal.querySelectorAll('[data-context]').forEach(function (el) { el.hidden = true; });
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            var err = form.querySelector('.dbw-modal__error');
            if (err) err.remove();

            goToStep(1);
            modal.showModal();
        });
    });

    // --- Close ---
    btnClose.addEventListener('click', function () { modal.close(); });
    modal.querySelectorAll('[data-close-modal]').forEach(function (b) {
        b.addEventListener('click', function () { modal.close(); });
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.close();
    });

    // --- Submit (AJAX) ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (form.website.value) return; // honeypot

        submitBtn.disabled = true;
        submitBtn.textContent = (window.dbwContactModal.i18n && window.dbwContactModal.i18n.sending) || 'Senden\u2026';

        var data = new FormData(form);
        data.append('action', 'dbw_immo_contact');

        fetch(window.dbwContactModal.ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            if (!j.success) throw new Error(j.data || 'Fehler');

            // Personalize success
            var userName = (form.querySelector('[name="name"]').value || '').trim();
            var firstName = userName.split(' ')[0];
            var nameEl = modal.querySelector('[data-success-name]');
            if (nameEl) {
                nameEl.textContent = firstName ? ', ' + firstName + '!' : '!';
            }

            goToStep('success');
        })
        .catch(function (err) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            var msg = err.message || (window.dbwContactModal.i18n && window.dbwContactModal.i18n.network_error) || 'Netzwerkfehler';
            var existing = form.querySelector('.dbw-modal__error');
            if (existing) existing.remove();
            var errEl = document.createElement('div');
            errEl.className = 'dbw-modal__error';
            errEl.setAttribute('role', 'alert');
            errEl.textContent = msg;
            submitBtn.parentNode.insertBefore(errEl, submitBtn);
        });
    });

    // --- Expose Request Modal ---
    var exposeModal = document.getElementById('dbw-expose-modal');
    if (exposeModal) {
        var exposeForm = exposeModal.querySelector('#dbw-expose-form');
        var exposeSubmitBtn = exposeForm ? exposeForm.querySelector('button[type="submit"]') : null;
        var exposeOriginalText = exposeSubmitBtn ? exposeSubmitBtn.textContent.trim() : '';

        // Open
        document.querySelectorAll('[data-dbw-open-expose]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                exposeForm.reset();
                exposeSubmitBtn.disabled = false;
                exposeSubmitBtn.textContent = exposeOriginalText;
                var err = exposeForm.querySelector('.dbw-modal__error');
                if (err) err.remove();

                // Show form, hide success
                exposeModal.querySelectorAll('[data-expose-step="form"]').forEach(function (el) { el.hidden = false; });
                exposeModal.querySelectorAll('[data-expose-step="success"]').forEach(function (el) { el.hidden = true; });

                exposeModal.showModal();
            });
        });

        // Close
        exposeModal.querySelectorAll('[data-close-expose]').forEach(function (b) {
            b.addEventListener('click', function () { exposeModal.close(); });
        });
        exposeModal.addEventListener('click', function (e) {
            if (e.target === exposeModal) exposeModal.close();
        });

        // Submit
        if (exposeForm) {
            exposeForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (exposeForm.website.value) return;

                exposeSubmitBtn.disabled = true;
                exposeSubmitBtn.textContent = (window.dbwContactModal.i18n && window.dbwContactModal.i18n.sending) || 'Senden\u2026';

                var data = new FormData(exposeForm);
                data.append('action', 'dbw_immo_expose_request');

                fetch(window.dbwContactModal.ajaxurl, {
                    method: 'POST',
                    body: data
                })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j.success) throw new Error(j.data || 'Fehler');

                    // Personalize success
                    var userName = (exposeForm.querySelector('[name="name"]').value || '').trim();
                    var firstName = userName.split(' ')[0];
                    var nameEl = exposeModal.querySelector('[data-expose-name]');
                    if (nameEl) {
                        nameEl.textContent = firstName ? ', ' + firstName + '!' : '!';
                    }

                    // Show success, hide form
                    exposeModal.querySelectorAll('[data-expose-step="form"]').forEach(function (el) { el.hidden = true; });
                    exposeModal.querySelectorAll('[data-expose-step="success"]').forEach(function (el) { el.hidden = false; });
                })
                .catch(function (err) {
                    exposeSubmitBtn.disabled = false;
                    exposeSubmitBtn.textContent = exposeOriginalText;
                    var msg = err.message || (window.dbwContactModal.i18n && window.dbwContactModal.i18n.network_error) || 'Netzwerkfehler';
                    var existing = exposeForm.querySelector('.dbw-modal__error');
                    if (existing) existing.remove();
                    var errEl = document.createElement('div');
                    errEl.className = 'dbw-modal__error';
                    errEl.setAttribute('role', 'alert');
                    errEl.textContent = msg;
                    exposeSubmitBtn.parentNode.insertBefore(errEl, exposeSubmitBtn);
                });
            });
        }
    }

    // --- Mobile sticky CTA bar ---
    if (stickyBar) {
        stickyBar.hidden = false;
        var sidebar = document.querySelector('.dbw-sidebar');

        if (sidebar && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                stickyBar.classList.toggle('is-visible', !entries[0].isIntersecting);
            }, { threshold: 0 });
            observer.observe(sidebar);
        } else {
            stickyBar.classList.add('is-visible');
        }
    }
})();
