document.addEventListener('DOMContentLoaded', () => {
    const previewTheme = document.body.dataset.previewTheme;

    if (previewTheme) {
        document.querySelectorAll('a[href]').forEach((link) => {
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || url.hash || url.pathname.startsWith('/admin')) return;
            if (!url.searchParams.has('theme')) url.searchParams.set('theme', previewTheme);
            link.href = url.toString();
        });
    }

    const closeHeaderMenus = (header, returnFocus = false) => {
        if (!header) return;
        const toggle = header.querySelector('[data-nova-menu-toggle]');
        const wasOpen = header.classList.contains('is-menu-open');
        const openDisclosure = header.querySelector('[data-nova-dropdown][open] > summary');
        header.classList.remove('is-menu-open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', 'Open navigation menu');
        header.querySelectorAll('[data-nova-dropdown][open]').forEach((dropdown) => {
            dropdown.open = false;
            dropdown.querySelector(':scope > summary')?.setAttribute('aria-expanded', 'false');
        });
        if (returnFocus) {
            if (wasOpen) toggle?.focus();
            else openDisclosure?.focus();
        }
    };

    const updateNovaHeader = (header) => {
        const slot = header?.closest('[data-nova-header-slot]');
        if (!header || !slot) return;
        const isHomePage = header.dataset.homePage === 'true';
        const hero = isHomePage ? document.querySelector('.nova-hero') : null;
        const compact = Boolean(hero && hero.getBoundingClientRect().bottom <= 0);
        slot.classList.toggle('is-no-hero', isHomePage && !hero);
        header.classList.toggle('is-compact', compact);
        slot.classList.toggle('is-compact', compact);
        if (!window.matchMedia('(max-width: 1000px)').matches && header.classList.contains('is-menu-open')) closeHeaderMenus(header);
    };

    const initializeNovaHeaders = () => {
        document.querySelectorAll('.theme-rexxsol-nova [data-nova-header]').forEach((header) => {
            if (header.dataset.novaHeaderInitialized !== 'true') {
                header.dataset.novaHeaderInitialized = 'true';
            }
            header.querySelectorAll('[data-nova-dropdown]').forEach((dropdown) => {
                if (dropdown.dataset.novaDropdownInitialized !== 'true') {
                    dropdown.dataset.novaDropdownInitialized = 'true';
                    const summary = dropdown.querySelector(':scope > summary');
                    const syncExpanded = () => summary?.setAttribute('aria-expanded', String(dropdown.open));
                    dropdown.addEventListener('toggle', syncExpanded);
                    dropdown.addEventListener('pointerenter', (event) => {
                        if (event.pointerType === 'mouse' && window.matchMedia('(min-width: 1001px)').matches) dropdown.open = true;
                    });
                    dropdown.addEventListener('pointerleave', (event) => {
                        if (event.pointerType === 'mouse' && window.matchMedia('(min-width: 1001px)').matches && !dropdown.contains(document.activeElement)) dropdown.open = false;
                    });
                    dropdown.addEventListener('focusout', (event) => {
                        if (!dropdown.contains(event.relatedTarget) && !dropdown.matches(':hover')) dropdown.open = false;
                    });
                    syncExpanded();
                }
            });
            header.querySelectorAll('.nova-mobile-group').forEach((group) => {
                if (group.dataset.novaGroupInitialized !== 'true') {
                    group.dataset.novaGroupInitialized = 'true';
                    const summary = group.querySelector(':scope > summary');
                    const syncExpanded = () => summary?.setAttribute('aria-expanded', String(group.open));
                    group.addEventListener('toggle', syncExpanded);
                    syncExpanded();
                }
            });
            updateNovaHeader(header);
        });
    };

    if (document.documentElement.dataset.novaHeaderEvents !== 'hero-boundary-v2') {
        document.documentElement.dataset.novaHeaderEvents = 'hero-boundary-v2';
        let headerFrame = 0;
        const scheduleHeaderUpdate = () => {
            if (headerFrame) return;
            headerFrame = window.requestAnimationFrame(() => {
                headerFrame = 0;
                document.querySelectorAll('.theme-rexxsol-nova [data-nova-header]').forEach(updateNovaHeader);
            });
        };

        window.addEventListener('scroll', scheduleHeaderUpdate, { passive: true });
        window.addEventListener('resize', scheduleHeaderUpdate, { passive: true });
        window.addEventListener('pageshow', scheduleHeaderUpdate);
        window.addEventListener('popstate', scheduleHeaderUpdate);
        document.addEventListener('visibilitychange', scheduleHeaderUpdate);
        document.addEventListener('livewire:navigated', () => {
            initializeNovaHeaders();
            scheduleHeaderUpdate();
        });
        document.addEventListener('click', (event) => {
            const backToTop = event.target.closest('[data-nova-back-to-top]');
            if (backToTop) {
                window.scrollTo({
                    top: 0,
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                });
                return;
            }

            const toggle = event.target.closest('[data-nova-menu-toggle]');
            if (toggle) {
                const header = toggle.closest('[data-nova-header]');
                const isOpen = header.classList.toggle('is-menu-open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                if (!isOpen) closeHeaderMenus(header);
                return;
            }

            const containingHeader = event.target.closest('[data-nova-header]');
            document.querySelectorAll('.theme-rexxsol-nova [data-nova-header]').forEach((header) => {
                if (header !== containingHeader) closeHeaderMenus(header);
                if (header === containingHeader && event.target.closest('.nova-header-menu a')) closeHeaderMenus(header);
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.theme-rexxsol-nova [data-nova-header]').forEach((header) => {
                const hasOpenMenu = header.classList.contains('is-menu-open') || header.querySelector('[data-nova-dropdown][open]');
                if (hasOpenMenu) closeHeaderMenus(header, true);
            });
        });
    }
    initializeNovaHeaders();

    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || !input.matches('[data-phone-number-only]')) return;

        const sanitize = (value) => {
            const hasLeadingPlus = value.startsWith('+');
            const digitsAndFormatting = value.replace(/[^\d().\s-]/g, '');
            return hasLeadingPlus ? `+${digitsAndFormatting}` : digitsAndFormatting;
        };
        const cursor = input.selectionStart ?? input.value.length;
        const sanitizedCursor = sanitize(input.value.slice(0, cursor)).length;
        const sanitizedValue = sanitize(input.value);

        if (sanitizedValue !== input.value) {
            input.value = sanitizedValue;
            input.setSelectionRange(sanitizedCursor, sanitizedCursor);
        }
    });

    document.querySelectorAll('.theme-rexxsol-nova [data-contact-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.contactSubmitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.contactSubmitting = 'true';
            const button = form.querySelector('[data-contact-submit]');
            if (!button) return;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('is-loading');
            const label = button.querySelector('[data-contact-submit-label]');
            if (label) label.textContent = 'Sending...';
            const icon = button.querySelector('[data-contact-submit-icon]');
            const spinner = button.querySelector('[data-contact-submit-spinner]');
            if (icon) icon.hidden = true;
            if (spinner) spinner.hidden = false;
        });
    });

    const faq = document.querySelector('.theme-rexxsol-nova [data-nova-faq]');
    if (faq) {
        const buttons = [...faq.querySelectorAll('button[aria-controls]')];
        const setExpanded = (button, isExpanded) => {
            const panel = document.getElementById(button.getAttribute('aria-controls'));
            button.setAttribute('aria-expanded', String(isExpanded));
            button.closest('.nova-faq-item')?.classList.toggle('is-expanded', isExpanded);
            panel?.setAttribute('aria-hidden', String(!isExpanded));
        };

        buttons.forEach((button, index) => {
            setExpanded(button, index === 0);
            button.addEventListener('click', () => {
                const willOpen = button.getAttribute('aria-expanded') !== 'true';
                buttons.forEach((otherButton) => setExpanded(otherButton, otherButton === button && willOpen));
            });
        });
    }

    const carousel = document.querySelector('[data-nova-testimonials]');
    if (carousel) {
        carousel.classList.add('is-carousel-ready');
        const viewport = carousel.querySelector('[data-testimonial-viewport]');
        const track = carousel.querySelector('[data-testimonial-track]');
        const cards = [...track.children];
        const controls = carousel.querySelector('[data-testimonial-controls]');
        const dots = carousel.querySelector('[data-testimonial-dots]');
        let firstVisible = 0;
        let pointerStart = null;
        const visibleCount = () => window.matchMedia('(max-width: 520px)').matches ? 1 : (window.matchMedia('(max-width: 1050px)').matches ? 2 : 3);
        const pageStart = (page) => page * visibleCount();

        const render = (resetDots = false) => {
            const perPage = visibleCount();
            const pageCount = Math.ceil(cards.length / perPage);
            const activePage = Math.min(Math.floor(firstVisible / perPage), Math.max(0, pageCount - 1));
            firstVisible = pageStart(activePage);
            const activeCard = firstVisible + Math.min(perPage - 1, Math.floor(perPage / 2));

            cards.forEach((card, index) => card.classList.toggle('is-active', index === activeCard));
            controls.hidden = pageCount <= 1;
            dots.hidden = pageCount <= 1;
            if (resetDots || dots.children.length !== pageCount) {
                dots.replaceChildren(...Array.from({ length: pageCount }, (_, index) => {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.setAttribute('aria-label', `Show testimonial page ${index + 1}`);
                    dot.addEventListener('click', () => {
                        firstVisible = pageStart(index);
                        render();
                    });
                    return dot;
                }));
            }
            [...dots.children].forEach((dot, index) => dot.setAttribute('aria-current', String(index === activePage)));
            const activeOffset = cards[firstVisible]?.offsetLeft ?? 0;
            track.style.transform = `translate3d(-${activeOffset}px, 0, 0)`;
        };

        carousel.querySelector('[data-testimonial-prev]').addEventListener('click', () => {
            const perPage = visibleCount();
            firstVisible = firstVisible === 0 ? Math.max(0, (Math.ceil(cards.length / perPage) - 1) * perPage) : firstVisible - perPage;
            render();
        });
        carousel.querySelector('[data-testimonial-next]').addEventListener('click', () => {
            const perPage = visibleCount();
            firstVisible = firstVisible + perPage >= cards.length ? 0 : firstVisible + perPage;
            render();
        });
        carousel.addEventListener('keydown', (event) => {
            if (event.target.closest('button, a')) return;
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                carousel.querySelector(event.key === 'ArrowLeft' ? '[data-testimonial-prev]' : '[data-testimonial-next]').click();
            }
        });
        viewport.addEventListener('pointerdown', (event) => { pointerStart = event.clientX; });
        viewport.addEventListener('pointerup', (event) => {
            if (pointerStart === null) return;
            const delta = event.clientX - pointerStart;
            pointerStart = null;
            if (Math.abs(delta) < 40) return;
            carousel.querySelector(delta < 0 ? '[data-testimonial-next]' : '[data-testimonial-prev]').click();
        });
        viewport.addEventListener('pointercancel', () => { pointerStart = null; });
        window.addEventListener('resize', () => render(true), { passive: true });
        render(true);
    }

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
        const revealTargets = document.querySelectorAll('.nova-section, .nova-testimonials, .nova-process, .nova-why, .nova-faq, .nova-project-cta, .nova-about-page [data-about-reveal]');
        revealTargets.forEach((target) => target.classList.add('nova-reveal', 'is-pending'));
        const observer = new IntersectionObserver((entries, revealObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting && entry.boundingClientRect.bottom > 0) return;
                entry.target.classList.remove('is-pending');
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
        const revealPassedTargets = () => {
            revealTargets.forEach((target) => {
                if (!target.classList.contains('is-pending') || target.getBoundingClientRect().bottom > 0) return;
                target.classList.remove('is-pending');
                target.classList.add('is-visible');
                observer.unobserve(target);
            });
        };
        revealTargets.forEach((target) => observer.observe(target));
        window.addEventListener('scroll', revealPassedTargets, { passive: true });
        window.addEventListener('resize', revealPassedTargets, { passive: true });
        revealPassedTargets();
    }
});