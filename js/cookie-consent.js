(function () {
  'use strict';

  var tool = document.getElementById('cookieTool');
  var acceptButton = document.getElementById('acceptAllCookies');
  var declineButton = document.getElementById('declineOptionalCookies');
  var settingsButton = document.getElementById('cookieSettingsButton');
  var cookieName = 'gew_cookie_consent';
  var consentVersion = 2;

  if (!tool || !acceptButton || !declineButton) return;

  function saveConsent(optionalAllowed) {
    var value = encodeURIComponent(JSON.stringify({ necessary: true, optional: optionalAllowed, version: consentVersion }));
    var secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = cookieName + '=' + value + '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
    document.documentElement.dataset.optionalCookies = optionalAllowed ? 'accepted' : 'declined';
    delete tool.dataset.consentPending;
    tool.removeAttribute('style');
    tool.hidden = true;
    window.location.reload();
  }

  if (tool.dataset.consentPending === '1') {
    tool.hidden = false;
  }

  acceptButton.addEventListener('click', function (event) {
    if (!event.isTrusted) return;
    saveConsent(true);
  });

  declineButton.addEventListener('click', function (event) {
    if (!event.isTrusted) return;
    saveConsent(false);
  });

  if (settingsButton) {
    settingsButton.addEventListener('click', function () {
      tool.dataset.consentPending = '1';
      tool.hidden = false;
      declineButton.focus();
    });
  }
}());
