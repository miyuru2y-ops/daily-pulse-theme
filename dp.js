/* Daily Pulse article tools: Listen (text-to-speech), Copy link, Save (localStorage). */
(function () {
  function onReady(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  onReady(function () {
    /* ---- Listen: read the article aloud ---- */
    var listenBtn = document.getElementById('dp-listen');
    var label = listenBtn ? listenBtn.querySelector('span') : null;
    var baseLabel = label ? label.textContent : '';
    var speaking = false;

    function setIdle() {
      speaking = false;
      if (listenBtn) listenBtn.classList.remove('on');
      if (label) label.textContent = baseLabel;
    }

    if (listenBtn && label) {
      if (!('speechSynthesis' in window)) {
        listenBtn.style.display = 'none';
      } else {
        listenBtn.addEventListener('click', function () {
          if (speaking) {
            window.speechSynthesis.cancel();
            setIdle();
            return;
          }
          var body = document.getElementById('dp-body');
          var text = body ? body.innerText.replace(/\s+/g, ' ').trim() : '';
          if (!text) return;
          window.speechSynthesis.cancel();
          var u = new SpeechSynthesisUtterance(text);
          u.onend = setIdle;
          u.onerror = setIdle;
          window.speechSynthesis.speak(u);
          speaking = true;
          listenBtn.classList.add('on');
          label.textContent = 'Stop';
        });
      }
    }

    /* ---- Copy link ---- */
    var copyBtn = document.getElementById('dp-copy');
    if (copyBtn) {
      var copyLabel = copyBtn.querySelector('span');
      var copyBase = copyLabel ? copyLabel.textContent : '';
      copyBtn.addEventListener('click', function () {
        function done() {
          if (copyLabel) copyLabel.textContent = 'Copied';
          copyBtn.classList.add('ok');
          setTimeout(function () {
            if (copyLabel) copyLabel.textContent = copyBase;
            copyBtn.classList.remove('ok');
          }, 1500);
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(window.location.href).then(done, done);
        } else {
          var ta = document.createElement('textarea');
          ta.value = window.location.href;
          document.body.appendChild(ta);
          ta.select();
          try { document.execCommand('copy'); } catch (e) {}
          document.body.removeChild(ta);
          done();
        }
      });
    }

    /* ---- Save: bookmark ids in localStorage ---- */
    var saveBtn = document.getElementById('dp-save');
    if (saveBtn) {
      var pid = String(saveBtn.getAttribute('data-id') || '');
      var saveLabel = saveBtn.querySelector('span');
      function getSaved() {
        try { return JSON.parse(localStorage.getItem('dp_saved') || '[]'); }
        catch (e) { return []; }
      }
      function paint() {
        var saved = getSaved().indexOf(pid) !== -1;
        saveBtn.classList.toggle('on', saved);
        if (saveLabel) saveLabel.textContent = saved ? 'Saved' : 'Save';
      }
      paint();
      saveBtn.addEventListener('click', function () {
        var s = getSaved();
        var i = s.indexOf(pid);
        if (i === -1) s.push(pid); else s.splice(i, 1);
        try { localStorage.setItem('dp_saved', JSON.stringify(s)); } catch (e) {}
        paint();
      });
    }
  });
})();
