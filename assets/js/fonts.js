(function(){
  var storageKey = 'rms-dashboard-font';
  var root = document.documentElement;

  function applyFont(font) {
    if (!font) font = 'yekan';
    root.setAttribute('data-font', font);
  }

  function loadFont() {
    try {
      var saved = localStorage.getItem(storageKey);
      if (saved) {
        applyFont(saved);
        return;
      }
    } catch (e) {}
    applyFont(root.getAttribute('data-font') || 'yekan');
  }

  function bindSwitchers(){
    var selects = [
      document.getElementById('fontSwitcherSelect'),
      document.getElementById('navbarFontSelect')
    ].filter(Boolean);

    selects.forEach(function(select){
      select.addEventListener('change', function(){
        var value = this.value || 'yekan';
        applyFont(value);
        selects.forEach(function(other){
          if (other !== select) {
            other.value = value;
          }
        });
        try { localStorage.setItem(storageKey, value); } catch(e){}
      });
    });

    if (selects.length) {
      var current = root.getAttribute('data-font') || 'yekan';
      selects.forEach(function(select){ select.value = current; });
    }
  }

  function init(){
    loadFont();
    bindSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

