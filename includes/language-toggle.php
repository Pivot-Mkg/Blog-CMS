<div class="lang-switcher">
    <div class="container lang-switcher__inner">
        <div class="lang-menu" aria-label="Language selection">
            <button class="lang-toggle" id="lang-toggle" type="button" aria-haspopup="true" aria-expanded="false">
                <span class="lang-toggle__label">Translate</span>
                <span aria-hidden="true" class="lang-toggle__caret">&#9662;</span>
            </button>
            <div class="lang-menu__list" id="lang-menu-list" role="menu" aria-label="Select language"></div>
        </div>
    </div>
</div>
<div id="google_translate_element" class="sr-only"></div>
<script>
    (function () {
        const languages = [
            { code: 'en', label: 'English' },
            { code: 'es', label: 'Spanish' },
            { code: 'fr', label: 'French' },
            { code: 'de', label: 'German' },
            { code: 'hi', label: 'Hindi' },
            { code: 'ar', label: 'Arabic' },
            { code: 'pt', label: 'Portuguese' },
            { code: 'ru', label: 'Russian' },
            { code: 'ja', label: 'Japanese' },
            { code: 'ko', label: 'Korean' },
            { code: 'it', label: 'Italian' },
            { code: 'nl', label: 'Dutch' },
            { code: 'tr', label: 'Turkish' },
            { code: 'bn', label: 'Bengali' },
            { code: 'ur', label: 'Urdu' },
            { code: 'fa', label: 'Persian' },
            { code: 'id', label: 'Indonesian' },
            { code: 'ms', label: 'Malay' },
            { code: 'vi', label: 'Vietnamese' },
            { code: 'th', label: 'Thai' },
            { code: 'pl', label: 'Polish' },
            { code: 'uk', label: 'Ukrainian' },
            { code: 'sv', label: 'Swedish' },
            { code: 'zh-CN', label: 'Chinese (Simplified)' },
            { code: 'zh-TW', label: 'Chinese (Traditional)' }
        ];
        const languageNames = Object.fromEntries(languages.map(item => [item.code, item.label]));

        window.googleTranslateElementInit = function () {
            if (window.google && google.translate) {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: languages.map(item => item.code).join(','),
                    autoDisplay: false
                }, 'google_translate_element');
            }
        };

        const toggle = document.getElementById('lang-toggle');
        const label = toggle ? toggle.querySelector('.lang-toggle__label') : null;
        const menu = document.getElementById('lang-menu-list');

        if (menu) {
            const fragment = document.createDocumentFragment();
            languages.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'lang-option';
                button.dataset.lang = item.code;
                button.setAttribute('role', 'menuitem');
                button.textContent = item.label;
                fragment.appendChild(button);
            });
            menu.appendChild(fragment);
        }

        const options = menu ? menu.querySelectorAll('[data-lang]') : [];

        const getCurrentLang = () => {
            const match = document.cookie.match(/(?:^|;)\s*googtrans=([^;]+)/);
            if (!match) return 'en';
            const parts = decodeURIComponent(match[1]).split('/');
            return parts[2] || 'en';
        };

        const setActive = (lang) => {
            options.forEach(btn => {
                if (btn.dataset.lang === lang) {
                    btn.classList.add('is-active');
                } else {
                    btn.classList.remove('is-active');
                }
            });
            if (label) {
                label.textContent = languageNames[lang] || 'Translate';
            }
        };

        const setLanguage = (lang) => {
            const value = lang === 'en' ? '/auto/en' : `/auto/${lang}`;
            document.cookie = `googtrans=${value};path=/`;
            document.cookie = `googtrans=${value};domain=${location.hostname};path=/`;
            location.reload();
        };

        const toggleMenu = () => {
            if (!menu || !toggle) return;
            const isOpen = menu.classList.contains('is-open');
            menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(!isOpen));
        };

        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleMenu();
            });
        }

        options.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const lang = btn.dataset.lang;
                setActive(lang);
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                setLanguage(lang);
            });
        });

        document.addEventListener('click', (event) => {
            if (!menu || !toggle) return;
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        const hideTranslateBanner = () => {
            const iframe = document.querySelector('iframe.goog-te-banner-frame');
            const banner = document.querySelector('.goog-te-banner-frame');
            if (iframe && iframe.parentNode) iframe.parentNode.removeChild(iframe);
            if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
            const skip = document.querySelector('.skiptranslate');
            if (skip && skip.parentNode && skip.tagName === 'BODY') skip.parentNode.removeChild(skip);
            document.documentElement.style.setProperty('margin-top', '0px', 'important');
            document.documentElement.style.setProperty('top', '0px', 'important');
            document.body.style.setProperty('top', '0px', 'important');
            document.body.style.setProperty('position', 'static', 'important');
        };

        window.addEventListener('load', () => {
            hideTranslateBanner();
            setActive(getCurrentLang());
            let attempts = 0;
            const interval = setInterval(() => {
                hideTranslateBanner();
                attempts += 1;
                if (attempts > 20) clearInterval(interval);
            }, 400);

            const observer = new MutationObserver(() => hideTranslateBanner());
            observer.observe(document.documentElement, { childList: true, subtree: true });
        });
    })();
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
