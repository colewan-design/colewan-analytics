(function () {
  'use strict';

  var id  = window._analyticsId;
  var api = window._analyticsUrl;

  if (!id || !api) return;

  // ---------- Session ----------
  var SESSION_KEY = 'ana_sid';
  function getSession() {
    var s = sessionStorage.getItem(SESSION_KEY);
    if (!s) {
      s = Math.random().toString(36).slice(2) + Date.now().toString(36);
      sessionStorage.setItem(SESSION_KEY, s);
    }
    return s;
  }

  // ---------- Send ----------
  function send(endpoint, payload) {
    var url = api + '/api/' + endpoint;
    try {
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload),
        keepalive: true,
      }).catch(function () {});
    } catch (e) {}
  }

  // ---------- Page View ----------
  function trackPageview() {
    send('track/pageview', {
      tracking_id: id,
      session_id:  getSession(),
      url:         location.href,
      title:       document.title || null,
      referrer:    document.referrer || null,
      screen_w:    screen.width,
      screen_h:    screen.height,
      language:    navigator.language || null,
    });
  }

  // ---------- Click ----------
  function trackClick(e) {
    var el = e.target;

    // Walk up to find the meaningful element (link, button, input)
    var depth = 0;
    while (el && el !== document.body && depth < 5) {
      var tag = (el.tagName || '').toLowerCase();
      if (['a', 'button', 'input', 'select', 'textarea', 'label'].indexOf(tag) !== -1) break;
      el = el.parentElement;
      depth++;
    }
    if (!el || el === document.body) el = e.target;

    var tag  = (el.tagName || 'unknown').toLowerCase();
    var text = (el.innerText || el.value || el.alt || el.placeholder || '').trim().slice(0, 200);
    var href = el.href || null;
    var elId = el.id || null;
    var cls  = (el.className && typeof el.className === 'string') ? el.className.slice(0, 200) : null;

    send('track/click', {
      tracking_id:   id,
      session_id:    getSession(),
      page_url:      location.href,
      element_tag:   tag,
      element_id:    elId,
      element_class: cls,
      element_text:  text || null,
      element_href:  href || null,
      x:             Math.round(e.clientX),
      y:             Math.round(e.clientY),
    });
  }

  // ---------- SPA support (history navigation) ----------
  function onNavigation() {
    setTimeout(function () {
      trackPageview();
    }, 50);
  }

  // ---------- Init ----------
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', trackPageview);
  } else {
    trackPageview();
  }

  document.addEventListener('click', trackClick, true);

  // Intercept pushState / replaceState for SPA frameworks
  var _push = history.pushState;
  var _replace = history.replaceState;
  history.pushState = function () {
    _push.apply(history, arguments);
    onNavigation();
  };
  history.replaceState = function () {
    _replace.apply(history, arguments);
    onNavigation();
  };
  window.addEventListener('popstate', onNavigation);
})();
