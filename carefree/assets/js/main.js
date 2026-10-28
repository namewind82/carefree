/*!
 * Sleek theme scripts
 * 主题交互：深色模式 / 抽屉导航 / 搜索 / 加载更多 / 灯箱 / 代码复制 / 表情
 */
(function () {
    'use strict';

    var doc = document;
    var root = doc.documentElement;
    var body = doc.body;

    function $(selector, scope) {
        return (scope || doc).querySelector(selector);
    }

    function $all(selector, scope) {
        return Array.prototype.slice.call((scope || doc).querySelectorAll(selector));
    }

    /* ------------------------------------------------------------ theme */
    var THEME_KEY = 'sleek-mode';
    var metaTheme = $('meta[name="theme-color"]');

    function storedMode() {
        try {
            return localStorage.getItem(THEME_KEY);
        } catch (e) {
            return null;
        }
    }

    function systemDark() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function applyTheme(mode, persist) {
        var dark = mode === 'dark' || (mode === 'auto' && systemDark());
        root.classList.toggle('dark', dark);
        root.setAttribute('data-mode', mode);
        root.style.colorScheme = dark ? 'dark' : 'light';
        if (metaTheme) {
            metaTheme.setAttribute('content', dark ? '#0e1626' : '#f4f5f7');
        }
        if (persist) {
            try {
                localStorage.setItem(THEME_KEY, mode);
            } catch (e) {
                /* ignore */
            }
        }
    }

    function currentMode() {
        var mode = root.getAttribute('data-mode');
        return mode === 'light' || mode === 'dark' ? mode : 'auto';
    }

    function toggleTheme() {
        applyTheme(root.classList.contains('dark') ? 'light' : 'dark', true);
    }

    applyTheme(storedMode() || currentMode(), false);

    ['theme-toggle', 'drawer-theme', 'footer-theme'].forEach(function (id) {
        var el = doc.getElementById(id);
        if (el) {
            el.addEventListener('click', toggleTheme);
        }
    });

    if (window.matchMedia) {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var onSystemChange = function () {
            if (!storedMode() && currentMode() === 'auto') {
                applyTheme('auto', false);
            }
        };
        if (mq.addEventListener) {
            mq.addEventListener('change', onSystemChange);
        } else if (mq.addListener) {
            mq.addListener(onSystemChange);
        }
    }

    /* ------------------------------------------------------------ drawer */
    var menuToggle = doc.getElementById('menu-toggle');
    var drawerMask = doc.getElementById('drawer-mask');
    var drawer = doc.getElementById('nav-drawer');

    function setDrawer(open) {
        body.classList.toggle('nav-open', open);
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (drawer) {
            drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            setDrawer(!body.classList.contains('nav-open'));
        });
    }
    if (drawerMask) {
        drawerMask.addEventListener('click', function () {
            setDrawer(false);
        });
    }
    if (drawer) {
        $all('a', drawer).forEach(function (link) {
            link.addEventListener('click', function () {
                setDrawer(false);
            });
        });
    }

    /* ------------------------------------------------------------ search */
    var overlay = doc.getElementById('search-overlay');
    var searchInput = doc.getElementById('search-input');
    var lastFocus = null;

    function openSearch() {
        if (!overlay) {
            return;
        }
        setDrawer(false);
        lastFocus = doc.activeElement;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        body.classList.add('overlay-open');
        window.setTimeout(function () {
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }, 60);
    }

    function closeSearch() {
        if (!overlay || !overlay.classList.contains('open')) {
            return;
        }
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        body.classList.remove('overlay-open');
        if (lastFocus && lastFocus.focus) {
            lastFocus.focus();
        }
    }

    ['search-toggle', 'drawer-search', 'error-search'].forEach(function (id) {
        var el = doc.getElementById(id);
        if (el) {
            el.addEventListener('click', openSearch);
        }
    });

    if (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeSearch();
            }
        });
    }

    doc.addEventListener('keydown', function (event) {
        var tag = (event.target.tagName || '').toLowerCase();
        var typing = tag === 'input' || tag === 'textarea' || event.target.isContentEditable;

        if (event.key === 'Escape') {
            closeSearch();
            closeLightbox();
            setDrawer(false);
            return;
        }

        if (!typing && (event.key === '/' || ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k'))) {
            event.preventDefault();
            openSearch();
        }
    });

    /* ------------------------------------------------------------ floating header */
    var siteHeader = doc.getElementById('site-header');
    var headerNav = $('.header-nav');
    var navGlider = headerNav ? $('.nav-glider', headerNav) : null;

    function moveNavGlider(link) {
        if (!headerNav || !navGlider || !link) {
            return;
        }

        var navRect = headerNav.getBoundingClientRect();
        var linkRect = link.getBoundingClientRect();

        navGlider.style.width = linkRect.width + 'px';
        navGlider.style.height = linkRect.height + 'px';
        navGlider.style.transform = 'translate('
            + (linkRect.left - navRect.left + headerNav.scrollLeft) + 'px,'
            + (linkRect.top - navRect.top + headerNav.scrollTop) + 'px)';
        navGlider.classList.add('is-visible');
    }

    function hideNavGlider() {
        if (navGlider) {
            navGlider.classList.remove('is-visible');
        }
    }

    function resetNavGlider() {
        if (!headerNav) {
            return;
        }

        var current = $('.nav-link.current', headerNav);
        if (current) {
            moveNavGlider(current);
        } else {
            hideNavGlider();
        }
    }

    if (headerNav) {
        $all('.nav-link', headerNav).forEach(function (link) {
            link.addEventListener('mouseenter', function () {
                moveNavGlider(link);
            });
            link.addEventListener('focus', function () {
                moveNavGlider(link);
            });
        });

        headerNav.addEventListener('mouseleave', resetNavGlider);
        headerNav.addEventListener('scroll', resetNavGlider, { passive: true });

        resetNavGlider();
        window.addEventListener('load', resetNavGlider);
        window.addEventListener('resize', resetNavGlider);

        if (doc.fonts && doc.fonts.ready && doc.fonts.ready.then) {
            doc.fonts.ready.then(resetNavGlider);
        }
    }

    function updateHeaderState() {
        if (!siteHeader) {
            return;
        }

        var state = window.pageYOffset > 12 ? '1' : '0';
        if (siteHeader.getAttribute('data-scrolled') !== state) {
            siteHeader.setAttribute('data-scrolled', state);
        }
    }

    if (siteHeader) {
        window.addEventListener('scroll', updateHeaderState, { passive: true });
        updateHeaderState();
    }

    /* ------------------------------------------------------------ WeChat dropdown nav */
    var wechatNav = doc.querySelector('[data-wechat-nav]');

    if (wechatNav) {
        var wechatToggle = wechatNav.querySelector('.wechat-nav-toggle');
        var wechatPanel = wechatNav.querySelector('.wechat-nav-panel');

        function setWechatNav(open) {
            if (!wechatToggle || !wechatPanel) {
                return;
            }

            wechatPanel.hidden = !open;
            wechatToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            wechatNav.classList.toggle('is-open', open);
        }

        if (wechatToggle && wechatPanel) {
            wechatToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                setWechatNav(wechatPanel.hidden);
            });

            doc.addEventListener('click', function (event) {
                if (!wechatNav.contains(event.target)) {
                    setWechatNav(false);
                }
            });

            doc.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !wechatPanel.hidden) {
                    setWechatNav(false);
                    wechatToggle.focus();
                }
            });
        }
    }

    /* ------------------------------------------------------------ compact glass nav */
    var compactNav = doc.getElementById('compact-nav');

    if (compactNav) {
        var compactTick = false;
        var compactLinks = $all('.compact-link', compactNav);

        function updateCompactNav() {
            var hero = doc.querySelector('.site-hero');

            if (!hero) {
                return;
            }

            var nav = hero.dataset.headerStyle === 'wechat'
                ? hero.querySelector('.hero-cover')
                : hero.querySelector('.hero-nav');

            if (!nav) {
                return;
            }

            var show = nav.getBoundingClientRect().bottom <= 8;
            var visible = compactNav.classList.contains('is-visible');

            if (show === visible) {
                return;
            }

            compactNav.classList.toggle('is-visible', show);
            compactNav.setAttribute('aria-hidden', show ? 'false' : 'true');

            if (show) {
                compactNav.removeAttribute('inert');
            } else {
                compactNav.setAttribute('inert', '');
            }

            compactLinks.forEach(function (link) {
                link.tabIndex = show ? 0 : -1;
            });
        }

        function queueCompactNav() {
            if (compactTick) {
                return;
            }

            compactTick = true;
            window.requestAnimationFrame(function () {
                compactTick = false;
                updateCompactNav();
            });
        }

        compactLinks.forEach(function (link) {
            link.addEventListener('mouseenter', function () {
                compactNav.classList.add('is-hovering');
            });

            link.addEventListener('focus', function () {
                compactNav.classList.add('is-hovering');
            });
        });

        compactNav.addEventListener('mouseleave', function () {
            compactNav.classList.remove('is-hovering');
        });

        compactNav.addEventListener('focusout', function (event) {
            if (!compactNav.contains(event.relatedTarget)) {
                compactNav.classList.remove('is-hovering');
            }
        });

        window.addEventListener('scroll', queueCompactNav, { passive: true });
        window.addEventListener('resize', queueCompactNav);
        window.addEventListener('load', updateCompactNav);
        doc.addEventListener('sleek:pjax', updateCompactNav);
        updateCompactNav();
    }

    /* ------------------------------------------------------------ infinite scroll */
    var feedList = null;
    var pager = null;
    var feedEnd = null;
    var loadStatus = null;
    var loadRetry = null;
    var feedSentinel = null;
    var feedBusy = false;
    var feedObserver = null;
    var lastLoadedHref = '';
    var scrollTick = false;
    var feedScrollBound = false;

    function nextPageLink() {
        if (!pager) {
            return null;
        }
        return $('li.next > a', pager) || $('a.next', pager);
    }

    function setFeedLoading(on) {
        if (loadStatus) {
            loadStatus.hidden = !on;
        }
        if (on && loadRetry) {
            loadRetry.hidden = true;
        }
    }

    function endOfFeed() {
        if (feedObserver) {
            feedObserver.disconnect();
            feedObserver = null;
        }
        if (pager) {
            pager.style.display = 'none';
        }
        if (loadStatus) {
            loadStatus.hidden = true;
        }
        if (loadRetry) {
            loadRetry.hidden = true;
        }
        if (feedEnd) {
            feedEnd.hidden = false;
        }
        feedBusy = false;
    }

    function nearFeedEnd() {
        if (feedSentinel) {
            return feedSentinel.getBoundingClientRect().top < window.innerHeight + 700;
        }
        return (window.innerHeight + window.pageYOffset)
            >= (document.documentElement.scrollHeight - 700);
    }

    function scheduleFeedCheck(delay) {
        window.setTimeout(function () {
            if (!feedBusy && nearFeedEnd()) {
                loadNextFeedPage();
            }
        }, delay || 0);
    }

    function loadNextFeedPage() {
        if (feedBusy) {
            return;
        }

        var link = nextPageLink();
        if (!link) {
            endOfFeed();
            return;
        }
        if (link.href === lastLoadedHref) {
            endOfFeed();
            return;
        }

        var requestHref = link.href;
        feedBusy = true;
        setFeedLoading(true);
        if (pager) {
            pager.style.display = 'none';
        }

        fetch(requestHref, {
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        }).then(function (html) {
            var parsed = new DOMParser().parseFromString(html, 'text/html');
            var items = $all('#feed-list .feed-item', parsed);

            if (!items.length) {
                endOfFeed();
                return;
            }

            var fragment = doc.createDocumentFragment();
            items.forEach(function (item) {
                var clone = doc.importNode(item, true);
                clone.style.animationDelay = '0ms';
                fragment.appendChild(clone);
            });
            feedList.appendChild(fragment);
            lastLoadedHref = requestHref;

            var newPager = parsed.getElementById('pager');
            if (newPager && pager) {
                pager.innerHTML = newPager.innerHTML;
            }

            feedBusy = false;
            setFeedLoading(false);

            if (!nextPageLink()) {
                endOfFeed();
                return;
            }

            // 内容不足一屏时继续补齐，避免停在半屏。
            scheduleFeedCheck(350);
        }).catch(function () {
            feedBusy = false;
            setFeedLoading(false);
            if (loadRetry) {
                loadRetry.hidden = false;
            }
        });
    }

    function setupInfiniteScroll() {
        if (feedObserver) {
            feedObserver.disconnect();
            feedObserver = null;
        }

        feedList = doc.getElementById('feed-list');
        pager = doc.getElementById('pager');
        feedEnd = doc.getElementById('feed-end');
        loadStatus = doc.getElementById('load-status');
        loadRetry = doc.getElementById('load-retry');
        feedSentinel = doc.getElementById('feed-sentinel');
        feedBusy = false;
        lastLoadedHref = '';

        if (!feedList) {
            return;
        }

        if (pager) {
            pager.style.display = 'none';
        }

        if (!nextPageLink()) {
            return;
        }

        if (loadRetry) {
            loadRetry.addEventListener('click', function () {
                lastLoadedHref = '';
                loadRetry.hidden = true;
                loadNextFeedPage();
            });
        }

        if (!feedScrollBound) {
            feedScrollBound = true;

            window.addEventListener('scroll', function () {
                if (!feedList || scrollTick) {
                    return;
                }
                scrollTick = true;
                window.requestAnimationFrame(function () {
                    scrollTick = false;
                    if (nearFeedEnd()) {
                        loadNextFeedPage();
                    }
                });
            }, { passive: true });

            window.addEventListener('resize', function () {
                if (feedList && nearFeedEnd()) {
                    loadNextFeedPage();
                }
            });
        }

        if ('IntersectionObserver' in window && feedSentinel) {
            feedObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadNextFeedPage();
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px 700px 0px',
                threshold: 0
            });

            feedObserver.observe(feedSentinel);
        }

        scheduleFeedCheck(500);
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', setupInfiniteScroll);
    } else {
        setupInfiniteScroll();
    }

    doc.addEventListener('sleek:pjax', setupInfiniteScroll);

    /* ------------------------------------------------------------ lightbox */
    var lightbox = doc.getElementById('lightbox');
    var lbImage = doc.getElementById('lb-image');
    var lbCaption = doc.getElementById('lb-caption');
    var lbPrev = doc.getElementById('lb-prev');
    var lbNext = doc.getElementById('lb-next');
    var lbClose = doc.getElementById('lb-close');
    var gallery = [];
    var galleryIndex = 0;

    function collectGroup(target) {
        var item = target.closest('.gallery-item, .photo-item');
        if (item) {
            var container = item.closest('.gallery, .photo-grid, .shuo-photos');
            if (container) {
                return $all('.gallery-item, .photo-item', container);
            }
            return [item];
        }

        if (target.tagName === 'IMG' && target.closest('.article')) {
            return $all('.article img', target.closest('.article'));
        }

        return [];
    }

    function openLightbox(target) {
        if (!lightbox || !lbImage) {
            return;
        }

        var items = collectGroup(target);
        if (!items.length) {
            return;
        }

        lightbox._items = items.map(function (el) {
            return {
                src: el.getAttribute('data-src') || el.getAttribute('src') || (el.querySelector('img') ? el.querySelector('img').getAttribute('src') : ''),
                alt: el.getAttribute('data-alt') || el.getAttribute('alt') || ''
            };
        });
        gallery = lightbox._items;

        var index = items.indexOf(target) > -1 ? items.indexOf(target) : 0;
        if (target.tagName === 'IMG') {
            index = items.indexOf(target);
        }
        showLightboxItem(index < 0 ? 0 : index);
        lightbox.classList.add('open');
        lightbox.setAttribute('aria-hidden', 'false');
        body.classList.add('lightbox-open');
    }

    function showLightboxItem(index) {
        if (!gallery.length) {
            return;
        }
        galleryIndex = (index + gallery.length) % gallery.length;
        var item = gallery[galleryIndex];
        lbImage.src = item.src;
        lbImage.alt = item.alt || '';
        if (lbCaption) {
            lbCaption.textContent = item.alt || '';
        }
        if (lbPrev && lbNext) {
            var single = gallery.length < 2;
            lbPrev.hidden = single;
            lbNext.hidden = single;
        }
    }

    function closeLightbox() {
        if (!lightbox || !lightbox.classList.contains('open')) {
            return;
        }
        lightbox.classList.remove('open');
        lightbox.setAttribute('aria-hidden', 'true');
        body.classList.remove('lightbox-open');
        if (lbImage) {
            lbImage.src = '';
        }
    }

    doc.addEventListener('click', function (event) {
        var target = event.target;

        if (target.closest('.gallery-item, .photo-item')) {
            event.preventDefault();
            openLightbox(target.closest('.gallery-item, .photo-item'));
            return;
        }

        if (target.tagName === 'IMG' && target.closest('.article')) {
            event.preventDefault();
            openLightbox(target);
        }
    });

    if (lbClose) {
        lbClose.addEventListener('click', closeLightbox);
    }
    if (lightbox) {
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
    }
    if (lbPrev) {
        lbPrev.addEventListener('click', function () {
            showLightboxItem(galleryIndex - 1);
        });
    }
    if (lbNext) {
        lbNext.addEventListener('click', function () {
            showLightboxItem(galleryIndex + 1);
        });
    }

    doc.addEventListener('keydown', function (event) {
        if (!lightbox || !lightbox.classList.contains('open')) {
            return;
        }
        if (event.key === 'ArrowLeft') {
            showLightboxItem(galleryIndex - 1);
        } else if (event.key === 'ArrowRight') {
            showLightboxItem(galleryIndex + 1);
        }
    });

    /* ------------------------------------------------------------ code copy */
    function enhanceCode() {
        $all('.article pre').forEach(function (pre) {
            if (pre.querySelector('.code-copy')) {
                return;
            }
            var code = pre.querySelector('code');
            var text = (code || pre).textContent || '';
            var btn = doc.createElement('button');
            btn.type = 'button';
            btn.className = 'code-copy';
            btn.textContent = '复制';
            btn.addEventListener('click', function () {
                var done = function () {
                    btn.textContent = '已复制';
                    btn.classList.add('is-done');
                    window.setTimeout(function () {
                        btn.textContent = '复制';
                        btn.classList.remove('is-done');
                    }, 1600);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done, fallback);
                } else {
                    fallback();
                }

                function fallback() {
                    var area = doc.createElement('textarea');
                    area.value = text;
                    area.style.position = 'fixed';
                    area.style.opacity = '0';
                    doc.body.appendChild(area);
                    area.select();
                    try {
                        doc.execCommand('copy');
                        done();
                    } catch (e) {
                        /* ignore */
                    }
                    doc.body.removeChild(area);
                }
            });
            pre.appendChild(btn);
        });
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', enhanceCode);
    } else {
        enhanceCode();
    }

    /* ------------------------------------------------------------ emoji */
function closeEmojiPanels() {
    $all('.emoji-panel').forEach(function (panel) {
        panel.hidden = true;
    });

    $all('[data-emoji-toggle]').forEach(function (button) {
        button.setAttribute('aria-expanded', 'false');
    });
}

doc.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-emoji-toggle]');

    if (toggle) {
        event.preventDefault();
        event.stopPropagation();

        var form = toggle.closest('form');
        var panel = form ? form.querySelector('.emoji-panel') : null;

        if (!form) {
            return;
        }

        if (!panel) {
            var holder = document.createElement('div');
            holder.innerHTML = homeEmojiPanelHTML();
            panel = holder.firstElementChild;

            if (!panel) {
                return;
            }

            var compose = form.querySelector('.qzone-compose');

            if (compose && compose.parentNode) {
                compose.parentNode.insertBefore(panel, compose.nextSibling);
            } else {
                form.appendChild(panel);
            }
        }

        var open = panel.hidden;
        closeEmojiPanels();
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
    }

    var item = event.target.closest('.emoji-item');

    if (item) {
        var emojiPanel = item.closest('.emoji-panel');
        var emojiForm = emojiPanel ? emojiPanel.closest('form') : null;
        var textarea = emojiForm ? emojiForm.querySelector('textarea[name="text"], input[name="text"]') : null;

        if (!textarea) {
            return;
        }

        var emoji = item.getAttribute('data-emoji') || item.textContent;
        var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : textarea.value.length;
        var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : start;
        var value = textarea.value;

        textarea.value = value.slice(0, start) + emoji + value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
        textarea.focus();
        return;
    }

    if (!event.target.closest('.emoji-panel')) {
        closeEmojiPanels();
    }
});

/* ------------------------------------------------------------ to top */
    var toTop = doc.getElementById('to-top');

    function onScroll() {
        if (!toTop) {
            return;
        }
        toTop.classList.toggle('show', window.pageYOffset > 420);
    }

    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ------------------------------------------------------------ home comments */
    var homeActive = null;
    var homeProfileKey = 'sleek-comment-profile';

    function escapeAttr(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[ch];
        });
    }

    function readCommentProfile() {
        try {
            return JSON.parse(localStorage.getItem(homeProfileKey) || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    function saveCommentProfile(values) {
        try {
            localStorage.setItem(homeProfileKey, JSON.stringify({
                author: values.author || '',
                mail: values.mail || '',
                url: values.url || ''
            }));
        } catch (e) {
            /* ignore */
        }
    }

    function commentCountOf(root) {
        if (!root) {
            return 0;
        }
        var title = root.querySelector('.comments-title');
        if (title) {
            var match = title.textContent.match(/\d+/);
            if (match) {
                return parseInt(match[0], 10);
            }
        }
        return root.querySelectorAll('.comment-body').length;
    }

    function sanitizeCommentList(list) {
        if (!list) {
            return '';
        }
        var clone = list.cloneNode(true);
        Array.prototype.forEach.call(clone.querySelectorAll('script, iframe, style, link'), function (node) {
            if (node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });
        Array.prototype.forEach.call(clone.querySelectorAll('[id]'), function (node) {
            node.removeAttribute('id');
        });
        Array.prototype.forEach.call(clone.querySelectorAll('*'), function (node) {
            Array.prototype.slice.call(node.attributes || []).forEach(function (attr) {
                if (/^on/i.test(attr.name)) {
                    node.removeAttribute(attr.name);
                }
            });
        });
        return clone.outerHTML;
    }

    var homeEmojiString = '😀😄😁😆😅😂🤣😊😇🙂🙃😉😌😍🥰😘😋😛😝😜🤪🤨🧐🤓😎🥳😏😒😞😔😟😕🙁☹️😣😖😫😩🥺😢😭😤😠😡🤬🤯😳🥵🥶😱😨😰😥😓🤗🤔🤭🤫🤥😶😐😑😬🙄😯😦😧😮😲🥱😴🤤😪😵🤐🥴🤢🤮🤧😷🤒🤕🤑🤠😈👿👹👺🤡💩👻💀👽🤖😺😸😹😻😼😽🙀😿😾';

function homeEmojiPanelHTML() {
    var buttons = Array.from(homeEmojiString).map(function (emoji) {
        return '<button type="button" class="emoji-item" data-emoji="' + emoji + '">' + emoji + '</button>';
    }).join('');

    return '<div class="emoji-panel" hidden>' + buttons + '</div>';
}

function homeFormHTML(toggle, count) {
        var profile = readCommentProfile();
        var requireMail = toggle.getAttribute('data-require-mail') === '1';
        var requireUrl = toggle.getAttribute('data-require-url') === '1';
        var mailStar = requireMail ? ' <em>*</em>' : '';
        var urlStar = requireUrl ? ' <em>*</em>' : '';
        var mailRequired = requireMail ? ' required' : '';
        var urlRequired = requireUrl ? ' required' : '';

        return [
            '<form class="home-comment-form" method="post" action="',
            escapeAttr(toggle.getAttribute('data-comment-url') || ''),
            '" data-cid="',
            escapeAttr(toggle.getAttribute('data-cid') || ''),
            '">',
            '<div class="reply-hint" hidden><span class="reply-hint-text"></span>',
            '<button type="button" class="reply-cancel">取消回复</button></div>',
            '<div class="form-grid">',
            '<label class="field"><span class="field-label">称呼 <em>*</em></span>',
            '<input type="text" name="author" value="', escapeAttr(profile.author || ''),
            '" placeholder="怎么称呼你" autocomplete="nickname" required></label>',
            '<label class="field"><span class="field-label">邮箱', mailStar, '</span>',
            '<input type="email" name="mail" value="', escapeAttr(profile.mail || ''),
            '" placeholder="name@example.com" autocomplete="email"', mailRequired, '></label>',
            '<label class="field"><span class="field-label">网站', urlStar, '</span>',
            '<input type="url" name="url" value="', escapeAttr(profile.url || ''),
            '" placeholder="https://" autocomplete="url"', urlRequired, '></label>',
            '</div>',
            '<div class="form-editor">',
            '<textarea name="text" rows="3" placeholder="' + replyPlaceholder + '" required></textarea>',
            '<div class="editor-tools"><button type="button" class="tool-btn" data-emoji-toggle aria-expanded="false">',
'<svg class="icon" aria-hidden="true"><use href="#i-smile"></use></svg>表情</button>',
'<span class="tool-tip">友善交流</span>',
            '<button type="submit" class="submit-btn"><span class="submit-label">发送</span>',
            '<svg class="icon" aria-hidden="true"><use href="#i-send"></use></svg></button>',
            '</div>',
homeEmojiPanelHTML(),
'</div>',
            '<p class="comment-notice" hidden></p>',
            '</form>'
        ].join('');
    }

    function updateHomeCount(toggle, count) {
        var counter = toggle.querySelector('.stat-count');
        if (counter && !isNaN(count)) {
            counter.textContent = count;
        }
    }

    function showHomeNotice(panel, text, type) {
        if (!panel) {
            return;
        }
        var notice = panel.querySelector('.comment-notice');
        if (!notice) {
            notice = doc.createElement('p');
            notice.className = 'comment-notice';
            var form = panel.querySelector('.home-comment-form');
            (form || panel).appendChild(notice);
        }
        notice.hidden = false;
        notice.className = 'comment-notice is-' + (type || 'success');
        notice.textContent = text;
    }

    function hideHomeNotice(panel) {
        var notice = panel && panel.querySelector('.comment-notice');
        if (notice) {
            notice.hidden = true;
            notice.textContent = '';
        }
    }

    function renderHomePanel(panel, toggle, data) {
        var count = parseInt(data.count, 10);
        if (isNaN(count)) {
            count = 0;
        }

        var html = '<div class="home-comments-inner">';
        html += '<div class="home-comments-head">';
        html += '<span class="home-comments-title">评论</span>';
        if (count > 0) {
            html += '<span class="home-comments-count">' + count + '</span>';
        }
        html += '<a class="home-comments-all" href="'
            + escapeAttr(toggle.getAttribute('data-permalink'))
            + '#comments">查看全部</a></div>';

        if (data.listHTML) {
            html += '<div class="home-comments-list">' + data.listHTML + '</div>';
        } else {
            html += '<p class="home-comments-empty">还没有评论，来坐个沙发。</p>';
        }

        if (data.canComment) {
            html += homeFormHTML(toggle, count);
        } else {
            html += '<p class="home-comments-closed">评论已关闭。</p>';
        }

        html += '</div>';
        panel.innerHTML = html;
        panel.dataset.loaded = '1';
        panel.dataset.count = String(count);
        panel.dataset.parent = '0';
        updateHomeCount(toggle, count);

        if (data.notice) {
            if (data.notice && data.noticeType !== 'success') {
        showHomeNotice(panel, data.notice, data.noticeType);
    }
        }
    }

    function loadHomePanel(panel, toggle) {
        if (panel.dataset.loading === '1') {
            return;
        }

        var permalink = toggle.getAttribute('data-permalink');
        panel.dataset.loading = '1';
        panel.setAttribute('aria-busy', 'true');
        panel.innerHTML = '<div class="home-comments-loading" aria-hidden="true"><span></span><span></span><span></span></div>';

        fetch(permalink, {
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        }).then(function (html) {
            var parsed = new DOMParser().parseFromString(html, 'text/html');
            var root = parsed.querySelector('#comments');

            if (!root) {
                throw new Error('未找到评论区域');
            }

            renderHomePanel(panel, toggle, {
                listHTML: sanitizeCommentList(root.querySelector('.comment-list')),
                count: commentCountOf(root),
                canComment: !!root.querySelector('.respond form')
            });
        }).catch(function () {
            panel.dataset.loaded = '';
            panel.innerHTML = '<div class="home-comments-error">评论暂时加载失败，'
                + '<a href="' + escapeAttr(permalink) + '#comments">去文章页查看</a></div>';
        }).then(function () {
            panel.dataset.loading = '';
            panel.removeAttribute('aria-busy');
        });
    }

    function setReplyPreviewVisibility(card, open) {
        var preview = card && card.querySelector('[data-reply-preview]');
        if (preview) {
            preview.hidden = !!open;
        }

        var say = card && card.querySelector('[data-say-comment]');
        if (say) {
            say.hidden = !!open;
        }
    }

    function toggleHomePanel(toggle) {
        var card = toggle.closest('.feed-item');
        var panel = card && card.querySelector('.home-comments');

        if (!panel) {
            return;
        }

        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');

        var form = panel.querySelector('.home-comment-form');
        var field = form && form.querySelector('textarea[name="text"], input[name="text"]');

        if (form) {
            form.classList.add('is-visible');
            form.classList.add('is-active');
        }

        if (field) {
            field.focus();
        }

        window.setTimeout(function () {
            var rect = panel.getBoundingClientRect();
            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 80);
    }

    function setHomeReply(panel, id, name) {
        var form = panel && panel.querySelector('.home-comment-form');
        if (!form || !id) {
            return;
        }

        panel.dataset.parent = id;
        panel.dataset.replyName = name || '';
        form.classList.add('is-visible');
        form.classList.add('is-active');
        var hint = form.querySelector('.reply-hint');
        if (hint) {
            hint.hidden = false;
            var text = hint.querySelector('.reply-hint-text');
            if (text) {
                text.textContent = '正在回复 @' + (name || '评论');
            }
        }

        var textarea = form.querySelector('textarea[name="text"], input[name="text"]');
        if (textarea) {
            textarea.focus();
        }

        var rect = form.getBoundingClientRect();
        if (rect.top < 0 || rect.bottom > window.innerHeight) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function cancelHomeReply(panel) {
        if (!panel) {
            return;
        }
        panel.dataset.parent = '0';
        panel.dataset.replyName = '';
        var hint = panel.querySelector('.reply-hint');
        if (hint) {
            hint.hidden = true;
        }
    }

    function appendHomeComment(panel, values, pending, parentId) {
        var inner = panel.querySelector('.home-comments-inner');
        if (!inner) {
            return;
        }

        var social = panel.querySelector('.home-social');
        var list = panel.querySelector('.qzone-comment-list');

        if (!social) {
            social = doc.createElement('div');
            social.className = 'home-social';
            var form = panel.querySelector('.home-comment-form');
            inner.insertBefore(social, form || null);
        }

        if (!list) {
            list = doc.createElement('div');
            list.className = 'qzone-comment-list';
            social.appendChild(list);
        }

        social.classList.add('has-activity');

        var empty = panel.querySelector('.qzone-empty');
        if (empty && empty.parentNode) {
            empty.parentNode.removeChild(empty);
        }

        var item = doc.createElement('div');
        item.className = 'qzone-comment' + (parentId && parentId !== '0' ? ' is-reply' : '');

        var avatar = doc.createElement('img');
        avatar.className = 'qzone-avatar';
        avatar.src = panel.getAttribute('data-default-avatar') || '';
        avatar.alt = '';
        avatar.width = parentId && parentId !== '0' ? 26 : 30;
        avatar.height = avatar.width;

        var body = doc.createElement('div');
        body.className = 'qzone-body';

        var head = doc.createElement('div');
        head.className = 'qzone-head';

        var name = doc.createElement('span');
        name.className = 'qzone-name';
        name.textContent = values.author;

        var time = doc.createElement('time');
        time.className = 'qzone-time';
        var now = new Date();
        time.textContent = (now.getMonth() + 1) + '月' + now.getDate() + '日 '
            + (now.getHours() < 10 ? '0' : '') + now.getHours() + ':'
            + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();

        head.appendChild(name);

        if (pending) {
            var badge = doc.createElement('span');
            badge.className = 'qzone-pending';
            badge.textContent = '待审核';
            head.appendChild(badge);
        }

        

        var text = doc.createElement('div');
        text.className = 'qzone-text';

        if (parentId && parentId !== '0' && panel.dataset.replyName) {
            var replyTo = doc.createElement('span');
            replyTo.className = 'qzone-reply-to';
            var replyName = doc.createElement('b');
            replyName.textContent = panel.dataset.replyName;
            replyTo.appendChild(doc.createTextNode('回复 '));
            replyTo.appendChild(replyName);
            replyTo.appendChild(doc.createTextNode('：'));
            text.appendChild(replyTo);
        }

        text.appendChild(doc.createTextNode(values.text));
        body.appendChild(head);
        body.appendChild(text);

        var foot = doc.createElement('div');
        foot.className = 'qzone-foot';
        foot.appendChild(time);
        body.appendChild(foot);

        var input = panel.querySelector('input[name="text"], textarea[name="text"]');

        if (input) {
            input.placeholder = '我也说一句';
        }

        item.appendChild(avatar);
        item.appendChild(body);
        list.appendChild(item);
        panel.classList.remove('is-empty');

        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function submitHomeComment(form) {
        if (form.dataset.busy === '1') {
            return;
        }

        var panel = form.closest('.home-comments');
        var card = form.closest('.feed-item');
        var toggle = card && card.querySelector('[data-comment-toggle]');
        if (!panel || !toggle) {
            return;
        }

        var authorField = form.elements['author'];
        var mailField = form.elements['mail'];
        var urlField = form.elements['url'];
        var textField = form.elements['text'];
        var values = {
            author: authorField ? authorField.value.trim() : '',
            mail: mailField ? mailField.value.trim() : '',
            url: urlField ? urlField.value.trim() : '',
            text: textField ? textField.value.trim() : ''
        };

        if (!values.author) {
            form.classList.add('is-active');
            showHomeNotice(panel, '请填写称呼。', 'error');
            if (authorField) {
                authorField.focus();
            }
            return;
        }
        if (!values.text) {
            form.classList.add('is-active');
            showHomeNotice(panel, '请填写评论内容。', 'error');
            if (textField) {
                textField.focus();
            }
            return;
        }
        if (toggle.getAttribute('data-require-mail') === '1' && !values.mail) {
            form.classList.add('is-active');
            showHomeNotice(panel, '请填写邮箱地址。', 'error');
            if (mailField) {
                mailField.focus();
            }
            return;
        }
        if (toggle.getAttribute('data-require-url') === '1' && !values.url) {
            form.classList.add('is-active');
            showHomeNotice(panel, '请填写网站地址。', 'error');
            if (urlField) {
                urlField.focus();
            }
            return;
        }

        form.dataset.busy = '1';
        var button = form.querySelector('.submit-btn');
        var originalButton = button ? button.innerHTML : '';
        if (button) {
            button.disabled = true;
            button.classList.add('is-loading');

            var submitLabel = button.querySelector('.submit-label');

            if (submitLabel) {
                submitLabel.textContent = '提交中…';
            }
        }
        hideHomeNotice(panel);

        var beforeCount = parseInt(panel.dataset.count || '0', 10);
        var parentId = panel.dataset.parent || '0';

        submitHomeViaFrame(
            toggle.getAttribute('data-permalink'),
            values,
            parentId,
            beforeCount
        ).then(function (result) {
            saveCommentProfile(values);

            var count = parseInt(result.count, 10);
            if (isNaN(count)) {
                count = beforeCount + (result.pending ? 0 : 1);
            }

            appendHomeComment(panel, values, !!result.pending, parentId);
            updateHomeCount(toggle, count);
            panel.dataset.count = String(count);
            panel.dataset.parent = '0';
            panel.dataset.replyName = '';

            var hint = form.querySelector('.reply-hint');
            if (hint) {
                hint.hidden = true;
            }

            if (textField) {
                textField.value = '';
            }

            form.dataset.busy = '0';
            form.classList.remove('is-active');
            form.classList.remove('is-visible');

            if (button) {
                button.disabled = false;
                button.classList.remove('is-loading');
                button.innerHTML = originalButton;
            }

            if (result.pending) {
            showHomeNotice(panel, '评论已提交，审核通过后显示。', 'success');
        } else {
            hideHomeNotice(panel);
        }
        }).catch(function (error) {
            showHomeNotice(panel, (error && error.message) || '评论提交失败，请稍后再试。', 'error');
            form.dataset.busy = '0';
            if (button) {
                button.disabled = false;
                button.classList.remove('is-loading');
                button.innerHTML = originalButton;
            }
        });
    }

    function submitHomeViaFrame(permalink, values, parentId, beforeCount) {
        return new Promise(function (resolve, reject) {
            var frame = doc.createElement('iframe');
            frame.className = 'sleek-hidden-frame';
            frame.setAttribute('aria-hidden', 'true');
            frame.setAttribute('tabindex', '-1');
            frame.title = '评论提交';
            doc.body.appendChild(frame);

            var finished = false;
            var initialDoc = null;

            function finish(callback, value) {
                if (finished) {
                    return;
                }
                finished = true;
                if (frame.parentNode) {
                    frame.parentNode.removeChild(frame);
                }
                callback(value);
            }

            function fail(message) {
                finish(reject, new Error(message));
            }

            function waitForForm(started) {
                if (finished) {
                    return;
                }
                var frameDoc = null;
                try {
                    frameDoc = frame.contentDocument;
                } catch (e) {
                    frameDoc = null;
                }

                if (frameDoc && frameDoc.querySelector('#comment-form')) {
                    initialDoc = frameDoc;
                    fillAndSubmit(frameDoc);
                    return;
                }

                if (Date.now() - started > 15000) {
                    fail('文章页加载超时，请重试。');
                    return;
                }

                window.setTimeout(function () {
                    waitForForm(started);
                }, 100);
            }

            function fillAndSubmit(frameDoc) {
                var form = frameDoc.querySelector('#comment-form')
                    || frameDoc.querySelector('.respond form');

                if (!form) {
                    fail('该文章的评论已关闭。');
                    return;
                }

                ['author', 'mail', 'url', 'text'].forEach(function (name) {
                    var field = form.elements[name];
                    if (field) {
                        field.value = values[name] || '';
                    }
                });

                var parent = form.elements['parent'];
                if (!parent) {
                    parent = frameDoc.createElement('input');
                    parent.type = 'hidden';
                    parent.name = 'parent';
                    form.appendChild(parent);
                }
                parent.value = parentId || '0';

                var tokenStart = Date.now();

                function addTokenEvents() {
                    ['scroll', 'mousemove', 'keyup', 'touchstart'].forEach(function (type) {
                        try {
                            var EventCtor = frame.contentWindow.Event;
                            frame.contentWindow.dispatchEvent(new EventCtor(type));
                        } catch (e) {
                            /* ignore */
                        }
                    });
                }

                function submitWhenReady() {
                    if (finished) {
                        return;
                    }

                    addTokenEvents();

                    var token = form.querySelector('input[name="_"]');
                    if (!token && Date.now() - tokenStart < 700) {
                        window.setTimeout(submitWhenReady, 100);
                        return;
                    }

                    try {
                        window.HTMLFormElement.prototype.submit.call(form);
                    } catch (e) {
                        fail('无法提交评论。');
                        return;
                    }
                    waitForResult(Date.now());
                }

                submitWhenReady();
            }

            function waitForResult(started) {
                if (finished) {
                    return;
                }

                var frameDoc = null;
                try {
                    frameDoc = frame.contentDocument;
                } catch (e) {
                    frameDoc = null;
                }

                if (frameDoc && frameDoc !== initialDoc) {
                    if (!frameDoc.body || frameDoc.readyState === 'loading') {
                        window.setTimeout(function () {
                            waitForResult(started);
                        }, 100);
                        return;
                    }

                    var root = frameDoc.querySelector('#comments');
                    if (!root) {
                        var errorNode = frameDoc.querySelector('h1, .error, .message');
                        var errorText = (errorNode ? errorNode.textContent : (frameDoc.body ? frameDoc.body.textContent : ''))
                            .replace(/\s+/g, ' ')
                            .trim()
                            .slice(0, 100);
                        fail(errorText || '提交失败，请稍后重试。');
                        return;
                    }

                    var list = root.querySelector('.comment-list');
                    var count = commentCountOf(root);
                    var pending = !!root.querySelector('.badge-pending, .comment-awaiting-moderation');
                    var hasText = false;
                    var needle = (values.text || '').trim().slice(0, 12);

                    if (needle) {
                        hasText = Array.prototype.some.call(root.querySelectorAll('.comment-text'), function (node) {
                            return node.textContent.indexOf(needle) !== -1;
                        });
                    }

                    if (!pending && count <= beforeCount && !hasText) {
                        fail('评论未能提交，请刷新后重试或前往文章页评论。');
                        return;
                    }

                    finish(resolve, {
                        listHTML: sanitizeCommentList(list),
                        canComment: !!root.querySelector('.respond form'),
                        count: count,
                        pending: pending
                    });
                    return;
                }

                if (Date.now() - started > 20000) {
                    fail('提交超时，请刷新后重试。');
                    return;
                }

                window.setTimeout(function () {
                    waitForResult(started);
                }, 100);
            }

            frame.src = permalink;
            waitForForm(Date.now());
        });
    }

    doc.addEventListener('click', function (event) {
        var say = event.target.closest('[data-say-comment]');
        if (say) {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var sayCard = say.closest('.feed-item');
            var sayToggle = sayCard && sayCard.querySelector('[data-comment-toggle]');
            var sayPanel = sayCard && sayCard.querySelector('.home-comments');

            if (sayToggle && sayPanel) {
                event.preventDefault();
                if (sayPanel.hidden) {
                    toggleHomePanel(sayToggle);
                } else {
                    var sayInput = sayPanel.querySelector('textarea');
                    if (sayInput) {
                        sayInput.focus();
                    }
                }
            }
            return;
        }

        var preview = event.target.closest('[data-reply-preview]');
        if (preview) {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var card = preview.closest('.feed-item');
            var toggle = card && card.querySelector('[data-comment-toggle]');
            var panel = card && card.querySelector('.home-comments');

            if (toggle && panel) {
                event.preventDefault();
                if (panel.hidden) {
                    toggleHomePanel(toggle);
                } else {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
            return;
        }

        var toggle = event.target.closest('[data-comment-toggle]');
        if (toggle) {
            event.preventDefault();
            var toggleCard = toggle.closest('.feed-item');
            var togglePanel = toggleCard && toggleCard.querySelector('.home-comments');
            var toggleForm = togglePanel && togglePanel.querySelector('.qzone-form');

            $all('.home-comments').forEach(function (panel) {
                hideHomeNotice(panel);
            });

            if (toggleForm && toggleForm.classList.contains('is-visible') && !homeFormHasComment(toggleForm)) {
                hideHomeCommentForm(toggleForm);
                var closeField = toggleForm.querySelector('input[name="text"], textarea[name="text"]');
                if (closeField) {
                    closeField.blur();
                }
                return;
            }

            $all('.home-comments .home-comment-form.is-visible').forEach(function (otherForm) {
                if (otherForm !== toggleForm && !homeFormHasComment(otherForm)) {
                    hideHomeCommentForm(otherForm);
                }
            });

            if (toggleForm && togglePanel && !togglePanel.hidden && homeFormCanCollapse(toggleForm)) {
                collapseHomeForm(toggleForm);
                var toggleField = toggleForm.querySelector('input[name="text"], textarea[name="text"]');
                if (toggleField) {
                    toggleField.blur();
                }
                return;
            }

            $all('.qzone-form.is-active').forEach(function (otherForm) {
                if (otherForm !== toggleForm && homeFormCanCollapse(otherForm)) {
                    collapseHomeForm(otherForm);
                }
            });

            toggleHomePanel(toggle);
            return;
        }

        var cancel = event.target.closest('.home-comments .reply-cancel');
        if (cancel) {
            event.preventDefault();
            cancelHomeReply(cancel.closest('.home-comments'));
            return;
        }

        var qzoneReply = event.target.closest('.home-comments .qzone-reply');
        if (qzoneReply) {
            event.preventDefault();
            var qzonePanel = qzoneReply.closest('.home-comments');
            setHomeReply(
                qzonePanel,
                qzoneReply.getAttribute('data-reply-to'),
                qzoneReply.getAttribute('data-reply-name')
            );
            return;
        }

        var reply = event.target.closest('.home-comments .comment-actions a');
        if (!reply) {
            return;
        }

        var panel = reply.closest('.home-comments');
        var form = panel && panel.querySelector('.home-comment-form');
        if (!form) {
            return;
        }

        var match = (reply.getAttribute('href') || '').match(/replyTo=(\d+)/);
        if (!match) {
            return;
        }

        event.preventDefault();
        var body = reply.closest('.comment-body');
        var nameNode = body && body.querySelector('.comment-name');
        setHomeReply(panel, match[1], nameNode ? nameNode.textContent.trim() : '');
    });

    doc.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !form.classList || !form.classList.contains('home-comment-form')) {
            return;
        }
        event.preventDefault();
        submitHomeComment(form);
    }, true);

    /* ------------------------------------------------------------ misc */
    var hydratedProfile = readCommentProfile();

    $all('.qzone-form').forEach(function (form) {
        ['author', 'mail', 'url'].forEach(function (name) {
            var field = form.elements[name];

            if (field && !field.value && hydratedProfile[name]) {
                field.value = hydratedProfile[name];
            }

            if (field) {
                field.addEventListener('input', function () {
                    form.setAttribute('data-fields-touched', '1');
                });
            }
        });

        var input = form.querySelector('input[name="text"], textarea[name="text"]');
        if (input) {
            input.addEventListener('focus', function () {
                form.classList.add('is-active');
                syncCommentAction(form);
            });
        }
    });

    function syncCommentAction(form) {
    if (!form) {
        return;
    }

    var card = form.closest('.feed-item');
    var toggle = card && card.querySelector('[data-comment-toggle]');

    if (toggle) {
        toggle.classList.toggle('is-commenting', form.classList.contains('is-active'));
    }
}

function collapseHomeForm(form) {
    if (!form) {
        return;
    }

    form.classList.remove('is-active');
    syncCommentAction(form);

    var panel = form.closest('.home-comments');
    if (panel) {
        hideHomeNotice(panel);
    }
}

function homeFormHasComment(form) {
    var field = form && form.querySelector('textarea[name="text"], input[name="text"]');

    return !!(field && field.value.trim() !== '');
}

function hideHomeCommentForm(form) {
    if (!form) {
        return;
    }

    form.classList.remove('is-visible');
    form.classList.remove('is-active');

    var panel = form.closest('.home-comments');
    if (panel) {
        hideHomeNotice(panel);
        cancelHomeReply(panel);
    }
}

function homeFormCanCollapse(form) {
        if (!form || !form.classList.contains('is-active')) {
            return false;
        }

        var touched = form.getAttribute('data-fields-touched') === '1';
        var empty = ['author', 'mail', 'url'].every(function (name) {
            var field = form.elements[name];
            return !field || String(field.value || '').trim() === '';
        });

        return empty || !touched;
    }

    doc.addEventListener('click', function (event) {
        if (event.target.closest('[data-comment-toggle]')) {
            return;
        }

        var clickedForm = event.target.closest('.qzone-form');

        $all('.qzone-form.is-active').forEach(function (form) {
            if (form === clickedForm) {
                return;
            }

            if (homeFormCanCollapse(form)) {
                collapseHomeForm(form);
            }
        });

        if (clickedForm) {
            var clickedPanel = clickedForm.closest('.home-comments');

            $all('.home-comments').forEach(function (panel) {
                if (panel !== clickedPanel) {
                    hideHomeNotice(panel);
                }
            });

            $all('.home-comments .home-comment-form.is-visible').forEach(function (form) {
                if (form !== clickedForm && !homeFormHasComment(form)) {
                    hideHomeCommentForm(form);
                }
            });
            return;
        }

        $all('.home-comments').forEach(function (panel) {
            hideHomeNotice(panel);
        });

        $all('.home-comments .home-comment-form.is-visible').forEach(function (form) {
            if (!homeFormHasComment(form)) {
                hideHomeCommentForm(form);
            }
        });

        $all('.qzone-form.is-active').forEach(function (form) {
            if (homeFormCanCollapse(form)) {
                collapseHomeForm(form);
            }
        });
    });

    doc.addEventListener('focusin', function (event) {
        var form = event.target.closest('.qzone-form');

        if (!form || !event.target.matches('input, textarea')) {
            return;
        }

        form.classList.add('is-active');
        syncCommentAction(form);
    });

    doc.addEventListener('input', function (event) {
        var field = event.target.closest('.qzone-form input, .qzone-form textarea');

        if (!field) {
            return;
        }

        var form = field.closest('.qzone-form');

        if (field.name === 'author' || field.name === 'mail' || field.name === 'url') {
            form.setAttribute('data-fields-touched', '1');
        }

        var panel = form.closest('.home-comments');
        if (panel) {
            hideHomeNotice(panel);
        }
    });

    var LIKED_POSTS_KEY = 'sleek-liked-posts';

    function readLikedPosts() {
        try {
            var raw = localStorage.getItem(LIKED_POSTS_KEY);
            var list = raw ? JSON.parse(raw) : [];

            return Array.isArray(list) ? list.map(String) : [];
        } catch (e) {
            return [];
        }
    }

    function rememberLikedPost(cid) {
        cid = String(cid || '');

        if (!cid) {
            return;
        }

        try {
            var list = readLikedPosts();

            if (list.indexOf(cid) === -1) {
                list.push(cid);
                localStorage.setItem(LIKED_POSTS_KEY, JSON.stringify(list));
            }
        } catch (e) {
            /* ignore */
        }
    }

    function forgetLikedPost(cid) {
        cid = String(cid || '');

        try {
            var list = readLikedPosts().filter(function (item) {
                return item !== cid;
            });

            localStorage.setItem(LIKED_POSTS_KEY, JSON.stringify(list));
        } catch (e) {
            /* ignore */
        }
    }

    function setLikeButtonState(button, liked) {
        button.classList.toggle('is-liked', liked);
        button.setAttribute('aria-pressed', liked ? 'true' : 'false');
        button.disabled = false;
        button.title = liked ? '取消点赞' : '点赞';

        var label = button.querySelector('.shuo-act-label');

        if (label) {
            label.textContent = liked ? '已赞' : '点赞';
        }
    }

    function syncHomeSocial(panel) {
    var social = panel && panel.querySelector('.home-social');

    if (!social) {
        return;
    }

    var likes = social.querySelector('.qzone-likes:not([hidden])');
    var list = social.querySelector('.qzone-comment-list');
    var hasActivity = !!likes || !!(list && list.children.length);

    social.classList.toggle('has-activity', hasActivity);
}

function syncLikeSummary(cid, count) {
        $all('[data-like-summary-cid="' + cid + '"]').forEach(function (summary) {
            var counter = summary.querySelector('.like-summary-count');

            if (counter) {
                counter.textContent = count;
            }

            summary.hidden = count < 1;

            var panel = summary.closest('.home-comments');

            if (!panel) {
                return;
            }

            if (count > 0 || panel.querySelector('.qzone-comment')) {
                panel.classList.remove('is-empty');
            } else {
                panel.classList.add('is-empty');
            }
        });
    }

    function syncLikedButtons() {
        var liked = readLikedPosts();

        $all('[data-like-cid]').forEach(function (button) {
            var cid = String(button.getAttribute('data-like-cid') || '');
            var isLiked = liked.indexOf(cid) !== -1 || button.classList.contains('is-liked');

            setLikeButtonState(button, isLiked);

            if (isLiked && liked.indexOf(cid) === -1) {
                rememberLikedPost(cid);
            }
        });
    }

    syncLikedButtons();
    doc.addEventListener('sleek:pjax', syncLikedButtons);

    /* Likes: one delegated handler toggles like and unlike */
    var likeBusy = {};
    var LIKE_CLIENT_KEY = 'sleek-like-client';

    function likeClientId() {
        try {
            var id = localStorage.getItem(LIKE_CLIENT_KEY);

            if (!id) {
                if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                    id = window.crypto.randomUUID().replace(/-/g, '');
                } else {
                    id = 'c' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
                }

                localStorage.setItem(LIKE_CLIENT_KEY, id);
            }

            return id;
        } catch (e) {
            return '';
        }
    }
    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0) {
            return;
        }

        var button = event.target.closest ? event.target.closest('[data-like-cid]') : null;

        if (!button || button.dataset.busy === '1') {
            return;
        }

        var cid = button.getAttribute('data-like-cid');
        var url = button.getAttribute('data-like-url');

        if (!cid || !url || likeBusy[cid]) {
            return;
        }

        event.preventDefault();

        var wasLiked = button.classList.contains('is-liked') || readLikedPosts().indexOf(String(cid)) !== -1;
        var client = likeClientId();
        var requestUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'action=' + (wasLiked ? 'unlike' : 'like')
            + (client ? '&client=' + encodeURIComponent(client) : '');

        likeBusy[cid] = true;
        button.dataset.busy = '1';
        $all('[data-like-cid="' + cid + '"]').forEach(function (item) {
            item.disabled = true;
            item.classList.add('is-loading');
        });

        fetch(requestUrl, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (!data || !data.ok) {
                throw new Error('Like failed');
            }

            if (data.liked) {
                rememberLikedPost(cid);
            } else {
                forgetLikedPost(cid);
            }

            $all('[data-like-cid="' + cid + '"]').forEach(function (item) {
                var counter = item.querySelector('.like-count');

                if (counter) {
                    counter.textContent = data.count;
                }

                item.classList.remove('is-loading');
                setLikeButtonState(item, !!data.liked);
            });

            syncLikeSummary(cid, Number(data.count) || 0);
        }).catch(function () {
            $all('[data-like-cid="' + cid + '"]').forEach(function (item) {
                item.disabled = false;
                item.classList.remove('is-loading');
            });
        }).finally(function () {
            delete likeBusy[cid];
            button.dataset.busy = '0';
            $all('[data-like-cid="' + cid + '"]').forEach(function (item) {
                item.disabled = false;
                item.classList.remove('is-loading');
            });
        });
    });

    $all('.hero-avatar img, .shuo-avatar img, .drawer-brand img').forEach(function (img) {
        function dropAvatar() {
            if (img.parentNode) {
                img.parentNode.removeChild(img);
            }
        }

        if (img.complete) {
            if (!img.naturalWidth) {
                dropAvatar();
            }
            return;
        }

        var timer = window.setTimeout(function () {
            if (!img.complete || !img.naturalWidth) {
                dropAvatar();
            }
        }, 1800);

        img.addEventListener('load', function () {
            window.clearTimeout(timer);
        }, { once: true });

        img.addEventListener('error', function () {
            window.clearTimeout(timer);
            dropAvatar();
        }, { once: true });
    });

    $all('.post-nav .pn-item').forEach(function (item) {
        var link = item.querySelector('a[href]');

        if (!link) {
            return;
        }

        item.addEventListener('click', function (event) {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            if (event.target.closest('a, button')) {
                return;
            }

            window.location.href = link.href;
        });
    });

    $all('.article a[href^="http"]').forEach(function (link) {
        if (link.hostname && link.hostname !== window.location.hostname) {
            link.target = '_blank';
            link.rel = 'noopener nofollow';
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && body.classList.contains('nav-open')) {
            setDrawer(false);
        }
    });

    /* 评论提交时给出反馈，避免重复点击 */
    var commentForm = doc.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function () {
            var btn = doc.getElementById('comment-submit');
            if (btn) {
                window.setTimeout(function () {
                    btn.disabled = true;
                    btn.classList.add('is-loading');

                    var submitLabel = btn.querySelector('.submit-label');

                    if (submitLabel) {
                        submitLabel.textContent = '提交中…';
                    }
                }, 10);
            }
        });
    }


/* ------------------------------------------------------------ card reveal */
(function () {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion || !('IntersectionObserver' in window)) {
        return;
    }

    var selector = '.feed-item, .card, .about-card, .about-heat-card, .archive-card, .archive-year, .archive-item, .album-card, .album-item, .links-card, .link-card, .tag-card, .tag-cloud-card, .post-card';
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, {
        root: null,
        rootMargin: '0px 0px -6% 0px',
        threshold: 0.04
    });

    var queued = false;

    function prepare(root) {
        var scope = root && root.nodeType === 1 ? root : document;
        var nodes = [];

        if (scope.matches && scope.matches(selector)) {
            nodes.push(scope);
        }

        if (scope.querySelectorAll) {
            Array.prototype.forEach.call(scope.querySelectorAll(selector), function (node) {
                nodes.push(node);
            });
        }

        nodes.forEach(function (node, index) {
            if (node.classList.contains('sleek-reveal') || node.closest('.sleek-music-dock, .site-header, .hero, .site-footer, .comments-list, .comment-body, .post-nav, .lightbox, .search-overlay, .drawer')) {
                return;
            }

            node.classList.add('sleek-reveal');
            node.style.setProperty('--reveal-delay', Math.min(index * 32, 140) + 'ms');
            observer.observe(node);
        });
    }

    function queuePrepare() {
        if (queued) {
            return;
        }

        queued = true;
        window.requestAnimationFrame(function () {
            queued = false;
            prepare(document);
        });
    }

    prepare(document);
    document.addEventListener('sleek:pjax', queuePrepare);

    if ('MutationObserver' in window) {
        var mutationObserver = new MutationObserver(queuePrepare);
        mutationObserver.observe(document.body, { childList: true, subtree: true });
    }
})();

})();

(function () {
    'use strict';

    var cleanup = null;

    function initPostToc() {
        if (cleanup) {
            cleanup();
            cleanup = null;
        }

        var article = document.querySelector('.post-card .article');
        var card = article ? article.closest('.post-card') : null;

        if (!article || !card) {
            return;
        }

        var headings = Array.prototype.slice.call(article.querySelectorAll('h2, h3')).filter(function (heading) {
            return heading.textContent.trim() !== '';
        });

        if (headings.length < 2) {
            return;
        }

        var usedIds = {};

        headings.forEach(function (heading, index) {
            if (!heading.id) {
                heading.id = 'toc-' + (index + 1);
            }

            var base = heading.id;
            var suffix = 2;

            while (usedIds[heading.id]) {
                heading.id = base + '-' + suffix;
                suffix++;
            }

            usedIds[heading.id] = true;
        });

        function buildNav(className) {
            var nav = document.createElement('nav');
            nav.className = className;

            headings.forEach(function (heading) {
                var link = document.createElement('a');
                link.href = '#' + heading.id;
                link.textContent = heading.textContent.trim().replace(/\s+/g, ' ');
                link.className = heading.tagName === 'H3' ? 'is-sub' : '';
                nav.appendChild(link);
            });

            return nav;
        }

        var aside = document.createElement('aside');
        aside.className = 'post-toc';
        aside.setAttribute('data-post-toc', '');
        aside.setAttribute('aria-label', '文章目录');
        aside.innerHTML = '<div class="post-toc-head"><div><span class="post-toc-kicker">CONTENTS</span><strong>目录</strong></div><em>' + headings.length + ' 节</em></div><div class="post-toc-progress"><i></i></div>';
        aside.appendChild(buildNav('post-toc-nav'));
        document.body.appendChild(aside);

        var mobile = document.createElement('details');
        mobile.className = 'post-toc-mobile';
        mobile.setAttribute('data-post-toc', '');
        mobile.innerHTML = '<summary><span><b>目录</b><em>' + headings.length + ' 节</em></span><i aria-hidden="true"></i></summary>';
        mobile.appendChild(buildNav('post-toc-mobile-nav'));
        card.insertBefore(mobile, article);

        var links = Array.prototype.slice.call(document.querySelectorAll('.post-toc-nav a, .post-toc-mobile-nav a'));
        var progress = aside.querySelector('.post-toc-progress i');
        var articleTop = 0;
        var articleHeight = 0;

        function measure() {
            var rect = article.getBoundingClientRect();
            articleTop = rect.top + window.pageYOffset;
            articleHeight = article.offsetHeight;
        }

        function update() {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            var threshold = scrollY + 150;
            var current = 0;

            headings.forEach(function (heading, index) {
                if (heading.getBoundingClientRect().top + scrollY <= threshold) {
                    current = index;
                }
            });

            if (scrollY + window.innerHeight >= document.documentElement.scrollHeight - 80) {
                current = headings.length - 1;
            }

            links.forEach(function (link) {
                var active = link.getAttribute('href') === '#' + headings[current].id;
                link.classList.toggle('is-active', active);

                if (active) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });

            var visible = scrollY + 180 > articleTop && scrollY < articleTop + articleHeight - 160;
            aside.classList.toggle('is-visible', visible);

            if (progress) {
                var ratio = (scrollY + 160 - articleTop) / Math.max(1, articleHeight - 260);
                progress.style.width = Math.min(100, Math.max(0, ratio * 100)) + '%';
            }
        }

        var ticking = false;

        function onScroll() {
            if (ticking) {
                return;
            }

            ticking = true;
            window.requestAnimationFrame(function () {
                ticking = false;
                update();
            });
        }

        links.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var target = document.getElementById(link.getAttribute('href').slice(1));

                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                var parent = link.closest('details');

                if (parent) {
                    parent.open = false;
                }
            });
        });

        measure();
        update();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', measure, { passive: true });

        cleanup = function () {
            aside.remove();
            mobile.remove();
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', measure);
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPostToc);
    } else {
        initPostToc();
    }

    document.addEventListener('sleek:pjax', initPostToc);
})();

/* Homepage photo carousel: drag, damping, arrows and native touch swipe */
(function () {
    function initPhotoCarousels() {
        var carousels = document.querySelectorAll('[data-photo-carousel]');

        Array.prototype.forEach.call(carousels, function (carousel) {
            if (carousel.getAttribute('data-carousel-ready') === '1') {
                return;
            }

            carousel.setAttribute('data-carousel-ready', '1');
            var wrapper = carousel.closest('.shuo-gallery');
            var prev = wrapper ? wrapper.querySelector('[data-gallery-prev]') : null;
            var next = wrapper ? wrapper.querySelector('[data-gallery-next]') : null;
            var down = false;
            var moved = false;
            var startX = 0;
            var startScroll = 0;
            var lastX = 0;
            var lastTime = 0;
            var velocity = 0;
            var glideFrame = 0;

            function stepSize() {
                var first = carousel.querySelector('.shuo-photo');

                if (!first) {
                    return carousel.clientWidth;
                }

                var gap = parseFloat(getComputedStyle(carousel).columnGap || '0') || 0;

                return first.getBoundingClientRect().width + gap;
            }

            function maxScroll() {
                return Math.max(0, carousel.scrollWidth - carousel.clientWidth);
            }

            function updateControls() {
                var max = maxScroll();
                var navigated = carousel.getAttribute('data-gallery-navigated') === '1';

                if (prev) {
                    // Appears immediately after using the right arrow; also at the far end.
                    prev.disabled = !navigated && carousel.scrollLeft < max - 2;
                }

                if (next) {
                    next.disabled = carousel.scrollLeft >= max - 2;
                }
            }

            function stopGlide() {
                if (glideFrame) {
                    cancelAnimationFrame(glideFrame);
                    glideFrame = 0;
                }

                carousel.classList.remove('is-gliding');
            }

            function snapToNearest() {
                var step = stepSize();
                var max = maxScroll();
                var target;

                if (carousel.scrollLeft >= max - 2) {
                    target = max;
                } else {
                    target = Math.round(carousel.scrollLeft / step) * step;
                }

                carousel.scrollTo({
                    left: Math.max(0, Math.min(max, target)),
                    behavior: 'smooth'
                });
            }

            function glide(initialVelocity) {
                stopGlide();

                if (Math.abs(initialVelocity) < 0.4) {
                    snapToNearest();
                    return;
                }

                carousel.classList.add('is-gliding');
                var speed = initialVelocity;
                var last = performance.now();

                function frame(now) {
                    var dt = Math.min(32, now - last);
                    last = now;
                    var factor = dt / 16.67;
                    var before = carousel.scrollLeft;

                    carousel.scrollLeft = before + speed * factor;
                    speed *= Math.pow(0.9, factor);

                    if (carousel.scrollLeft === before) {
                        speed = 0;
                    }

                    if (Math.abs(speed) > 0.3) {
                        glideFrame = requestAnimationFrame(frame);
                    } else {
                        glideFrame = 0;
                        carousel.classList.remove('is-gliding');
                        snapToNearest();
                    }
                }

                glideFrame = requestAnimationFrame(frame);
            }

            function scrollByStep(direction) {
                stopGlide();
                carousel.scrollBy({
                    left: direction * stepSize(),
                    behavior: 'smooth'
                });
            }

            carousel.addEventListener('dragstart', function (event) {
                event.preventDefault();
            });

            carousel.addEventListener('pointerdown', function (event) {
                if (event.pointerType !== 'mouse' || event.button !== 0) {
                    return;
                }

                stopGlide();
                down = true;
                moved = false;
                velocity = 0;
                startX = event.clientX;
                startScroll = carousel.scrollLeft;
                lastX = event.clientX;
                lastTime = performance.now();
                carousel.classList.add('is-dragging');
            });

            function moveDrag(event) {
                if (event.__sleekPhotoMoved) {
                    return;
                }

                event.__sleekPhotoMoved = true;

                if (!down) {
                    return;
                }

                var distance = event.clientX - startX;
                var now = performance.now();

                if (Math.abs(distance) > 4) {
                    moved = true;
                    carousel.scrollLeft = startScroll - distance;
                    event.preventDefault();
                }

                if (now - lastTime > 0) {
                    velocity = (event.clientX - lastX) / (now - lastTime);
                }

                lastX = event.clientX;
                lastTime = now;
            }

            // The document fallback keeps dragging working when pointer capture is unavailable.
            carousel.addEventListener('pointermove', moveDrag);
            document.addEventListener('pointermove', moveDrag, { passive: false });

            function endDrag(event) {
                if (event.__sleekPhotoEnded) {
                    return;
                }

                event.__sleekPhotoEnded = true;

                if (!down) {
                    return;
                }

                down = false;
                carousel.classList.remove('is-dragging');

                var recent = performance.now() - lastTime < 90;

                if (moved && recent && Math.abs(velocity) > 0.05) {
                    glide(-velocity * 14);
                } else {
                    snapToNearest();
                }
            }

            carousel.addEventListener('pointerup', endDrag);
            carousel.addEventListener('pointercancel', endDrag);
            document.addEventListener('pointerup', endDrag);
            document.addEventListener('pointercancel', endDrag);

            carousel.addEventListener('click', function (event) {
                if (moved) {
                    event.preventDefault();
                    event.stopPropagation();
                    moved = false;
                }
            }, true);

            carousel.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }

                event.preventDefault();
                var goingNext = event.key === 'ArrowRight';

                if (goingNext) {
                    carousel.setAttribute('data-gallery-navigated', '1');
                }

                scrollByStep(goingNext ? 1 : -1);
            });

            carousel.addEventListener('scroll', updateControls, { passive: true });
            window.addEventListener('resize', updateControls);

            updateControls();
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest
            ? event.target.closest('[data-gallery-prev], [data-gallery-next]')
            : null;

        if (!button || button.disabled) {
            return;
        }

        var gallery = button.closest('.shuo-gallery');
        var carousel = gallery ? gallery.querySelector('[data-photo-carousel]') : null;

        if (!carousel) {
            return;
        }

        event.preventDefault();
        var first = carousel.querySelector('.shuo-photo');
        var gap = parseFloat(getComputedStyle(carousel).columnGap || '0') || 0;
        var step = first ? first.getBoundingClientRect().width + gap : carousel.clientWidth / 3;
        var max = Math.max(0, carousel.scrollWidth - carousel.clientWidth);
        var goingNext = button.hasAttribute('data-gallery-next');
        var target = carousel.scrollLeft + (goingNext ? step : -step);

        if (goingNext) {
            carousel.setAttribute('data-gallery-navigated', '1');
        } else if (target <= 2) {
            carousel.removeAttribute('data-gallery-navigated');
        }

        var prevControl = gallery.querySelector('[data-gallery-prev]');

        if (prevControl) {
            prevControl.disabled = !goingNext && target <= 2;
        }

        carousel.classList.remove('is-gliding');
        carousel.scrollTo({
            left: Math.max(0, Math.min(max, target)),
            behavior: 'smooth'
        });
    });

    document.addEventListener('DOMContentLoaded', initPhotoCarousels);
    document.addEventListener('sleek:pjax', initPhotoCarousels);

    // Infinite scroll appends new cards without a pjax event; initialize those too.
    if (window.MutationObserver) {
        var photoObserverQueued = false;
        var photoObserver = new MutationObserver(function () {
            if (photoObserverQueued) {
                return;
            }

            photoObserverQueued = true;
            requestAnimationFrame(function () {
                photoObserverQueued = false;
                initPhotoCarousels();
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (document.body) {
                photoObserver.observe(document.body, { childList: true, subtree: true });
            }
        });
    }
})();


/* Single-image thumbnails: 16:9 images get a wider preview */
(function () {
    function markSingleImage(box, img) {
        if (!box || !img || !img.naturalWidth || !img.naturalHeight) {
            return;
        }

        var ratio = img.naturalWidth / img.naturalHeight;
        box.classList.toggle('is-wide', Math.abs(ratio - 16 / 9) <= 0.05);
    }

    function initSingleImages() {
        var boxes = document.querySelectorAll('.shuo-photos.count-1');
        Array.prototype.forEach.call(boxes, function (box) {
            var img = box.querySelector('img');

            if (!img) {
                return;
            }

            if (img.complete) {
                markSingleImage(box, img);
            } else {
                img.addEventListener('load', function () {
                    markSingleImage(box, img);
                }, { once: true });
            }
        });
    }

    document.addEventListener('load', function (event) {
        var img = event.target;

        if (img && img.tagName === 'IMG') {
            markSingleImage(img.closest('.shuo-photos.count-1'), img);
        }
    }, true);

    document.addEventListener('DOMContentLoaded', initSingleImages);
    document.addEventListener('sleek:pjax', initSingleImages);
})();

/* Hero status dialog */
(function () {
    var modal = document.getElementById('status-modal');

    if (!modal) {
        return;
    }

    var openButton = document.querySelector('[data-status-open]');
    var statusUrl = modal.getAttribute('data-status-url') || '';
    var statusToken = modal.getAttribute('data-status-token') || '';
    var grid = modal.querySelector('.status-grid');
    var form = modal.querySelector('[data-status-form]');
    var input = form ? form.querySelector('input[name="statusText"]') : null;
    var error = modal.querySelector('[data-status-error]');
    var closeButtons = modal.querySelectorAll('[data-status-close]');
    var clearButton = modal.querySelector('[data-status-clear]');

    function setError(message) {
        if (error) {
            error.textContent = message || '';
        }
    }

    function statusIconHtml(icon) {
        var source = grid
            ? grid.querySelector('[data-status-icon="' + String(icon).replace(/"/g, '') + '"] .status-option-icon')
            : null;

        return source ? source.innerHTML : '';
    }

    function updateStatusLabel(icon, text) {
        var buttons = document.querySelectorAll('[data-status-open]');

        Array.prototype.forEach.call(buttons, function (button) {
            button.classList.toggle('is-empty', !text);
            button.innerHTML = '';

            if (text) {
                var iconNode = document.createElement('span');
                iconNode.className = 'hero-status-icon';
                iconNode.innerHTML = statusIconHtml(icon || 'leaf');
                var textNode = document.createElement('span');
                textNode.className = 'hero-status-text';
                textNode.textContent = text;
                button.appendChild(iconNode);
                button.appendChild(textNode);
            } else {
                var plus = document.createElement('span');
                plus.className = 'hero-status-plus';
                plus.textContent = '+';
                var label = document.createElement('span');
                label.textContent = '状态';
                button.appendChild(plus);
                button.appendChild(label);
            }
        });

        if (input) {
            input.value = text || '';
        }
    }

    function markActive(icon, text) {
        if (!grid) {
            return;
        }

        Array.prototype.forEach.call(grid.querySelectorAll('.status-option'), function (option) {
            option.classList.toggle(
                'is-active',
                option.getAttribute('data-status-icon') === icon &&
                option.getAttribute('data-status-text') === text
            );
        });
    }

    function close() {
        modal.hidden = true;
        document.body.classList.remove('status-open');
        document.body.style.paddingRight = '';
        setError('');
    }

    function open() {
        var scrollbarWidth = Math.max(0, window.innerWidth - document.documentElement.clientWidth);

        document.body.style.paddingRight = scrollbarWidth > 0 ? scrollbarWidth + 'px' : '';
        modal.hidden = false;
        document.body.classList.add('status-open');
        setError('');

        if (input) {
            setTimeout(function () {
                try {
                    input.focus({ preventScroll: true });
                } catch (e) {
                    input.focus();
                }
                input.select();
            }, 40);
        }
    }

    function save(icon, text) {
        if (!statusUrl) {
            setError('保存地址无效');
            return;
        }

        setError('');
        modal.classList.add('is-saving');

        var data = new FormData();
        data.append('icon', icon || '');
        data.append('text', text || '');
        data.append('token', statusToken);

        fetch(statusUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        }).then(function (response) {
            return response.json().then(function (json) {
                if (!response.ok || !json.ok) {
                    throw new Error(json.error || '保存失败');
                }

                return json;
            });
        }).then(function (json) {
            var status = json.status || {};
            updateStatusLabel(status.icon || '', status.text || '');
            markActive(status.icon || '', status.text || '');
            modal.classList.remove('is-saving');
            close();
        }).catch(function (err) {
            modal.classList.remove('is-saving');
            setError(err.message || '保存失败');
        });
    }

    if (openButton) {
        openButton.addEventListener('click', open);
    }

    Array.prototype.forEach.call(closeButtons, function (button) {
        button.addEventListener('click', close);
    });

    if (grid) {
        grid.addEventListener('click', function (event) {
            var option = event.target.closest('.status-option');

            if (!option) {
                return;
            }

            save(
                option.getAttribute('data-status-icon') || '',
                option.getAttribute('data-status-text') || ''
            );
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var text = input ? input.value.trim() : '';

            if (text === '') {
                setError('请输入状态内容');
                return;
            }

            save('pencil', text);
        });
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            save('', '');
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            close();
        }
    });
})();

/* Sliding navigation indicator */
(function () {
    function initNavIndicator() {
        var nav = document.querySelector('.hero-nav .header-nav');

        if (!nav || nav.getAttribute('data-indicator-ready') === '1') {
            return;
        }

        var links = Array.prototype.slice.call(nav.querySelectorAll('.nav-link'));

        if (!links.length) {
            return;
        }

        nav.setAttribute('data-indicator-ready', '1');

        var indicator = nav.querySelector('.nav-indicator');

        if (!indicator) {
            indicator = document.createElement('span');
            indicator.className = 'nav-indicator';
            indicator.setAttribute('aria-hidden', 'true');
            nav.insertBefore(indicator, nav.firstChild);
        }

        var active = nav.querySelector('.nav-link.current') || links[0];
        var current = active;
        var immediate = true;

        function place(link) {
            if (!link) {
                return;
            }

            var navRect = nav.getBoundingClientRect();
            var linkRect = link.getBoundingClientRect();

            if (immediate) {
                indicator.style.transition = 'none';
            }

            indicator.style.left = (linkRect.left - navRect.left + nav.scrollLeft) + 'px';
            indicator.style.width = linkRect.width + 'px';

            if (immediate) {
                requestAnimationFrame(function () {
                    indicator.style.transition = '';
                    immediate = false;
                });
            }
        }

        function show(link) {
            current = link || active;
            nav.classList.toggle('is-hovering', current !== active);
            place(current);
        }

        function reset() {
            current = active;
            nav.classList.remove('is-hovering');
            place(active);
        }

        links.forEach(function (link) {
            link.addEventListener('mouseenter', function () {
                show(link);
            });

            link.addEventListener('focus', function () {
                show(link);
            });
        });

        nav.addEventListener('mouseleave', reset);
        nav.addEventListener('focusout', function (event) {
            if (!nav.contains(event.relatedTarget)) {
                reset();
            }
        });

        window.addEventListener('resize', function () {
            place(current);
        });

        nav.classList.add('nav-indicator-ready');
        place(active);
    }

    document.addEventListener('DOMContentLoaded', initNavIndicator);
    document.addEventListener('sleek:pjax', initNavIndicator);
})();
