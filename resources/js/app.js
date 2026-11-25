/**
 * Shared client-side UI primitives for PST BPS Kota Tasikmalaya.
 * Zero external dependencies; progressive enhancement for <dialog>, ARIA validation, and toasts.
 */

const isPointerOutsideDialog = (event, dialog) => {
    const rect = dialog.getBoundingClientRect();
    return (
        event.clientX < rect.left ||
        event.clientX > rect.right ||
        event.clientY < rect.top ||
        event.clientY > rect.bottom
    );
};

export function initDialogs(root = document) {
    const supportsClosedBy = 'closedBy' in HTMLDialogElement.prototype;

    root.querySelectorAll('dialog.pst-dialog').forEach((dialog) => {
        if (dialog.dataset.pstDialogBound === 'true') return;
        dialog.dataset.pstDialogBound = 'true';

        if (!supportsClosedBy && dialog.getAttribute('closedby') === 'any') {
            let pressedOutside = false;
            dialog.addEventListener('pointerdown', (e) => {
                pressedOutside = isPointerOutsideDialog(e, dialog);
            });
            dialog.addEventListener('pointerup', (e) => {
                if (pressedOutside && isPointerOutsideDialog(e, dialog)) {
                    dialog.close('');
                }
                pressedOutside = false;
            });
        }

        dialog.addEventListener('close', () => {
            dialog.returnValue = '';
            if (dialog.dataset.resetOnClose === 'true') {
                dialog.querySelectorAll('form').forEach((form) => {
                    if (form.method !== 'dialog') form.reset();
                });
            }
            flushQueuedToasts();
        });
    });

    root.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
        if (trigger.dataset.pstTriggerBound === 'true') return;
        trigger.dataset.pstTriggerBound = 'true';
        trigger.addEventListener('click', () => {
            openModal(trigger.dataset.dialogOpen);
        });
    });

    root.querySelectorAll('[data-dialog-close]').forEach((closer) => {
        if (closer.dataset.pstCloserBound === 'true') return;
        closer.dataset.pstCloserBound = 'true';
        closer.addEventListener('click', () => {
            const targetId = closer.dataset.dialogClose;
            if (targetId) {
                closeModal(targetId);
            } else {
                closer.closest('dialog')?.close('');
            }
        });
    });
}

export function openModal(id) {
    const dialog = document.getElementById(id);
    if (!dialog || typeof dialog.showModal !== 'function') return null;
    if (!dialog.open) {
        dialog.showModal();
    }
    return dialog;
}

export function closeModal(id) {
    const dialog = document.getElementById(id);
    if (dialog && dialog.open) {
        dialog.close('');
    }
}

const pendingToasts = [];

function hasOpenModal() {
    return Boolean(document.querySelector('dialog[open]'));
}

function flushQueuedToasts() {
    if (hasOpenModal() || pendingToasts.length === 0) return;
    while (pendingToasts.length > 0) {
        const next = pendingToasts.shift();
        renderToast(next.message, next.type, next.durationMs);
    }
}

function renderToast(message, type = 'info', durationMs = 5000) {
    const container = document.getElementById('pst-toast-stack');
    const live = document.getElementById('pst-toast-announcer');

    if (live) {
        live.textContent = '';
        window.setTimeout(() => {
            live.textContent = message;
        }, 40);
    }

    if (!container) return null;

    const toneClasses = {
        success: 'border-emerald-300 bg-emerald-50 text-emerald-950',
        error: 'border-red-300 bg-red-50 text-red-950',
        warning: 'border-amber-300 bg-amber-50 text-amber-950',
        info: 'border-[#CBD5E1] bg-white text-[#0F172A]',
    };

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-start justify-between gap-3 rounded-lg border px-4 py-3 text-sm shadow-md ${toneClasses[type] || toneClasses.info}`;

    const text = document.createElement('p');
    text.className = 'font-medium leading-snug';
    text.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'shrink-0 rounded px-1.5 py-0.5 text-xs font-semibold opacity-75 hover:opacity-100';
    closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
    closeBtn.textContent = 'Tutup';

    let timerId = null;
    const dismiss = () => {
        if (timerId) window.clearTimeout(timerId);
        toast.remove();
    };

    const startTimer = () => {
        if (durationMs > 0) {
            timerId = window.setTimeout(dismiss, durationMs);
        }
    };

    const pauseTimer = () => {
        if (timerId) {
            window.clearTimeout(timerId);
            timerId = null;
        }
    };

    closeBtn.addEventListener('click', dismiss);
    toast.addEventListener('mouseenter', pauseTimer);
    toast.addEventListener('mouseleave', startTimer);
    toast.addEventListener('focusin', pauseTimer);
    toast.addEventListener('focusout', startTimer);

    toast.append(text, closeBtn);
    container.append(toast);
    startTimer();
    return toast;
}

export function showToast(message, type = 'info', durationMs = 5000) {
    if (hasOpenModal()) {
        pendingToasts.push({ message, type, durationMs });
        return null;
    }
    return renderToast(message, type, durationMs);
}

export function showConfirm({
    title = 'Konfirmasi Tindakan',
    message = 'Apakah Anda yakin ingin melanjutkan?',
    confirmLabel = 'Lanjutkan',
    cancelLabel = 'Batal',
    tone = 'primary',
} = {}) {
    const dialog = document.getElementById('pst-confirm-dialog');
    if (!dialog) {
        return Promise.resolve(false);
    }

    const titleEl = dialog.querySelector('[data-confirm-title]');
    const messageEl = dialog.querySelector('[data-confirm-message]');
    const okBtn = dialog.querySelector('[data-confirm-accept]');
    const cancelBtn = dialog.querySelector('[data-confirm-cancel]');

    if (titleEl) titleEl.textContent = title;
    if (messageEl) messageEl.textContent = message;
    if (okBtn) {
        okBtn.textContent = confirmLabel;
        okBtn.className =
            tone === 'danger'
                ? 'rounded-md bg-red-700 px-4 py-2 text-xs font-semibold text-white hover:bg-red-800'
                : 'rounded-md bg-[#0B4F9C] px-4 py-2 text-xs font-semibold text-white hover:bg-[#083D7A]';
    }
    if (cancelBtn) cancelBtn.textContent = cancelLabel;

    return new Promise((resolve) => {
        const handleClose = () => {
            const confirmed = dialog.returnValue === 'confirm';
            dialog.returnValue = '';
            dialog.removeEventListener('close', handleClose);
            resolve(confirmed);
        };
        dialog.addEventListener('close', handleClose);
        dialog.showModal();
    });
}

export function initNavDrawer(root = document) {
    const toggle = root.getElementById('workspace-nav-toggle');
    const navList = root.getElementById('workspace-nav-list');
    if (!toggle || !navList) return;

    const closeDrawer = () => {
        toggle.setAttribute('aria-expanded', 'false');
        navList.classList.add('hidden');
        navList.classList.remove('flex');
    };

    toggle.addEventListener('click', () => {
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            closeDrawer();
        } else {
            toggle.setAttribute('aria-expanded', 'true');
            navList.classList.remove('hidden');
            navList.classList.add('flex');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            closeDrawer();
            toggle.focus();
        }
    });
}

export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        if (btn.dataset.pstPwdBound === 'true') return;
        btn.dataset.pstPwdBound = 'true';

        btn.addEventListener('click', () => {
            const targetId = btn.dataset.passwordToggle;
            const input = document.getElementById(targetId);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            btn.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');

            const eyeOn = btn.querySelector('[data-eye-show]');
            const eyeOff = btn.querySelector('[data-eye-hide]');
            if (eyeOn && eyeOff) {
                eyeOn.classList.toggle('hidden', isHidden);
                eyeOff.classList.toggle('hidden', !isHidden);
            }
        });
    });
}

export function clearFormErrors(formEl, summaryEl) {
    if (summaryEl) {
        summaryEl.classList.add('hidden');
        const list = summaryEl.querySelector('[data-error-list]');
        if (list) list.replaceChildren();
    }
    if (!formEl) return;

    formEl.querySelectorAll('[data-error]').forEach((input) => {
        input.setAttribute('aria-invalid', 'false');
        const errorId = input.dataset.error;
        const ids = (input.getAttribute('aria-describedby') || '')
            .split(/\s+/)
            .filter((id) => id && id !== errorId);
        if (ids.length > 0) {
            input.setAttribute('aria-describedby', ids.join(' '));
        } else {
            input.removeAttribute('aria-describedby');
        }
        const errorEl = document.getElementById(errorId);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.dataset.visible = 'false';
        }
    });
}

export function renderFormErrors(formEl, summaryEl, errors = {}, topMessage = null) {
    clearFormErrors(formEl, summaryEl);

    const list = summaryEl?.querySelector('[data-error-list]');
    const entries = Object.entries(errors || {});

    entries.forEach(([fieldName, messages]) => {
        const msg = Array.isArray(messages) ? messages[0] : String(messages);
        const input = formEl?.querySelector(`#${CSS.escape(fieldName)}, [name="${CSS.escape(fieldName)}"]`);
        if (input) {
            input.setAttribute('aria-invalid', 'true');
            const errorId = input.dataset.error;
            if (errorId) {
                const errorEl = document.getElementById(errorId);
                if (errorEl) {
                    errorEl.textContent = msg;
                    errorEl.dataset.visible = 'true';
                }
                const ids = (input.getAttribute('aria-describedby') || '')
                    .split(/\s+/)
                    .filter(Boolean);
                if (!ids.includes(errorId)) ids.push(errorId);
                input.setAttribute('aria-describedby', ids.join(' '));
            }
        }

        if (list) {
            const li = document.createElement('li');
            if (input?.id) {
                const link = document.createElement('a');
                link.href = `#${input.id}`;
                link.className = 'underline font-medium hover:text-red-950';
                link.textContent = msg;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    input.focus();
                });
                li.append(link);
            } else {
                li.textContent = msg;
            }
            list.append(li);
        }
    });

    if (entries.length === 0 && topMessage && list) {
        const li = document.createElement('li');
        li.textContent = topMessage;
        list.append(li);
    }

    if (summaryEl && (entries.length > 0 || topMessage)) {
        summaryEl.classList.remove('hidden');
        summaryEl.focus();
    }
}

window.PstUi = {
    openModal,
    closeModal,
    showToast,
    showConfirm,
    clearFormErrors,
    renderFormErrors,
};

document.addEventListener('DOMContentLoaded', () => {
    initDialogs();
    initNavDrawer();
    initPasswordToggles();
});
