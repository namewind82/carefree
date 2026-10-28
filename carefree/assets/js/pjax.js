(function () {
    'use strict';

    if (!window.fetch || !window.history || !window.history.pushState || !window.DOMParser) {
        return;
    }

    var root = document.documentElement;
    var main = document.getElementById('main');
    var controller = null;
    var loading = false;
    var pjaxMode = false;
    var lastPath = window.location.pathname + window.location.search;

    if (!main) {
        return;
    }

    function musicPlaying() {
        var audios = document.querySelectorAll('audio');

        return Array.prototype.some.call(audios, function (audio) {
            return !audio.paused && !audio.ended;
        });
    }

    function isEligible(link) {
        if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-no-pjax')) {
            return false;
        }

        if (link.origin !== window.location.origin) {
            return false;
        }

        if (!musicPlaying()) {
            return false;
        }

        var href = link.getAttribute('href') || '';

        if (href === '' || href.charAt(0) === '#' || /^(?:mailto|tel|javascript):/i.test(href)) {
            return false;
        }

        if (link.pathname === '/' && link.hash !== '') {
            return false;
        }

        if (/\/(?:admin|action|comment)(?:\/|$)/i.test(link.pathname)) {
            return false;
        }

        if (/\/(?:feed|rss)(?:\/|$)/i.test(link.pathname) || /\.(?:xml|json|zip|rar|7z|pdf|jpe?g|png|webp|gif|mp3|mp4|m3u8)$/i.test(link.pathname)) {
            return false;
        }

        return true;
    }

    function syncNav(nextDoc) {
        var nextCurrent = nextDoc.querySelector('.hero-nav .nav-link.current');
        var nextHref = nextCurrent ? nextCurrent.getAttribute('href') : '';

        Array.prototype.forEach.call(document.querySelectorAll('.hero-nav .nav-link'), function (link) {
            var match = nextHref !== '' && link.getAttribute('href') === nextHref;
            link.classList.toggle('current', match);

            if (match) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function runScripts(container) {
        Array.prototype.forEach.call(container.querySelectorAll('script'), function (oldScript) {
            var script = document.createElement('script');

            Array.prototype.forEach.call(oldScript.attributes, function (attr) {
                script.setAttribute(attr.name, attr.value);
            });

            script.text = oldScript.textContent;
            oldScript.parentNode.replaceChild(script, oldScript);
        });
    }

    function scrollToHash(hash) {
        if (!hash || hash.length < 2) {
            return;
        }

        var id = hash.slice(1);

        try {
            id = decodeURIComponent(id);
        } catch (e) {
            /* keep raw id */
        }

        var target = document.getElementById(id);

        if (!target) {
            return;
        }

        var top = target.getBoundingClientRect().top + window.pageYOffset - 86;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    function replaceMain(nextDoc, url, push) {
        var nextMain = nextDoc.getElementById('main');

        if (!nextMain) {
            throw new Error('missing main');
        }

        main.replaceWith(nextMain);
        main = nextMain;
        pjaxMode = true;

        document.title = nextDoc.title;
        syncNav(nextDoc);
        runScripts(main);

        if (push) {
            window.history.pushState({ sleekPjax: true }, '', url);
        }

        window.scrollTo(0, 0);
        lastPath = window.location.pathname + window.location.search;

        if (window.location.hash) {
            scrollToHash(window.location.hash);
        }

        root.classList.remove('is-pjaxing');
        loading = false;

        document.dispatchEvent(new CustomEvent('sleek:pjax', {
            detail: { url: url }
        }));
    }

    function load(url, push) {
        if (loading) {
            return;
        }

        loading = true;
        root.classList.add('is-pjaxing');

        if (controller) {
            controller.abort();
        }

        controller = window.AbortController ? new AbortController() : null;

        fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'SleekPjax'
            },
            signal: controller ? controller.signal : undefined
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('http ' + response.status);
                }

                return response.text();
            })
            .then(function (html) {
                var nextDoc = new DOMParser().parseFromString(html, 'text/html');
                replaceMain(nextDoc, url, push);
            })
            .catch(function (error) {
                root.classList.remove('is-pjaxing');
                loading = false;

                if (!error || error.name !== 'AbortError') {
                    window.location.href = url;
                }
            });
    }

    window.addEventListener('click', function (event) {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        var link = event.target.closest('a');

        if (link && link.hash && link.pathname === window.location.pathname && link.search === window.location.search) {
            event.preventDefault();

            if (link.hash.length > 1) {
                window.history.pushState({ sleekPjax: true }, '', link.hash);
                scrollToHash(link.hash);
            }

            return;
        }

        if (!isEligible(link)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        document.dispatchEvent(new CustomEvent('sleek:before-pjax'));
        load(link.href, true);
    }, true);

    window.addEventListener('popstate', function () {
        var currentPath = window.location.pathname + window.location.search;

        if (currentPath === lastPath) {
            if (window.location.hash) {
                scrollToHash(window.location.hash);
            }

            return;
        }

        lastPath = currentPath;

        if ((window.location.pathname === '/' || window.location.pathname === '') && !musicPlaying()) {
            window.location.reload();
            return;
        }

        load(window.location.href, false);
    });

    document.addEventListener('click', function (event) {
        if (!pjaxMode || event.defaultPrevented || event.button !== 0) {
            return;
        }

        var button = event.target.closest('[data-like-cid]');

        if (!button || button.dataset.busy === '1' || button.classList.contains('is-liked')) {
            return;
        }

        event.preventDefault();

        var cid = button.getAttribute('data-like-cid');
        var url = button.getAttribute('data-like-url');

        if (!cid || !url) {
            return;
        }

        button.dataset.busy = '1';
        button.classList.add('is-loading');

        fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (!data || !data.ok) {
                throw new Error('like failed');
            }

            Array.prototype.forEach.call(document.querySelectorAll('[data-like-cid="' + cid + '"]'), function (item) {
                var counter = item.querySelector('.like-count');

                if (counter) {
                    counter.textContent = data.count;
                }

                item.classList.remove('is-loading');
                item.classList.add('is-liked');
                item.setAttribute('aria-pressed', 'true');
                item.disabled = true;
            });

            Array.prototype.forEach.call(document.querySelectorAll('[data-like-summary-cid="' + cid + '"]'), function (summary) {
                var counter = summary.querySelector('.like-summary-count');

                if (counter) {
                    counter.textContent = data.count;
                }

                summary.hidden = false;
            });
        }).catch(function () {
            button.classList.remove('is-loading');
        }).then(function () {
            button.dataset.busy = '0';
        });
    });

    window.history.replaceState({ sleekPjax: true }, '', window.location.href);
})();
