(function(){
  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn();
  }

  ready(function(){
    const cfg = (window.RMS && window.RMS.notifications) ? window.RMS.notifications : null;
    if (!cfg) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // عناصر را به‌صورت lazy resolve نگه می‌داریم تا اگر اول کار موجود نبودند، هنگام نمایش offcanvas پیدا شوند
    function els(){
      return {
        bellBadge: document.getElementById('notif-badge'),
        listContainer: document.getElementById('notif-list'),
        emptyState: document.getElementById('notif-empty'),
        markAllBtn: document.getElementById('notif-mark-all'),
        spinner: document.getElementById('notif-spinner'),
        offcanvas: document.getElementById('notifications'),
      };
    }

    function setBadge(count){
      const { bellBadge } = els();
      if (!bellBadge) return;
      if (count > 0){
        bellBadge.textContent = String(count);
        bellBadge.classList.remove('d-none');
      } else {
        bellBadge.textContent = '0';
        bellBadge.classList.add('d-none');
      }
    }

    function renderItems(items){
      const { listContainer, emptyState } = els();
      if (!listContainer || !emptyState) return;
      listContainer.innerHTML = '';
      if (!items || items.length === 0){
        emptyState.classList.remove('d-none');
        return;
      }
      emptyState.classList.add('d-none');

      items.forEach(function(it){
        const id = (it && (it.id ?? it.ID ?? it.Id)) ?? null;
        const li = document.createElement('div');
        li.className = 'd-flex align-items-start mb-3 cursor-pointer notif-item';
        if (id !== null) li.setAttribute('data-id', String(id));
        li.innerHTML = (
          '<div class="me-2">' +
            '<span class="badge rounded-pill bg-light text-body border">' + escapeHtml(it.category || '') + '</span>' +
          '</div>' +
          '<div class="flex-fill">' +
            (it.title ? ('<div class="fw-semibold">' + escapeHtml(it.title) + '</div>') : '') +
            '<div class="text-muted mt-1 notification-content">' + (it.message || '') + '</div>' +
            '<div class="d-flex justify-content-end mt-1">' +
              '<div class="fs-sm text-muted">' + escapeHtml(it.created_at_persian || formatTime(it.created_at)) + '</div>' +
            '</div>' +
          '</div>' +
          '<div class="ms-2 d-flex align-items-center">' +
            '<button type="button" class="btn btn-success btn-sm mark-read" title="Mark as read">' +
              '<i class="ph-checks"></i>' +
            '</button>' +
          '</div>'
        );
        listContainer.appendChild(li);
      });
    }

    function escapeHtml(s){
      return String(s ?? '').replace(/[&<>"']/g, function(c){
        return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c] || c;
      });
    }

    function formatTime(ts){
      try { return new Date(ts).toLocaleString(); } catch(e){ return ''; }
    }

    async function fetchUnread(){
      const { spinner } = els();
      if (!cfg.unread_url) return;
      spinner && spinner.classList.remove('d-none');
      try{
        const res = await fetch(cfg.unread_url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        setBadge(data.count || 0);
        renderItems(data.items || []);
      }catch(err){
        // console.warn('notifications fetch failed', err);
      } finally {
        spinner && spinner.classList.add('d-none');
      }
    }

    async function markRead(id){
      if (!cfg.mark_read_url || !id) return;
      const url = cfg.mark_read_url.replace('__ID__', String(id));
      try{
        await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf || '', 'Accept': 'application/json' } });
        await fetchUnread();
      }catch(e){ /* ignore */ }
    }

    async function markAll(){
      if (!cfg.mark_all_read_url) return;
      try{
        await fetch(cfg.mark_all_read_url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf || '', 'Accept': 'application/json' } });
        await fetchUnread();
      }catch(e){ /* ignore */ }
    }

    document.addEventListener('click', function(e){
      const btn = e.target.closest('.mark-read');
      if (btn){
        const item = btn.closest('.notif-item');
        const id = item && item.getAttribute('data-id');
        if (id) markRead(id);
      }
    });

    // بستن همه
    ready(function(){
      const { markAllBtn, offcanvas } = els();
      if (markAllBtn){ markAllBtn.addEventListener('click', function(){ markAll(); }); }
      if (offcanvas){
        offcanvas.addEventListener('shown.bs.offcanvas', fetchUnread);
      }
    });

    // بارگذاری اولیه و polling توسعه‌ای
    fetchUnread();
    setInterval(fetchUnread, 20000);
  });
})();
