import './bootstrap';

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.data('languageManager', () => ({
        lang: localStorage.getItem('eciudadagad_lang') || 'fil',
        init() {
            this.applyLang(this.lang);
            window.addEventListener('set-lang', (e) => {
                this.setLang(e.detail);
            });
        },
        setLang(l) {
            if (l !== 'fil' && l !== 'en') l = 'fil';
            this.lang = l;
            localStorage.setItem('eciudadagad_lang', l);
            this.applyLang(l);
            window.location.reload();
        },
        applyLang(l) {
            document.documentElement.setAttribute('lang', l === 'fil' ? 'fil' : 'en');
        }
    }));
});

// ---- Navigation-aware page behaviors ----
// These use delegated listeners scoped on the document so they keep working
// after Livewire `wire:navigate` swaps the page (DOMContentLoaded only fires
// on the very first full page load).

function applySavedTheme() {
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        html.classList.add('dark');
    } else if (savedTheme === 'light') {
        html.classList.remove('dark');
    }
}

function uppercaseInput(el) {
    const start = el.selectionStart;
    const end = el.selectionEnd;
    el.value = el.value.toUpperCase();
    try {
        el.setSelectionRange(start, end);
    } catch (_) {
        /* ignore */
    }
}

function formatPhone(el) {
    let value = el.value.replace(/[^0-9]/g, '');
    if (value.length > 0 && value[0] !== '0') {
        value = '0' + value;
    }
    if (value.length > 11) value = value.substring(0, 11);
    let formatted = '';
    if (value.length > 0) formatted = value.substring(0, 4);
    if (value.length > 4) formatted += '-' + value.substring(4, 7);
    if (value.length > 7) formatted += '-' + value.substring(7, 11);
    el.value = formatted;
}

function handleFileUpload(input) {
    const wrapper = input.closest('.file-upload-wrapper');
    const preview = wrapper?.querySelector('.file-preview');
    const errorEl = wrapper?.querySelector('.file-error');
    const nameEl = wrapper?.querySelector('.file-name');

    if (errorEl) errorEl.textContent = '';
    if (nameEl) nameEl.textContent = '';

    const file = input.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        if (errorEl) errorEl.textContent = 'File size must not exceed 5 MB.';
        input.value = '';
        return;
    }

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!validTypes.includes(file.type)) {
        if (errorEl) errorEl.textContent = 'Only JPG, JPEG, PNG, and PDF files are allowed.';
        input.value = '';
        return;
    }

    if (nameEl) nameEl.textContent = file.name;

    if (preview && file.type !== 'application/pdf') {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.innerHTML = `<img src="${e.target.result}" class="max-h-32 rounded-lg" alt="Preview" />`;
        };
        reader.readAsDataURL(file);
    }
}

function computeAgeGroup(group) {
    const month = group.querySelector('[data-month]') || document.getElementById(group.dataset.month || '');
    const day = group.querySelector('[data-day]') || document.getElementById(group.dataset.day || '');
    const year = group.querySelector('[data-year]') || document.getElementById(group.dataset.year || '');
    const target = group.querySelector('[data-age-target]') || document.getElementById(group.dataset.target || '');

    if (month && day && year && target && month.value && day.value && year.value) {
        const birthDate = new Date(year.value, month.value - 1, day.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const mDiff = today.getMonth() - birthDate.getMonth();
        if (mDiff < 0 || (mDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        target.value = age > 0 ? age : 0;
        target.dispatchEvent(new Event('input'));
    }
}

function activateTab(btn) {
    const tab = btn.getAttribute('data-tab');
    const scope = btn.closest('main') || btn.closest('body');
    if (!tab || !scope) return;

    scope.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-tab') === tab);
    });
    scope.querySelectorAll('[data-tab-content]').forEach(c => {
        c.style.display = c.getAttribute('data-tab-content') === tab ? 'block' : 'none';
    });
}

document.addEventListener('click', (e) => {
    const themeToggle = e.target.closest('#theme-toggle');
    if (themeToggle) {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }

    const tabBtn = e.target.closest('.tab-btn');
    if (tabBtn) {
        activateTab(tabBtn);
    }
});

// Capture phase so these run before Alpine's own element-level handlers
document.addEventListener('input', (e) => {
    const el = e.target;
    if (!el || !el.classList) return;
    if (el.classList.contains('uppercase-input')) uppercaseInput(el);
    if (el.classList.contains('phone-mask')) formatPhone(el);
}, true);

document.addEventListener('change', (e) => {
    const el = e.target;
    if (!el || !el.classList) return;
    if (el.classList.contains('file-upload-input')) handleFileUpload(el);

    const group = el.closest('[data-age-field]');
    if (group) computeAgeGroup(group);
}, true);

const initPageBehaviors = () => {
    applySavedTheme();
};

document.addEventListener('DOMContentLoaded', initPageBehaviors);
window.addEventListener('livewire:navigated', initPageBehaviors);

// Alpine is started by Livewire v4 — no need to call Alpine.start() here