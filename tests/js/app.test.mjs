import test from 'node:test';
import assert from 'node:assert/strict';

class FakeClassList {
    constructor(initial = []) {
        this._set = new Set(initial);
    }
    add(...tokens) {
        tokens.forEach((t) => this._set.add(t));
    }
    remove(...tokens) {
        tokens.forEach((t) => this._set.delete(t));
    }
    contains(token) {
        return this._set.has(token);
    }
    toggle(token, force) {
        if (force === undefined) {
            if (this._set.has(token)) {
                this._set.delete(token);
                return false;
            }
            this._set.add(token);
            return true;
        }
        if (force) {
            this._set.add(token);
            return true;
        }
        this._set.delete(token);
        return false;
    }
}

function matchesSingleSelector(el, selector) {
    if (selector === 'dialog.pst-dialog') {
        return el.tagName === 'DIALOG' && el.classList.contains('pst-dialog');
    }
    if (selector === 'dialog[open]') {
        return el.tagName === 'DIALOG' && Boolean(el.open);
    }
    if (selector === 'form') {
        return el.tagName === 'FORM';
    }
    if (selector.startsWith('#')) {
        return el.id === selector.slice(1).replace(/\\/g, '');
    }
    const attrEq = selector.match(/^\[([a-zA-Z0-9_-]+)="([^"]*)"\]$/);
    if (attrEq) {
        return el.getAttribute(attrEq[1]) === attrEq[2].replace(/\\/g, '');
    }
    const attrOnly = selector.match(/^\[([a-zA-Z0-9_-]+)\]$/);
    if (attrOnly) {
        return el.hasAttribute(attrOnly[1]);
    }
    return false;
}

function matchesSelector(el, selector) {
    return selector
        .split(',')
        .map((s) => s.trim())
        .some((part) => matchesSingleSelector(el, part));
}

class FakeElement {
    constructor(tagName, { id = '', classes = [], attrs = {} } = {}) {
        this.tagName = tagName.toUpperCase();
        this.id = id;
        this.classList = new FakeClassList(classes);
        this._attrs = new Map(Object.entries(attrs));
        if (id) this._attrs.set('id', id);
        this.dataset = {};
        for (const [k, v] of Object.entries(attrs)) {
            if (k.startsWith('data-')) {
                const camel = k.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase());
                this.dataset[camel] = v;
            }
        }
        this.children = [];
        this.parentNode = null;
        this._listeners = new Map();
        this.textContent = '';
        this.focusCount = 0;
        this.ownerDocument = null;
    }

    getAttribute(name) {
        if (name === 'id') return this.id || null;
        if (name.startsWith('data-')) {
            const camel = name.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase());
            if (camel in this.dataset) return String(this.dataset[camel]);
        }
        return this._attrs.has(name) ? this._attrs.get(name) : null;
    }

    setAttribute(name, value) {
        const str = String(value);
        if (name === 'id') this.id = str;
        this._attrs.set(name, str);
        if (name.startsWith('data-')) {
            const camel = name.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase());
            this.dataset[camel] = str;
        }
    }

    removeAttribute(name) {
        this._attrs.delete(name);
        if (name.startsWith('data-')) {
            const camel = name.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase());
            delete this.dataset[camel];
        }
    }

    hasAttribute(name) {
        return this._attrs.has(name);
    }

    contains(node) {
        let cur = node;
        while (cur) {
            if (cur === this) return true;
            cur = cur.parentNode;
        }
        return false;
    }

    remove() {
        if (this.parentNode) {
            this.parentNode.children = this.parentNode.children.filter((c) => c !== this);
            this.parentNode = null;
        }
    }

    addEventListener(type, fn) {
        if (!this._listeners.has(type)) this._listeners.set(type, []);
        this._listeners.get(type).push(fn);
    }

    removeEventListener(type, fn) {
        const list = this._listeners.get(type);
        if (!list) return;
        const idx = list.indexOf(fn);
        if (idx !== -1) list.splice(idx, 1);
    }

    dispatchEvent(event) {
        const evt = {
            type: event.type,
            target: event.target ?? this,
            defaultPrevented: false,
            preventDefault() {
                this.defaultPrevented = true;
            },
            ...event,
        };
        const list = [...(this._listeners.get(evt.type) || [])];
        for (const fn of list) {
            fn(evt);
        }
        return !evt.defaultPrevented;
    }

    append(...nodes) {
        for (const node of nodes) {
            if (typeof node === 'object' && node !== null) {
                node.parentNode = this;
                this.children.push(node);
            }
        }
    }

    replaceChildren(...nodes) {
        this.children = [];
        this.append(...nodes);
    }

    querySelectorAll(selector) {
        const results = [];
        const walk = (node) => {
            for (const child of node.children) {
                if (matchesSelector(child, selector)) results.push(child);
                walk(child);
            }
        };
        walk(this);
        return results;
    }

    querySelector(selector) {
        return this.querySelectorAll(selector)[0] ?? null;
    }

    closest(selector) {
        let cur = this;
        while (cur) {
            if (matchesSelector(cur, selector)) return cur;
            cur = cur.parentNode;
        }
        return null;
    }

    focus() {
        this.focusCount += 1;
        if (this.ownerDocument) {
            this.ownerDocument.activeElement = this;
        }
    }
}

class FakeDialogElement extends FakeElement {
    constructor(opts = {}) {
        super('dialog', opts);
        this.open = false;
        this.returnValue = '';
        this.rect = { left: 100, right: 400, top: 100, bottom: 400 };
    }

    getBoundingClientRect() {
        return this.rect;
    }

    showModal() {
        if (this.open) {
            const err = new Error('Failed to execute "showModal": dialog is already open.');
            err.name = 'InvalidStateError';
            throw err;
        }
        this.open = true;
    }

    close(returnValue) {
        if (returnValue !== undefined) {
            this.returnValue = String(returnValue);
        }
        if (!this.open) return;
        this.open = false;
        this.dispatchEvent({ type: 'close', target: this });
    }
}

class FakeDocument {
    constructor() {
        this.body = new FakeElement('body');
        this.body.ownerDocument = this;
        this._listeners = new Map();
        this.activeElement = null;
    }

    register(el) {
        el.ownerDocument = this;
        this.body.append(el);
        return el;
    }

    createElement(tag) {
        const el = tag.toLowerCase() === 'dialog'
            ? new FakeDialogElement()
            : new FakeElement(tag);
        el.ownerDocument = this;
        return el;
    }

    getElementById(id) {
        return this.querySelector(`#${id}`);
    }

    querySelectorAll(selector) {
        return this.body.querySelectorAll(selector);
    }

    querySelector(selector) {
        return this.body.querySelector(selector);
    }

    addEventListener(type, fn) {
        if (!this._listeners.has(type)) this._listeners.set(type, []);
        this._listeners.get(type).push(fn);
    }

    removeEventListener(type, fn) {
        const list = this._listeners.get(type);
        if (!list) return;
        const idx = list.indexOf(fn);
        if (idx !== -1) list.splice(idx, 1);
    }

    dispatchEvent(event) {
        const evt = {
            defaultPrevented: false,
            preventDefault() {
                this.defaultPrevented = true;
            },
            ...event,
        };
        const list = [...(this._listeners.get(evt.type) || [])];
        for (const fn of list) {
            fn(evt);
        }
        return !evt.defaultPrevented;
    }
}

function setupFakeDom() {
    const doc = new FakeDocument();
    globalThis.document = doc;
    globalThis.window = {
        setTimeout: globalThis.setTimeout.bind(globalThis),
        clearTimeout: globalThis.clearTimeout.bind(globalThis),
    };
    globalThis.HTMLDialogElement = class FakeHTMLDialogElement {};
    globalThis.CSS = {
        escape(value) {
            return String(value).replace(/([^\w-])/g, '\\$1');
        },
    };
    return doc;
}

const initialDoc = setupFakeDom();
const app = await import('../../resources/js/app.js');

function buildConfirmDialog(doc) {
    const dialog = new FakeDialogElement({
        id: 'pst-confirm-dialog',
        classes: ['pst-dialog'],
        attrs: { closedby: 'any' },
    });
    const titleEl = new FakeElement('h2', { attrs: { 'data-confirm-title': '' } });
    const messageEl = new FakeElement('p', { attrs: { 'data-confirm-message': '' } });
    const okBtn = new FakeElement('button', { attrs: { 'data-confirm-accept': '' } });
    const cancelBtn = new FakeElement('button', { attrs: { 'data-confirm-cancel': '' } });
    dialog.append(titleEl, messageEl, okBtn, cancelBtn);
    [titleEl, messageEl, okBtn, cancelBtn].forEach((el) => {
        el.ownerDocument = doc;
    });
    doc.register(dialog);
    return { dialog, titleEl, messageEl, okBtn, cancelBtn };
}

test('initDialogs + showConfirm preserves returnValue for confirm/cancel, resets afterwards, and guards dialog.open', async () => {
    const doc = setupFakeDom();
    const { dialog } = buildConfirmDialog(doc);

    app.initDialogs(doc);

    // Pre-existing stale returnValue must be cleared before showModal
    dialog.returnValue = 'confirm';

    const confirmPromise = app.showConfirm({
        title: 'Hapus antrean',
        message: 'Lanjutkan penghapusan?',
    });

    assert.equal(dialog.open, true);
    assert.equal(dialog.returnValue, '');

    dialog.close('confirm');
    const confirmed = await confirmPromise;
    assert.equal(confirmed, true);

    await new Promise((resolve) => queueMicrotask(resolve));
    assert.equal(dialog.returnValue, '');

    // Cancel / Escape dismissal (no returnValue arg) resolves false even if previous state existed
    const cancelPromise = app.showConfirm({ title: 'Batalkan' });
    assert.equal(dialog.open, true);
    dialog.close();
    const cancelled = await cancelPromise;
    assert.equal(cancelled, false);

    await new Promise((resolve) => queueMicrotask(resolve));
    assert.equal(dialog.returnValue, '');

    // Guard when dialog.open is already true: resolves false without throwing InvalidStateError
    dialog.open = true;
    const blocked = await app.showConfirm({ title: 'Should not reopen' });
    assert.equal(blocked, false);
    assert.equal(dialog.open, true);
});

test('initDialogs closedby="any" fallback ignores pointerdown originating from child popover inside dialog', () => {
    const doc = setupFakeDom();
    const { dialog, okBtn } = buildConfirmDialog(doc);

    app.initDialogs(doc);
    app.openModal('pst-confirm-dialog');
    assert.equal(dialog.open, true);

    // Overflowing child element pressed at coordinates outside dialog box must NOT dismiss
    dialog.dispatchEvent({ type: 'pointerdown', target: okBtn, clientX: 50, clientY: 50 });
    dialog.dispatchEvent({ type: 'pointerup', target: dialog, clientX: 50, clientY: 50 });
    assert.equal(dialog.open, true);

    // Backdrop click with target === dialog outside dialog rect DOES dismiss
    dialog.dispatchEvent({ type: 'pointerdown', target: dialog, clientX: 50, clientY: 50 });
    dialog.dispatchEvent({ type: 'pointerup', target: dialog, clientX: 50, clientY: 50 });
    assert.equal(dialog.open, false);
});

test('initNavDrawer called twice binds click and Escape handlers only once', () => {
    const doc = setupFakeDom();
    const toggle = new FakeElement('button', {
        id: 'workspace-nav-toggle',
        attrs: { 'aria-expanded': 'false' },
    });
    const navList = new FakeElement('ul', {
        id: 'workspace-nav-list',
        classes: ['hidden'],
    });
    doc.register(toggle);
    doc.register(navList);

    app.initNavDrawer(doc);
    app.initNavDrawer(doc);

    assert.equal(toggle.dataset.pstNavBound, 'true');
    assert.equal(navList.dataset.pstEscBound, 'true');

    // Single click opens once (duplicate binding would open then immediately close)
    toggle.dispatchEvent({ type: 'click' });
    assert.equal(toggle.getAttribute('aria-expanded'), 'true');
    assert.equal(navList.classList.contains('hidden'), false);
    assert.equal(navList.classList.contains('flex'), true);

    // Escape closes once and focuses toggle once
    doc.dispatchEvent({ type: 'keydown', key: 'Escape' });
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(navList.classList.contains('hidden'), true);
    assert.equal(navList.classList.contains('flex'), false);
    assert.equal(toggle.focusCount, 1);
});

test('renderFormErrors and clearFormErrors sync aria-invalid, aria-describedby, and reset inputs without data-error', () => {
    const doc = setupFakeDom();

    const summaryEl = new FakeElement('div', {
        id: 'form-summary',
        classes: ['hidden'],
    });
    const errorList = new FakeElement('ul', { attrs: { 'data-error-list': '' } });
    summaryEl.append(errorList);
    errorList.ownerDocument = doc;
    doc.register(summaryEl);

    const formEl = new FakeElement('form', { id: 'test-form' });
    const emailInput = new FakeElement('input', {
        id: 'email',
        attrs: {
            name: 'email',
            'data-error': 'email-error',
            'aria-describedby': 'email-hint',
        },
    });
    const noteInput = new FakeElement('input', {
        id: 'note',
        attrs: {
            name: 'note',
        },
    });
    const emailErrorEl = new FakeElement('p', {
        id: 'email-error',
        attrs: { 'data-visible': 'false' },
    });

    [emailInput, noteInput].forEach((el) => {
        el.ownerDocument = doc;
        formEl.append(el);
    });
    doc.register(formEl);
    doc.register(emailErrorEl);

    app.renderFormErrors(formEl, summaryEl, {
        email: ['Alamat email wajib diisi.'],
        note: ['Catatan terlalu pendek.'],
    });

    assert.equal(emailInput.getAttribute('aria-invalid'), 'true');
    assert.equal(emailInput.getAttribute('aria-describedby'), 'email-hint email-error');
    assert.equal(emailErrorEl.textContent, 'Alamat email wajib diisi.');
    assert.equal(emailErrorEl.dataset.visible, 'true');
    assert.equal(noteInput.getAttribute('aria-invalid'), 'true');
    assert.equal(summaryEl.classList.contains('hidden'), false);
    assert.equal(summaryEl.focusCount, 1);
    assert.equal(errorList.children.length, 2);

    app.clearFormErrors(formEl, summaryEl);

    assert.equal(emailInput.getAttribute('aria-invalid'), 'false');
    assert.equal(emailInput.getAttribute('aria-describedby'), 'email-hint');
    assert.equal(emailErrorEl.textContent, '');
    assert.equal(emailErrorEl.dataset.visible, 'false');
    assert.equal(noteInput.getAttribute('aria-invalid'), 'false');
    assert.equal(summaryEl.classList.contains('hidden'), true);
    assert.equal(errorList.children.length, 0);
});

test('initNavDrawer works on a non-Document element root and FakeElement.removeAttribute syncs dataset', () => {
    const doc = setupFakeDom();
    const header = new FakeElement('header', { id: 'workspace-header' });
    const toggle = new FakeElement('button', {
        id: 'workspace-nav-toggle',
        attrs: { 'aria-expanded': 'false', 'data-temp-flag': '1' },
    });
    const navList = new FakeElement('ul', {
        id: 'workspace-nav-list',
        classes: ['hidden'],
    });

    toggle.ownerDocument = doc;
    navList.ownerDocument = doc;
    header.append(toggle, navList);
    doc.register(header);

    assert.equal(toggle.dataset.tempFlag, '1');
    toggle.removeAttribute('data-temp-flag');
    assert.equal('tempFlag' in toggle.dataset, false);

    app.initNavDrawer(header);

    toggle.dispatchEvent({ type: 'click' });
    assert.equal(toggle.getAttribute('aria-expanded'), 'true');
    assert.equal(navList.classList.contains('hidden'), false);

    doc.dispatchEvent({ type: 'keydown', key: 'Escape' });
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(navList.classList.contains('hidden'), true);
    assert.equal(toggle.focusCount, 1);
});

test('showToast debounces burst live announcements and keeps focused toast open on mouseleave', async () => {
    const doc = setupFakeDom();
    const stack = new FakeElement('div', { id: 'pst-toast-stack' });
    const announcer = new FakeElement('div', { id: 'pst-toast-announcer' });
    doc.register(stack);
    doc.register(announcer);

    app.showToast('Pesan pertama', 'info', 40);
    const toast = app.showToast('Pesan kedua', 'success', 40);
    assert.ok(toast);
    assert.equal(stack.children.length, 2);

    const closeBtn = toast.children[1];
    doc.activeElement = closeBtn;
    toast.dispatchEvent({ type: 'focusin', target: closeBtn });
    toast.dispatchEvent({ type: 'mouseleave', target: toast });

    await new Promise((resolve) => setTimeout(resolve, 70));
    assert.equal(announcer.textContent, 'Pesan kedua');
    assert.equal(stack.children.includes(toast), true);

    doc.activeElement = null;
    toast.dispatchEvent({ type: 'focusout', target: closeBtn });

    await new Promise((resolve) => setTimeout(resolve, 70));
    assert.equal(stack.children.includes(toast), false);
});

test('initAccountMenu toggles menu, closes on outside click and Escape with focus restoration', () => {
    const doc = setupFakeDom();
    const wrapper = new FakeElement('div', { attrs: { 'data-account-menu': '' } });
    const toggle = new FakeElement('button', {
        id: 'workspace-account-toggle',
        attrs: { 'aria-expanded': 'false' },
    });
    const menu = new FakeElement('div', {
        id: 'workspace-account-menu',
        classes: ['hidden'],
    });
    const outside = new FakeElement('div', { id: 'outside' });

    wrapper.ownerDocument = doc;
    toggle.ownerDocument = doc;
    menu.ownerDocument = doc;
    outside.ownerDocument = doc;
    wrapper.append(toggle, menu);
    doc.register(wrapper);
    doc.register(outside);

    app.initAccountMenu(doc);
    app.initAccountMenu(doc);

    toggle.dispatchEvent({ type: 'click', target: toggle });
    assert.equal(toggle.getAttribute('aria-expanded'), 'true');
    assert.equal(menu.classList.contains('hidden'), false);

    doc.dispatchEvent({ type: 'click', target: outside });
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(menu.classList.contains('hidden'), true);

    toggle.dispatchEvent({ type: 'click', target: toggle });
    assert.equal(menu.classList.contains('hidden'), false);

    doc.dispatchEvent({ type: 'keydown', key: 'Escape' });
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(menu.classList.contains('hidden'), true);
    assert.equal(toggle.focusCount, 1);
});
