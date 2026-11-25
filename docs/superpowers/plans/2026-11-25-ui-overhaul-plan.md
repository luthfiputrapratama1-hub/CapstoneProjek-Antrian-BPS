# UI Overhaul & Frontend Bugfix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Overhaul the entire UI of the PST BPS Kota Tasikmalaya queue and guestbook system to a zero-CDN, offline-ready, Civic Institutional Precision design system while eliminating AI-slop aesthetics and fixing frontend functional/accessibility bugs across all 15 web views and 1 PDF export template.

**Architecture:** Compile Tailwind CSS v4 locally via Vite (`resources/css/app.css`, `resources/js/app.js`) with centralized `@theme` design tokens and zero external network dependencies. Unify 15 standalone Blade templates under 3 purpose-built layouts (`layouts/portal`, `layouts/workspace`, `layouts/screen`) plus anonymous Blade components (`resources/views/components/`), native `<dialog>` modals/confirmations, `aria-live` toast notifications, inline SVG icons, and safe DOM state synchronization.

**Tech Stack:** Laravel 13 (Blade), PHP 8.5 (inside `my-distrobox`), Vite 8, Tailwind CSS v4 (`@tailwindcss/vite`), Vanilla ES Modules, Chart.js (local `public/vendor/chartjs/chart.umd.js`), DomPDF, PHPUnit 12.

## Global Constraints

- **Offline LAN Zero-CDN:** Remove `https://cdn.tailwindcss.com` from all Blade files and remove `bunny(...)` font CDN usage from `vite.config.js`. Zero external network requests at runtime.
- **No New Frontend Frameworks:** Keep Vanilla JS + Blade; do not install Alpine.js, React, Vue, or external icon NPM packages.
- **No Emoji Icons:** Replace all structural emojis (`🏛️`, `🎫`, `📺`, `🖥️`, `📊`, `🛡️`, `⚠️`, `🔊`, `🕒`, `📢`, `✍️`, `👥`, `⏳`, `⏱️`, `🔍`, `📥`, `📄`, `⛔`, `🔒`) with `<x-icon>` inline SVG icons (`stroke-width="1.75"`, `aria-hidden="true"` when decorative).
- **Anti-AI-Slop Discipline:** Use `#F4F6F9` light canvas, `#FFFFFF` surfaces with `1px solid #DCE3EC` borders, controlled radius (`rounded-md` controls, `rounded-lg`/`rounded-xl` cards/dialogs, `rounded-2xl` max on Kiosk touch buttons), `tabular-nums` for digits/clocks/KPIs, no `rounded-3xl`/`shadow-2xl`/`animate-bounce`/`animate-ping`/`hover:-translate-y-1`. Display TV (`/display`) uses dark board `#090E17` with `#111827` cards and `#F59E0B` ticket ink.
- **Native Accessibility:** Use native `<dialog>` (`showModal()`, `closedby="any"` + bounding-box pointer fallback, no redundant `role="dialog"`/`aria-modal`, scroll containment via `dialog:modal` and `dialog::backdrop`, `returnValue` reset on close), one `<nav>` tree with `aria-current="page"`, `<x-table-wrapper>` (`tabindex="0" role="region"`), and `@media (prefers-reduced-motion: reduce)`.
- **Backend & Test Compatibility:** Do not alter database migrations, controllers, route names, or JSON API payloads. Preserve all Indonesian strings asserted by existing tests.
- **Container Toolchain & AGENTS.md:** Run PHP/Composer/Node/npm/PHPUnit inside `distrobox enter my-distrobox -- <cmd>`. Check `AGENTS.md` prerequisite (`laravel/boost` installation check) before application code edits.

---

### Task 1: Environment Readiness, Offline Vite Pipeline, Design Tokens & Shared JS

**Files:**
- Modify: `vite.config.js`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Modify: `tests/TestCase.php`
- Create: `tests/Feature/UiArchitectureTest.php`

**Interfaces:**
- Produces CSS design tokens & utility classes in `resources/css/app.css`: `.tabular-nums-strict`, `.print-only`, `.tv-ticker-track`, `.visually-hidden`, `.field-error`, native `dialog:modal` / `dialog::backdrop` scroll containment.
- Produces global JS helpers in `resources/js/app.js` attached to `window.PstUi`: `openModal(id)`, `closeModal(id)`, `showToast(message, type = 'info')`, `showConfirm({ title, message, confirmLabel, cancelLabel, tone })` returning `Promise<boolean>`, `renderFormErrors(formEl, summaryEl, errors)`, `clearFormErrors(formEl, summaryEl)`.

- [ ] **Step 1: Verify `AGENTS.md` prerequisite and install npm dependencies inside `my-distrobox`**

Run:
```bash
distrobox enter my-distrobox -- sh -c "composer show laravel/boost >/dev/null 2>&1 || (composer require laravel/boost --dev && php artisan boost:install --no-interaction) || true; npm install --ignore-scripts"
```
Expected: `node_modules/` created (Vite 8 + `@tailwindcss/vite` installed). Note in commit/log if `laravel/boost` was installed or if offline/unavailable.

- [ ] **Step 2: Write the failing test for Vite offline config, CSS tokens, JS module exports, and TestCase manifest guard**

Create `tests/Feature/UiArchitectureTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiArchitectureTest extends TestCase
{
    public function test_vite_config_does_not_reference_external_font_cdn(): void
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        $this->assertNotFalse($viteConfig);
        $this->assertStringNotContainsString('bunny', $viteConfig);
        $this->assertStringContainsString('resources/css/app.css', $viteConfig);
        $this->assertStringContainsString('resources/js/app.js', $viteConfig);
    }

    public function test_app_css_defines_civic_institutional_tokens_and_dialog_print_rules(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('--color-canvas: #F4F6F9;', $css);
        $this->assertStringContainsString('--color-bps-navy: #0A2540;', $css);
        $this->assertStringContainsString('--color-bps-cobalt: #0B4F9C;', $css);
        $this->assertStringContainsString('--color-tv-canvas: #090E17;', $css);
        $this->assertStringContainsString('dialog:modal', $css);
        $this->assertStringContainsString('overscroll-behavior: contain;', $css);
        $this->assertStringContainsString('.print-only', $css);
        $this->assertStringContainsString('#ticket-print-area', $css);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $css);
    }

    public function test_app_js_defines_shared_ui_primitives(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertNotFalse($js);
        $this->assertStringContainsString('window.PstUi', $js);
        $this->assertStringContainsString('showToast', $js);
        $this->assertStringContainsString('showConfirm', $js);
        $this->assertStringContainsString('closedBy', $js);
        $this->assertStringContainsString('renderFormErrors', $js);
    }
}
```

- [ ] **Step 3: Run the new test to verify it fails**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/UiArchitectureTest.php
```
Expected: FAIL (`vite.config.js` still contains `bunny`, `resources/css/app.css` lacks tokens, `resources/js/app.js` is empty).

- [ ] **Step 4: Update `vite.config.js` to remove external `bunny` CDN fonts**

Replace `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

- [ ] **Step 5: Author `resources/css/app.css` with `@theme` Civic Institutional tokens, dialog containment, responsive table rules, ticker animation, and Kiosk thermal `@media print`**

Replace `resources/css/app.css`:

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../views/**/*.blade.php';
@source '../js/**/*.js';

@theme {
    --font-sans: 'Public Sans', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;

    --color-canvas: #F4F6F9;
    --color-surface: #FFFFFF;
    --color-surface-muted: #EDF1F6;
    --color-border: #DCE3EC;
    --color-border-strong: #CBD5E1;

    --color-ink: #0F172A;
    --color-ink-secondary: #475569;
    --color-ink-muted: #64748B;

    --color-bps-navy: #0A2540;
    --color-bps-cobalt: #0B4F9C;
    --color-bps-cobalt-hover: #083D7A;
    --color-bps-green: #15803D;
    --color-bps-orange: #C2410C;
    --color-bps-teal: #0F766E;

    --color-tv-canvas: #090E17;
    --color-tv-surface: #111827;
    --color-tv-elevated: #0D1420;
    --color-tv-border: #1F2937;
    --color-tv-amber: #F59E0B;
    --color-tv-call: #EF4444;
}

@layer base {
    html {
        color: var(--color-ink);
        background-color: var(--color-canvas);
        font-family: var(--font-sans);
    }

    body {
        min-height: 100dvh;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    a,
    button,
    select,
    summary,
    [role='button'] {
        cursor: pointer;
    }

    button:disabled,
    [aria-disabled='true'] {
        cursor: not-allowed;
    }

    :focus-visible {
        outline: 2px solid var(--color-bps-cobalt);
        outline-offset: 2px;
    }

    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible {
        outline: 2px solid var(--color-bps-cobalt);
        outline-offset: 0;
        border-color: var(--color-bps-cobalt);
    }

    :is(input, select, textarea):is(:user-invalid, .user-invalid-fallback),
    :is(input, select, textarea)[aria-invalid='true'] {
        border-color: #DC2626;
    }
}

/* Tabular numbers for clocks, queue codes, KPIs, and counts */
.tabular-nums-strict {
    font-variant-numeric: tabular-nums;
}

/* Native accessible visually-hidden pattern */
.visually-hidden:where(:not(:focus-within, :active)) {
    position: absolute !important;
    clip-path: inset(50%) !important;
    overflow: hidden !important;
    width: 1px !important;
    height: 1px !important;
    margin: -1px !important;
    padding: 0 !important;
    border: 0 !important;
    white-space: nowrap !important;
}

/* Native <dialog> scroll containment and backdrop styling */
dialog.pst-dialog {
    margin: auto;
    padding: 0;
    border: 1px solid var(--color-border-strong);
    border-radius: 0.75rem;
    background-color: var(--color-surface);
    color: var(--color-ink);
    max-width: min(32rem, calc(100dvw - 2rem));
    width: 100%;
    max-height: calc(100dvh - 2rem);
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgb(15 23 42 / 0.12), 0 8px 10px -6px rgb(15 23 42 / 0.08);
}

dialog.pst-dialog[open] {
    display: flex;
    flex-direction: column;
}

dialog:modal {
    overscroll-behavior: contain;
}

dialog::backdrop {
    overflow: hidden;
    overscroll-behavior: contain;
    background-color: rgb(15 23 42 / 0.55);
}

/* Inline field error visibility */
.field-error-text {
    display: none;
    margin-top: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #B91C1C;
}

.field-error-text[data-visible='true'] {
    display: block;
}

/* TV Display running ticker */
@keyframes pst-marquee {
    0% {
        transform: translateX(0%);
    }
    100% {
        transform: translateX(-50%);
    }
}

.tv-ticker-viewport {
    overflow: hidden;
    position: relative;
    flex: 1 1 0%;
}

.tv-ticker-track {
    display: inline-flex;
    width: max-content;
    white-space: nowrap;
    animation: pst-marquee 34s linear infinite;
}

.tv-ticker-track:hover {
    animation-play-state: paused;
}

/* Active TV call highlight */
@keyframes tv-call-pulse {
    0%, 100% {
        border-color: #EF4444;
        background-color: rgb(239 68 68 / 0.08);
    }
    50% {
        border-color: #F59E0B;
        background-color: rgb(245 158 11 / 0.1);
    }
}

.tv-card-calling {
    animation: tv-call-pulse 1.8s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }

    .tv-ticker-track {
        animation: none !important;
        white-space: normal;
        width: auto;
    }

    .tv-ticker-duplicate {
        display: none !important;
    }
}

/* Screen vs Print isolation for Kiosk 58mm / 80mm thermal receipts */
@media screen {
    .print-only {
        display: none !important;
    }
}

@media print {
    body > *:not(#ticket-print-area) {
        display: none !important;
    }

    #ticket-print-area {
        display: block !important;
        position: static !important;
        width: 58mm;
        max-width: 100%;
        margin: 0;
        padding: 3mm 2mm;
        font-family: 'Courier New', Courier, monospace;
        color: #000000 !important;
        background: #FFFFFF !important;
        font-size: 11px;
        line-height: 1.35;
    }

    @page {
        size: 58mm auto;
        margin: 0;
    }
}
```

- [ ] **Step 6: Author `resources/js/app.js` with dialog light-dismiss fallback, toast notifications, confirm dialog helper, responsive nav drawer, password toggle, and accessible form error bridge**

Replace `resources/js/app.js`:

```js
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
```

- [ ] **Step 7: Update `tests/TestCase.php` with safe Vite manifest fallback when `public/build/manifest.json` is missing**

Replace `tests/TestCase.php`:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(public_path('build/manifest.json'))) {
            $this->withoutVite();
        }
    }
}
```

- [ ] **Step 8: Build Vite assets and run `UiArchitectureTest` plus full PHPUnit suite**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test"
```
Expected: `npm run build` outputs `public/build/manifest.json` + CSS/JS bundles without warnings, and all PHPUnit tests PASS (45 tests including `UiArchitectureTest`).

- [ ] **Step 9: Commit Task 1**

```bash
git add vite.config.js resources/css/app.css resources/js/app.js tests/TestCase.php tests/Feature/UiArchitectureTest.php
git commit -m "feat(ui): configure offline Vite Tailwind v4 tokens and shared JS primitives"
```

---

### Task 2: Shared Blade Components, 3 Context Layouts, and Tailwind v4 Pagination

**Files:**
- Create: `resources/views/components/bps-brand.blade.php`
- Create: `resources/views/components/icon.blade.php`
- Create: `resources/views/components/badge.blade.php`
- Create: `resources/views/components/table-wrapper.blade.php`
- Create: `resources/views/components/modal.blade.php`
- Create: `resources/views/components/confirm-dialog.blade.php`
- Create: `resources/views/components/toast-region.blade.php`
- Create: `resources/views/layouts/portal.blade.php`
- Create: `resources/views/layouts/workspace.blade.php`
- Create: `resources/views/layouts/screen.blade.php`
- Create: `resources/views/vendor/pagination/tailwind.blade.php`
- Create: `tests/Feature/UiComponentsTest.php`

**Interfaces:**
- `<x-bps-brand :subtitle="..." :dark="false" :size="'md'" :href="route('home')" />`
- `<x-icon name="ticket|tv|monitor|chart|shield|clock|volume-on|volume-off|search|download|document|plus|edit|lock|check|warning|close|menu|arrow-right|arrow-left|refresh|users|eye|eye-off|sliders" class="w-5 h-5" />`
- `<x-badge tone="neutral|cobalt|green|orange|teal|danger">...</x-badge>`
- `<x-table-wrapper labelled-by="id">...</x-table-wrapper>`
- `<x-modal id="..." title="..." :subtitle="null" :reset-on-close="false" max-width="max-w-lg">...</x-modal>`
- `<x-confirm-dialog />` (renders `#pst-confirm-dialog`)
- `<x-toast-region />` (renders `#pst-toast-stack` + `#pst-toast-announcer`)
- Layouts yield: `title`, `header-subtitle`, `header-actions`, `page-title`, `content`, and stacks `head`, `scripts`.

- [ ] **Step 1: Write the failing test for components, layouts, and pagination view**

Create `tests/Feature/UiComponentsTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_core_blade_components_render_clean_svg_and_native_dialog(): void
    {
        $brandHtml = Blade::render('<x-bps-brand subtitle="Kota Tasikmalaya" />');
        $this->assertStringContainsString('<svg', $brandHtml);
        $this->assertStringContainsString('Kota Tasikmalaya', $brandHtml);

        $iconHtml = Blade::render('<x-icon name="ticket" class="w-5 h-5" />');
        $this->assertStringContainsString('<svg', $iconHtml);
        $this->assertStringContainsString('aria-hidden="true"', $iconHtml);

        $modalHtml = Blade::render('<x-modal id="test-modal" title="Judul Modal"><p>Isi</p></x-modal>');
        $this->assertStringContainsString('<dialog', $modalHtml);
        $this->assertStringContainsString('closedby="any"', $modalHtml);
        $this->assertStringNotContainsString('role="dialog"', $modalHtml);
        $this->assertStringNotContainsString('aria-modal', $modalHtml);
    }

    public function test_workspace_layout_renders_single_nav_with_aria_current_and_password_link(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $html = view('layouts.workspace', [
            'activeNav' => 'reports',
            'pageTitle' => 'Rekap Laporan',
            'pageSubtitle' => 'PST BPS Kota Tasikmalaya',
        ])->render();

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString(route('password.change'), $html);
        $this->assertStringContainsString('id="pst-toast-stack"', $html);
        $this->assertStringContainsString('id="pst-confirm-dialog"', $html);
    }
}
```

- [ ] **Step 2: Run `UiComponentsTest` to verify it fails**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/UiComponentsTest.php
```
Expected: FAIL (`Unable to locate a class or view for component [bps-brand]`).

- [ ] **Step 3: Create `<x-bps-brand>` and `<x-icon>` Blade components**

Create `resources/views/components/bps-brand.blade.php`:

```blade
@props([
    'title' => 'BADAN PUSAT STATISTIK',
    'subtitle' => 'Pelayanan Statistik Terpadu · Kota Tasikmalaya',
    'href' => route('home'),
    'dark' => false,
    'size' => 'md',
])

@php
    $boxSize = $size === 'lg' ? 'w-12 h-12' : 'w-10 h-10';
    $titleClass = $dark ? 'text-white' : 'text-[#0F172A]';
    $subClass = $dark ? 'text-slate-300' : 'text-[#475569]';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group focus-visible:outline-2 focus-visible:outline-offset-2 rounded-md']) }}>
    <span class="{{ $boxSize }} shrink-0 rounded-lg border {{ $dark ? 'border-white/15 bg-white/10' : 'border-[#DCE3EC] bg-white' }} flex items-center justify-center p-1.5">
        <svg viewBox="0 0 36 36" class="w-full h-full" aria-hidden="true">
            <rect x="3" y="6" width="30" height="6" rx="1.5" fill="#0B4F9C" />
            <rect x="3" y="15" width="23" height="6" rx="1.5" fill="#15803D" />
            <rect x="3" y="24" width="16" height="6" rx="1.5" fill="#EA580C" />
            <circle cx="28" cy="27" r="3" fill="{{ $dark ? '#F8FAFC' : '#0A2540' }}" />
        </svg>
    </span>
    <span class="min-w-0 leading-tight">
        <span class="block text-sm sm:text-base font-bold tracking-tight {{ $titleClass }}">{{ $title }}</span>
        @if($subtitle)
            <span class="block text-xs font-medium {{ $subClass }} truncate">{{ $subtitle }}</span>
        @endif
    </span>
</a>
```

Create `resources/views/components/icon.blade.php`:

```blade
@props([
    'name',
    'decorative' => true,
])

@php
    $paths = [
        'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5A1.5 1.5 0 0 1 5.25 6h13.5a1.5 1.5 0 0 1 1.5 1.5v2.25a2.25 2.25 0 0 0 0 4.5v2.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-2.25a2.25 2.25 0 0 0 0-4.5V7.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 6v12" />',
        'tv' => '<rect x="3" y="4.5" width="18" height="12.5" rx="1.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 20h7.5M12 17v3" />',
        'monitor' => '<rect x="3" y="4" width="18" height="12" rx="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 20h9M12 16v4M7.5 10h4.5M7.5 13h2.5" />',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-5.25M12 16.5v-9M17.25 16.5v-12" />',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.25 4.5 6.5v5.25c0 4.7 3.2 8.85 7.5 9.95 4.3-1.1 7.5-5.25 7.5-9.95V6.5L12 3.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m9.25 12 1.85 1.85 3.65-3.7" />',
        'clock' => '<circle cx="12" cy="12" r="8.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.75" />',
        'volume-on' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.25 6.75 9H3.75v6h3L11 18.75V5.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.25 8.75a4.75 4.75 0 0 1 0 6.5M18 6.25a8.25 8.25 0 0 1 0 11.5" />',
        'volume-off' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.25 6.75 9H3.75v6h3L11 18.75V5.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m15.5 9.5 5 5m0-5-5 5" />',
        'search' => '<circle cx="10.75" cy="10.75" r="6.75" /><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 19.5-3.75-3.75" />',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v10.5m0 0-4-4m4 4 4-4M4.5 18.75h15" />',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75L18.75 8.25v12a1.5 1.5 0 0 1-1.5 1.5H7.5a1.5 1.5 0 0 1-1.5-1.5v-15a1.5 1.5 0 0 1 1.5-1.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75v4.5h4.5M9 12.75h6M9 16.25h4.5" />',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5a2.12 2.12 0 1 1 3 3L8.25 18.75l-4 1 1-4L16.5 4.5Z" />',
        'lock' => '<rect x="5" y="10.5" width="14" height="9.75" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 10.5V7.75a3.75 3.75 0 1 1 7.5 0v2.75" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.75 12.5 4.5 4.5 10-10" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 2.75 19.25h18.5L12 3.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v4.25M12 16.75h.01" />',
        'close' => '<path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />',
        'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />',
        'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0-5.25-5.25M19.5 12l-5.25 5.25" />',
        'arrow-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0 5.25-5.25M4.5 12l5.25 5.25" />',
        'refresh' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12a7.5 7.5 0 1 1-2.2-5.3L19.5 8.75M19.5 4.5v4.25h-4.25" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19.25v-1.5a3.75 3.75 0 0 0-3.75-3.75h-4.5A3.75 3.75 0 0 0 4 17.75v1.5M10 10.5a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM20 19.25v-1.25a3.25 3.25 0 0 0-2.5-3.16M15.75 4.2a3.25 3.25 0 0 1 0 6.1" />',
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.25 9.5-6.25S21.5 12 21.5 12s-3.5 6.25-9.5 6.25S2.5 12 2.5 12Z" /><circle cx="12" cy="12" r="2.75" />',
        'eye-off' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.5 3.5 20.5 20.5M9.9 5.95A9.7 9.7 0 0 1 12 5.75c6 0 9.5 6.25 9.5 6.25a16.8 16.8 0 0 1-3.3 4.05M6.35 7.6C4.05 9.25 2.5 12 2.5 12s3.5 6.25 9.5 6.25c1.55 0 2.95-.4 4.2-1.05" />',
        'sliders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15M4.5 16.5h15M8.5 5v5M15.5 14v5" />',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5h3.75l8.25-4.5v12l-8.25-4.5H4.5v-3ZM8.25 13.5l1.5 5.25" />',
    ];
    $svgBody = $paths[$name] ?? '<circle cx="12" cy="12" r="8" />';
@endphp

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
     @if($decorative) aria-hidden="true" @endif
     {{ $attributes->merge(['class' => 'w-5 h-5 shrink-0']) }}>
    {!! $svgBody !!}
</svg>
```

- [ ] **Step 4: Create `<x-badge>`, `<x-table-wrapper>`, `<x-modal>`, `<x-confirm-dialog>`, and `<x-toast-region>`**

Create `resources/views/components/badge.blade.php`:

```blade
@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'bg-[#EDF1F6] text-[#334155] border-[#DCE3EC]',
        'cobalt' => 'bg-blue-50 text-[#0B4F9C] border-blue-200',
        'green' => 'bg-emerald-50 text-[#15803D] border-emerald-200',
        'orange' => 'bg-orange-50 text-[#C2410C] border-orange-200',
        'teal' => 'bg-teal-50 text-[#0F766E] border-teal-200',
        'danger' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-semibold ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
```

Create `resources/views/components/table-wrapper.blade.php`:

```blade
@props([
    'labelledBy' => null,
    'label' => 'Tabel data',
])

<div tabindex="0"
     role="region"
     @if($labelledBy) aria-labelledby="{{ $labelledBy }}" @else aria-label="{{ $label }}" @endif
     {{ $attributes->merge(['class' => 'overflow-x-auto focus-visible:outline-2 focus-visible:outline-[#0B4F9C]']) }}>
    {{ $slot }}
</div>
```

Create `resources/views/components/modal.blade.php`:

```blade
@props([
    'id',
    'title',
    'subtitle' => null,
    'maxWidth' => 'max-w-lg',
    'resetOnClose' => false,
])

<dialog id="{{ $id }}"
        closedby="any"
        aria-labelledby="{{ $id }}-title"
        @if($resetOnClose) data-reset-on-close="true" @endif
        {{ $attributes->merge(['class' => "pst-dialog {$maxWidth}"]) }}>
    <div class="flex items-start justify-between gap-4 border-b border-[#DCE3EC] px-6 py-4">
        <div>
            <h1 id="{{ $id }}-title" class="text-base font-bold text-[#0F172A]">{{ $title }}</h1>
            @if($subtitle)
                <p class="mt-0.5 text-xs text-[#475569]">{{ $subtitle }}</p>
            @endif
        </div>
        <button type="button"
                data-dialog-close="{{ $id }}"
                aria-label="Tutup dialog"
                class="rounded-md p-1.5 text-[#475569] hover:bg-[#EDF1F6] hover:text-[#0F172A]">
            <x-icon name="close" class="w-4 h-4" />
        </button>
    </div>

    <div class="px-6 py-5">
        {{ $slot }}
    </div>
</dialog>
```

Create `resources/views/components/confirm-dialog.blade.php`:

```blade
<dialog id="pst-confirm-dialog"
        closedby="closerequest"
        aria-labelledby="pst-confirm-title"
        class="pst-dialog max-w-md">
    <div class="border-b border-[#DCE3EC] px-6 py-4">
        <h1 id="pst-confirm-title" data-confirm-title class="text-base font-bold text-[#0F172A]">Konfirmasi Tindakan</h1>
    </div>
    <form method="dialog" class="px-6 py-5 space-y-5">
        <p data-confirm-message class="text-sm leading-relaxed text-[#475569]">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
        <div class="flex items-center justify-end gap-2.5 border-t border-[#DCE3EC] pt-4">
            <button type="submit"
                    value="cancel"
                    data-confirm-cancel
                    autofocus
                    class="rounded-md border border-[#CBD5E1] bg-white px-4 py-2 text-xs font-semibold text-[#334155] hover:bg-[#F4F6F9]">
                Batal
            </button>
            <button type="submit"
                    value="confirm"
                    data-confirm-accept
                    class="rounded-md bg-[#0B4F9C] px-4 py-2 text-xs font-semibold text-white hover:bg-[#083D7A]">
                Lanjutkan
            </button>
        </div>
    </form>
</dialog>
```

Create `resources/views/components/toast-region.blade.php`:

```blade
<div role="region" aria-label="Notifikasi Sistem" class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2 px-4 sm:px-0">
    <p class="visually-hidden">Pemberitahuan status sistem</p>
    <div id="pst-toast-announcer" aria-live="polite" class="visually-hidden"></div>
    <div id="pst-toast-stack" class="flex flex-col gap-2"></div>
</div>
```

- [ ] **Step 5: Create the 3 layouts (`layouts/portal`, `layouts/workspace`, `layouts/screen`) and custom pagination view (`vendor/pagination/tailwind`)**

Create `resources/views/layouts/portal.blade.php`:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased">
    @hasSection('hide-header')
    @else
        <header class="bg-[#0A2540] text-white border-b border-white/10 sticky top-0 z-40">
            <div class="h-1 w-full flex" aria-hidden="true">
                <span class="flex-1 bg-[#0B4F9C]"></span>
                <span class="flex-1 bg-[#15803D]"></span>
                <span class="flex-1 bg-[#EA580C]"></span>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4">
                <x-bps-brand
                    title="Pelayanan Statistik Terpadu (PST)"
                    subtitle="Badan Pusat Statistik Kota Tasikmalaya"
                    :dark="true"
                />

                <div class="flex items-center gap-2.5">
                    @yield('header-actions')
                </div>
            </div>
        </header>
    @endif

    <main class="@yield('main-class', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full')">
        @yield('content')
    </main>

    @hasSection('hide-footer')
    @else
        <footer class="bg-white border-t border-[#DCE3EC] py-5 px-4 sm:px-6 lg:px-8 text-xs text-[#475569]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <p>
                    <strong class="font-semibold text-[#0F172A]">Badan Pusat Statistik Kota Tasikmalaya</strong>
                    · Jl. Ir. H. Juanda No. 84, Sukamulya, Bungursari, Kota Tasikmalaya 46151
                </p>
                <p class="text-[#64748B]">Sistem Antrean &amp; Rekapitulasi PST © 2026</p>
            </div>
        </footer>
    @endif

    <x-toast-region />
    @stack('scripts')
</body>
</html>
```

Create `resources/views/layouts/workspace.blade.php`:

```blade
@php
    $user = Auth::user();
    $isAdmin = $user && $user->role === 'admin';
    $navItems = $isAdmin
        ? [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['key' => 'reports', 'label' => 'Rekap Laporan', 'route' => 'admin.reports.index'],
            ['key' => 'services', 'label' => 'Layanan', 'route' => 'admin.services.index'],
            ['key' => 'counters', 'label' => 'Loket', 'route' => 'admin.counters.index'],
            ['key' => 'users', 'label' => 'Pengguna', 'route' => 'admin.users.index'],
            ['key' => 'loket', 'label' => 'Meja Loket', 'route' => 'counter.index'],
        ]
        : [
            ['key' => 'loket', 'label' => 'Meja Loket', 'route' => 'counter.index'],
        ];
    $currentNav = $activeNav ?? '';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($pageTitle ?? 'Workspace') . ' — PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased">
    <header class="bg-[#0A2540] text-white border-b border-white/10 sticky top-0 z-40">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <x-bps-brand
                    :title="$pageTitle ?? 'Workspace PST BPS'"
                    :subtitle="$pageSubtitle ?? 'Badan Pusat Statistik Kota Tasikmalaya'"
                    :dark="true"
                />
            </div>

            <div class="flex items-center gap-3">
                <nav aria-label="Navigasi Utama" class="relative flex items-center">
                    <button id="workspace-nav-toggle"
                            type="button"
                            aria-expanded="false"
                            aria-controls="workspace-nav-list"
                            class="lg:hidden inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20">
                        <x-icon name="menu" class="w-4 h-4" />
                        <span>Menu</span>
                    </button>

                    <ul id="workspace-nav-list"
                        class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-1 lg:gap-1.5 absolute lg:static right-0 top-full mt-2 lg:mt-0 w-64 lg:w-auto rounded-lg lg:rounded-none border lg:border-0 border-[#1E3A5F] bg-[#0A2540] p-2 lg:p-0 shadow-lg lg:shadow-none z-50">
                        @foreach($navItems as $item)
                            @php $isCurrent = $currentNav === $item['key']; @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @if($isCurrent) aria-current="page" @endif
                                   class="block rounded-md px-3 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap {{ $isCurrent ? 'bg-white text-[#0A2540] border-l-4 lg:border-l-0 lg:border-b-2 border-[#EA580C]' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach

                        <li class="lg:hidden border-t border-white/15 mt-1 pt-2 px-1 flex flex-col gap-1.5">
                            <div class="text-[11px] text-slate-300 px-2">
                                <span class="font-semibold text-white">{{ $user?->name }}</span>
                                <span class="uppercase text-slate-400">({{ $user?->role }})</span>
                            </div>
                            <a href="{{ route('password.change') }}"
                               class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-semibold text-slate-200 hover:bg-white/10 hover:text-white">
                                <x-icon name="lock" class="w-3.5 h-3.5" />
                                <span>Ubah Kata Sandi</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left rounded-md bg-red-700/90 hover:bg-red-700 px-2.5 py-1.5 text-xs font-semibold text-white">
                                    Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="hidden lg:flex items-center gap-2.5 border-l border-white/15 pl-3">
                    <div class="text-right leading-tight">
                        <div class="text-xs font-semibold text-white">{{ $user?->name }}</div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-300">{{ $user?->role }}</div>
                    </div>
                    <a href="{{ route('password.change') }}"
                       class="inline-flex items-center gap-1 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-2.5 py-1.5 text-xs font-semibold text-white whitespace-nowrap">
                        <x-icon name="lock" class="w-3.5 h-3.5" />
                        <span>Kata Sandi</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="rounded-md bg-red-700 hover:bg-red-800 px-3 py-1.5 text-xs font-semibold text-white whitespace-nowrap">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-[#DCE3EC] py-4 px-4 sm:px-6 lg:px-8 text-xs text-[#64748B]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <span>Sistem Informasi Antrean &amp; Rekapitulasi Pengunjung PST BPS Kota Tasikmalaya © 2026</span>
            <span>Kepatuhan UU No. 27 Tahun 2022 (UU PDP)</span>
        </div>
    </footer>

    <x-confirm-dialog />
    <x-toast-region />
    @stack('scripts')
</body>
</html>
```

Create `resources/views/layouts/screen.blade.php`:

```blade
@props([
    'theme' => 'light',
])
@php
    $isDark = ($screenTheme ?? $theme) === 'dark';
    $bodyClass = $isDark
        ? 'bg-[#090E17] text-slate-100 min-h-dvh flex flex-col justify-between antialiased select-none overflow-x-hidden'
        : 'bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased select-none';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Layar Layanan — PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}">
    @yield('content')
    @stack('scripts')
</body>
</html>
```

Create `resources/views/vendor/pagination/tailwind.blade.php`:

```blade
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-3 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#F4F6F9]">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#F4F6F9]">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-3 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-[#475569] tabular-nums-strict">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-semibold text-[#0F172A]">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-[#0F172A]">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                dari <span class="font-semibold text-[#0F172A]">{{ $paginator->total() }}</span> data
            </p>

            <span class="inline-flex items-center gap-1 tabular-nums-strict">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman sebelumnya"
                          class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-2.5 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                       class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#EDF1F6]">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2 py-1 text-xs text-[#64748B]">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center rounded-md border border-[#0B4F9C] bg-[#0B4F9C] px-3 py-1.5 text-xs font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   aria-label="Ke halaman {{ $page }}"
                                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#334155] hover:bg-[#EDF1F6]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                       class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#EDF1F6]">
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman berikutnya"
                          class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-2.5 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
```

- [ ] **Step 6: Run `UiComponentsTest` and `npm run build` to verify Task 2**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test tests/Feature/UiComponentsTest.php"
```
Expected: `npm run build` succeeds and both tests in `UiComponentsTest` PASS.

- [ ] **Step 7: Commit Task 2**

```bash
git add resources/views/components resources/views/layouts resources/views/vendor tests/Feature/UiComponentsTest.php
git commit -m "feat(ui): add modular Blade components, layouts, and pagination view"
```

---

### Task 3: Portal Beranda (`/`), Auth (`/login`, `/password/change`), and Error Pages (`403`, `404`, `419`, `500`)

**Files:**
- Modify: `resources/views/welcome.blade.php`
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/auth/change-password.blade.php`
- Modify: `resources/views/errors/403.blade.php`
- Modify: `resources/views/errors/404.blade.php`
- Modify: `resources/views/errors/419.blade.php`
- Modify: `resources/views/errors/500.blade.php`
- Modify: `tests/Feature/LandingAndErrorPagesTest.php`

**Interfaces:**
- Consumes: `layouts/portal`, `<x-bps-brand>`, `<x-icon>`, `<x-badge>`, `initPasswordToggles()` from `resources/js/app.js`.
- Preserves all assertions in `LandingAndErrorPagesTest.php` (`Pelayanan Statistik Terpadu (PST)`, `Badan Pusat Statistik Kota Tasikmalaya`, `Anjungan Kiosk Tiket`, `Layar Monitor TV`, `Meja Kerja Pelayanan`, `Dashboard & Rekap`, `UU No. 27 Tahun 2022`, `404`, `Halaman Tidak Ditemukan`, `403`, `Akses Ditolak`, `Kembali ke Beranda Utama`).

- [ ] **Step 1: Extend `tests/Feature/LandingAndErrorPagesTest.php` to fail against old CDN/emoji views and verify new portal/auth/error design**

Append these tests inside `tests/Feature/LandingAndErrorPagesTest.php` (before the closing `}`):

```php
    public function test_portal_auth_and_error_pages_use_offline_vite_without_emojis_or_cdn(): void
    {
        $landing = $this->get('/')->assertStatus(200);
        $landing->assertSee('Layanan Publik &amp; Ruang Tunggu', false);
        $landing->assertSee('Operasional Staf &amp; Pimpinan', false);
        $landing->assertDontSee('cdn.tailwindcss.com');
        $landing->assertDontSee('🏛️');
        $landing->assertDontSee('🎫');

        $login = $this->get('/login')->assertStatus(200);
        $login->assertSee('autocomplete="username"', false);
        $login->assertSee('autocomplete="current-password"', false);
        $login->assertSee('data-password-toggle="password"', false);
        $login->assertDontSee('cdn.tailwindcss.com');

        $this->actingAs(User::where('role', 'petugas')->first());
        $changePwd = $this->get('/password/change')->assertStatus(200);
        $changePwd->assertSee('autocomplete="new-password"', false);
        $changePwd->assertDontSee('cdn.tailwindcss.com');
        $changePwd->assertDontSee('🔒');

        $notFound = $this->get('/halaman-yang-tidak-pernah-ada-123')->assertStatus(404);
        $notFound->assertDontSee('cdn.tailwindcss.com');
        $notFound->assertDontSee('🔍');

        $forbidden = $this->get('/admin/dashboard')->assertStatus(403);
        $forbidden->assertDontSee('cdn.tailwindcss.com');
        $forbidden->assertDontSee('⛔');
    }

    public function test_419_and_500_error_views_render_cleanly_without_cdn_or_emojis(): void
    {
        $view419 = view('errors.419')->render();
        $this->assertStringContainsString('Sesi Halaman Kedaluwarsa', $view419);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $view419);
        $this->assertStringNotContainsString('⏳', $view419);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('Terjadi Kesalahan Sistem', $view500);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $view500);
        $this->assertStringNotContainsString('⚠️', $view500);
    }
```

- [ ] **Step 2: Run `LandingAndErrorPagesTest` to verify the new tests fail**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/LandingAndErrorPagesTest.php
```
Expected: FAIL (`cdn.tailwindcss.com` found, `Layanan Publik & Ruang Tunggu` missing).

- [ ] **Step 3: Refactor `resources/views/welcome.blade.php` into the 2-Zone Civic Institutional Portal**

Replace `resources/views/welcome.blade.php`:

```blade
@extends('layouts.portal')

@section('title', 'Sistem Informasi Antrean & Rekapitulasi PST — BPS Kota Tasikmalaya')

@section('header-actions')
    <span class="hidden md:inline-flex items-center gap-1.5 rounded-md border border-white/15 bg-white/10 px-2.5 py-1 text-xs font-medium text-slate-200 tabular-nums-strict">
        <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-400" />
        <span>Jam Layanan: 08.00–15.30 WIB</span>
    </span>

    @auth
        @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}"
               class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-[#0A2540] hover:bg-slate-100">
                Panel Admin
            </a>
        @else
            <a href="{{ route('counter.index') }}"
               class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-[#0A2540] hover:bg-slate-100">
                Meja Loket
            </a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-md bg-red-700 hover:bg-red-800 px-3 py-1.5 text-xs font-semibold text-white">
                Keluar
            </button>
        </form>
    @else
        <a href="{{ route('login') }}"
           class="rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3.5 py-1.5 text-xs font-semibold text-white">
            Masuk Petugas / Admin
        </a>
    @endauth
@endsection

@section('content')
    <div class="space-y-10">
        {{-- Institutional Hero --}}
        <section class="bg-white border border-[#DCE3EC] rounded-xl p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl space-y-2.5">
                <x-badge tone="cobalt">Portal Sistem Antrean Terpadu</x-badge>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#0F172A]">
                    Selamat Datang di Pelayanan Statistik Terpadu (PST)
                </h1>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Sistem antrean dan buku tamu digital resmi <strong class="font-semibold text-[#0F172A]">Badan Pusat Statistik Kota Tasikmalaya</strong> untuk pengambilan tiket mandiri, layar panggilan ruang tunggu, pelayanan loket, dan rekapitulasi kunjungan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('kiosk.index') }}"
                   class="inline-flex items-center gap-2 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white">
                    <x-icon name="ticket" class="w-4 h-4" />
                    <span>Buka Anjungan Kiosk</span>
                </a>
                <a href="{{ route('display.index') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-4 py-2.5 text-sm font-semibold text-[#0F172A]">
                    <x-icon name="tv" class="w-4 h-4" />
                    <span>Layar Display TV</span>
                </a>
            </div>
        </section>

        {{-- Zona 1: Layanan Publik & Ruang Tunggu --}}
        <section class="space-y-4" aria-labelledby="zone-public-heading">
            <div class="flex items-center justify-between border-b border-[#DCE3EC] pb-2">
                <div>
                    <h2 id="zone-public-heading" class="text-base font-bold text-[#0F172A]">Layanan Publik &amp; Ruang Tunggu</h2>
                    <p class="text-xs text-[#475569]">Perangkat akses terbuka untuk pengunjung dan monitor ruang tunggu PST</p>
                </div>
                <x-badge tone="green">Akses Publik</x-badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <a href="{{ route('kiosk.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0B4F9C] p-6 flex flex-col justify-between transition-colors">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                            <x-icon name="ticket" class="w-5 h-5" />
                        </span>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#0B4F9C]">Pengunjung PST</span>
                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#0B4F9C] transition-colors">
                                Anjungan Kiosk Tiket
                            </h3>
                            <p class="text-xs text-[#475569] leading-relaxed">
                                Layar sentuh mandiri untuk memilih 4 kategori layanan statistik dan mencetak nomor antrean termal.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-[#EDF1F6] flex items-center justify-between text-xs font-semibold text-[#0B4F9C]">
                        <span>Buka Anjungan Kiosk</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </div>
                </a>

                <a href="{{ route('display.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#15803D] p-6 flex flex-col justify-between transition-colors">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center shrink-0">
                            <x-icon name="tv" class="w-5 h-5" />
                        </span>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#15803D]">Monitor Ruang Tunggu</span>
                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#15803D] transition-colors">
                                Layar Monitor TV
                            </h3>
                            <p class="text-xs text-[#475569] leading-relaxed">
                                Papan status panggilan loket realtime dengan nada panggil sintetis, riwayat panggilan, dan ringkasan tunggu.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-[#EDF1F6] flex items-center justify-between text-xs font-semibold text-[#15803D]">
                        <span>Buka Layar Display</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </div>
                </a>
            </div>
        </section>

        {{-- Zona 2: Operasional Staf & Pimpinan --}}
        <section class="space-y-4" aria-labelledby="zone-staff-heading">
            <div class="flex items-center justify-between border-b border-[#DCE3EC] pb-2">
                <div>
                    <h2 id="zone-staff-heading" class="text-base font-bold text-[#0F172A]">Operasional Staf &amp; Pimpinan</h2>
                    <p class="text-xs text-[#475569]">Area kerja internal terotentikasi untuk petugas loket dan administrator PST</p>
                </div>
                <x-badge tone="neutral">Perlu Masuk</x-badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <a href="{{ route('counter.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0A2540] p-5 flex items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-lg bg-[#EDF1F6] border border-[#DCE3EC] text-[#0A2540] flex items-center justify-center shrink-0">
                            <x-icon name="monitor" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#0F172A]">Meja Kerja Pelayanan</h3>
                                <span class="text-[11px] font-medium text-[#64748B]">· Petugas Loket</span>
                            </div>
                            <p class="text-xs text-[#475569] mt-1 leading-relaxed">
                                Panggil antrean FIFO, recall suara, pengisian buku tamu digital UU PDP, dan penyelesaian tiket.
                            </p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-[#475569] group-hover:text-[#0A2540] shrink-0" />
                </a>

                <a href="{{ route('admin.dashboard') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0A2540] p-5 flex items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-lg bg-[#EDF1F6] border border-[#DCE3EC] text-[#0A2540] flex items-center justify-center shrink-0">
                            <x-icon name="chart" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#0F172A]">Dashboard &amp; Rekap</h3>
                                <span class="text-[11px] font-medium text-[#64748B]">· Pimpinan &amp; Admin</span>
                            </div>
                            <p class="text-xs text-[#475569] mt-1 leading-relaxed">
                                Statistik MoM, grafik tren &amp; jam sibuk, manajemen layanan/loket/akun, serta ekspor CSV &amp; PDF kop BPS.
                            </p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-[#475569] group-hover:text-[#0A2540] shrink-0" />
                </a>
            </div>
        </section>

        {{-- 4 Pilar Layanan PST BPS --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-6" aria-labelledby="pillars-heading">
            <div class="mb-5">
                <h2 id="pillars-heading" class="text-base font-bold text-[#0F172A]">4 Pilar Layanan Statistik Terpadu (PST) BPS</h2>
                <p class="text-xs text-[#475569] mt-0.5">Standar Pelayanan Publik sesuai amanat UU No. 16 Tahun 1997 tentang Statistik</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#0B4F9C] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#0B4F9C] text-white font-mono font-bold text-xs flex items-center justify-center">A</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Konsultasi Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Konsultasi data statistik sektoral, konsep definisi, dan metodologi survei/sensus bagi peneliti dan masyarakat.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#15803D] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#15803D] text-white font-mono font-bold text-xs flex items-center justify-center">B</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Rekomendasi Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Penerbitan surat rekomendasi kegiatan statistik sektoral (Romantik) untuk OPD dan instansi pemerintah daerah.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#C2410C] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#C2410C] text-white font-mono font-bold text-xs flex items-center justify-center">C</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Data Mikro &amp; Publikasi</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Permohonan raw dataset mikro (Susenas, Sakernas, Sensus) serta salinan publikasi statistik resmi BPS.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#0F766E] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#0F766E] text-white font-mono font-bold text-xs flex items-center justify-center">D</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Perpustakaan Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Ruang baca koleksi pustaka, buletin indikator strategis daerah, dan literatur statistik cetak di kantor BPS.
                    </p>
                </div>
            </div>
        </section>

        {{-- Strip Kepatuhan UU PDP --}}
        <section class="rounded-xl border border-[#DCE3EC] bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-[#EDF1F6] text-[#0B4F9C] flex items-center justify-center shrink-0">
                    <x-icon name="shield" class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-xs font-bold text-[#0F172A]">Kepatuhan UU No. 27 Tahun 2022 (Pelindungan Data Pribadi)</h2>
                    <p class="text-xs text-[#475569] mt-0.5">Seluruh data identitas tamu di buku tamu digital dilindungi kerahasiaannya dan hanya digunakan untuk evaluasi standar pelayanan publik.</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-[#64748B] shrink-0">PST BPS Kota Tasikmalaya</span>
        </section>
    </div>
@endsection
```

- [ ] **Step 4: Refactor `resources/views/auth/login.blade.php` and `resources/views/auth/change-password.blade.php`**

Replace `resources/views/auth/login.blade.php`:

```blade
@extends('layouts.portal')

@section('title', 'Masuk Sistem — PST BPS Kota Tasikmalaya')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>

        <div class="p-6 sm:p-8">
            <div class="mb-6">
                <x-bps-brand
                    title="Pelayanan Statistik Terpadu"
                    subtitle="BPS Kota Tasikmalaya"
                    :href="route('home')"
                />
                <h1 class="mt-6 text-xl font-bold text-[#0F172A]">Masuk ke Sistem Operasional</h1>
                <p class="mt-1 text-xs text-[#475569]">Gunakan akun petugas loket atau administrator yang terdaftar.</p>
            </div>

            @if($errors->any())
                <div role="alert" class="mb-5 flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-xs text-red-800">
                    <x-icon name="warning" class="w-4 h-4 shrink-0 mt-0.5 text-red-600" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Nama Pengguna (Username)
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           autocomplete="username"
                           autocorrect="off"
                           autocapitalize="none"
                           spellcheck="false"
                           required
                           autofocus
                           placeholder="Contoh: petugas1 atau admin"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder:text-[#64748B]">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               autocomplete="current-password"
                               required
                               placeholder="Masukkan kata sandi"
                               class="w-full rounded-md border border-[#CBD5E1] bg-white pl-3.5 pr-11 py-2.5 text-sm text-[#0F172A] placeholder:text-[#64748B]">
                        <button type="button"
                                data-password-toggle="password"
                                aria-pressed="false"
                                aria-label="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-[#64748B] hover:text-[#0F172A]">
                            <span data-eye-show><x-icon name="eye" class="w-4 h-4" /></span>
                            <span data-eye-hide class="hidden"><x-icon name="eye-off" class="w-4 h-4" /></span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white transition-colors">
                    Masuk ke Sistem
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex items-center justify-between text-xs">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-semibold text-[#0B4F9C] hover:underline">
                    <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    <span>Kembali ke Beranda</span>
                </a>
                <span class="text-[#64748B]">Akses Terotentikasi</span>
            </div>
        </div>
    </div>
@endsection
```

Replace `resources/views/auth/change-password.blade.php`:

```blade
@extends('layouts.portal')

@section('title', 'Ubah Kata Sandi — PST BPS Kota Tasikmalaya')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-10 flex-1 flex flex-col justify-center')

@section('header-actions')
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-semibold text-white">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>Kembali ke Dashboard</span>
        </a>
    @else
        <a href="{{ route('counter.index') }}"
           class="inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-semibold text-white">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>Kembali ke Meja Loket</span>
        </a>
    @endif
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-[#EDF1F6]">
                <span class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                    <x-icon name="lock" class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-lg font-bold text-[#0F172A]">Ubah Kata Sandi</h1>
                    <p class="text-xs text-[#475569]">Akun aktif: <strong class="font-mono text-[#0F172A]">{{ Auth::user()->username }}</strong></p>
                </div>
            </div>

            @if(session('success'))
                <div role="status" class="mt-5 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-medium text-emerald-900">
                    <x-icon name="check" class="w-4 h-4 text-[#15803D]" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-xs text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi Saat Ini <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           autocomplete="current-password"
                           required
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi Baru <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="new-password"
                           minlength="6"
                           required
                           aria-describedby="new-password-help"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                    <p id="new-password-help" class="mt-1 text-[11px] text-[#64748B]">Minimal 6 karakter.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Konfirmasi Kata Sandi Baru <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           autocomplete="new-password"
                           minlength="6"
                           required
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white transition-colors">
                        Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
```

- [ ] **Step 5: Refactor all 4 error views (`403`, `404`, `419`, `500`) using `layouts/portal`**

Replace `resources/views/errors/403.blade.php`:

```blade
@extends('layouts.portal')

@section('title', '403 — Akses Ditolak | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full bg-red-700" aria-hidden="true"></div>
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center justify-center mx-auto mb-4">
                <x-icon name="shield" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">403</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Akses Ditolak</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                {{ $exception->getMessage() ?: 'Mohon maaf, Anda tidak memiliki hak akses atau wewenang untuk membuka halaman ini.' }}
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                            Kembali ke Dashboard Admin
                        </a>
                    @else
                        <a href="{{ route('counter.index') }}"
                           class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                            Kembali ke Meja Kerja Loket
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                        Masuk ke Sistem
                    </a>
                @endauth
                <a href="{{ route('home') }}"
                   class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                    Kembali ke Beranda Utama
                </a>
            </div>
        </div>
    </div>
@endsection
```

Replace `resources/views/errors/404.blade.php`:

```blade
@extends('layouts.portal')

@section('title', '404 — Halaman Tidak Ditemukan | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full bg-[#0B4F9C]" aria-hidden="true"></div>
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center mx-auto mb-4">
                <x-icon name="search" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">404</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Halaman Tidak Ditemukan</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                Mohon maaf, tautan atau halaman yang Anda tuju tidak ditemukan atau telah dipindahkan ke alamat lain.
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                <a href="{{ route('home') }}"
                   class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                    Kembali ke Beranda Utama
                </a>
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                            Buka Dashboard Admin
                        </a>
                    @else
                        <a href="{{ route('counter.index') }}"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                            Buka Meja Kerja Loket
                        </a>
                    @endif
                @endauth
                <a href="{{ route('kiosk.index') }}"
                   class="w-full rounded-md border border-[#DCE3EC] bg-[#F4F6F9] hover:bg-[#EDF1F6] py-2.5 text-xs font-semibold text-[#334155]">
                    Anjungan Kiosk Tiket
                </a>
            </div>
        </div>
    </div>
@endsection
```

Replace `resources/views/errors/419.blade.php`:

```blade
@extends('layouts.portal')

@section('title', '419 — Sesi Halaman Kedaluwarsa | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full bg-[#C2410C]" aria-hidden="true"></div>
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-orange-50 border border-orange-200 text-[#C2410C] flex items-center justify-center mx-auto mb-4">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">419</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Sesi Halaman Kedaluwarsa</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                Halaman ini telah terbuka terlalu lama sehingga token keamanan formulir berakhir. Silakan muat ulang halaman untuk memperbarui sesi Anda.
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                <button type="button" onclick="window.location.reload()"
                        class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                    Perbarui &amp; Muat Ulang Halaman
                </button>
                <a href="{{ route('home') }}"
                   class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                    Kembali ke Beranda Utama
                </a>
            </div>
        </div>
    </div>
@endsection
```

Replace `resources/views/errors/500.blade.php`:

```blade
@extends('layouts.portal')

@section('title', '500 — Terjadi Kesalahan Sistem | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full bg-red-700" aria-hidden="true"></div>
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center justify-center mx-auto mb-4">
                <x-icon name="warning" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">500</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Terjadi Kesalahan Sistem</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                Sistem mendeteksi kendala pada server internal saat memproses permintaan Anda. Silakan muat ulang halaman atau hubungi administrator PST BPS.
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                <button type="button" onclick="window.location.reload()"
                        class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                    Muat Ulang Halaman
                </button>
                <a href="{{ route('home') }}"
                   class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                    Kembali ke Beranda Utama
                </a>
            </div>
        </div>
    </div>
@endsection
```

- [ ] **Step 6: Rebuild Vite CSS and run `LandingAndErrorPagesTest` + `RoleSecurityTest`**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test tests/Feature/LandingAndErrorPagesTest.php tests/Feature/RoleSecurityTest.php"
```
Expected: All tests in both files PASS.

- [ ] **Step 7: Commit Task 3**

```bash
git add resources/views/welcome.blade.php resources/views/auth resources/views/errors tests/Feature/LandingAndErrorPagesTest.php
git commit -m "feat(ui): redesign portal, auth, and error pages with civic precision layout"
```

---

### Task 4: Anjungan Kiosk Layar Sentuh (`/kiosk`) & Thermal Print Fix

**Files:**
- Modify: `resources/views/kiosk/index.blade.php`
- Modify: `tests/Feature/KioskTicketTest.php`

**Interfaces:**
- Consumes: `layouts/screen` (`screenTheme = 'light'`), `<x-bps-brand>`, `<x-icon>`, `window.PstUi.openModal/closeModal`, `.print-only` CSS print isolation.
- Preserves DOM hooks & endpoints: `POST /kiosk/ticket`, `#services-grid`, `#ticket-print-area`, `#print-ticket-code`, `#print-service-name`, `#print-ticket-time`, `#print-ticket-ahead`.

- [ ] **Step 1: Extend `tests/Feature/KioskTicketTest.php` with failing assertions for print-area CSS fix, native `<dialog>`, and zero CDN/emoji**

Append to `tests/Feature/KioskTicketTest.php`:

```php
    public function test_kiosk_page_fixes_print_visibility_uses_native_dialogs_and_has_no_cdn_or_emojis(): void
    {
        $response = $this->get('/kiosk');
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('⚠️', $html);
        $this->assertStringContainsString('<dialog id="ticket-modal"', $html);
        $this->assertStringContainsString('<dialog id="error-modal"', $html);
        $this->assertStringContainsString('id="ticket-print-area" class="print-only"', $html);
        $this->assertStringNotContainsString('id="ticket-print-area" class="hidden"', $html);
    }
```

- [ ] **Step 2: Run `KioskTicketTest` to confirm the new test fails**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/KioskTicketTest.php
```
Expected: FAIL (`cdn.tailwindcss.com` present, `#ticket-print-area` still uses `class="hidden"`).

- [ ] **Step 3: Refactor `resources/views/kiosk/index.blade.php` using `layouts/screen`, deterministic A–D color coding, native `<dialog>`, and `.print-only` thermal receipt**

Replace `resources/views/kiosk/index.blade.php`:

```blade
@extends('layouts.screen', ['screenTheme' => 'light'])

@section('title', 'Anjungan Tiket Mandiri — PST BPS Kota Tasikmalaya')

@php
    $servicePalette = [
        'A' => ['bar' => 'border-l-[#0B4F9C]', 'badge' => 'bg-[#0B4F9C] text-white', 'tag' => 'text-[#0B4F9C] bg-blue-50 border-blue-200'],
        'B' => ['bar' => 'border-l-[#15803D]', 'badge' => 'bg-[#15803D] text-white', 'tag' => 'text-[#15803D] bg-emerald-50 border-emerald-200'],
        'C' => ['bar' => 'border-l-[#C2410C]', 'badge' => 'bg-[#C2410C] text-white', 'tag' => 'text-[#C2410C] bg-orange-50 border-orange-200'],
        'D' => ['bar' => 'border-l-[#0F766E]', 'badge' => 'bg-[#0F766E] text-white', 'tag' => 'text-[#0F766E] bg-teal-50 border-teal-200'],
    ];
@endphp

@section('content')
    <header class="bg-[#0A2540] text-white border-b border-white/10">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <x-bps-brand
                title="BADAN PUSAT STATISTIK"
                subtitle="Pelayanan Statistik Terpadu (PST) Kota Tasikmalaya"
                :dark="true"
                size="lg"
            />
            <div class="text-right rounded-lg border border-white/15 bg-white/5 px-4 py-2 tabular-nums-strict">
                <div id="live-clock" class="text-2xl font-mono font-bold tracking-wider text-white">--:--:--</div>
                <div id="live-date" class="text-xs text-slate-300">--</div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 flex-1 flex flex-col justify-center w-full">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-[#DCE3EC] pb-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-[#0B4F9C]">Anjungan Mandiri Layar Sentuh</span>
                <h1 class="text-2xl md:text-3xl font-bold text-[#0F172A] mt-1">Pilih Layanan Statistik Anda</h1>
            </div>
            <p class="text-sm text-[#475569]">Sentuh salah satu kategori di bawah untuk menerbitkan nomor antrean.</p>
        </div>

        <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($services as $service)
                @php
                    $prefix = strtoupper($service->code_prefix);
                    $tone = $servicePalette[$prefix] ?? $servicePalette['A'];
                @endphp
                <button type="button"
                        id="btn-service-{{ $service->id }}"
                        data-service-id="{{ $service->id }}"
                        class="service-btn group w-full min-h-[144px] rounded-2xl border border-[#DCE3EC] border-l-4 {{ $tone['bar'] }} bg-white p-6 text-left transition-colors hover:border-[#0B4F9C] hover:bg-[#F8FAFC] active:bg-[#EDF1F6] flex items-center gap-5">
                    <span class="w-20 h-20 rounded-xl {{ $tone['badge'] }} flex items-center justify-center font-mono font-bold text-4xl shrink-0 tabular-nums-strict">
                        {{ $service->code_prefix }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="inline-block rounded-md border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider {{ $tone['tag'] }}">
                            Layanan {{ $service->code_prefix }}
                        </span>
                        <span class="block text-xl font-bold text-[#0F172A] mt-2">
                            {{ $service->name }}
                        </span>
                        <span class="block text-xs sm:text-sm text-[#475569] mt-1 line-clamp-2">
                            {{ $service->description ?? 'Layanan konsultasi dan informasi statistik terpadu.' }}
                        </span>
                    </span>
                </button>
            @empty
                <div class="col-span-2 rounded-xl border border-[#DCE3EC] bg-white p-10 text-center">
                    <p class="text-sm font-medium text-[#475569]">Belum ada layanan aktif yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <footer class="bg-white border-t border-[#DCE3EC] py-3.5 px-6 text-xs text-[#475569]">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© 2026 Badan Pusat Statistik Kota Tasikmalaya · Pelayanan Statistik Terpadu</span>
            <x-badge tone="green">Anjungan Siap Operasional</x-badge>
        </div>
    </footer>

    {{-- Processing Overlay --}}
    <div id="loading-overlay" role="status" aria-live="polite" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl border border-[#DCE3EC] p-6 max-w-xs w-full text-center shadow-lg">
            <div class="w-10 h-10 border-4 border-[#DCE3EC] border-t-[#0B4F9C] rounded-full animate-spin mx-auto mb-3"></div>
            <h2 class="text-base font-bold text-[#0F172A]">Menerbitkan Tiket...</h2>
            <p class="text-xs text-[#475569] mt-1">Mohon tunggu, sistem sedang menyiapkan nomor antrean Anda.</p>
        </div>
    </div>

    {{-- Native Dialog: Virtual Ticket Preview --}}
    <dialog id="ticket-modal" closedby="any" aria-labelledby="ticket-modal-title" class="pst-dialog max-w-sm">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center mx-auto mb-3">
                <x-icon name="check" class="w-6 h-6" />
            </div>
            <h1 id="ticket-modal-title" class="text-xl font-bold text-[#0F172A]">Tiket Diterbitkan</h1>
            <p class="text-xs text-[#475569] mt-1">Silakan ambil struk tiket dan tunggu panggilan pada layar ruang tunggu.</p>

            <div class="my-5 rounded-lg border border-dashed border-[#CBD5E1] bg-[#F8FAFC] p-4 text-left font-mono text-xs text-[#334155]">
                <div class="text-center font-bold pb-2.5 border-b border-dashed border-[#CBD5E1]">
                    BADAN PUSAT STATISTIK
                    <span class="block font-normal text-[11px] text-[#64748B]">PST BPS KOTA TASIKMALAYA</span>
                </div>
                <div class="text-center my-3.5">
                    <div class="font-sans text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Nomor Antrean</div>
                    <div id="modal-ticket-code" class="text-4xl font-bold text-[#0A2540] my-1 tracking-wider tabular-nums-strict">A-001</div>
                    <div id="modal-service-name" class="font-sans font-semibold text-sm text-[#0F172A]">Konsultasi Statistik</div>
                </div>
                <div class="border-t border-dashed border-[#CBD5E1] pt-2.5 space-y-1.5 text-xs tabular-nums-strict">
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Waktu:</span>
                        <span id="modal-ticket-time" class="font-semibold text-[#0F172A]">-</span>
                    </div>
                    <div class="flex justify-between rounded bg-orange-50 border border-orange-200 px-2 py-1 font-semibold text-[#C2410C]">
                        <span>Antrean di Depan:</span>
                        <span id="modal-ticket-ahead">0 Orang</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="w-full bg-[#EDF1F6] rounded-full h-1.5 overflow-hidden">
                    <div id="countdown-progress" class="bg-[#0B4F9C] h-1.5 transition-all duration-1000" style="width: 100%;"></div>
                </div>
                <p class="text-xs text-[#475569] mt-1.5 tabular-nums-strict">
                    Kembali otomatis dalam <span id="countdown-number" class="font-bold text-[#0F172A]">5</span> detik
                </p>
            </div>

            <button type="button" id="btn-close-ticket-modal"
                    class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-3 text-sm font-semibold text-white">
                Selesai / Kembali Sekarang
            </button>
        </div>
    </dialog>

    {{-- Native Dialog: Error Feedback --}}
    <dialog id="error-modal" closedby="any" aria-labelledby="error-modal-title" class="pst-dialog max-w-sm">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 border border-red-200 text-red-700 flex items-center justify-center mx-auto mb-3">
                <x-icon name="warning" class="w-6 h-6" />
            </div>
            <h1 id="error-modal-title" class="text-lg font-bold text-[#0F172A]">Gagal Memproses Tiket</h1>
            <p id="error-modal-message" class="text-xs text-[#475569] my-3 leading-relaxed">
                Gagal memproses tiket antrean. Silakan coba kembali atau hubungi petugas.
            </p>
            <button type="button" id="btn-close-error-modal"
                    class="w-full rounded-md bg-[#0A2540] hover:bg-[#0F172A] py-2.5 text-sm font-semibold text-white">
                Tutup &amp; Coba Lagi
            </button>
        </div>
    </dialog>

    {{-- Physical Thermal Receipt Area (58mm / 80mm @media print) --}}
    <div id="ticket-print-area" class="print-only">
        <div style="text-align: center; font-weight: bold; padding-bottom: 4px; border-bottom: 1px dashed #000;">
            BADAN PUSAT STATISTIK<br>
            PST BPS KOTA TASIKMALAYA
        </div>
        <div style="text-align: center; margin: 10px 0;">
            <div style="font-size: 10px; font-weight: bold;">NOMOR ANTREAN</div>
            <div id="print-ticket-code" style="font-size: 32px; font-weight: 900; margin: 4px 0; letter-spacing: 1px;">A-001</div>
            <div id="print-service-name" style="font-size: 12px; font-weight: bold;">Konsultasi Statistik</div>
        </div>
        <div style="border-top: 1px dashed #000; padding-top: 6px; font-size: 10px; line-height: 1.4;">
            <div>Waktu: <span id="print-ticket-time">--/--/---- --:-- WIB</span></div>
            <div>Antrean di Depan: <span id="print-ticket-ahead">0 Orang</span></div>
        </div>
        <div style="text-align: center; font-size: 9px; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px;">
            Silakan menunggu hingga nomor Anda dipanggil di meja loket.<br>
            Terima kasih atas kunjungan Anda.
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const clockEl = document.getElementById('live-clock');
        const dateEl = document.getElementById('live-date');
        const loadingOverlay = document.getElementById('loading-overlay');
        const ticketDialog = document.getElementById('ticket-modal');
        const errorDialog = document.getElementById('error-modal');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let isSubmitting = false;
        let countdownInterval = null;

        function updateClock() {
            const now = new Date();
            if (clockEl) {
                clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
        updateClock();
        window.setInterval(updateClock, 1000);

        function setButtonsDisabled(disabled) {
            document.querySelectorAll('.service-btn').forEach((btn) => {
                btn.disabled = disabled;
                btn.classList.toggle('opacity-50', disabled);
                btn.classList.toggle('pointer-events-none', disabled);
            });
        }

        function setLoadingVisible(visible) {
            if (!loadingOverlay) return;
            loadingOverlay.classList.toggle('hidden', !visible);
            loadingOverlay.classList.toggle('flex', visible);
        }

        function resetKioskState() {
            window.clearInterval(countdownInterval);
            isSubmitting = false;
            setButtonsDisabled(false);
        }

        ticketDialog?.addEventListener('close', resetKioskState);
        errorDialog?.addEventListener('close', resetKioskState);

        document.getElementById('btn-close-ticket-modal')?.addEventListener('click', () => ticketDialog?.close(''));
        document.getElementById('btn-close-error-modal')?.addEventListener('click', () => errorDialog?.close(''));

        function startCountdown() {
            window.clearInterval(countdownInterval);
            let remaining = 5;
            const progressEl = document.getElementById('countdown-progress');
            const numberEl = document.getElementById('countdown-number');

            if (numberEl) numberEl.textContent = String(remaining);
            if (progressEl) progressEl.style.width = '100%';

            countdownInterval = window.setInterval(() => {
                remaining -= 1;
                if (numberEl) numberEl.textContent = String(Math.max(0, remaining));
                if (progressEl) progressEl.style.width = `${Math.max(0, (remaining / 5) * 100)}%`;
                if (remaining <= 0) {
                    window.clearInterval(countdownInterval);
                    if (ticketDialog?.open) ticketDialog.close('');
                }
            }, 1000);
        }

        async function takeTicket(serviceId) {
            if (isSubmitting) return;
            isSubmitting = true;
            setButtonsDisabled(true);
            setLoadingVisible(true);

            try {
                const response = await fetch("{{ route('kiosk.ticket') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ service_id: serviceId }),
                });
                const result = await response.json();
                setLoadingVisible(false);

                if (response.ok && result.status === 'success') {
                    const ticket = result.data;
                    document.getElementById('modal-ticket-code').textContent = ticket.ticket_code;
                    document.getElementById('modal-service-name').textContent = ticket.service_name;
                    document.getElementById('modal-ticket-time').textContent = ticket.created_at;
                    document.getElementById('modal-ticket-ahead').textContent = `${ticket.waiting_ahead} Orang`;

                    document.getElementById('print-ticket-code').textContent = ticket.ticket_code;
                    document.getElementById('print-service-name').textContent = ticket.service_name;
                    document.getElementById('print-ticket-time').textContent = ticket.created_at;
                    document.getElementById('print-ticket-ahead').textContent = `${ticket.waiting_ahead} Orang`;

                    ticketDialog?.showModal();
                    if (!ticket.mock_print) {
                        try {
                            window.print();
                        } catch (e) {
                            console.warn('Silent print invocation:', e);
                        }
                    }
                    startCountdown();
                } else {
                    document.getElementById('error-modal-message').textContent =
                        result.message || 'Gagal menerbitkan nomor antrean. Silakan coba kembali.';
                    errorDialog?.showModal();
                }
            } catch (err) {
                console.error(err);
                setLoadingVisible(false);
                document.getElementById('error-modal-message').textContent =
                    'Terjadi kendala jaringan saat berkomunikasi dengan peladen sistem antrean.';
                errorDialog?.showModal();
            }
        }

        document.querySelectorAll('.service-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                takeTicket(Number(btn.dataset.serviceId));
            });
        });
    });
</script>
@endpush
```

- [ ] **Step 4: Build Vite assets and run `KioskTicketTest`**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test tests/Feature/KioskTicketTest.php"
```
Expected: All tests in `KioskTicketTest` PASS.

- [ ] **Step 5: Commit Task 4**

```bash
git add resources/views/kiosk/index.blade.php tests/Feature/KioskTicketTest.php
git commit -m "feat(ui): redesign kiosk touchscreen with native dialogs and fix thermal print visibility"
```

---

### Task 5: Layar Monitor TV (`/display`) — High-Contrast Dark Board & State Sync Fix

**Files:**
- Modify: `resources/views/display/index.blade.php`
- Modify: `tests/Feature/DisplayStreamTest.php`

**Interfaces:**
- Consumes: `layouts/screen` (`screenTheme = 'dark'`), `<x-bps-brand>`, `<x-icon>`, `.tv-ticker-track`, `.tv-card-calling`.
- Preserves DOM hooks & endpoints: `GET /display/stream` (`initial-state`, `queue-called`), `GET /display/poll-updates`, Web Audio chime + Web Speech announcer FIFO queue, audio unlock `#btn-enable-audio`, hidden debug controls `#audio-debug-panel` toggled via `Alt+D` or subtle footer button.

- [ ] **Step 1: Extend `tests/Feature/DisplayStreamTest.php` with failing checks for dark board theme, hidden debug panel, safe DOM updates, and zero CDN/emojis/bounce/ping**

Append to `tests/Feature/DisplayStreamTest.php`:

```php
    public function test_display_page_uses_dark_board_hidden_debug_panel_and_no_cdn_or_emojis(): void
    {
        $response = $this->get('/display');
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('animate-bounce', $html);
        $this->assertStringNotContainsString('animate-ping', $html);
        $this->assertStringNotContainsString('🔊', $html);
        $this->assertStringNotContainsString('📊', $html);
        $this->assertStringNotContainsString('🕒', $html);
        $this->assertStringContainsString('id="audio-debug-panel" class="hidden', $html);
        $this->assertStringContainsString('resetAllCounterCards', $html);
        $this->assertStringContainsString('renderRecentCalls', $html);
    }
```

- [ ] **Step 2: Run `DisplayStreamTest` to verify the new test fails**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/DisplayStreamTest.php
```
Expected: FAIL (`cdn.tailwindcss.com`, `animate-bounce`, emojis present; `resetAllCounterCards` absent).

- [ ] **Step 3: Refactor `resources/views/display/index.blade.php` with clean dark board, CSS ticker, hidden debug drawer (`Alt+D`), safe `textContent` rendering, and counter reset sync**

Replace `resources/views/display/index.blade.php`:

```blade
@extends('layouts.screen', ['screenTheme' => 'dark'])

@section('title', 'Layar Monitor Antrean Ruang Tunggu — PST BPS Kota Tasikmalaya')

@section('content')
    <header class="bg-[#0D1420] border-b border-[#1F2937]">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>
        <div class="max-w-[1700px] mx-auto px-6 py-3.5 flex flex-wrap items-center justify-between gap-4">
            <x-bps-brand
                title="BADAN PUSAT STATISTIK"
                subtitle="Pelayanan Statistik Terpadu · Informasi Antrean Ruang Tunggu"
                :dark="true"
                size="lg"
            />

            <div class="flex items-center gap-3">
                <button id="btn-enable-audio" type="button"
                        class="inline-flex items-center gap-2 rounded-md bg-[#F59E0B] hover:bg-amber-400 px-3.5 py-2 text-xs font-bold text-[#090E17] transition-colors">
                    <x-icon name="volume-on" class="w-4 h-4" />
                    <span>AKTIFKAN SUARA TV</span>
                </button>

                <div id="audio-active-badge"
                     class="hidden items-center gap-1.5 rounded-md border border-emerald-500/40 bg-emerald-950/60 px-3 py-1.5 text-xs font-semibold text-emerald-300">
                    <x-icon name="volume-on" class="w-4 h-4" />
                    <span>Audio Aktif</span>
                </div>

                <span id="connection-status"
                      class="inline-flex items-center gap-1.5 rounded-md border border-emerald-500/40 bg-emerald-950/60 px-3 py-1.5 text-xs font-semibold text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400" aria-hidden="true"></span>
                    <span>SSE Terhubung</span>
                </span>

                <div class="text-right rounded-lg border border-[#1F2937] bg-[#111827] px-4 py-1.5 tabular-nums-strict">
                    <div id="display-clock" class="text-2xl font-mono font-bold tracking-wider text-[#F59E0B]">--:--:--</div>
                    <div id="display-date" class="text-[11px] text-slate-300">--</div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-[1700px] w-full mx-auto px-6 py-5 flex flex-col gap-5">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch flex-1">
            {{-- Left 2/3: Active Counter Cards + Service Waiting Strip --}}
            <div class="lg:col-span-2 flex flex-col justify-between gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 flex-1">
                    @foreach($counters as $counter)
                        <section id="counter-card-{{ $counter->counter_number }}"
                                 data-counter-card="{{ $counter->counter_number }}"
                                 aria-labelledby="counter-heading-{{ $counter->counter_number }}"
                                 class="rounded-xl border border-[#1F2937] bg-[#111827] p-5 flex flex-col justify-between transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <h2 id="counter-heading-{{ $counter->counter_number }}" class="text-lg font-bold tracking-wide text-slate-100 uppercase">
                                    {{ $counter->name }}
                                </h2>
                                <span id="counter-badge-{{ $counter->counter_number }}"
                                      class="rounded-md border border-[#374151] bg-[#1F2937] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-300">
                                    Menunggu
                                </span>
                            </div>

                            <div class="my-4 rounded-lg border border-[#1F2937] bg-[#090E17] py-6 px-4 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Nomor Antrean</div>
                                <div id="counter-ticket-{{ $counter->counter_number }}"
                                     class="text-6xl xl:text-7xl font-mono font-bold text-[#F59E0B] my-2 tracking-wider tabular-nums-strict">
                                    ---
                                </div>
                                <div id="counter-service-{{ $counter->counter_number }}"
                                     class="text-sm font-medium text-slate-300 truncate">
                                    Belum ada antrean
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>

                {{-- Waiting Count Strip per Service --}}
                <section aria-labelledby="waiting-strip-heading" class="rounded-xl border border-[#1F2937] bg-[#111827] p-4">
                    <h2 id="waiting-strip-heading" class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">
                        Antrean Menunggu per Layanan PST Hari Ini
                    </h2>
                    <div id="service-waiting-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($services as $srv)
                            <div id="service-card-{{ $srv->id }}" class="rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="inline-block rounded bg-[#0B4F9C] px-1.5 py-0.5 font-mono text-[11px] font-bold text-white">
                                        {{ $srv->code_prefix }}
                                    </span>
                                    <div class="text-xs font-medium text-slate-300 truncate mt-1" title="{{ $srv->name }}">
                                        {{ $srv->name }}
                                    </div>
                                </div>
                                <span id="service-count-{{ $srv->id }}" class="text-2xl font-mono font-bold text-[#F59E0B] tabular-nums-strict">
                                    {{ $srv->queues_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Right 1/3: Recent 5 Calls --}}
            <aside aria-labelledby="recent-calls-heading" class="rounded-xl border border-[#1F2937] bg-[#111827] p-5 flex flex-col">
                <div class="flex items-center justify-between border-b border-[#1F2937] pb-3 mb-4">
                    <h2 id="recent-calls-heading" class="text-sm font-bold uppercase tracking-wider text-slate-200">
                        Riwayat 5 Panggilan Terakhir
                    </h2>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Realtime</span>
                </div>

                <div id="recent-calls-list" class="space-y-2.5 flex-1">
                    @forelse($recentQueues as $rq)
                        <div class="rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-xl font-mono font-bold text-[#F59E0B] tracking-wider tabular-nums-strict">{{ $rq->ticket_code }}</span>
                                <div class="text-xs text-slate-300 truncate">{{ $rq->service?->name ?? 'Layanan PST' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block rounded-md border border-blue-500/30 bg-blue-950/70 px-2.5 py-1 text-xs font-semibold text-blue-200">
                                    {{ $rq->counter?->name ?? 'Loket' }}
                                </span>
                                <div class="text-[11px] font-mono text-slate-400 mt-1 tabular-nums-strict">{{ $rq->updated_at->format('H:i') }} WIB</div>
                            </div>
                        </div>
                    @empty
                        <div id="empty-recent-notice" class="py-16 text-center text-xs text-slate-400">
                            Belum ada riwayat antrean hari ini
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>

    {{-- Ticker Footer + Subtle Technician Audio Panel Toggle --}}
    <footer class="bg-[#0D1420] border-t border-[#1F2937] px-6 py-2.5 flex items-center gap-4">
        <span class="rounded bg-[#F59E0B] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#090E17] shrink-0">
            INFO PST BPS
        </span>

        <div class="tv-ticker-viewport text-sm font-medium text-slate-200">
            <div class="tv-ticker-track">
                <span class="pr-12">Selamat datang di Pelayanan Statistik Terpadu Badan Pusat Statistik Kota Tasikmalaya. PST melayani Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Permintaan Data Mikro &amp; Publikasi, serta Perpustakaan Statistik. Harap persiapkan identitas Anda saat dipanggil ke loket.</span>
                <span class="tv-ticker-duplicate pr-12" aria-hidden="true">Selamat datang di Pelayanan Statistik Terpadu Badan Pusat Statistik Kota Tasikmalaya. PST melayani Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Permintaan Data Mikro &amp; Publikasi, serta Perpustakaan Statistik. Harap persiapkan identitas Anda saat dipanggil ke loket.</span>
            </div>
        </div>

        <button id="btn-toggle-debug"
                type="button"
                title="Pengaturan &amp; Uji Audio (Alt+D)"
                aria-label="Buka panel uji audio teknisi"
                aria-expanded="false"
                aria-controls="audio-debug-panel"
                class="rounded-md border border-[#1F2937] bg-[#111827] p-1.5 text-slate-400 hover:text-white shrink-0">
            <x-icon name="sliders" class="w-4 h-4" />
        </button>
    </footer>

    {{-- Hidden Technician Audio Test Drawer (Alt+D) --}}
    <div id="audio-debug-panel" class="hidden bg-[#090E17] border-t border-[#1F2937] px-6 py-3 text-xs text-slate-300">
        <div class="max-w-[1700px] mx-auto flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-slate-200">Uji Audio Teknisi:</span>
                <span id="audio-engine-state" class="text-[#F59E0B] font-medium">Menunggu Aktivasi</span>
                <span id="audio-now-playing" class="text-slate-400 italic"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" data-test-announce="A,1,1" class="rounded bg-[#0B4F9C] hover:bg-[#083D7A] px-2.5 py-1 font-semibold text-white">Panggil A-001 (Loket 1)</button>
                <button type="button" data-test-announce="B,15,2" class="rounded bg-[#15803D] hover:bg-emerald-700 px-2.5 py-1 font-semibold text-white">Panggil B-015 (Loket 2)</button>
                <button type="button" data-test-announce="C,105,1" class="rounded bg-[#C2410C] hover:bg-orange-700 px-2.5 py-1 font-semibold text-white">Panggil C-105 (Loket 1)</button>
                <button type="button" id="btn-test-chime" class="rounded border border-[#374151] bg-[#111827] hover:bg-[#1F2937] px-2.5 py-1 font-semibold text-slate-200">Uji Ding-Dong</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const clockEl = document.getElementById('display-clock');
        const dateEl = document.getElementById('display-date');
        const statusBadge = document.getElementById('connection-status');
        const debugPanel = document.getElementById('audio-debug-panel');
        const debugToggleBtn = document.getElementById('btn-toggle-debug');

        function updateDisplayClock() {
            const now = new Date();
            if (clockEl) clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        updateDisplayClock();
        window.setInterval(updateDisplayClock, 1000);

        let audioCtx = null;
        let isAudioUnlocked = false;

        function enableAudioEngine() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                isAudioUnlocked = true;
                document.getElementById('btn-enable-audio')?.classList.add('hidden');
                const badge = document.getElementById('audio-active-badge');
                badge?.classList.remove('hidden');
                badge?.classList.add('inline-flex');
                const stateEl = document.getElementById('audio-engine-state');
                if (stateEl) {
                    stateEl.textContent = 'Audio Aktif';
                    stateEl.className = 'text-emerald-400 font-semibold';
                }
                playChime(0.15);
            } catch (e) {
                console.warn('Audio activation error:', e);
            }
        }

        function playChime(volume = 0.3) {
            return new Promise((resolve) => {
                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const now = audioCtx.currentTime;
                    const osc1 = audioCtx.createOscillator();
                    const gain1 = audioCtx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now);
                    gain1.gain.setValueAtTime(volume, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                    osc1.connect(gain1);
                    gain1.connect(audioCtx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.6);

                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880.0, now + 0.4);
                    gain2.gain.setValueAtTime(volume, now + 0.4);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start(now + 0.4);
                    osc2.stop(now + 1.2);

                    window.setTimeout(resolve, 1100);
                } catch (e) {
                    console.warn('Audio Context Error:', e);
                    resolve();
                }
            });
        }

        function speakText(text) {
            return new Promise((resolve) => {
                if (!('speechSynthesis' in window)) {
                    resolve();
                    return;
                }
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.88;
                utterance.pitch = 1.0;
                utterance.onend = () => resolve();
                utterance.onerror = () => resolve();
                window.speechSynthesis.speak(utterance);
            });
        }

        const audioQueue = [];
        let isPlayingAudio = false;
        const processedCallKeys = [];
        const MAX_CALL_KEYS = 200;

        function rememberCallKey(key) {
            if (processedCallKeys.includes(key)) return false;
            processedCallKeys.push(key);
            if (processedCallKeys.length > MAX_CALL_KEYS) {
                processedCallKeys.shift();
            }
            return true;
        }

        async function processAudioQueue() {
            if (isPlayingAudio || audioQueue.length === 0) return;
            isPlayingAudio = true;

            const item = audioQueue.shift();
            const textToSpeak = `Nomor antrean, ${item.prefix}, ${item.number}, menuju ${item.counterName}`;
            const playingEl = document.getElementById('audio-now-playing');
            if (playingEl) playingEl.textContent = `(${textToSpeak})`;

            await playChime(0.3);
            await speakText(textToSpeak);

            if (playingEl) playingEl.textContent = '';
            isPlayingAudio = false;

            if (audioQueue.length > 0) {
                window.setTimeout(processAudioQueue, 500);
            }
        }

        function setCounterBadge(badgeEl, status) {
            if (!badgeEl) return;
            if (status === 'calling') {
                badgeEl.textContent = 'Dipanggil';
                badgeEl.className = 'rounded-md border border-red-500/60 bg-red-600 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white';
            } else if (status === 'serving') {
                badgeEl.textContent = 'Melayani';
                badgeEl.className = 'rounded-md border border-blue-500/50 bg-[#0B4F9C] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white';
            } else {
                badgeEl.textContent = 'Menunggu';
                badgeEl.className = 'rounded-md border border-[#374151] bg-[#1F2937] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-300';
            }
        }

        function resetAllCounterCards() {
            document.querySelectorAll('[data-counter-card]').forEach((card) => {
                const counterNum = card.dataset.counterCard;
                const ticketEl = document.getElementById(`counter-ticket-${counterNum}`);
                const serviceEl = document.getElementById(`counter-service-${counterNum}`);
                const badgeEl = document.getElementById(`counter-badge-${counterNum}`);
                if (ticketEl) ticketEl.textContent = '---';
                if (serviceEl) serviceEl.textContent = 'Belum ada antrean';
                setCounterBadge(badgeEl, 'idle');
                card.classList.remove('tv-card-calling');
            });
        }

        function applyActiveQueues(activeQueues) {
            resetAllCounterCards();
            if (!Array.isArray(activeQueues)) return;
            activeQueues.forEach((q) => {
                if (!q.counter) return;
                const counterNum = q.counter.counter_number;
                const card = document.getElementById(`counter-card-${counterNum}`);
                const ticketEl = document.getElementById(`counter-ticket-${counterNum}`);
                const serviceEl = document.getElementById(`counter-service-${counterNum}`);
                const badgeEl = document.getElementById(`counter-badge-${counterNum}`);
                if (ticketEl) ticketEl.textContent = q.ticket_code || '---';
                if (serviceEl) serviceEl.textContent = q.service?.name || 'Layanan PST';
                setCounterBadge(badgeEl, q.status);
                card?.classList.toggle('tv-card-calling', q.status === 'calling');
            });
        }

        function createRecentCallRow(ticketCode, serviceName, counterName, timeLabel) {
            const row = document.createElement('div');
            row.className = 'rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-3 flex items-center justify-between gap-3';

            const left = document.createElement('div');
            left.className = 'min-w-0';
            const codeEl = document.createElement('span');
            codeEl.className = 'text-xl font-mono font-bold text-[#F59E0B] tracking-wider tabular-nums-strict';
            codeEl.textContent = ticketCode;
            const srvEl = document.createElement('div');
            srvEl.className = 'text-xs text-slate-300 truncate';
            srvEl.textContent = serviceName;
            left.append(codeEl, srvEl);

            const right = document.createElement('div');
            right.className = 'text-right shrink-0';
            const counterEl = document.createElement('span');
            counterEl.className = 'inline-block rounded-md border border-blue-500/30 bg-blue-950/70 px-2.5 py-1 text-xs font-semibold text-blue-200';
            counterEl.textContent = counterName;
            const timeEl = document.createElement('div');
            timeEl.className = 'text-[11px] font-mono text-slate-400 mt-1 tabular-nums-strict';
            timeEl.textContent = `${timeLabel} WIB`;
            right.append(counterEl, timeEl);

            row.append(left, right);
            return row;
        }

        function renderRecentCalls(recentQueues) {
            const list = document.getElementById('recent-calls-list');
            if (!list || !Array.isArray(recentQueues)) return;

            if (recentQueues.length === 0) {
                const empty = document.createElement('div');
                empty.id = 'empty-recent-notice';
                empty.className = 'py-16 text-center text-xs text-slate-400';
                empty.textContent = 'Belum ada riwayat antrean hari ini';
                list.replaceChildren(empty);
                return;
            }

            const nodes = recentQueues.slice(0, 5).map((rq) => {
                const dateObj = rq.updated_at ? new Date(rq.updated_at) : new Date();
                const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                return createRecentCallRow(
                    rq.ticket_code || '---',
                    rq.service?.name || 'Layanan PST',
                    rq.counter?.name || 'Loket',
                    timeStr
                );
            });
            list.replaceChildren(...nodes);
        }

        function prependRecentCall(data) {
            const list = document.getElementById('recent-calls-list');
            if (!list) return;
            document.getElementById('empty-recent-notice')?.remove();
            const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            list.prepend(createRecentCallRow(data.ticket_code, data.service_name, data.counter_name, timeNow));
            while (list.children.length > 5) {
                list.lastElementChild?.remove();
            }
        }

        function enqueueCall(data, shouldAnnounce = true) {
            const card = document.getElementById(`counter-card-${data.counter_number}`);
            const ticketEl = document.getElementById(`counter-ticket-${data.counter_number}`);
            const serviceEl = document.getElementById(`counter-service-${data.counter_number}`);
            const badgeEl = document.getElementById(`counter-badge-${data.counter_number}`);

            if (ticketEl) ticketEl.textContent = data.ticket_code;
            if (serviceEl) serviceEl.textContent = data.service_name;
            setCounterBadge(badgeEl, 'calling');
            if (card) {
                card.classList.add('tv-card-calling');
                window.setTimeout(() => card.classList.remove('tv-card-calling'), 7000);
            }
            prependRecentCall(data);

            const callKey = `${data.ticket_code}-${data.counter_number}-${data.updated_at || Date.now()}`;
            if (shouldAnnounce && isAudioUnlocked && rememberCallKey(callKey)) {
                audioQueue.push({
                    prefix: data.code_prefix,
                    number: data.ticket_number,
                    counterName: data.counter_name,
                });
                processAudioQueue();
            }
        }

        function updateServiceWaitingCounts(services) {
            if (!Array.isArray(services)) return;
            services.forEach((s) => {
                const countEl = document.getElementById(`service-count-${s.id}`);
                if (countEl) countEl.textContent = String(s.queues_count ?? 0);
            });
        }

        function setConnectionStatus(mode, text) {
            if (!statusBadge) return;
            const styles = {
                sse: 'inline-flex items-center gap-1.5 rounded-md border border-emerald-500/40 bg-emerald-950/60 px-3 py-1.5 text-xs font-semibold text-emerald-300',
                reconnecting: 'inline-flex items-center gap-1.5 rounded-md border border-amber-500/40 bg-amber-950/60 px-3 py-1.5 text-xs font-semibold text-amber-300',
                polling: 'inline-flex items-center gap-1.5 rounded-md border border-blue-500/40 bg-blue-950/60 px-3 py-1.5 text-xs font-semibold text-blue-200',
            };
            statusBadge.className = styles[mode] || styles.sse;
            const label = statusBadge.querySelector('span:last-child');
            if (label) label.textContent = text;
        }

        let eventSource = null;
        let sseFailCount = 0;
        let pollingInterval = null;

        function startFallbackPolling() {
            if (pollingInterval) return;
            setConnectionStatus('polling', 'Mode Polling LAN');

            pollingInterval = window.setInterval(async () => {
                try {
                    const res = await fetch("{{ route('display.poll') }}");
                    if (!res.ok) return;
                    const json = await res.json();
                    if (json.status !== 'success' || !json.data) return;

                    applyActiveQueues(json.data.active_queues);
                    renderRecentCalls(json.data.recent_queues);
                    updateServiceWaitingCounts(json.data.services);

                    if (json.data.latest_calling) {
                        const latest = json.data.latest_calling;
                        const callKey = `${latest.ticket_code}-${latest.counter_number}-${latest.updated_at}`;
                        if (!processedCallKeys.includes(callKey)) {
                            enqueueCall(latest, true);
                        }
                    }
                } catch (e) {
                    console.warn('Polling fallback error:', e);
                }
            }, 2500);
        }

        function stopPolling() {
            if (pollingInterval) {
                window.clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        function connectSSE() {
            if (eventSource) eventSource.close();
            eventSource = new EventSource("{{ route('display.stream') }}");

            eventSource.addEventListener('initial-state', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    applyActiveQueues(data.active_queues);
                    updateServiceWaitingCounts(data.services);
                } catch (err) {
                    console.error('Initial state error:', err);
                }
            });

            eventSource.addEventListener('queue-called', (e) => {
                try {
                    enqueueCall(JSON.parse(e.data), true);
                } catch (err) {
                    console.error('Queue called error:', err);
                }
            });

            eventSource.onopen = () => {
                sseFailCount = 0;
                stopPolling();
                setConnectionStatus('sse', 'SSE Terhubung');
            };

            eventSource.onerror = () => {
                sseFailCount += 1;
                setConnectionStatus('reconnecting', 'Menghubungkan Ulang');
                if (sseFailCount >= 2 && !pollingInterval) {
                    startFallbackPolling();
                }
            };
        }

        function toggleDebugPanel() {
            if (!debugPanel || !debugToggleBtn) return;
            const willOpen = debugPanel.classList.contains('hidden');
            debugPanel.classList.toggle('hidden', !willOpen);
            debugToggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        }

        document.getElementById('btn-enable-audio')?.addEventListener('click', enableAudioEngine);
        debugToggleBtn?.addEventListener('click', toggleDebugPanel);
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                toggleDebugPanel();
            }
        });

        document.querySelectorAll('[data-test-announce]').forEach((btn) => {
            btn.addEventListener('click', () => {
                enableAudioEngine();
                const [prefix, numStr, counterStr] = btn.dataset.testAnnounce.split(',');
                const number = Number(numStr);
                const counterNum = Number(counterStr);
                enqueueCall({
                    ticket_code: `${prefix}-${String(number).padStart(3, '0')}`,
                    code_prefix: prefix,
                    ticket_number: number,
                    counter_number: counterNum,
                    counter_name: `Loket ${counterNum}`,
                    service_name: 'Uji Coba Pengumuman Suara',
                }, true);
            });
        });

        document.getElementById('btn-test-chime')?.addEventListener('click', () => {
            enableAudioEngine();
            playChime(0.35);
        });

        connectSSE();
    });
</script>
@endpush
```

- [ ] **Step 4: Build Vite assets and run `DisplayStreamTest` & `DisplayPollTest`**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test tests/Feature/DisplayStreamTest.php tests/Feature/DisplayPollTest.php"
```
Expected: All tests PASS.

- [ ] **Step 5: Commit Task 5**

```bash
git add resources/views/display/index.blade.php tests/Feature/DisplayStreamTest.php
git commit -m "feat(ui): overhaul display TV dark board, fix counter reset sync, and hide debug drawer"
```

---

### Task 6: Meja Kerja Pelayanan Loket (`/loket`) & Non-Blocking UX

**Files:**
- Modify: `resources/views/counter/index.blade.php`
- Modify: `tests/Feature/CounterOperationsTest.php`

**Interfaces:**
- Consumes: `layouts/workspace` (`activeNav = 'loket'`), `window.PstUi.showToast`, `window.PstUi.showConfirm`, `window.PstUi.renderFormErrors`, `window.PstUi.clearFormErrors`, `<x-icon>`, `<x-badge>`.
- Preserves routes & payload contracts: `counter.index`, `counter.callNext`, `counter.recall`, `counter.startServing`, `counter.skip`, `counter.recallSkipped`, `counter.complete` (`queue_id`, `full_name`, `phone_number`, `email`, `institution_type`, `institution_name`, `data_needs_description`, `pdp_consent`).

- [ ] **Step 1: Extend `tests/Feature/CounterOperationsTest.php` with failing assertions for workspace shell, 3-step stepper, error summary, and absence of `alert`/`confirm`/emojis/CDN**

Append to `tests/Feature/CounterOperationsTest.php`:

```php
    public function test_loket_page_uses_workspace_layout_non_blocking_ux_and_accessible_error_summary(): void
    {
        $this->actingAs($this->petugas);

        $response = $this->get('/loket');
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('📢', $html);
        $this->assertStringNotContainsString('✍️', $html);
        $this->assertStringNotContainsString('⚠️', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('confirm(', $html);
        $this->assertStringContainsString(route('password.change'), $html);
        $this->assertStringContainsString('id="guestbook-error-summary"', $html);
        $this->assertStringContainsString('1. Panggil', $html);
        $this->assertStringContainsString('2. Layani &amp; Buku Tamu', $html);
        $this->assertStringContainsString('3. Selesai', $html);
    }
```

- [ ] **Step 2: Run `CounterOperationsTest` to verify the new test fails**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/CounterOperationsTest.php
```
Expected: FAIL (`cdn.tailwindcss.com`, `alert(`, emojis present; `#guestbook-error-summary` absent).

- [ ] **Step 3: Refactor `resources/views/counter/index.blade.php` using `layouts/workspace`, 3-step progress bar, toast/confirm UX, button loading states, and inline field error summary**

Replace `resources/views/counter/index.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'loket',
    'pageTitle' => 'Meja Kerja Pelayanan Loket',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    @php
        $status = $activeQueue?->status;
        $isServing = $status === 'serving';
        $isCalling = $status === 'calling';
        $stepState = $isServing ? 2 : ($isCalling ? 1 : 0);
    @endphp

    <div class="space-y-6">
        {{-- Counter Toolbar & 3-Step Workflow Indicator --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form action="{{ route('counter.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <label for="counter_id" class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Loket Bertugas:</label>
                <select name="counter_id" id="counter_id" onchange="this.form.submit()"
                        class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-sm font-semibold text-[#0F172A]">
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}" {{ $selectedCounter?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                <x-badge tone="cobalt" class="tabular-nums-strict">{{ $waitingCount }} Menunggu</x-badge>
            </form>

            <nav aria-label="Tahapan Layanan Loket">
                <ol class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 {{ $stepState === 0 ? 'border-[#0B4F9C] bg-blue-50 text-[#0B4F9C]' : 'border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]' }}" @if($stepState === 0) aria-current="step" @endif>
                        <span>1. Panggil</span>
                    </li>
                    <li aria-hidden="true" class="text-[#64748B]">→</li>
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 {{ $stepState === 1 || $stepState === 2 ? 'border-[#0B4F9C] bg-blue-50 text-[#0B4F9C]' : 'border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]' }}" @if($stepState === 1 || $stepState === 2) aria-current="step" @endif>
                        <span>2. Layani &amp; Buku Tamu</span>
                    </li>
                    <li aria-hidden="true" class="text-[#64748B]">→</li>
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]">
                        <span>3. Selesai</span>
                    </li>
                </ol>
            </nav>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Left Column (1/3): Queue Control --}}
            <div class="space-y-5">
                {{-- Assigned Services --}}
                <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Kategori Layanan Loket</h2>
                        <span class="text-xs font-semibold text-[#0F172A]">{{ $selectedCounter?->name }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($selectedCounter?->services ?? [] as $srv)
                            <x-badge tone="neutral">
                                <span class="font-mono font-bold text-[#0B4F9C]">{{ $srv->code_prefix }}</span>
                                <span>{{ $srv->name }}</span>
                            </x-badge>
                        @empty
                            <span class="text-xs text-red-700">Loket belum memiliki penugasan layanan.</span>
                        @endforelse
                    </div>
                </section>

                {{-- Active Ticket Card --}}
                <section class="bg-white rounded-xl border border-[#DCE3EC] p-6 text-center">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Antrean Sedang Ditangani</span>
                    <div id="active-ticket-code" class="text-5xl font-mono font-bold text-[#0A2540] my-3 tracking-wider tabular-nums-strict">
                        {{ $activeQueue?->ticket_code ?? '---' }}
                    </div>
                    <div id="active-service-name" class="text-sm font-semibold text-[#0F172A]">
                        {{ $activeQueue?->service?->name ?? 'Belum ada antrean yang dipanggil' }}
                    </div>
                    <div class="mt-3 flex flex-col items-center gap-1.5">
                        <span id="active-status-badge">
                            @if($isServing)
                                <x-badge tone="green">Sedang Dilayani</x-badge>
                            @elseif($isCalling)
                                <x-badge tone="orange">Sedang Dipanggil</x-badge>
                            @else
                                <x-badge tone="neutral">Kosong</x-badge>
                            @endif
                        </span>
                        @if($isCalling)
                            <div class="text-xs font-medium text-[#475569] tabular-nums-strict">
                                Panggilan Ulang: <strong class="text-[#0F172A]">{{ $activeQueue->recall_count }} / 3</strong>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 space-y-2.5">
                        <button type="button"
                                id="btn-call-next"
                                {{ $activeQueue ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-[#15803D] hover:bg-emerald-800 disabled:opacity-40 disabled:pointer-events-none px-4 py-3 text-xs font-bold uppercase tracking-wider text-white transition-colors">
                            <x-icon name="megaphone" class="w-4 h-4" />
                            <span>Panggil Berikutnya</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button"
                                    id="btn-recall"
                                    {{ (!$isCalling || $activeQueue->recall_count >= 3) ? 'disabled' : '' }}
                                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] disabled:opacity-40 disabled:pointer-events-none px-3 py-2.5 text-xs font-semibold text-white tabular-nums-strict transition-colors">
                                <x-icon name="refresh" class="w-3.5 h-3.5" />
                                <span>Ulang ({{ $activeQueue?->recall_count ?? 0 }}/3)</span>
                            </button>

                            <button type="button"
                                    id="btn-skip"
                                    {{ (!$isCalling || !$canSkip) ? 'disabled' : '' }}
                                    title="{{ !$canSkip ? 'Tombol aktif setelah pemanggilan ulang ke-3' : 'Lewati antrean' }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-[#CBD5E1] bg-white hover:bg-red-50 hover:text-red-700 hover:border-red-300 disabled:opacity-40 disabled:pointer-events-none px-3 py-2.5 text-xs font-semibold text-[#334155] transition-colors">
                                <span>Lewati (Skip)</span>
                            </button>
                        </div>

                        @if($isCalling && !$canSkip)
                            <p class="flex items-center justify-center gap-1.5 rounded-md border border-orange-200 bg-orange-50 px-3 py-1.5 text-[11px] font-medium text-[#C2410C]">
                                <x-icon name="warning" class="w-3.5 h-3.5 shrink-0" />
                                <span>Tombol Lewati aktif setelah panggilan ke-3</span>
                            </p>
                        @endif

                        <button type="button"
                                id="btn-start-serve"
                                {{ !$isCalling ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-[#0A2540] hover:bg-[#0F172A] disabled:opacity-40 disabled:pointer-events-none px-4 py-2.5 text-xs font-bold text-white transition-colors">
                            <x-icon name="edit" class="w-4 h-4" />
                            <span>Mulai Melayani (Buka Buku Tamu)</span>
                        </button>
                    </div>
                </section>

                {{-- Skipped Queues Today --}}
                @if(isset($skippedQueues) && $skippedQueues->count() > 0)
                    <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Antrean Terlewat Hari Ini</h2>
                            <x-badge tone="orange" class="tabular-nums-strict">{{ $skippedQueues->count() }}</x-badge>
                        </div>
                        <p class="text-[11px] text-[#64748B] mb-3">Dapat dipanggil kembali satu kali ke urutan paling depan:</p>
                        <div class="space-y-2 max-h-52 overflow-y-auto">
                            @foreach($skippedQueues as $sq)
                                <div class="flex items-center justify-between gap-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] px-3 py-2 text-xs">
                                    <div class="min-w-0">
                                        <span class="font-mono font-bold text-[#0A2540] tabular-nums-strict">{{ $sq->ticket_code }}</span>
                                        <div class="text-[11px] text-[#475569] truncate">{{ $sq->service?->name }}</div>
                                    </div>
                                    <button type="button"
                                            data-recall-skipped-id="{{ $sq->id }}"
                                            data-recall-skipped-code="{{ $sq->ticket_code }}"
                                            class="btn-recall-skipped shrink-0 rounded-md bg-[#C2410C] hover:bg-orange-800 px-2.5 py-1 text-[11px] font-semibold text-white">
                                        Panggil Kembali
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Right Column (2/3): Guest Book Form --}}
            <section class="lg:col-span-2 bg-white rounded-xl border border-[#DCE3EC] p-6 sm:p-7" aria-labelledby="guestbook-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#DCE3EC] pb-4 mb-5">
                    <div>
                        <h2 id="guestbook-heading" class="text-lg font-bold text-[#0F172A]">Formulir Buku Tamu Digital PST</h2>
                        <p class="text-xs text-[#475569] mt-0.5">Wajib diisi dan disimpan saat melayani pengunjung sebelum menyelesaikan antrean</p>
                    </div>
                    <x-badge tone="neutral">SKF-05 BPS Terpadu</x-badge>
                </div>

                {{-- Focusable Validation Error Summary --}}
                <div id="guestbook-error-summary"
                     role="alert"
                     tabindex="-1"
                     class="hidden mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-xs text-red-900">
                    <div class="flex items-center gap-2 font-bold">
                        <x-icon name="warning" class="w-4 h-4 text-red-700" />
                        <span>Periksa kembali isian formulir buku tamu:</span>
                    </div>
                    <ul data-error-list class="mt-2 list-disc list-inside space-y-1"></ul>
                </div>

                <form id="form-guestbook" class="space-y-5" novalidate>
                    <input type="hidden" id="form-queue-id" value="{{ $activeQueue?->id ?? '' }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Nama Lengkap Pengunjung <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="full_name"
                                   name="full_name"
                                   required
                                   aria-invalid="false"
                                   data-error="full_name-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="Contoh: Dedi Mulyadi"
                                   value="{{ $activeQueue?->guestBook?->full_name ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="full_name-error" class="field-error-text"></p>
                        </div>

                        <div>
                            <label for="phone_number" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Nomor WhatsApp / HP <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="phone_number"
                                   name="phone_number"
                                   inputmode="tel"
                                   required
                                   aria-invalid="false"
                                   data-error="phone_number-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ $activeQueue?->guestBook?->phone_number ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="phone_number-error" class="field-error-text"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Alamat Email (Opsional)
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   aria-invalid="false"
                                   data-error="email-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="nama@instansi.go.id"
                                   value="{{ $activeQueue?->guestBook?->email ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="email-error" class="field-error-text"></p>
                        </div>

                        <div>
                            <label for="institution_type" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Kategori Pemohon / Asal <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <select id="institution_type"
                                    name="institution_type"
                                    required
                                    aria-invalid="false"
                                    data-error="institution_type-error"
                                    {{ !$isServing ? 'disabled' : '' }}
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                                <option value="pendidikan" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pendidikan' ? 'selected' : '' }}>Lembaga Pendidikan / Mahasiswa / Peneliti</option>
                                <option value="pemerintah" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pemerintah' ? 'selected' : '' }}>Instansi Pemerintah (K/L/Pemda)</option>
                                <option value="swasta" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'swasta' ? 'selected' : '' }}>Sektor Swasta / Bisnis</option>
                                <option value="lsm" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'lsm' ? 'selected' : '' }}>LSM / Organisasi Masyarakat / Media</option>
                                <option value="umum" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'umum' ? 'selected' : '' }}>Individu / Masyarakat Umum</option>
                            </select>
                            <p id="institution_type-error" class="field-error-text"></p>
                        </div>
                    </div>

                    <div>
                        <label for="institution_name" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                            Nama Instansi / Universitas / Lembaga Asal <span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="institution_name"
                               name="institution_name"
                               required
                               aria-invalid="false"
                               data-error="institution_name-error"
                               {{ !$isServing ? 'disabled' : '' }}
                               placeholder="Contoh: Universitas Siliwangi / Dinas Kesehatan Kota Tasikmalaya"
                               value="{{ $activeQueue?->guestBook?->institution_name ?? '' }}"
                               class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                        <p id="institution_name-error" class="field-error-text"></p>
                    </div>

                    <div>
                        <label for="data_needs_description" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                            Rincian Kebutuhan Data / Maksud Konsultasi <span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <textarea id="data_needs_description"
                                  name="data_needs_description"
                                  rows="3"
                                  required
                                  aria-invalid="false"
                                  data-error="data_needs_description-error"
                                  {{ !$isServing ? 'disabled' : '' }}
                                  placeholder="Tuliskan data statistik yang dibutuhkan atau topik konsultasi teknis..."
                                  class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">{{ $activeQueue?->guestBook?->data_needs_description ?? '' }}</textarea>
                        <p id="data_needs_description-error" class="field-error-text"></p>
                    </div>

                    <div class="rounded-lg border border-blue-200 bg-blue-50/60 p-4">
                        <label for="pdp_consent" class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox"
                                   id="pdp_consent"
                                   name="pdp_consent"
                                   value="1"
                                   required
                                   aria-invalid="false"
                                   data-error="pdp_consent-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   class="mt-0.5 h-4 w-4 rounded border-[#CBD5E1] text-[#0B4F9C] accent-[#0B4F9C]">
                            <span class="text-xs text-[#334155] leading-relaxed">
                                <strong class="font-bold text-[#0A2540]">Pernyataan UU Pelindungan Data Pribadi (UU PDP):</strong>
                                Pengunjung menyatakan data identitas dan kontak di atas adalah benar, serta menyetujui penggunaannya secara kedinasan oleh BPS untuk pencatatan buku tamu dan evaluasi pelayanan statistik publik.
                            </span>
                        </label>
                        <p id="pdp_consent-error" class="field-error-text"></p>
                    </div>

                    <div class="pt-4 border-t border-[#DCE3EC] flex justify-end">
                        <button type="submit"
                                id="btn-submit-complete"
                                {{ !$isServing ? 'disabled' : '' }}
                                class="inline-flex items-center gap-2 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] disabled:opacity-40 disabled:pointer-events-none px-6 py-3 text-xs font-bold uppercase tracking-wider text-white transition-colors">
                            <x-icon name="check" class="w-4 h-4" />
                            <span>Simpan Buku Tamu &amp; Selesaikan Antrean</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const selectedCounterId = {{ $selectedCounter?->id ?? 1 }};
        const queueIdInput = document.getElementById('form-queue-id');
        const guestbookForm = document.getElementById('form-guestbook');
        const errorSummary = document.getElementById('guestbook-error-summary');
        const { showToast, showConfirm, renderFormErrors, clearFormErrors } = window.PstUi;

        async function postAction(url, payload, triggerBtn) {
            if (triggerBtn) triggerBtn.disabled = true;
            let keepDisabled = false;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    keepDisabled = true;
                }
                return { res, json };
            } finally {
                if (triggerBtn && !keepDisabled) triggerBtn.disabled = false;
            }
        }

        const btnCallNext = document.getElementById('btn-call-next');
        btnCallNext?.addEventListener('click', async () => {
            try {
                const { res, json } = await postAction("{{ route('counter.callNext') }}", { counter_id: selectedCounterId }, btnCallNext);
                if (res.ok && json.status === 'success') {
                    window.location.reload();
                } else {
                    showToast(json.message || 'Tidak ada antrean menunggu.', 'info');
                }
            } catch (e) {
                console.error(e);
                showToast('Gagal memanggil antrean berikutnya.', 'error');
            }
        });

        const btnRecall = document.getElementById('btn-recall');
        btnRecall?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            try {
                const { res, json } = await postAction("{{ route('counter.recall') }}", { queue_id: queueId }, btnRecall);
                if (res.ok && json.status === 'success') {
                    showToast(json.message || 'Panggilan ulang dikirim ke layar Display TV.', 'success');
                    window.setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast(json.message || 'Gagal memanggil ulang antrean.', 'warning');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan saat memanggil ulang.', 'error');
            }
        });

        const btnStartServe = document.getElementById('btn-start-serve');
        btnStartServe?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            try {
                const { res, json } = await postAction("{{ route('counter.startServing') }}", { queue_id: queueId }, btnStartServe);
                if (res.ok && json.status === 'success') {
                    window.location.reload();
                } else {
                    showToast(json.message || 'Gagal memulai pelayanan.', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan koneksi.', 'error');
            }
        });

        const btnSkip = document.getElementById('btn-skip');
        btnSkip?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            const confirmed = await showConfirm({
                title: 'Lewati Nomor Antrean?',
                message: 'Antrean ini akan ditandai terlewat karena pengunjung tidak hadir di loket.',
                confirmLabel: 'Ya, Lewati Antrean',
                cancelLabel: 'Batal',
                tone: 'danger',
            });
            if (!confirmed) return;

            try {
                const { res, json } = await postAction("{{ route('counter.skip') }}", { queue_id: queueId }, btnSkip);
                if (res.ok && json.status === 'success') {
                    showToast(json.message || 'Antrean berhasil dilewati.', 'success');
                    window.setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(json.message || 'Gagal melewati antrean.', 'warning');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan koneksi saat melewati antrean.', 'error');
            }
        });

        document.querySelectorAll('.btn-recall-skipped').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const queueId = btn.dataset.recallSkippedId;
                const ticketCode = btn.dataset.recallSkippedCode;
                const confirmed = await showConfirm({
                    title: `Panggil Kembali ${ticketCode}?`,
                    message: `Antrean ${ticketCode} akan dikembalikan ke urutan paling depan layanannya (maksimal 1 kali).`,
                    confirmLabel: 'Kembalikan ke Antrean',
                    cancelLabel: 'Batal',
                    tone: 'primary',
                });
                if (!confirmed) return;

                try {
                    const { res, json } = await postAction("{{ route('counter.recallSkipped') }}", { queue_id: queueId }, btn);
                    if (res.ok && json.status === 'success') {
                        showToast(json.message || `Antrean ${ticketCode} dikembalikan ke antrean terdepan.`, 'success');
                        window.setTimeout(() => window.location.reload(), 600);
                    } else {
                        showToast(json.message || 'Gagal memanggil kembali antrean terlewat.', 'warning');
                    }
                } catch (e) {
                    console.error(e);
                    showToast('Terjadi kesalahan saat memproses antrean terlewat.', 'error');
                }
            });
        });

        guestbookForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const queueId = queueIdInput?.value;
            if (!queueId) return;

            clearFormErrors(guestbookForm, errorSummary);

            const clientErrors = {};
            const fullName = document.getElementById('full_name').value.trim();
            const phone = document.getElementById('phone_number').value.trim();
            const email = document.getElementById('email').value.trim();
            const instType = document.getElementById('institution_type').value;
            const instName = document.getElementById('institution_name').value.trim();
            const needsDesc = document.getElementById('data_needs_description').value.trim();
            const pdpConsent = document.getElementById('pdp_consent').checked;

            if (!fullName) clientErrors.full_name = ['Nama lengkap pengunjung wajib diisi.'];
            if (!phone) clientErrors.phone_number = ['Nomor WhatsApp / HP pengunjung wajib diisi.'];
            if (!instName) clientErrors.institution_name = ['Nama instansi asal wajib diisi.'];
            if (!needsDesc) clientErrors.data_needs_description = ['Rincian kebutuhan data wajib diisi.'];
            if (!pdpConsent) clientErrors.pdp_consent = ['Pernyataan persetujuan UU PDP wajib disetujui.'];

            if (Object.keys(clientErrors).length > 0) {
                renderFormErrors(guestbookForm, errorSummary, clientErrors);
                return;
            }

            const submitBtn = document.getElementById('btn-submit-complete');
            try {
                const { res, json } = await postAction(
                    "{{ route('counter.complete') }}",
                    {
                        queue_id: queueId,
                        full_name: fullName,
                        phone_number: phone,
                        email: email || null,
                        institution_type: instType,
                        institution_name: instName,
                        data_needs_description: needsDesc,
                        pdp_consent: true,
                    },
                    submitBtn
                );

                if (res.ok && json.status === 'success') {
                    showToast('Buku tamu tersimpan dan antrean selesai.', 'success');
                    window.setTimeout(() => window.location.reload(), 500);
                } else {
                    renderFormErrors(guestbookForm, errorSummary, json.errors || {}, json.message || 'Gagal menyimpan buku tamu.');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kendala saat menyimpan data buku tamu.', 'error');
            }
        });
    });
</script>
@endpush
```

- [ ] **Step 4: Build Vite assets and run `CounterOperationsTest` + `GuestBookValidationTest`**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test tests/Feature/CounterOperationsTest.php tests/Feature/GuestBookValidationTest.php"
```
Expected: All tests PASS.

- [ ] **Step 5: Commit Task 6**

```bash
git add resources/views/counter/index.blade.php tests/Feature/CounterOperationsTest.php
git commit -m "feat(ui): redesign counter workspace with non-blocking dialogs and inline form errors"
```

---

### Task 7: Admin Console (`dashboard`, `reports`, `services`, `counters`, `users`) & PDF Template Polish

**Files:**
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `resources/views/admin/reports/index.blade.php`
- Modify: `resources/views/admin/services/index.blade.php`
- Modify: `resources/views/admin/counters/index.blade.php`
- Modify: `resources/views/admin/users/index.blade.php`
- Modify: `resources/views/admin/reports/pdf.blade.php`
- Modify: `tests/Feature/DashboardReportTest.php`
- Modify: `tests/Feature/AdminManagementTest.php`

**Interfaces:**
- Consumes: `layouts/workspace` (`activeNav = 'dashboard'|'reports'|'services'|'counters'|'users'`), `<x-modal>`, `<x-table-wrapper>`, `<x-badge>`, `<x-icon>`, `window.PstUi.openModal`, local `public/vendor/chartjs/chart.umd.js`.
- Preserves all strings tested in `DashboardReportTest` and `AdminManagementTest`.

- [ ] **Step 1: Extend `DashboardReportTest.php` and `AdminManagementTest.php` with failing assertions for unified shell, `<dialog>`, readable institution labels, and zero CDN/emojis/addslashes**

Append to `tests/Feature/DashboardReportTest.php`:

```php
    public function test_admin_dashboard_and_reports_use_unified_workspace_readable_labels_and_no_cdn_or_emojis(): void
    {
        $service = Service::first();
        $q = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-099',
            'ticket_number' => 99,
            'service_id' => $service->id,
            'status' => 'completed',
        ]);
        GuestBook::create([
            'queue_id' => $q->id,
            'full_name' => 'Rina Kartika',
            'phone_number' => '0811223344',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Data PDRB Kota Tasikmalaya',
        ]);

        $this->actingAs($this->admin);

        $dashboard = $this->get('/admin/dashboard')->assertStatus(200);
        $dashHtml = $dashboard->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $dashHtml);
        $this->assertStringNotContainsString('👥', $dashHtml);
        $this->assertStringNotContainsString('⏳', $dashHtml);
        $this->assertStringNotContainsString('⏱️', $dashHtml);
        $this->assertStringContainsString('aria-current="page"', $dashHtml);
        $this->assertStringContainsString(route('password.change'), $dashHtml);

        $reports = $this->get('/admin/reports')->assertStatus(200);
        $repHtml = $reports->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $repHtml);
        $this->assertStringNotContainsString('🔍', $repHtml);
        $this->assertStringNotContainsString('📥', $repHtml);
        $this->assertStringNotContainsString('📄', $repHtml);
        $this->assertStringContainsString('Pendidikan / Riset', $repHtml);
        $this->assertStringContainsString(route('password.change'), $repHtml);
    }
```

Append to `tests/Feature/AdminManagementTest.php`:

```php
    public function test_admin_crud_pages_use_native_dialog_json_payloads_and_consistent_workspace_header(): void
    {
        $this->actingAs($this->admin);

        foreach (['/admin/services', '/admin/counters', '/admin/users'] as $url) {
            $html = $this->get($url)->assertStatus(200)->getContent();
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
            $this->assertStringNotContainsString('addslashes', $html);
            $this->assertStringContainsString('<dialog', $html);
            $this->assertStringContainsString('data-payload=', $html);
            $this->assertStringContainsString(route('password.change'), $html);
            $this->assertStringContainsString('aria-current="page"', $html);
        }
    }
```

- [ ] **Step 2: Run `DashboardReportTest` and `AdminManagementTest` to confirm the new tests fail**

Run:
```bash
distrobox enter my-distrobox -- php artisan test tests/Feature/DashboardReportTest.php tests/Feature/AdminManagementTest.php
```
Expected: FAIL (`cdn.tailwindcss.com`, emojis, missing `<dialog>` / `data-payload=` / `password.change`).

- [ ] **Step 3: Refactor `resources/views/admin/dashboard.blade.php`**

Replace `resources/views/admin/dashboard.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'dashboard',
    'pageTitle' => 'Mini Dashboard Rekapitulasi PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@push('head')
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
@endpush

@section('content')
    <div class="space-y-6">
        {{-- Default Password Security Notice --}}
        @if($isUsingDefaultPassword)
            <div role="alert" class="rounded-xl border border-orange-200 border-l-4 border-l-[#C2410C] bg-orange-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="warning" class="w-5 h-5 text-[#C2410C] shrink-0 mt-0.5" />
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Peringatan Keamanan Akun</h2>
                        <p class="text-xs text-[#475569] mt-0.5">Akun Anda masih menggunakan kata sandi bawaan sistem. Segera perbarui kata sandi untuk menjaga keamanan data operasional PST.</p>
                    </div>
                </div>
                <a href="{{ route('password.change') }}"
                   class="inline-flex items-center justify-center rounded-md bg-[#C2410C] hover:bg-orange-800 px-3.5 py-2 text-xs font-semibold text-white shrink-0">
                    Ubah Kata Sandi
                </a>
            </div>
        @endif

        {{-- Explicit Month & Year Filter --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-[#0F172A]">Ringkasan Evaluasi Kinerja PST</h1>
                <p class="text-xs text-[#475569] mt-0.5">
                    Periode Aktif: <strong class="font-semibold text-[#0F172A]">{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</strong>
                    (Pembanding: {{ $monthNames[$prevMonth] }} {{ $prevYear }})
                </p>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-2.5">
                <div>
                    <label for="month" class="block text-[11px] font-semibold text-[#475569] mb-1">Bulan</label>
                    <select id="month" name="month" class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-semibold text-[#0F172A]">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth === $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-[11px] font-semibold text-[#475569] mb-1">Tahun</label>
                    <select id="year" name="year" class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-semibold text-[#0F172A] tabular-nums-strict">
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">
                    Terapkan Filter
                </button>
            </form>
        </section>

        {{-- Row 1: 4 Structured KPI Cards --}}
        <section aria-label="Indikator Kinerja Utama" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Total Kunjungan</span>
                        <div class="text-3xl font-bold text-[#0A2540] mt-1.5 tabular-nums-strict">{{ $totalMonth }}</div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                        <x-icon name="users" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] flex items-center justify-between text-xs tabular-nums-strict">
                    <span class="text-[#475569]">Bulan lalu: <strong class="text-[#0F172A]">{{ $prevMonthTotal }}</strong></span>
                    <x-badge :tone="$diffTotal >= 0 ? 'green' : 'danger'">
                        {{ $diffTotal >= 0 ? 'Naik +' : 'Turun ' }}{{ $growthRate }}%
                    </x-badge>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Selesai Dilayani</span>
                        <div class="text-3xl font-bold text-[#15803D] mt-1.5 tabular-nums-strict">{{ $completedMonth }}</div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center shrink-0">
                        <x-icon name="check" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] grid grid-cols-3 gap-1.5 text-center text-[11px] font-semibold text-[#475569] tabular-nums-strict">
                    <div class="rounded bg-[#F4F6F9] py-1">Layani: {{ $servingMonth }}</div>
                    <div class="rounded bg-[#F4F6F9] py-1">Tunggu: {{ $waitingMonth }}</div>
                    <div class="rounded bg-red-50 text-red-700 py-1">Skip: {{ $skippedMonth }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Rerata Waktu Tunggu</span>
                        <div class="text-3xl font-bold text-[#C2410C] mt-1.5 tabular-nums-strict">
                            {{ $avgWaitMinutes }} <span class="text-sm font-medium text-[#475569]">mnt</span>
                        </div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-orange-50 border border-orange-200 text-[#C2410C] flex items-center justify-center shrink-0">
                        <x-icon name="clock" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] text-xs text-[#475569]">
                    Standar BPS: <strong class="font-semibold text-[#15803D]">&lt; 15 Menit</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Rerata Durasi Layanan</span>
                        <div class="text-3xl font-bold text-[#0F766E] mt-1.5 tabular-nums-strict">
                            {{ $avgServeMinutes }} <span class="text-sm font-medium text-[#475569]">mnt</span>
                        </div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-teal-50 border border-teal-200 text-[#0F766E] flex items-center justify-center shrink-0">
                        <x-icon name="chart" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] text-xs text-[#475569]">
                    Waktu konsultasi per pemohon
                </div>
            </div>
        </section>

        {{-- Row 2: Daily Trend & Peak Hours --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="lg:col-span-2 bg-white rounded-xl border border-[#DCE3EC] p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Tren Volume Kunjungan Harian</h2>
                        <p class="text-xs text-[#475569]">Pemohon per tanggal pada {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</p>
                    </div>
                    <x-badge tone="cobalt">Harian</x-badge>
                </div>
                <div class="h-64 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Jam Sibuk Pelayanan</h2>
                        <p class="text-xs text-[#475569]">Distribusi waktu kedatangan (WIB)</p>
                    </div>
                    <x-badge tone="orange">Peak Hours</x-badge>
                </div>
                <div class="h-64 relative">
                    <canvas id="busyHoursChart"></canvas>
                </div>
            </section>
        </div>

        {{-- Row 3: Service Distribution & Institution Type --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <h2 class="text-sm font-bold text-[#0F172A]">Distribusi Akses Kategori Layanan PST</h2>
                <p class="text-xs text-[#475569] mb-4">Proporsi permohonan berdasarkan 4 layanan resmi BPS</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="serviceChart"></canvas>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <h2 class="text-sm font-bold text-[#0F172A]">Proporsi Kategori Instansi Pemohon</h2>
                <p class="text-xs text-[#475569] mb-4">Segmentasi asal pengunjung (Pendidikan, Pemerintah, Swasta, LSM, Umum)</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="institutionChart"></canvas>
                </div>
            </section>
        </div>

        {{-- Row 4: Recent 10 Visitors Table --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <div class="p-5 border-b border-[#DCE3EC] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 id="recent-visitors-title" class="text-sm font-bold text-[#0F172A]">Daftar Tamu Terverifikasi Terakhir (Bulan Ini)</h2>
                    <p class="text-xs text-[#475569] mt-0.5">Rekaman buku tamu digital pengunjung yang selesai dilayani</p>
                </div>
                <a href="{{ route('admin.reports.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-3.5 py-2 text-xs font-semibold text-[#0B4F9C] shrink-0">
                    <span>Lihat Seluruh Laporan &amp; Ekspor PDF / CSV</span>
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <x-table-wrapper labelled-by="recent-visitors-title">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Tanggal</th>
                            <th scope="col" class="py-3 px-4">Tiket</th>
                            <th scope="col" class="py-3 px-4">Nama Pengunjung</th>
                            <th scope="col" class="py-3 px-4">Instansi Asal</th>
                            <th scope="col" class="py-3 px-4">Layanan PST</th>
                            <th scope="col" class="py-3 px-4">Durasi</th>
                            <th scope="col" class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @forelse($recentVisitors as $v)
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3 px-4 text-xs text-[#475569] whitespace-nowrap tabular-nums-strict">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[#0A2540] whitespace-nowrap tabular-nums-strict">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-xs text-[#475569]">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-badge tone="cobalt">{{ $v->queue->service?->name ?? '-' }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono text-[#475569] whitespace-nowrap tabular-nums-strict">
                                    {{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }} mnt
                                </td>
                                <td class="py-3 px-4 text-xs text-[#475569] max-w-xs" title="{{ $v->data_needs_description }}">
                                    <span class="block truncate">{{ $v->data_needs_description }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-xs text-[#64748B]">Belum ada data kunjungan pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.color = '#475569';
        Chart.defaults.font.family = "'Public Sans', 'Inter', system-ui, sans-serif";
        Chart.defaults.font.size = 11;

        const gridColor = '#E2E8F0';

        const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
        if (ctxTrend) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: {!! json_encode($trendData) !!},
                        borderColor: '#0B4F9C',
                        backgroundColor: 'rgba(11, 79, 156, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.25,
                        pointRadius: 2.5,
                        pointBackgroundColor: '#0A2540',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        const ctxBusy = document.getElementById('busyHoursChart')?.getContext('2d');
        if (ctxBusy) {
            new Chart(ctxBusy, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($busyHourLabels) !!},
                    datasets: [{
                        label: 'Pemohon',
                        data: {!! json_encode($busyHourData) !!},
                        backgroundColor: '#C2410C',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        const ctxService = document.getElementById('serviceChart')?.getContext('2d');
        if (ctxService) {
            new Chart(ctxService, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($serviceChartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($serviceChartData) !!},
                        backgroundColor: ['#0B4F9C', '#15803D', '#C2410C', '#0F766E'],
                        borderColor: '#FFFFFF',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 11 } } },
                    },
                },
            });
        }

        const ctxInst = document.getElementById('institutionChart')?.getContext('2d');
        if (ctxInst) {
            new Chart(ctxInst, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($institutionChartLabels) !!},
                    datasets: [{
                        label: 'Pemohon',
                        data: {!! json_encode($institutionChartData) !!},
                        backgroundColor: ['#0B4F9C', '#15803D', '#C2410C', '#0F766E', '#475569'],
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    },
                },
            });
        }
    });
</script>
@endpush
```

- [ ] **Step 4: Refactor `resources/views/admin/reports/index.blade.php` and `resources/views/admin/reports/pdf.blade.php`**

Replace `resources/views/admin/reports/index.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'reports',
    'pageTitle' => 'Rekapitulasi Kunjungan Buku Tamu PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@php
    $institutionLabels = [
        'pendidikan' => 'Pendidikan / Riset',
        'pemerintah' => 'Instansi Pemerintah',
        'swasta' => 'Sektor Swasta',
        'lsm' => 'LSM / Media',
        'umum' => 'Masyarakat Umum',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
            <search>
                <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        <div>
                            <label for="start_date" class="block text-xs font-semibold text-[#0F172A] mb-1">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A] tabular-nums-strict">
                        </div>

                        <div>
                            <label for="end_date" class="block text-xs font-semibold text-[#0F172A] mb-1">Tanggal Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A] tabular-nums-strict">
                        </div>

                        <div>
                            <label for="service_id" class="block text-xs font-semibold text-[#0F172A] mb-1">Layanan PST</label>
                            <select id="service_id" name="service_id"
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                                <option value="">Semua Layanan</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" {{ $serviceId == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }} ({{ $s->code_prefix }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="institution_type" class="block text-xs font-semibold text-[#0F172A] mb-1">Kategori Instansi</label>
                            <select id="institution_type" name="institution_type"
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                                <option value="">Semua Kategori</option>
                                @foreach($institutionLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $institutionType == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-xs font-semibold text-[#0F172A] mb-1">Cari Nama / Instansi / Tiket</label>
                            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#EDF1F6]">
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">
                                <x-icon name="search" class="w-3.5 h-3.5" />
                                <span>Terapkan Filter</span>
                            </button>
                            @if($search || $serviceId || $institutionType || $startDate != now()->startOfMonth()->toDateString() || $endDate != now()->toDateString())
                                <a href="{{ route('admin.reports.index') }}"
                                   class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-3.5 py-2 text-xs font-semibold text-[#334155]">
                                    Reset
                                </a>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B] mr-1">Ekspor Data:</span>
                            <a href="{{ route('admin.reports.exportCsv', request()->query()) }}"
                               title="Unduh format spreadsheet CSV dengan UTF-8 BOM"
                               class="inline-flex items-center gap-1.5 rounded-md bg-[#15803D] hover:bg-emerald-800 px-3.5 py-2 text-xs font-semibold text-white">
                                <x-icon name="download" class="w-3.5 h-3.5" />
                                <span>Ekspor CSV</span>
                            </a>
                            <a href="{{ route('admin.reports.exportPdf', request()->query()) }}"
                               target="_blank"
                               title="Cetak/Unduh dokumen resmi PDF ber-kop BPS"
                               class="inline-flex items-center gap-1.5 rounded-md bg-red-700 hover:bg-red-800 px-3.5 py-2 text-xs font-semibold text-white">
                                <x-icon name="document" class="w-3.5 h-3.5" />
                                <span>Ekspor PDF Resmi</span>
                            </a>
                        </div>
                    </div>
                </form>
            </search>
        </section>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <div class="p-5 border-b border-[#DCE3EC] flex items-center justify-between gap-3">
                <h1 id="reports-table-title" class="text-base font-bold text-[#0F172A] tabular-nums-strict">
                    Daftar Pengunjung Terverifikasi (Total: {{ $visitors->total() }} Data)
                </h1>
            </div>

            <x-table-wrapper labelled-by="reports-table-title">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Tanggal</th>
                            <th scope="col" class="py-3 px-4">Tiket</th>
                            <th scope="col" class="py-3 px-4">Layanan</th>
                            <th scope="col" class="py-3 px-4">Nama Lengkap</th>
                            <th scope="col" class="py-3 px-4">Kontak</th>
                            <th scope="col" class="py-3 px-4">Instansi Asal</th>
                            <th scope="col" class="py-3 px-4">Kategori</th>
                            <th scope="col" class="py-3 px-4">Tunggu / Layanan</th>
                            <th scope="col" class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @forelse($visitors as $v)
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3 px-4 text-xs text-[#475569] whitespace-nowrap tabular-nums-strict">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[#0A2540] whitespace-nowrap tabular-nums-strict">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 text-xs font-medium text-[#0F172A]">{{ $v->queue->service?->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-xs text-[#475569] tabular-nums-strict">{{ $v->phone_number }}</td>
                                <td class="py-3 px-4 text-xs text-[#334155]">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-badge tone="neutral">{{ $institutionLabels[$v->institution_type] ?? $v->institution_type }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono text-[#475569] whitespace-nowrap tabular-nums-strict">
                                    {{ round(($v->queue->wait_duration_seconds ?? 0) / 60, 1) }}m /
                                    {{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }}m
                                </td>
                                <td class="py-3 px-4 text-xs text-[#475569] max-w-xs" title="{{ $v->data_needs_description }}">
                                    <span class="block truncate">{{ $v->data_needs_description }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-xs text-[#64748B]">Tidak ada data kunjungan sesuai filter yang dipilih</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-wrapper>

            @if($visitors->hasPages())
                <div class="p-4 border-t border-[#DCE3EC] bg-[#F8FAFC]">
                    {{ $visitors->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
```

Polish `resources/views/admin/reports/pdf.blade.php` (standalone DomPDF HTML/CSS — keep existing structure, refine Kop emblem tri-color bar & human-readable `institution_type` labels):

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Pengunjung PST BPS</title>
    <style>
        @page {
            margin: 1.4cm 1.4cm 1.4cm 1.4cm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #0F172A;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .kop-logo {
            width: 68px;
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            color: #0A2540;
            border: 1.5px solid #0A2540;
            border-top: 4px solid #0B4F9C;
            padding: 7px 6px;
            letter-spacing: 2px;
        }
        .kop-text {
            text-align: center;
            padding-left: 10px;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 12.5pt;
            font-weight: bold;
            color: #0A2540;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h3 {
            margin: 2px 0;
            font-size: 10.5pt;
            font-weight: bold;
            color: #0B4F9C;
        }
        .kop-text p {
            margin: 1px 0;
            font-size: 8pt;
            color: #475569;
        }
        .double-line {
            border-top: 2px solid #0A2540;
            border-bottom: 1px solid #0A2540;
            height: 3px;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .report-title {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title h1 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0F172A;
        }
        .report-title .period {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 3px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #CBD5E1;
            padding: 6px 10px;
            background-color: #F4F6F9;
            font-size: 8pt;
        }
        .summary-box strong {
            color: #0A2540;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #0A2540;
            color: #FFFFFF;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #0A2540;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #CBD5E1;
            padding: 5px 5px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5pt;
            font-weight: bold;
            border: 1px solid #94A3B8;
            border-radius: 2px;
            background-color: #EDF1F6;
            color: #0F172A;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            width: 50%;
            vertical-align: top;
            font-size: 8.5pt;
            padding: 0 20px;
        }
        .sig-space {
            height: 60px;
        }
    </style>
</head>
<body>
@php
    $institutionLabels = [
        'pendidikan' => 'Pendidikan / Riset',
        'pemerintah' => 'Pemerintah',
        'swasta' => 'Swasta',
        'lsm' => 'LSM / Media',
        'umum' => 'Umum',
    ];
@endphp
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                <div class="kop-logo">BPS</div>
            </td>
            <td class="kop-text">
                <h2>Badan Pusat Statistik Kota Tasikmalaya</h2>
                <h3>Pelayanan Statistik Terpadu (PST)</h3>
                <p>Jl. Ir. H. Juanda No. 84, Sukamulya, Kec. Bungursari, Kota Tasikmalaya, Jawa Barat 46151</p>
                <p>Telp: (0265) 345678 | Email: bps3278@bps.go.id | Website: tasikmalayakota.bps.go.id</p>
            </td>
        </tr>
    </table>
    <div class="double-line"></div>

    <div class="report-title">
        <h1>Laporan Rekapitulasi Kunjungan Pengunjung PST</h1>
        <div class="period">
            Periode Data: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s.d.
            <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
        </div>
    </div>

    <table class="summary-box">
        <tr>
            <td style="width: 33%;">Total Tamu Dilayani: <strong>{{ number_format($totalRecords, 0, ',', '.') }} Orang</strong></td>
            <td style="width: 33%;">Rata-rata Waktu Tunggu: <strong>{{ $avgWaitMinutes }} Menit</strong></td>
            <td style="width: 34%;">Rata-rata Waktu Layanan: <strong>{{ $avgServeMinutes }} Menit</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 8%;">Tiket</th>
                <th style="width: 14%;">Layanan PST</th>
                <th style="width: 13%;">Nama Pengunjung</th>
                <th style="width: 10%;">Kontak</th>
                <th style="width: 13%;">Instansi Asal</th>
                <th style="width: 8%;">Kategori</th>
                <th style="width: 6%;">Tunggu</th>
                <th style="width: 7%;">Layanan</th>
                <th style="width: 9%;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $r)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $r->queue->queue_date->format('d/m/Y') }}</td>
                    <td class="text-center font-mono">{{ $r->queue->ticket_code }}</td>
                    <td>{{ $r->queue->service?->name ?? '-' }}</td>
                    <td><strong>{{ $r->full_name }}</strong></td>
                    <td>{{ $r->phone_number }}</td>
                    <td>{{ $r->institution_name }}</td>
                    <td class="text-center">
                        <span class="badge">{{ $institutionLabels[$r->institution_type] ?? $r->institution_type }}</span>
                    </td>
                    <td class="text-center">{{ round(($r->queue->wait_duration_seconds ?? 0) / 60, 1) }}m</td>
                    <td class="text-center">{{ round(($r->queue->serve_duration_seconds ?? 0) / 60, 1) }}m</td>
                    <td>{{ $r->queue->user?->name ?? ($r->queue->counter?->name ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 16px; color: #64748B;">
                        Tidak ada data kunjungan yang tercatat untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td class="text-center">
                <p>Mengetahui,</p>
                <p><strong>Kepala BPS Kota Tasikmalaya</strong></p>
                <div class="sig-space"></div>
                <p><strong>(......................................................)</strong></p>
                <p style="font-size: 7.5pt; color: #64748B;">NIP. ...................................................</p>
            </td>
            <td class="text-center">
                <p>Tasikmalaya, {{ $printedAt }}</p>
                <p><strong>Penanggung Jawab PST</strong></p>
                <div class="sig-space"></div>
                <p><strong>(......................................................)</strong></p>
                <p style="font-size: 7.5pt; color: #64748B;">NIP. ...................................................</p>
            </td>
        </tr>
    </table>
</body>
</html>
```

- [ ] **Step 5: Refactor `admin/services/index.blade.php`, `admin/counters/index.blade.php`, and `admin/users/index.blade.php` using `layouts/workspace`, `<x-modal>`, `<x-table-wrapper>`, and JSON `data-payload` attributes**

Replace `resources/views/admin/services/index.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'services',
    'pageTitle' => 'Pengelolaan Kategori Layanan PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div role="status" class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900">
                <x-icon name="check" class="w-4 h-4 text-[#15803D]" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="services-heading" class="text-lg font-bold text-[#0F172A]">Daftar Layanan Statistik Terpadu</h1>
                <p class="text-xs text-[#475569] mt-0.5">Kelola nama kategori layanan, kode prefix tiket, dan urutan tampilan anjungan Kiosk.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-service-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Layanan Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="services-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Prefix</th>
                            <th scope="col" class="py-3 px-4">Nama Layanan</th>
                            <th scope="col" class="py-3 px-4">Deskripsi</th>
                            <th scope="col" class="py-3 px-4 text-center">Urutan</th>
                            <th scope="col" class="py-3 px-4 text-center">Total Kunjungan</th>
                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($services as $s)
                            @php
                                $editPayload = [
                                    'action' => route('admin.services.update', $s->id),
                                    'name' => $s->name,
                                    'code_prefix' => $s->code_prefix,
                                    'description' => $s->description ?? '',
                                    'order_priority' => $s->order_priority,
                                    'is_active' => (bool) $s->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <span class="w-8 h-8 rounded-md bg-blue-50 border border-blue-200 text-[#0B4F9C] font-mono font-bold text-sm flex items-center justify-center">
                                        {{ $s->code_prefix }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#0F172A]">{{ $s->name }}</td>
                                <td class="py-3.5 px-4 text-xs text-[#475569] max-w-sm">{{ $s->description ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold tabular-nums-strict">{{ $s->order_priority }}</td>
                                <td class="py-3.5 px-4 text-center font-mono text-xs font-bold text-[#0A2540] tabular-nums-strict">{{ $s->queues_count }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$s->is_active ? 'green' : 'neutral'">
                                        {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-edit-service
                                                data-payload='@json($editPayload)'
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.services.toggle', $s->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $s->is_active ? 'border border-orange-200 bg-orange-50 hover:bg-orange-100 text-[#C2410C]' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                {{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>

    <x-modal id="create-service-modal" title="Tambah Layanan PST Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-service-prefix" class="block text-xs font-semibold text-[#0F172A] mb-1">Kode Prefix Tiket (1 Huruf)</label>
                <input type="text" id="create-service-prefix" name="code_prefix" required maxlength="4" autofocus placeholder="Misal: E"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono uppercase font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="create-service-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Layanan</label>
                <input type="text" id="create-service-name" name="name" required placeholder="Contoh: Pojok Statistik Desa"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-service-desc" class="block text-xs font-semibold text-[#0F172A] mb-1">Deskripsi Singkat</label>
                <textarea id="create-service-desc" name="description" rows="2" placeholder="Uraian layanan..."
                          class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]"></textarea>
            </div>
            <div>
                <label for="create-service-order" class="block text-xs font-semibold text-[#0F172A] mb-1">Urutan Tampilan</label>
                <input type="number" id="create-service-order" name="order_priority" value="5" min="1" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-service-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Layanan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-service-modal" title="Edit Data Layanan PST" max-width="max-w-md">
        <form id="edit-service-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit-service-prefix" class="block text-xs font-semibold text-[#0F172A] mb-1">Kode Prefix Tiket</label>
                <input type="text" id="edit-service-prefix" name="code_prefix" required maxlength="4" autofocus
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono uppercase font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="edit-service-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Layanan</label>
                <input type="text" id="edit-service-name" name="name" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="edit-service-desc" class="block text-xs font-semibold text-[#0F172A] mb-1">Deskripsi</label>
                <textarea id="edit-service-desc" name="description" rows="2"
                          class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]"></textarea>
            </div>
            <div>
                <label for="edit-service-order" class="block text-xs font-semibold text-[#0F172A] mb-1">Urutan Prioritas</label>
                <input type="number" id="edit-service-order" name="order_priority" min="1" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <label for="edit-service-active" class="inline-flex items-center gap-2 text-xs font-semibold text-[#0F172A] cursor-pointer">
                <input type="checkbox" id="edit-service-active" name="is_active" value="1" class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                <span>Layanan Aktif</span>
            </label>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="edit-service-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Perubahan</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-edit-service]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('edit-service-form').action = data.action || '';
                document.getElementById('edit-service-prefix').value = data.code_prefix || '';
                document.getElementById('edit-service-name').value = data.name || '';
                document.getElementById('edit-service-desc').value = data.description || '';
                document.getElementById('edit-service-order').value = data.order_priority || 1;
                document.getElementById('edit-service-active').checked = Boolean(data.is_active);
                window.PstUi.openModal('edit-service-modal');
            });
        });
    });
</script>
@endpush
```

Replace `resources/views/admin/counters/index.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'counters',
    'pageTitle' => 'Pengelolaan Meja Loket & Penugasan Layanan',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div role="status" class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900">
                <x-icon name="check" class="w-4 h-4 text-[#15803D]" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="counters-heading" class="text-lg font-bold text-[#0F172A]">Daftar Meja Loket Pelayanan</h1>
                <p class="text-xs text-[#475569] mt-0.5">Atur nama loket, nomor fisik loket, dan kategori layanan yang ditugaskan.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-counter-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Loket Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="counters-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Nomor</th>
                            <th scope="col" class="py-3 px-4">Nama Loket</th>
                            <th scope="col" class="py-3 px-4">Kategori Layanan Ditugaskan</th>
                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($counters as $c)
                            @php
                                $editPayload = [
                                    'action' => route('admin.counters.update', $c->id),
                                    'name' => $c->name,
                                    'counter_number' => $c->counter_number,
                                    'service_ids' => $c->services->pluck('id')->all(),
                                    'is_active' => (bool) $c->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <span class="w-8 h-8 rounded-md bg-blue-50 border border-blue-200 text-[#0B4F9C] font-mono font-bold text-sm flex items-center justify-center tabular-nums-strict">
                                        {{ $c->counter_number }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#0F172A]">{{ $c->name }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($c->services as $srv)
                                            <x-badge tone="neutral">
                                                <span class="font-mono font-bold text-[#0B4F9C]">{{ $srv->code_prefix }}</span>
                                                <span>{{ $srv->name }}</span>
                                            </x-badge>
                                        @empty
                                            <span class="text-xs text-red-700">Belum ada penugasan layanan</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$c->is_active ? 'green' : 'neutral'">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-edit-counter
                                                data-payload='@json($editPayload)'
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Edit Penugasan
                                        </button>
                                        <form action="{{ route('admin.counters.toggle', $c->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $c->is_active ? 'border border-orange-200 bg-orange-50 hover:bg-orange-100 text-[#C2410C]' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>

    <x-modal id="create-counter-modal" title="Tambah Meja Loket Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.counters.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-counter-number" class="block text-xs font-semibold text-[#0F172A] mb-1">Nomor Fisik Loket</label>
                <input type="number" id="create-counter-number" name="counter_number" required min="1" autofocus placeholder="Contoh: 3"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="create-counter-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Loket</label>
                <input type="text" id="create-counter-name" name="name" required placeholder="Contoh: Loket 3"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <fieldset>
                <legend class="text-xs font-semibold text-[#0F172A] mb-1.5">Penugasan Layanan PST</legend>
                <div class="space-y-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] p-3 max-h-40 overflow-y-auto">
                    @foreach($services as $srv)
                        <label class="flex items-center gap-2 text-xs font-medium text-[#334155] cursor-pointer">
                            <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" checked class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                            <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-counter-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Loket</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-counter-modal" title="Edit Meja Loket &amp; Penugasan" max-width="max-w-md">
        <form id="edit-counter-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit-counter-number" class="block text-xs font-semibold text-[#0F172A] mb-1">Nomor Fisik Loket</label>
                <input type="number" id="edit-counter-number" name="counter_number" required min="1" autofocus
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="edit-counter-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Loket</label>
                <input type="text" id="edit-counter-name" name="name" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <fieldset>
                <legend class="text-xs font-semibold text-[#0F172A] mb-1.5">Penugasan Kategori Layanan</legend>
                <div class="space-y-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] p-3 max-h-40 overflow-y-auto">
                    @foreach($services as $srv)
                        <label class="flex items-center gap-2 text-xs font-medium text-[#334155] cursor-pointer">
                            <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" class="edit-counter-service-cb h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                            <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <label for="edit-counter-active" class="inline-flex items-center gap-2 text-xs font-semibold text-[#0F172A] cursor-pointer">
                <input type="checkbox" id="edit-counter-active" name="is_active" value="1" class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                <span>Loket Siap Operasional (Aktif)</span>
            </label>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="edit-counter-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Perubahan</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-edit-counter]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('edit-counter-form').action = data.action || '';
                document.getElementById('edit-counter-name').value = data.name || '';
                document.getElementById('edit-counter-number').value = data.counter_number || 1;
                document.getElementById('edit-counter-active').checked = Boolean(data.is_active);
                const ids = Array.isArray(data.service_ids) ? data.service_ids.map(Number) : [];
                document.querySelectorAll('.edit-counter-service-cb').forEach((cb) => {
                    cb.checked = ids.includes(Number(cb.value));
                });
                window.PstUi.openModal('edit-counter-modal');
            });
        });
    });
</script>
@endpush
```

Replace `resources/views/admin/users/index.blade.php`:

```blade
@extends('layouts.workspace', [
    'activeNav' => 'users',
    'pageTitle' => 'Pengelolaan Akun Pengguna & Petugas Loket',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div role="status" class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-900">
                <x-icon name="check" class="w-4 h-4 text-[#15803D]" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="users-heading" class="text-lg font-bold text-[#0F172A]">Daftar Akun Pengguna Sistem</h1>
                <p class="text-xs text-[#475569] mt-0.5">Tambah akun petugas loket baru, kelola status aktif/nonaktif, dan atur ulang kata sandi dinas.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-user-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="users-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Nama Pegawai</th>
                            <th scope="col" class="py-3 px-4">Username</th>
                            <th scope="col" class="py-3 px-4">Email Dinas</th>
                            <th scope="col" class="py-3 px-4 text-center">Peran</th>
                            <th scope="col" class="py-3 px-4 text-center">Status Akun</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($users as $u)
                            @php
                                $resetPayload = [
                                    'action' => route('admin.users.resetPassword', $u->id),
                                    'name' => $u->name,
                                    'username' => $u->username,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-[#0F172A]">{{ $u->name }}</div>
                                    @if($u->id === Auth::id())
                                        <span class="text-[10px] font-bold uppercase text-[#0B4F9C]">Akun Anda</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#334155]">{{ $u->username }}</td>
                                <td class="py-3.5 px-4 text-xs text-[#475569]">{{ $u->email }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$u->role === 'admin' ? 'teal' : 'cobalt'">
                                        {{ strtoupper($u->role) }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$u->is_active ? 'green' : 'danger'">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-reset-user
                                                data-payload='@json($resetPayload)'
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Reset Password
                                        </button>

                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $u->is_active ? 'border border-red-200 bg-red-50 hover:bg-red-100 text-red-700' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>

    <x-modal id="create-user-modal" title="Tambah Akun Pegawai / Petugas Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-user-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Lengkap Pegawai</label>
                <input type="text" id="create-user-name" name="name" required autofocus placeholder="Contoh: Rina Melati, S.Tr.Stat."
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-username" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Pengguna (Username)</label>
                <input type="text" id="create-user-username" name="username" required autocorrect="off" autocapitalize="none" spellcheck="false" placeholder="Contoh: petugas2"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-email" class="block text-xs font-semibold text-[#0F172A] mb-1">Alamat Email Dinas</label>
                <input type="email" id="create-user-email" name="email" required placeholder="petugas2@bps.go.id"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-role" class="block text-xs font-semibold text-[#0F172A] mb-1">Peran Akses</label>
                <select id="create-user-role" name="role" required class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
                    <option value="petugas" selected>Petugas Loket Pelayanan</option>
                    <option value="admin">Administrator PST</option>
                </select>
            </div>
            <div>
                <label for="create-user-password" class="block text-xs font-semibold text-[#0F172A] mb-1">Kata Sandi Awal</label>
                <input type="password" id="create-user-password" name="password" required minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-user-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Pengguna</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="reset-user-modal" title="Atur Ulang Kata Sandi" :reset-on-close="true" max-width="max-w-md">
        <p id="reset-modal-desc" class="text-xs text-[#475569] mb-4"></p>
        <form id="reset-user-form" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="reset-new-password" class="block text-xs font-semibold text-[#0F172A] mb-1">Kata Sandi Baru</label>
                <input type="password" id="reset-new-password" name="new_password" required minlength="6" autofocus autocomplete="new-password" placeholder="Minimal 6 karakter"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="reset-user-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Atur Ulang Password</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-reset-user]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('reset-user-form').action = data.action || '';
                document.getElementById('reset-modal-desc').textContent =
                    `Masukkan kata sandi baru untuk akun ${data.name || ''} (${data.username || ''}).`;
                window.PstUi.openModal('reset-user-modal');
            });
        });
    });
</script>
@endpush
```

- [ ] **Step 6: Build Vite assets, run full PHPUnit suite, and check git status**

Run:
```bash
distrobox enter my-distrobox -- sh -c "npm run build && php artisan test" && git status -s
```
Expected: `npm run build` succeeds, ALL tests PASS (zero skipped, zero failures).

- [ ] **Step 7: Commit Task 7**

```bash
git add resources/views/admin tests/Feature/DashboardReportTest.php tests/Feature/AdminManagementTest.php
git commit -m "feat(ui): overhaul admin console views, readable report labels, and PDF styling"
```
