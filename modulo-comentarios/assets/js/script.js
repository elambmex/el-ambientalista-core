document.addEventListener('DOMContentLoaded', function () {
    const backdrop       = document.getElementById('eac-drawer-backdrop');
    const closeBtn       = document.getElementById('eac-drawer-close');
    const viewThread     = document.getElementById('eac-drawer-view-thread');
    const viewAll        = document.getElementById('eac-drawer-view-all');
    const activeThreadBox = document.getElementById('eac-active-thread-box');
    const commentsList   = document.getElementById('eac-drawer-comments-list');
    const searchInput    = document.getElementById('eac-comment-search');
    const sortSelect     = document.getElementById('eac-sort-select');
    const formContainer  = document.getElementById('eac-drawer-form-container');
    const toggleFormBtn  = document.getElementById('eac-toggle-form-btn');

    if (!backdrop) return;

    // Mover el backdrop al final de <body> para evitar problemas de z-index o transform en contenedores de Elementor / The Fox
    document.body.appendChild(backdrop);

    function openDrawer(mode, threadId, focusForm) {
        backdrop.classList.add('is-open');
        backdrop.setAttribute('aria-hidden', 'false');
        document.body.classList.add('eac-drawer-open');

        if (mode === 'thread' && threadId) {
            renderSingleThreadView(threadId);
        } else {
            showAllCommentsView(focusForm);
        }
    }

    function closeDrawer() {
        backdrop.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('eac-drawer-open');
    }

    function renderSingleThreadView(threadId) {
        if (!viewThread || !viewAll || !activeThreadBox) return;
        const sourceThread = document.getElementById('eac-thread-' + threadId);
        if (!sourceThread) {
            showAllCommentsView(false);
            return;
        }

        // Clonar el hilo completo y expandir todas sus respuestas automáticamente (como en la captura 1 y 2 de NYT)
        activeThreadBox.innerHTML = '';
        const clone = sourceThread.cloneNode(true);
        clone.removeAttribute('id');

        const toggleBar = clone.querySelector('.eac-drawer-replies-toggle-bar');
        if (toggleBar) toggleBar.style.display = 'none';

        const childrenContainer = clone.querySelector('.eac-drawer-children-container');
        if (childrenContainer) childrenContainer.style.display = 'block';

        activeThreadBox.appendChild(clone);
        bindInteractiveButtons(activeThreadBox);

        viewAll.style.display = 'none';
        viewThread.style.display = 'block';
        const scrollArea = backdrop.querySelector('.eac-drawer-scroll-area');
        if (scrollArea) scrollArea.scrollTop = 0;
    }

    function showAllCommentsView(focusForm) {
        if (viewThread) viewThread.style.display = 'none';
        if (viewAll) viewAll.style.display = 'block';

        if (focusForm && formContainer) {
            formContainer.style.display = 'block';
            const textarea = formContainer.querySelector('textarea');
            if (textarea) setTimeout(() => textarea.focus(), 150);
        }
    }

    // Botones que abren el Drawer desde la tarjeta del artículo
    document.querySelectorAll('.eac-open-drawer-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const mode      = btn.getAttribute('data-open-mode') || 'all';
            const threadId  = btn.getAttribute('data-thread-id') || '';
            const focusForm = btn.getAttribute('data-focus-form') === '1';
            openDrawer(mode, threadId, focusForm);
        });
    });

    // Botones "Los X comentarios" dentro de la vista de hilo para pasar a la vista completa
    backdrop.querySelectorAll('.eac-switch-to-all-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showAllCommentsView(false);
        });
    });

    // Tarjetas miniatura del carrusel horizontal ("Más comentarios sobre...")
    backdrop.querySelectorAll('.eac-mini-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const targetId = card.getAttribute('data-thread-target');
            if (targetId) renderSingleThreadView(targetId);
        });
    });

    // Flechas del carrusel horizontal
    const track = document.getElementById('eac-mini-cards-track');
    const prevBtn = document.getElementById('eac-carousel-prev');
    const nextBtn = document.getElementById('eac-carousel-next');
    if (track && prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => track.scrollBy({ left: -280, behavior: 'smooth' }));
        nextBtn.addEventListener('click', () => track.scrollBy({ left: 280, behavior: 'smooth' }));
    }

    // Cerrar con botón X, clic fuera o tecla Escape
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) closeDrawer();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.classList.contains('is-open')) closeDrawer();
    });

    // Mostrar/Ocultar formulario dentro del Drawer
    if (toggleFormBtn && formContainer) {
        toggleFormBtn.addEventListener('click', function () {
            const isHidden = formContainer.style.display === 'none';
            formContainer.style.display = isHidden ? 'block' : 'none';
        });
    }

    // Pestañas de filtrado: Todos | Selecciones de los lectores | De la redacción
    const filterTabs = backdrop.querySelectorAll('.eac-tab-pill');
    let currentFilter = 'all';

    function applyFilterAndSearch() {
        if (!commentsList) return;
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const items = commentsList.querySelectorAll('.eac-drawer-thread-item');

        items.forEach(function (item) {
            const isPick  = item.getAttribute('data-is-pick') === '1';
            const isStaff = item.getAttribute('data-is-staff') === '1';
            const text    = item.textContent.toLowerCase();

            let matchesTab = true;
            if (currentFilter === 'picks') matchesTab = isPick;
            if (currentFilter === 'staff') matchesTab = isStaff;

            const matchesQuery = !query || text.includes(query);
            item.style.display = (matchesTab && matchesQuery) ? 'block' : 'none';
        });
    }

    filterTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');
            currentFilter = tab.getAttribute('data-filter') || 'all';
            applyFilterAndSearch();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilterAndSearch);
    }

    // Ordenamiento: Más recientes, Más antiguos, Más recomendados
    if (sortSelect && commentsList) {
        sortSelect.addEventListener('change', function () {
            const mode = sortSelect.value;
            const items = Array.from(commentsList.querySelectorAll('.eac-drawer-thread-item'));

            items.sort(function (a, b) {
                const timeA  = parseInt(a.getAttribute('data-timestamp'), 10) || 0;
                const timeB  = parseInt(b.getAttribute('data-timestamp'), 10) || 0;
                const likesA = parseInt(a.getAttribute('data-likes'), 10) || 0;
                const likesB = parseInt(b.getAttribute('data-likes'), 10) || 0;

                if (mode === 'newest') return timeB - timeA;
                if (mode === 'oldest') return timeA - timeB;
                if (mode === 'recommended') return likesB - likesA;
                return 0;
            });

            items.forEach(item => commentsList.appendChild(item));
        });
    }

    // Funciones interactivas dentro de los comentarios (Expandir respuestas, Recomiendo AJAX, Responder y Compartir)
    function bindInteractiveButtons(container) {
        // 1. Expandir/Colapsar respuestas ("3 Respuestas v")
        container.querySelectorAll('.eac-drawer-expand-replies-btn').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';

            btn.addEventListener('click', function () {
                const threadItem = btn.closest('.eac-drawer-thread-item');
                const childrenBox = threadItem ? threadItem.querySelector('.eac-drawer-children-container') : null;
                if (!childrenBox) return;

                const count = parseInt(btn.getAttribute('data-count'), 10) || 1;
                const isExpanded = btn.getAttribute('data-expanded') === '1';
                const labelSpan = btn.querySelector('span');

                if (isExpanded) {
                    childrenBox.style.display = 'none';
                    btn.setAttribute('data-expanded', '0');
                    if (labelSpan) labelSpan.textContent = count === 1 ? '1 Respuesta' : `${count} Respuestas`;
                } else {
                    childrenBox.style.display = 'block';
                    btn.setAttribute('data-expanded', '1');
                    if (labelSpan) labelSpan.textContent = count === 1 ? 'Ocultar respuesta' : 'Ocultar respuestas';
                }
            });
        });

        // 2. Botón "Recomiendo" vía AJAX con persistencia en localStorage
        container.querySelectorAll('.eac-recommend-btn').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';

            const commentId = btn.getAttribute('data-comment-id');
            const storageKey = 'eac_rec_' + commentId;

            if (localStorage.getItem(storageKey) === '1') {
                btn.classList.add('is-recommended');
            }

            btn.addEventListener('click', function () {
                if (typeof eacCommentsData === 'undefined') return;
                const alreadyLiked = btn.classList.contains('is-recommended');
                const actionType   = alreadyLiked ? 'remove' : 'add';

                const formData = new FormData();
                formData.append('action', 'eac_recommend_comment');
                formData.append('nonce', eacCommentsData.nonce);
                formData.append('comment_id', commentId);
                formData.append('recommend_action', actionType);

                fetch(eacCommentsData.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data) {
                        document.querySelectorAll(`.eac-recommend-btn[data-comment-id="${commentId}"]`).forEach(function (b) {
                            const countEl = b.querySelector('.eac-rec-count');
                            if (countEl) countEl.textContent = res.data.count;
                            b.classList.toggle('is-recommended', !alreadyLiked);
                        });
                        if (alreadyLiked) {
                            localStorage.removeItem(storageKey);
                        } else {
                            localStorage.setItem(storageKey, '1');
                        }
                    }
                });
            });
        });

        // 3. Botón "Responder" que abre el formulario editorial apuntando al comentario padre
        container.querySelectorAll('.eac-inline-reply-btn').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';

            btn.addEventListener('click', function () {
                const replyToId = btn.getAttribute('data-reply-to');
                const authorName = btn.getAttribute('data-author');
                showAllCommentsView(true);

                if (formContainer) {
                    const parentInput = document.getElementById('eac-comment-parent');
                    if (parentInput) parentInput.value = replyToId;

                    const replyTitle = document.getElementById('eac-reply-title');
                    if (replyTitle) replyTitle.textContent = 'Respondiendo a ' + authorName;

                    const cancelReplyBtn = document.getElementById('eac-cancel-reply-btn');
                    if (cancelReplyBtn) cancelReplyBtn.style.display = 'inline-block';

                    formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });

        // 4. Botón Compartir comentario
        container.querySelectorAll('.eac-share-btn').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';

            btn.addEventListener('click', function () {
                const url = btn.getAttribute('data-share-url');
                if (navigator.clipboard && url) {
                    navigator.clipboard.writeText(url);
                    btn.title = '¡Enlace copiado!';
                }
            });
        });
    }

    bindInteractiveButtons(backdrop);

    // Envío AJAX y controles del Formulario Editorial Propio
    const customForm     = document.getElementById('eac-custom-comment-form');
    const closeFormBtn   = document.getElementById('eac-close-form-btn');
    const cancelReplyBtn = document.getElementById('eac-cancel-reply-btn');
    const parentInput    = document.getElementById('eac-comment-parent');
    const replyTitle     = document.getElementById('eac-reply-title');
    const feedbackBox    = document.getElementById('eac-form-feedback');

    function resetReplyState() {
        if (parentInput) parentInput.value = '0';
        if (replyTitle) replyTitle.textContent = 'Participa en la conversación';
        if (cancelReplyBtn) cancelReplyBtn.style.display = 'none';
    }

    const postAsSelect = document.getElementById('eac-post-as-identity');
    const customStaffFields = document.getElementById('eac-custom-staff-fields');
    if (postAsSelect && customStaffFields) {
        postAsSelect.addEventListener('change', function () {
            customStaffFields.style.display = (postAsSelect.value === 'custom_staff') ? 'grid' : 'none';
        });
    }

    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener('click', resetReplyState);
    }

    if (closeFormBtn && formContainer) {
        closeFormBtn.addEventListener('click', function () {
            formContainer.style.display = 'none';
            resetReplyState();
        });
    }

    if (customForm && typeof eacCommentsData !== 'undefined') {
        customForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('eac-submit-comment-btn');
            if (feedbackBox) {
                feedbackBox.style.display = 'none';
                feedbackBox.className = 'eac-form-feedback';
            }

            const formData = new FormData(customForm);
            formData.append('action', 'eac_submit_comment');
            formData.append('nonce', eacCommentsData.nonce);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Publicando...';
            }

            fetch(eacCommentsData.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Publicar comentario';
                }
                if (res.success && res.data) {
                    if (feedbackBox) {
                        feedbackBox.textContent = res.data.message;
                        feedbackBox.classList.add('is-success');
                        feedbackBox.style.display = 'block';
                    }
                    customForm.reset();
                    resetReplyState();
                    if (res.data.approved) {
                        setTimeout(() => window.location.reload(), 1100);
                    }
                } else {
                    const errMsg = (res.data && res.data.message) ? res.data.message : 'Por favor verifica que todos los campos obligatorios estén completos.';
                    if (feedbackBox) {
                        feedbackBox.innerHTML = errMsg;
                        feedbackBox.classList.add('is-error');
                        feedbackBox.style.display = 'block';
                    }
                }
            })
            .catch(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Publicar comentario';
                }
            });
        });
    }
});
