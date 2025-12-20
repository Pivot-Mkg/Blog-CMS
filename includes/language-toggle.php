<div class="lang-switcher">
    <div class="container lang-switcher__inner">
        <div class="lang-menu">
            <button class="lang-toggle" id="lang-toggle" type="button" aria-haspopup="true" aria-expanded="false">
                <span class="lang-toggle__label">Translate</span>
                <span aria-hidden="true">▼</span>
            </button>
            <div class="lang-menu__list" id="lang-menu-list" role="menu" aria-label="Select language">
                <button class="lang-option" data-lang="en" role="menuitem">English</button>
                <button class="lang-option" data-lang="es" role="menuitem">Español</button>
                <button class="lang-option" data-lang="fr" role="menuitem">Français</button>
            </div>
        </div>
    </div>
</div>
<div id="google_translate_element" class="sr-only"></div>
<script>
    function googleTranslateElementInit() {
        if (window.google && google.translate) {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,es,fr',
                autoDisplay: false
            }, 'google_translate_element');
        }
    }

    (function () {
        const toggle = document.getElementById('lang-toggle');
        const label = toggle ? toggle.querySelector('.lang-toggle__label') : null;
        const menu = document.getElementById('lang-menu-list');
        const options = menu ? menu.querySelectorAll('[data-lang]') : [];
        const names = { en: 'English', es: 'Español', fr: 'Français' };

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
                label.textContent = names[lang] || 'Translate';
            }
        };

        const setLanguage = (lang) => {
            const value = lang === 'en' ? '/auto/en' : `/auto/${lang}`;
            document.cookie = `googtrans=${value};path=/`;
            document.cookie = `googtrans=${value};domain=${location.hostname};path=/`;
            location.reload();
        };

        const toggleMenu = () => {
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

        setActive(getCurrentLang());
    })();
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
