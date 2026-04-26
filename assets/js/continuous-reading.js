/*! Hungry Flamingo Blog Companion — continuous-reading.js */
(function () {
	'use strict';

	var stack = document.querySelector('[data-hfb-stack]');
	if (!stack) { return; }
	if (!window.HFB_CR || typeof window.fetch !== 'function' || typeof window.IntersectionObserver !== 'function') {
		stack.remove();
		return;
	}

	var cfg = window.HFB_CR;
	var slots = stack.querySelectorAll('[data-hfb-slot]');
	if (!slots.length) { return; }

	var initial = document.querySelector('.hfb-article--main') || stack.closest('.hfb-article');
	var seenIds = [cfg.postId];
	var strings = (cfg && cfg.strings) || {};
	var canonicalTitle = document.title;
	var initialUrl = location.href;

	function liveRegion() {
		var el = document.getElementById('hfb-live');
		if (!el) {
			el = document.createElement('div');
			el.id = 'hfb-live';
			el.setAttribute('role', 'status');
			el.setAttribute('aria-live', 'polite');
			el.setAttribute('aria-atomic', 'true');
			el.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;';
			document.body.appendChild(el);
		}
		return el;
	}

	var lastAnnounced = '';

	function announce(text) {
		if (!text || text === lastAnnounced) { return; }
		lastAnnounced = text;
		liveRegion().textContent = text;
	}

	function buildUrl() {
		var params = new URLSearchParams();
		params.set('after', String(cfg.postId));
		params.set('count', String(slots.length));
			seenIds.forEach(function (id, i) {
				if (i === 0) { return; }
				params.append('seen[]', String(id));
			});
		return cfg.endpoint + '?' + params.toString();
	}

	function fillSlot(slot, item) {
		if (!item || !item.html) {
			slot.remove();
			return null;
		}

		slot.innerHTML = item.html;
		slot.setAttribute('aria-busy', 'false');
		slot.classList.add('hfb-post-stack__slot--loaded');
		slot.classList.remove('hfb-post-stack__slot--skeleton');
		slot.dataset.postId = String(item.id);
		slot.dataset.permalink = item.permalink;
		slot.dataset.title = item.title;
		seenIds.push(item.id);

		var inner = slot.querySelector('.hfb-article');
		if (inner) {
			inner.setAttribute('data-hfb-stack-item', '');
			return inner;
		}
		return slot;
	}

	function renderItems(items) {
		var rendered = [];
		slots.forEach(function (slot, i) {
			var el = fillSlot(slot, items[i]);
			if (el) { rendered.push(el); }
		});
		return rendered;
	}

	function fetchNext() {
		return fetch(buildUrl(), {
			credentials: 'same-origin',
			headers: {
				'Accept': 'application/json'
			}
		}).then(function (response) {
			if (!response.ok) { throw new Error('HTTP ' + response.status); }
			return response.json();
		});
	}

	function activate(article) {
		var permalink = article.getAttribute('data-permalink');
		var title = article.getAttribute('data-title');
		if (!permalink || !title || permalink === location.href) { return; }

		try {
			history.replaceState({ hfbPostId: Number(article.dataset.postId) }, '', permalink);
		} catch {
			return;
		}

		var suffix = '';
		var dashIdx = canonicalTitle.lastIndexOf(' — ');
		if (dashIdx === -1) { dashIdx = canonicalTitle.lastIndexOf(' – '); }
		if (dashIdx === -1) { dashIdx = canonicalTitle.lastIndexOf(' | '); }
		if (dashIdx > 0) { suffix = canonicalTitle.slice(dashIdx); }

		document.title = title + suffix;
		announce(title);

		window.dispatchEvent(new CustomEvent('hfb:postChanged', {
			detail: { id: Number(article.dataset.postId), url: permalink, title: title }
		}));
	}

	var observer = null;
	var rafPending = false;
	var pendingTarget = null;

	function observe(articles) {
		observer = new IntersectionObserver(function (entries) {
			var target = null;
			entries.forEach(function (entry) {
				if (entry.isIntersecting) { target = entry.target; }
			});
			if (!target) { return; }

			pendingTarget = target;
			if (rafPending) { return; }
			rafPending = true;

			requestAnimationFrame(function () {
				rafPending = false;
				var next = pendingTarget;
				pendingTarget = null;
				if (next) { activate(next); }
			});
		}, {
			root: null,
			rootMargin: '0px 0px -80% 0px',
			threshold: 0
		});

		articles.forEach(function (article) { observer.observe(article); });
		if (initial) { observer.observe(initial); }
	}

	window.addEventListener('pagehide', function () {
		if (observer) { observer.disconnect(); }
	});

	function copiedMsg() {
		return (strings && strings.linkCopied) || 'Link copied';
	}

	function setCopiedState(btn, copied) {
		var label = btn.querySelector('.share-btn__copied');
		if (!label) { return; }

		label.textContent = copied ? copiedMsg() : '';
		label.hidden = !copied;
		btn.classList.toggle('is-copied', copied);
	}

	function clipboardFallback(url, btn) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(function () {
				setCopiedState(btn, true);
				announce(copiedMsg());
				setTimeout(function () { setCopiedState(btn, false); }, 1800);
			}).catch(function () {
				window.prompt(copiedMsg() + ':', url);
			});
			return;
		}
		window.prompt(copiedMsg() + ':', url);
	}

	stack.addEventListener('click', function (e) {
		var btn = e.target.closest && e.target.closest('[data-hfb-share]');
		if (!btn) { return; }

		var url = btn.getAttribute('data-hfb-share') || location.href;
		if (navigator.share) {
			navigator.share({ url: url, title: document.title }).catch(function () {
				clipboardFallback(url, btn);
			});
			return;
		}
		clipboardFallback(url, btn);
	});

	fetchNext()
		.then(function (payload) {
			var items = (payload && payload.items) || [];
			var rendered = renderItems(items);
			observe(rendered);
		})
		.catch(function (err) {
			if (window.console && console.warn) {
				console.warn('HFB continuous-reading:', err);
			}
			slots.forEach(function (slot) { slot.remove(); });
		});

	window.addEventListener('popstate', function () {
		if (location.href === initialUrl) {
			document.title = canonicalTitle;
		}
	});
}());
