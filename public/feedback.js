(function () {
  'use strict';

  var id  = window._analyticsId;
  var api = window._analyticsUrl;
  if (!id || !api) return;

  var SESSION_KEY = 'ana_sid';
  function getSession() {
    var s = sessionStorage.getItem(SESSION_KEY);
    if (!s) {
      s = Math.random().toString(36).slice(2) + Date.now().toString(36);
      sessionStorage.setItem(SESSION_KEY, s);
    }
    return s;
  }

  var SHOWN_KEY = 'ana_fb_' + location.pathname;
  if (sessionStorage.getItem(SHOWN_KEY)) return;

  var widget = document.createElement('div');
  widget.style.cssText = [
    'position:fixed', 'bottom:20px', 'right:20px',
    'background:#fff', 'border:1px solid #e5e7eb',
    'border-radius:16px', 'padding:14px 16px',
    'box-shadow:0 4px 24px rgba(0,0,0,0.10)',
    'z-index:2147483647', 'font-family:ui-sans-serif,system-ui,sans-serif',
    'max-width:260px', 'opacity:0', 'transition:opacity .3s',
  ].join(';');

  widget.innerHTML = [
    '<p style="font-size:13px;font-weight:600;color:#111827;margin:0 0 10px">Was this page helpful?</p>',
    '<div style="display:flex;gap:8px">',
    btn(3, '😊', 'Helpful'),
    btn(2, '😐', 'Neutral'),
    btn(1, '😞', 'Not helpful'),
    '</div>',
  ].join('');

  function btn(rating, emoji, label) {
    return '<button data-r="' + rating + '" title="' + label + '" style="' + [
      'flex:1', 'padding:8px 4px', 'border:1px solid #e5e7eb',
      'border-radius:10px', 'background:#fff', 'cursor:pointer',
      'font-size:20px', 'line-height:1', 'transition:background .15s',
    ].join(';') + '">' + emoji + '</button>';
  }

  function send(rating) {
    sessionStorage.setItem(SHOWN_KEY, '1');
    fetch(api + '/api/track/feedback', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tracking_id: id,
        session_id: getSession(),
        page_url: location.href,
        rating: rating,
      }),
    }).catch(function () {});

    widget.innerHTML = '<p style="font-size:13px;font-weight:600;color:#111827;margin:0">Thanks for your feedback! 🙏</p>';
    setTimeout(dismiss, 2500);
  }

  function dismiss() {
    widget.style.opacity = '0';
    setTimeout(function () { widget.parentNode && widget.parentNode.removeChild(widget); }, 400);
  }

  widget.addEventListener('click', function (e) {
    var r = e.target.closest('[data-r]');
    if (r) send(parseInt(r.dataset.r, 10));
  });

  widget.addEventListener('mouseover', function (e) {
    var b = e.target.closest('[data-r]');
    if (b) b.style.background = '#fff7ed';
  });
  widget.addEventListener('mouseout', function (e) {
    var b = e.target.closest('[data-r]');
    if (b) b.style.background = '#fff';
  });

  function show() {
    document.body.appendChild(widget);
    setTimeout(function () { widget.style.opacity = '1'; }, 100);
    setTimeout(dismiss, 25000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(show, 4000); });
  } else {
    setTimeout(show, 4000);
  }
})();
