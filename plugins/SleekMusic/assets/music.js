(function () {
    'use strict';

    var STORE_KEY = 'sleek-music-now';
    var MODE_KEY = 'sleek-music-mode';
    var DOCK_KEY = 'sleek-music-dock-open';
    var players = [];
    var registeredRoots = [];
    var dock = null;
    var active = null;
    var saveTimer = 0;
    var preloadedInline = false;
    var pendingResume = false;
    var playlist = [];
    var playlistIndex = -1;
    var playlistPanel = null;
    var playMode = 'loop';
    var lyricsPanel = null;
    var lyricsButton = null;
    var lyricsLines = [];
    var lyricsCache = {};
    var lyricsData = [];
    var lyricsIndex = -2;
    var lyricsOpen = false;
    var lyricsTrackUrl = '';
    var lyricsRequest = 0;

    function normalize(url) {
        if (!url) {
            return '';
        }

        try {
            return new URL(url, window.location.href).href;
        } catch (e) {
            return String(url);
        }
    }

    function formatTime(value) {
        if (!isFinite(value) || value < 0) {
            return '0:00';
        }

        var total = Math.floor(value);
        var minutes = Math.floor(total / 60);
        var seconds = total % 60;

        return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }

    function textOf(root, selector) {
        var node = root.querySelector(selector);
        return node ? node.textContent.replace(/\s+/g, ' ').trim() : '';
    }

    function coverOf(root) {
        var image = root.querySelector('.sm-cover img, .sm-dock-cover img');
        return image ? (image.src || image.getAttribute('src') || '') : '';
    }

    function readStore() {
        try {
            var raw = localStorage.getItem(STORE_KEY);

            if (!raw) {
                return null;
            }

            var data = JSON.parse(raw);
            return data && data.url ? data : null;
        } catch (e) {
            return null;
        }
    }

    function saveStore(player, playing) {
        if (!player || !player.url) {
            return;
        }

        try {
            localStorage.setItem(STORE_KEY, JSON.stringify({
                url: normalize(player.url),
                title: player.title || '音乐',
                artist: player.artist || '未知艺术家',
                cover: player.cover || '',
                time: isFinite(player.audio.currentTime) ? player.audio.currentTime : 0,
                playing: playing !== false && !player.audio.paused,
                updated: Date.now()
            }));
        } catch (e) {
            /* ignore */
        }
    }

    function scheduleSave(player) {
        if (saveTimer) {
            return;
        }

        saveTimer = window.setTimeout(function () {
            saveTimer = 0;

            if (active) {
                saveStore(active, !active.audio.paused);
            }
        }, 900);
    }

    function setExpanded(expanded) {
        if (dock) {
            dock.root.classList.toggle('is-expanded', !!expanded);
        }
    }

    function setDockDisplay(data) {
        if (!dock || !data) {
            return;
        }

        dock.displayUrl = normalize(data.url);
        dock.displayTitle = data.title || '音乐';
        dock.displayArtist = data.artist || '未知艺术家';
        dock.displayCover = data.cover || dock.defaultCover;

        var title = dock.root.querySelector('.sm-dock-title');
        var artist = dock.root.querySelector('.sm-dock-artist');
        var image = dock.root.querySelector('.sm-dock-cover img');

        if (title) {
            title.textContent = dock.displayTitle;
        }

        if (artist) {
            artist.textContent = dock.displayArtist;
        }

        if (image && dock.displayCover) {
            image.src = dock.displayCover;
        }
    }

    function restoreDock() {
        if (dock && dock.own) {
            setDockDisplay(dock.own);
        }
    }

    function syncDock(player) {
        if (dock && player && player !== dock) {
            dock.own = {
                url: player.url,
                title: player.title,
                artist: player.artist,
                cover: player.cover,
                kind: player.kind || 'music',
                id: player.id || ''
            };
            setDockDisplay(dock.own);
        }
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[char];
        });
    }

    function addPlaylistTrack(track) {
        if (!track || !track.url) {
            return -1;
        }

        var url = normalize(track.url);

        for (var i = 0; i < playlist.length; i += 1) {
            if (normalize(playlist[i].url) === url) {
                return i;
            }
        }

        playlist.push({
            url: track.url,
            title: track.title || '音乐',
            artist: track.artist || '未知艺术家',
            cover: track.cover || '',
            post: track.post || '',
            postUrl: track.postUrl || '',
            cid: track.cid || 0,
            kind: track.kind || 'music',
            id: track.id || ''
        });

        return playlist.length - 1;
    }

    function findPlaylistIndex(url) {
        var target = normalize(url);

        for (var i = 0; i < playlist.length; i += 1) {
            if (normalize(playlist[i].url) === target) {
                return i;
            }
        }

        return -1;
    }

    function renderPlaylist() {
        if (!playlistPanel) {
            return;
        }

        var oldList = playlistPanel.querySelector('.sm-playlist-list');
        var oldScrollTop = oldList ? oldList.scrollTop : 0;
        var html = '<div class="sm-playlist-head"><span>播放列表</span><em>' + playlist.length + ' 首</em></div>';

        if (!playlist.length) {
            html += '<div class="sm-playlist-empty">暂无音乐</div>';
        } else {
            html += '<div class="sm-playlist-list">';

            playlist.forEach(function (track, index) {
            var activeClass = index === playlistIndex ? ' is-active' : '';
            var cover = track.cover || '';
            var isNetease = track.kind === '163';
    var kind = '<span class="sm-track-kind' + (isNetease ? '' : ' is-local') + '">' + (isNetease ? '网抑云' : '本地') + '</span>';
            var image = cover
                ? '<img src="' + escapeHtml(cover) + '" alt="" loading="lazy">'
                : '<span class="sm-track-cover-fallback">♪</span>';
            var indexText = (index + 1 < 10 ? '0' : '') + (index + 1);
            var postUrl = track.postUrl || (track.cid ? '/archives/' + track.cid + '.html' : '');
            var indexHtml = postUrl
                ? '<a class="sm-track-index is-link" href="' + escapeHtml(postUrl) + '" title="打开文章：' + escapeHtml(track.post || track.title) + '" aria-label="打开文章">' + indexText + '</a>'
                : '<span class="sm-track-index">' + indexText + '</span>';

            html += '<div class="sm-track' + activeClass + '">'
                + indexHtml
                + '<button type="button" class="sm-track-main" data-sm-track="' + index + '"'
                + (track.post ? ' title="' + escapeHtml(track.post) + '"' : '') + '>'
                + '<span class="sm-track-cover">' + image + '</span>'
                + '<span class="sm-track-info"><strong>' + escapeHtml(track.title) + '</strong><small>' + escapeHtml(track.artist) + '</small></span>'
                + kind
                + '</button></div>';
        });

        html += '</div>';
        }

        playlistPanel.innerHTML = html;

        var newList = playlistPanel.querySelector('.sm-playlist-list');

        if (newList && oldScrollTop > 0) {
            newList.scrollTop = oldScrollTop;
        }

        Array.prototype.forEach.call(playlistPanel.querySelectorAll('[data-sm-track]'), function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                playPlaylistIndex(parseInt(button.getAttribute('data-sm-track'), 10));
            });
        });
    }

    function setPlaylistOpen(open) {
        if (!playlistPanel || !dock) {
            return;
        }

        if (open && lyricsOpen) {
            setLyricsOpen(false);
        }

        playlistPanel.hidden = !open;
        dock.root.classList.toggle('is-list-open', !!open);

        var toggle = dock.root.querySelector('[data-sm-playlist-toggle]');

        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    }

    function readDockOpen() {
        try {
            return localStorage.getItem(DOCK_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function saveDockOpen(open) {
        try {
            localStorage.setItem(DOCK_KEY, open ? '1' : '0');
        } catch (e) {
            /* ignore */
        }
    }

    function showDock() {
        if (!dock) {
            return;
        }

        dock.root.hidden = false;
        var reopen = dock.root.parentNode
            ? dock.root.parentNode.querySelector('[data-sm-reopen]')
            : null;

        if (!reopen) {
            reopen = document.querySelector('[data-sm-reopen]');
        }

        if (reopen) {
            reopen.hidden = true;
        }

        saveDockOpen(true);
    }

    function closeDock() {
        if (!dock) {
            return;
        }

        if (active) {
            active.audio.pause();
        } else {
            dock.audio.pause();
        }

        dock.root.hidden = true;
        setPlaylistOpen(false);
        setLyricsOpen(false);
        var reopen = document.querySelector('[data-sm-reopen]');

        if (reopen) {
            reopen.hidden = false;
        }

        saveDockOpen(false);
    }

    function playPlaylistIndex(index) {
        if (!dock || !playlist[index]) {
            return;
        }

        var track = playlist[index];

        playlistIndex = index;
        dock.own = {
            url: track.url,
            title: track.title,
            artist: track.artist,
            cover: track.cover,
        kind: track.kind || 'music',
        id: track.id || ''
        };

        setDockDisplay(dock.own);
        dock.url = normalize(track.url);
        dock.audio.preload = 'auto';
        dock.audio.setAttribute('src', track.url);
        dock.audio.load();
        showDock();
        setExpanded(true);
        renderPlaylist();

        var promise = dock.audio.play();

        if (promise && promise.catch) {
            promise.catch(function () {
                pendingResume = true;
            });
        }
    }

    function playNextPlaylistTrack() {
        if (!playlist.length) {
            return;
        }

        var next = playlistIndex + 1;

        if (playMode === 'shuffle' && playlist.length > 1) {
            do {
                next = Math.floor(Math.random() * playlist.length);
            } while (next === playlistIndex);
        } else if (next >= playlist.length) {
            next = 0;
        }

        playPlaylistIndex(next);
    }

    function playPrevPlaylistTrack() {
        if (!playlist.length) {
            return;
        }

        var prev = playlistIndex - 1;

        if (playMode === 'shuffle' && playlist.length > 1) {
            do {
                prev = Math.floor(Math.random() * playlist.length);
            } while (prev === playlistIndex);
        } else if (prev < 0) {
            prev = playlist.length - 1;
        }

        playPlaylistIndex(prev);
    }

    function readPlayMode() {
        try {
            var saved = localStorage.getItem(MODE_KEY);

            if (saved === 'loop' || saved === 'single' || saved === 'shuffle') {
                return saved;
            }
        } catch (e) {
            /* ignore */
        }

        return 'loop';
    }

    function modeLabel(mode) {
        if (mode === 'single') {
            return '单曲循环';
        }

        if (mode === 'shuffle') {
            return '随机播放';
        }

        return '列表循环';
    }

    function setPlayMode(mode, silent) {
        if (mode !== 'single' && mode !== 'shuffle') {
            mode = 'loop';
        }

        playMode = mode;

        if (dock) {
            dock.audio.loop = mode === 'single';
        }

        var button = dock ? dock.root.querySelector('[data-sm-mode-toggle]') : null;

        if (button) {
            button.setAttribute('data-mode', mode);
            button.setAttribute('title', modeLabel(mode));
            button.setAttribute('aria-label', modeLabel(mode));
        }

        if (!silent) {
            try {
                localStorage.setItem(MODE_KEY, mode);
            } catch (e) {
                /* ignore */
            }
        }
    }

    function cyclePlayMode() {
        var order = ['loop', 'single', 'shuffle'];
        var index = order.indexOf(playMode);
        setPlayMode(order[(index + 1) % order.length]);
    }

    function handleTrackEnded() {
        if (!dock) {
            return;
        }

        if (playMode === 'single') {
            try {
                dock.audio.currentTime = 0;
            } catch (e) {
                /* ignore */
            }

            dock.audio.play();
            return;
        }

        playNextPlaylistTrack();
    }

    function syncPlaylistIndexForUrl(url) {
        var index = findPlaylistIndex(url);

        if (index === -1 && url && dock && dock.own) {
            index = addPlaylistTrack(dock.own);
        }

        playlistIndex = index;
        renderPlaylist();
    }

    function parseLrc(lrcText) {
        var lines = [];
        var pattern = /\[(\d{1,2}):(\d{2})(?:[.:](\d{1,3}))?\]([^\[]*)/g;
        var match;
        var text = String(lrcText || '');

        while ((match = pattern.exec(text)) !== null) {
            var minutes = parseInt(match[1], 10) || 0;
            var seconds = parseInt(match[2], 10) || 0;
            var fraction = match[3] ? parseInt(match[3], 10) / Math.pow(10, match[3].length) : 0;
            var line = String(match[4] || '').trim();

            if (!line || /^(作词|作曲|编曲|制作人|录音|混音|母带|吉他|贝斯|鼓|和声|弦乐|OP|SP)\s*[:：]/i.test(line)) {
                continue;
            }

            lines.push({
                time: minutes * 60 + seconds + fraction,
                text: line
            });
        }

        lines.sort(function (a, b) {
            return a.time - b.time;
        });

        return lines;
    }

    function setLyricLines(previous, current, next, after) {
        var texts = [previous, current, next, after];

        lyricsLines.forEach(function (line, index) {
            line.textContent = texts[index] || '';
            line.classList.toggle('is-current', index === 1);
        });
    }

    function renderLyrics() {
        if (!lyricsLines.length) {
            return;
        }

        if (!lyricsOpen) {
            return;
        }

        if (!lyricsData.length) {
            setLyricLines('', '暂无歌词', '', '');
            return;
        }

        var index = lyricsIndex;

        if (index < 0) {
            index = 0;
        }

        setLyricLines(
            lyricsData[index - 1] ? lyricsData[index - 1].text : '',
            lyricsData[index] ? lyricsData[index].text : '',
            lyricsData[index + 1] ? lyricsData[index + 1].text : '',
            lyricsData[index + 2] ? lyricsData[index + 2].text : ''
        );
    }

    function updateLyrics() {
        if (!lyricsOpen || !lyricsData.length || !dock) {
            return;
        }

        var source = active && active.audio ? active.audio : dock.audio;
        var currentTime = isFinite(source.currentTime) ? source.currentTime : 0;
        var index = 0;

        for (var i = 0; i < lyricsData.length; i++) {
            if (currentTime >= lyricsData[i].time) {
                index = i;
            } else {
                break;
            }
        }

        if (index !== lyricsIndex) {
            lyricsIndex = index;
            renderLyrics();
        }
    }

    function setLyricsMessage(message) {
        if (!lyricsPanel) {
            return;
        }

        var body = lyricsPanel.querySelector('.sm-lyrics-body');
        var empty = lyricsPanel.querySelector('[data-sm-lyrics-empty]');

        if (!body || !empty) {
            return;
        }

        if (message) {
            body.hidden = true;
            empty.hidden = false;
            empty.textContent = message;
        } else {
            body.hidden = false;
            empty.hidden = true;
        }
    }

    function loadLyricsForCurrent() {
        if (!lyricsOpen || !dock || !dock.own) {
            return;
        }

        var track = dock.own;
        var endpoint = dock.root.getAttribute('data-sm-lyrics-url') || '';
        var song = dock.root.querySelector('[data-sm-lyrics-song]');

        if (song) {
            song.textContent = track.title || '';
        }

        if (track.kind !== '163') {
            lyricsRequest++;
            lyricsTrackUrl = normalize(track.url);
            lyricsData = [];
            lyricsIndex = -2;
            setLyricsMessage('此歌曲为本地歌曲，暂时无法提供歌词显示。');
            return;
        }

        if (!track.url || !endpoint) {
            return;
        }

        setLyricsMessage('');

        var requestUrl;

        if (track.kind === '163' && track.id) {
            requestUrl = endpoint + '?id=' + encodeURIComponent(String(track.id));
        } else {
            requestUrl = endpoint
                + '?title=' + encodeURIComponent(track.title || '')
                + '&artist=' + encodeURIComponent(track.artist || '');
        }

        lyricsTrackUrl = normalize(track.url);

        if (lyricsCache[requestUrl]) {
            lyricsData = lyricsCache[requestUrl];
            lyricsIndex = -2;
            renderLyrics();
            return;
        }

        lyricsData = [];
        lyricsIndex = -2;
        setLyricLines('', '歌词加载中…', '', '');

        var request = ++lyricsRequest;

        fetch(requestUrl, {
            credentials: 'same-origin',
            cache: 'force-cache',
            headers: { 'Accept': 'application/json' }
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (request !== lyricsRequest || lyricsTrackUrl !== normalize(track.url) || !lyricsOpen) {
                return;
            }

            var parsed = data && data.ok ? parseLrc(data.lrc || '') : [];
            lyricsCache[requestUrl] = parsed;
            lyricsData = parsed;
            lyricsIndex = -2;
            renderLyrics();
        }).catch(function () {
            if (request !== lyricsRequest) {
                return;
            }

            lyricsData = [];
            lyricsIndex = -2;
            setLyricLines('', '歌词加载失败', '', '');
        });
    }

    function setLyricsOpen(open) {
        if (!lyricsPanel || !dock) {
            return;
        }

        lyricsOpen = !!open;
        lyricsPanel.hidden = !lyricsOpen;

        if (lyricsButton) {
            lyricsButton.classList.toggle('is-active', lyricsOpen);
            lyricsButton.setAttribute('aria-expanded', lyricsOpen ? 'true' : 'false');
        }

        if (lyricsOpen) {
            if (playlistPanel && !playlistPanel.hidden) {
                setPlaylistOpen(false);
            }

            loadLyricsForCurrent();
        }
    }

    function initLyrics() {
        if (!dock) {
            return;
        }

        lyricsPanel = dock.root.querySelector('[data-sm-lyrics]');
        lyricsButton = dock.root.querySelector('[data-sm-lyrics-toggle]');
        lyricsLines = Array.prototype.slice.call(dock.root.querySelectorAll('[data-sm-lyric]'));

        if (lyricsButton) {
            lyricsButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                setLyricsOpen(!lyricsOpen);
            });
        }

        document.addEventListener('click', function (event) {
            if (!lyricsOpen || !lyricsPanel || lyricsPanel.hidden) {
                return;
            }

            if (dock && dock.root.contains(event.target)) {
                return;
            }

            setLyricsOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && lyricsOpen) {
                setLyricsOpen(false);
            }
        });
    }

    function initPlaylist() {
        var node = document.getElementById('sleek-music-playlist');

        if (node) {
            try {
                var data = JSON.parse(node.textContent || '[]');

                if (Array.isArray(data)) {
                    data.forEach(function (track) {
                        addPlaylistTrack(track);
                    });
                }
            } catch (e) {
                /* ignore */
            }
        }

        if (!playlist.length && dock && dock.own) {
            addPlaylistTrack(dock.own);
        }

        playlistPanel = dock ? dock.root.querySelector('[data-sm-playlist]') : null;

        var toggle = dock ? dock.root.querySelector('[data-sm-playlist-toggle]') : null;

        if (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                setPlaylistOpen(playlistPanel ? playlistPanel.hidden : false);
            });
        }

        var reopen = document.querySelector('[data-sm-reopen]');

        if (reopen) {
            reopen.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                showDock();
                setExpanded(true);
                setPlaylistOpen(true);
            });
        }

        Array.prototype.forEach.call(players, function (player) {
            if (!player.isDock) {
                addPlaylistTrack(player);
            }
        });

        if (dock && dock.own) {
            syncPlaylistIndexForUrl(dock.own.url);
        } else {
            renderPlaylist();
        }

        document.addEventListener('click', function (event) {
            if (!playlistPanel || playlistPanel.hidden) {
                return;
            }

            if (dock && dock.root.contains(event.target)) {
                return;
            }

            setPlaylistOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && playlistPanel && !playlistPanel.hidden) {
                setPlaylistOpen(false);
            }
        });
    }

    function updateDock() {
        if (!dock) {
            return;
        }

        var source = active && active !== dock ? active : dock;
        var playing = source && !source.audio.paused;

        dock.root.classList.toggle('is-playing', !!playing);
        dock.root.classList.toggle('is-loading', !!playing && source.audio.readyState < 3);

        if (source && dock.seek) {
            var duration = isFinite(source.audio.duration) ? source.audio.duration : 0;
            var current = isFinite(source.audio.currentTime) ? source.audio.currentTime : 0;

            dock.seek.max = duration;
            dock.seek.value = duration > 0 ? Math.min(current, duration) : 0;
            dock.seek.style.setProperty('--sm-progress', (duration > 0 ? Math.min(100, (current / duration) * 100) : 0) + '%');

            var dockTime = dock.root.querySelector('.sm-dock-time');

            if (dockTime) {
                dockTime.textContent = formatTime(current) + ' / ' + formatTime(duration);
            }
        }

        var dockCount = dock.root.querySelector('.sm-dock-count');

        if (dockCount) {
            dockCount.textContent = playlist.length ? playlist.length + ' 首' : '';
        }
    }

    function updateUI() {
        players.forEach(function (player) {
            var audio = player.audio;
            var playing = !audio.paused;
            var duration = isFinite(audio.duration) ? audio.duration : 0;
            var current = isFinite(audio.currentTime) ? audio.currentTime : 0;

            player.root.classList.toggle('is-playing', playing);
            player.root.classList.toggle('is-loading', playing && audio.readyState < 3);
            player.root.classList.toggle('is-error', !!audio.error);

            if (player.seek) {
                player.seek.max = duration;
                player.seek.value = duration > 0 ? Math.min(current, duration) : 0;
                player.seek.style.setProperty('--sm-progress', (duration > 0 ? Math.min(100, (current / duration) * 100) : 0) + '%');
            }

            if (player.time) {
                player.time.textContent = formatTime(current) + ' / ' + formatTime(duration);
            }

            if (player.state) {
                if (audio.error) {
                    player.state.textContent = '音乐加载失败';
                } else if (playing) {
                    player.state.textContent = audio.readyState < 3 ? '加载中…' : '正在播放';
                } else if (current > 0) {
                    player.state.textContent = '已暂停';
                } else {
                    player.state.textContent = '点击播放';
                }
            }
        });

        updateDock();
        updateLyrics();
    }

    function pauseOthers(except) {
        players.forEach(function (player) {
            if (player !== except && !player.audio.paused) {
                player.audio.pause();
            }
        });
    }

    function onPlay(player) {
        active = player;
        showDock();

        try {
            player.audio.preload = 'auto';
        } catch (e) {
            /* ignore */
        }

        pauseOthers(player);

        if (player !== dock) {
            if (dock && !dock.audio.paused) {
                dock.audio.pause();
            }

            syncDock(player);
        } else {
            restoreDock();
        }

        setExpanded(true);
        updateUI();
        saveStore(player, true);

        if (player === dock) {
            syncPlaylistIndexForUrl(player.own ? player.own.url : player.url);

            if (lyricsOpen) {
                loadLyricsForCurrent();
            }
        }

    }

    function onPause(player) {
        if (active === player) {
            updateUI();
            saveStore(player, false);
        } else {
            updateUI();
        }
    }

    function playPlayer(player) {
        if (!player || !player.url) {
            return;
        }

        active = player;

        if (player !== dock) {
            if (dock && !dock.audio.paused) {
                dock.audio.pause();
            }

            syncDock(player);
        } else {
            restoreDock();
        }

        if (player.audio.preload === 'none') {
            player.audio.preload = 'auto';
        }

        var promise = player.audio.play();

        if (promise && typeof promise.catch === 'function') {
            promise.catch(function () {
                updateUI();
            });
        }
    }

    function togglePlayer(player) {
        if (!player) {
            return;
        }

        if (!player.audio.paused) {
            if (player.audio.readyState < 3 && player.audio.currentTime < 0.1) {
                var retry = player.audio.play();

                if (retry && typeof retry.catch === 'function') {
                    retry.catch(function () {
                        updateUI();
                    });
                }

                return;
            }

            player.audio.pause();
            return;
        }

        playPlayer(player);
    }

    function toggleDock() {
        if (active && active !== dock) {
            togglePlayer(active);
        } else {
            togglePlayer(dock);
        }
    }

    function seekPlayer(player, value) {
        if (!player || !isFinite(player.audio.duration)) {
            return;
        }

        player.audio.currentTime = Math.min(value, player.audio.duration);
    }

    function register(root, isDock) {
        if (!root || registeredRoots.indexOf(root) !== -1) {
            return null;
        }

        var audio = root.querySelector('audio[data-sm-audio]');

        if (!audio) {
            return null;
        }

        registeredRoots.push(root);

        var player = {
            root: root,
            audio: audio,
            isDock: !!isDock,
            url: normalize(audio.getAttribute('src') || audio.src),
            title: textOf(root, isDock ? '.sm-dock-title' : '.sm-title') || '音乐',
            artist: textOf(root, isDock ? '.sm-dock-artist' : '.sm-artist') || '未知艺术家',
            cover: coverOf(root),
        kind: root.getAttribute('data-sm-kind') || 'music',
        id: root.getAttribute('data-sm-id') || '',
            seek: root.querySelector(isDock ? '.sm-dock-seek' : '.sm-seek'),
            time: root.querySelector('.sm-time'),
            state: root.querySelector('.sm-state')
        };

        players.push(player);

        Array.prototype.forEach.call(root.querySelectorAll('[data-sm-toggle]'), function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                if (isDock) {
                    toggleDock();
                } else {
                    togglePlayer(player);
                }
            });
        });

        if (player.seek) {
            player.seek.addEventListener('input', function () {
                var value = parseFloat(player.seek.value) || 0;

                if (isDock && active && active !== dock) {
                    seekPlayer(active, value);
                } else {
                    seekPlayer(isDock ? dock : player, value);
                }
            });
        }

        if (!isDock) {
            root.addEventListener('click', function (event) {
                if (event.target.closest('input, button, a, [data-sm-toggle]')) {
                    return;
                }

                togglePlayer(player);
            });
        }

        audio.addEventListener('play', function () {
            onPlay(player);
        });

        audio.addEventListener('pause', function () {
            onPause(player);
        });

        audio.addEventListener('timeupdate', function () {
            if (active === player) {
                updateUI();
            }

            scheduleSave(player);
        });

        audio.addEventListener('loadedmetadata', updateUI);
        audio.addEventListener('durationchange', updateUI);
        audio.addEventListener('canplay', updateUI);
        audio.addEventListener('waiting', updateUI);
        audio.addEventListener('ended', function () {
            onPause(player);

            if (isDock) {
                handleTrackEnded();
            }
        });
        audio.addEventListener('error', updateUI);

        return player;
    }

    function boot() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-sm-player]'), function (root) {
            register(root, false);
        });

        var dockRoot = document.querySelector('[data-sm-dock]');

        if (dockRoot) {
            dock = register(dockRoot, true);
            dock.defaultUrl = dock.url;
            dock.defaultTitle = dock.title;
            dock.defaultArtist = dock.artist;
            dock.defaultCover = dock.cover;
            dock.own = {
                url: dock.defaultUrl,
                title: dock.defaultTitle,
                artist: dock.defaultArtist,
                cover: dock.defaultCover
            };

            var close = dockRoot.querySelector('[data-sm-close]');

            if (close) {
                close.addEventListener('click', function (event) {
                    event.preventDefault();
                    closeDock();
                });
            }

            var prevButton = dockRoot.querySelector('[data-sm-prev]');

            if (prevButton) {
                prevButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    playPrevPlaylistTrack();
                });
            }

            var nextButton = dockRoot.querySelector('[data-sm-next]');

            if (nextButton) {
                nextButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    playNextPlaylistTrack();
                });
            }

            var modeButton = dockRoot.querySelector('[data-sm-mode-toggle]');

            if (modeButton) {
                modeButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    cyclePlayMode();
                });
            }

            setPlayMode(readPlayMode(), true);
            setExpanded(true);
            // Start as the compact playlist capsule on every page load.
            var dockOpen = false;
            saveDockOpen(false);
            dock.root.hidden = true;
            var reopenButton = document.querySelector('[data-sm-reopen]');

            if (reopenButton) {
                reopenButton.hidden = false;
            }

            initPlaylist();
            initLyrics();

            var saved = readStore();

            if (saved && saved.playing && dockOpen) {
                dock.own = {
                    url: saved.url,
                    title: saved.title,
                    artist: saved.artist,
                    cover: saved.cover
                };

                setDockDisplay(dock.own);
                dock.url = normalize(saved.url);
                syncPlaylistIndexForUrl(saved.url);

                dock.audio.preload = 'auto';
                dock.audio.setAttribute('src', saved.url);
                dock.audio.load();
                var savedTrackIndex = findPlaylistIndex(saved.url);

                if (savedTrackIndex !== -1 && playlist[savedTrackIndex]) {
                    dock.own.kind = playlist[savedTrackIndex].kind || 'music';
                    dock.own.id = playlist[savedTrackIndex].id || '';
                }

                setExpanded(true);
                active = null;

                var savedTime = parseFloat(saved.time) || 0;

                function applySavedPlayback() {
                    if (savedTime > 0 && isFinite(dock.audio.duration)) {
                        try {
                            dock.audio.currentTime = Math.max(0, Math.min(savedTime, dock.audio.duration - 0.2));
                        } catch (e) {
                            /* ignore */
                        }
                    }

                    var promise = dock.audio.play();

                    if (promise && promise.catch) {
                        promise.catch(function () {
                            pendingResume = true;
                        });
                    }
                }

                if (dock.audio.readyState >= 1) {
                    applySavedPlayback();
                } else {
                    dock.audio.addEventListener('loadedmetadata', applySavedPlayback, { once: true });
                }
            } else {
                setDockDisplay(dock.own);
                playlistIndex = playlist.length ? 0 : -1;
                renderPlaylist();

                try {
                    localStorage.removeItem(STORE_KEY);
                } catch (e) {
                    /* ignore */
                }
            }
        }

        var firstInline = null;

        players.some(function (player) {
            if (!player.isDock) {
                firstInline = player;
                return true;
            }

            return false;
        });

        if (firstInline) {
            preloadedInline = true;
            firstInline.audio.preload = 'auto';
            firstInline.audio.load();
        }

        function registerNewPlayers() {
            Array.prototype.forEach.call(document.querySelectorAll('[data-sm-player]'), function (root) {
                var player = register(root, false);

                if (player) {
                    addPlaylistTrack(player);
                    renderPlaylist();

                    if (!preloadedInline) {
                        preloadedInline = true;
                        player.audio.preload = 'auto';
                        player.audio.load();
                    }
                }
            });
        }

        if ('MutationObserver' in window) {
            var observer = new MutationObserver(registerNewPlayers);
            observer.observe(document.body, { childList: true, subtree: true });
        }

        document.addEventListener('click', function (event) {
            var root = event.target && event.target.closest
                ? event.target.closest('[data-sm-player]')
                : null;

            if (!root || registeredRoots.indexOf(root) !== -1) {
                return;
            }

            var player = register(root, false);

            if (player && !preloadedInline) {
                preloadedInline = true;
                player.audio.preload = 'auto';
                player.audio.load();
            }
        }, true);

        updateUI();

        window.addEventListener('pagehide', function () {
            if (active) {
                saveStore(active, !active.audio.paused);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    function promoteInlineToDock() {
        if (!dock || !active || active === dock || !active.audio || active.audio.paused) {
            return;
        }

        var source = active;
        var resumeAt = isFinite(source.audio.currentTime) ? source.audio.currentTime : 0;

        dock.own = {
            url: source.url,
            title: source.title,
            artist: source.artist,
            cover: source.cover,
        kind: source.kind || 'music',
        id: source.id || ''
        };

        setDockDisplay(dock.own);
        dock.url = normalize(source.url);
        dock.audio.preload = 'auto';
        dock.audio.setAttribute('src', source.url);
        dock.audio.load();
        setExpanded(true);

        var started = false;

        function startDockPlayback() {
            if (started) {
                return;
            }

            started = true;

            try {
                dock.audio.currentTime = resumeAt;
            } catch (e) {
                /* ignore */
            }

            var promise = dock.audio.play();

            if (promise && promise.catch) {
                promise.catch(function () {
                    pendingResume = true;
                });
            }

            dock.audio.addEventListener('playing', function () {
                try {
                    source.audio.pause();
                } catch (e) {
                    /* ignore */
                }
            }, { once: true });
        }

        if (dock.audio.readyState >= 1) {
            startDockPlayback();
        } else {
            dock.audio.addEventListener('loadedmetadata', startDockPlayback, { once: true });
        }
    }

    function resumePendingPlayback() {
        if (pendingResume && dock && dock.audio.paused) {
            pendingResume = false;

            var promise = dock.audio.play();

            if (promise && promise.catch) {
                promise.catch(function () {
                    /* ignore */
                });
            }
        }
    }

    document.addEventListener('sleek:before-pjax', promoteInlineToDock);
    document.addEventListener('pointerdown', resumePendingPlayback, { passive: true });
    document.addEventListener('keydown', resumePendingPlayback);
})();
