document.addEventListener('DOMContentLoaded',function(){
  const form = document.getElementById('acc-form');
  const btnEdit = document.getElementById('acc-edit');
  const btnCancel = document.getElementById('acc-cancel');
  const btnSave = document.getElementById('acc-save');

  function setEditing(on){
    if(!form) return;
    if(on){
      form.classList.add('editing');
      if(btnCancel) btnCancel.disabled = false;
      if(btnSave) btnSave.disabled = false;
    } else {
      form.classList.remove('editing');
      if(btnCancel) btnCancel.disabled = true;
      if(btnSave) btnSave.disabled = true;
    }
  }

  // start in read mode
  setEditing(false);

  if(btnEdit){
    btnEdit.addEventListener('click',function(e){ e.preventDefault(); setEditing(true); });
  }
  if(btnCancel){
    btnCancel.addEventListener('click',function(e){ e.preventDefault(); window.location.href='account.php'; });
  }

  // Popup de cambios guardados ?update=ok
  const params = new URLSearchParams(window.location.search);
  const popup = document.getElementById('acc-popup');
  const okBtn = document.getElementById('acc-popup-ok');
  if(popup && params.get('update') === 'ok'){
    popup.style.display = 'flex';
  }
  if(okBtn){
    okBtn.addEventListener('click',function(){
      // limpiar query y cerrar popup
      const url = new URL(window.location.href);
      url.searchParams.delete('update');
      window.location.replace(url.toString());
    });
  }

  // Eliminar foto de perfil
  const btnRemove = document.getElementById('acc-remove-photo');
  const formRemove = document.getElementById('acc-remove-form');
  if(btnRemove && formRemove){
    btnRemove.addEventListener('click', function(){
      if (btnRemove.disabled) return;
      const ok = confirm('¿Eliminar foto de perfil? Esta acción no se puede deshacer.');
      if (ok) formRemove.submit();
    });
  }

  // Gestionar usuarios (solo si existe la tabla)
  const usersTable = document.getElementById('acc-users-table');
  if (usersTable){
    function loadUsers(q){
      const url = new URL('admin/users_list.php', window.location.href);
      if (q) url.searchParams.set('q', q);
      fetch(url.toString(), { credentials:'same-origin' })
        .then(r => r.json())
        .then(data => {
          const tb = usersTable.querySelector('tbody');
          if (!data || !Array.isArray(data.items)) { tb.innerHTML = '<tr><td colspan="5">No se pudo cargar</td></tr>'; return; }
          if (!data.items.length) { tb.innerHTML = '<tr><td colspan="5">Sin usuarios</td></tr>'; return; }
          tb.innerHTML = data.items.map(u => `
            <tr data-id="${u.id}" data-fullname="${u.fullname||''}" data-username="${u.username||''}" data-email="${u.email||''}" data-rol="${u.role||'user'}">
              <td>${u.id}</td>
              <td>${u.fullname || ''}</td>
              <td>@${u.username || ''}</td>
              <td>${u.email || ''}</td>
              <td>${u.role != null ? u.role : '-'}</td>
            </tr>
          `).join('');
        })
        .catch(() => {
          const tb = usersTable.querySelector('tbody');
          tb.innerHTML = '<tr><td colspan="5">Error al cargar</td></tr>';
        });
    }

    let currentQ = '';
    loadUsers(currentQ);

    // selección de fila
    usersTable.addEventListener('click', function(e){
      const tr = e.target.closest('tbody tr');
      if(!tr) return;
      usersTable.querySelectorAll('tbody tr').forEach(r=>r.classList.remove('selected'));
      tr.classList.add('selected');
    });

    // Botones CRUD
    const bCreate = document.getElementById('u-btn-create');
    const bEdit   = document.getElementById('u-btn-edit');
    const bDel    = document.getElementById('u-btn-delete');
    const bRef    = document.getElementById('u-btn-refresh');

    if (bRef) bRef.addEventListener('click', ()=>{ currentQ=''; const si=document.getElementById('u-search'); if(si) si.value=''; loadUsers(currentQ); });

    const bSearch = document.getElementById('u-btn-search');
    const iSearch = document.getElementById('u-search');
    if (bSearch) bSearch.addEventListener('click', ()=>{ currentQ = (iSearch?.value||'').trim(); loadUsers(currentQ); });
    if (iSearch) iSearch.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); currentQ = (iSearch.value||'').trim(); loadUsers(currentQ); } });

    if (bCreate){
      bCreate.addEventListener('click', ()=>{
        const tb = usersTable.querySelector('tbody');
        if (tb.querySelector('tr[data-mode="create"]')) return;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-mode','create');
        newRow.innerHTML = `
          <td>—</td>
          <td><input class="table-input" name="fullname" placeholder="Nombre completo"></td>
          <td><input class="table-input" name="username" placeholder="Usuario"></td>
          <td><input class="table-input" name="email" placeholder="Correo"></td>
          <td>
            <select class="table-select" name="rol">
              <option value="user">user</option>
              <option value="admin">admin</option>
            </select>
          </td>
          <td class="table-actions">
            <button class="btn btn-xs row-save" type="button">Guardar</button>
            <button class="btn btn-xs btn-ghost row-cancel" type="button">Cancelar</button>
          </td>`;
        tb.prepend(newRow);
      });
    }

    if (bEdit){
      bEdit.addEventListener('click', ()=>{
        const tr = usersTable.querySelector('tbody tr.selected');
        if (!tr) { alert('Selecciona un usuario'); return; }
        if (tr.getAttribute('data-mode')==='edit') return;
        tr.setAttribute('data-mode','edit');
        const id = tr.dataset.id;
        const fullname = tr.dataset.fullname || '';
        const username = tr.dataset.username || '';
        const email    = tr.dataset.email || '';
        const rol      = tr.dataset.rol || 'user';
        tr.innerHTML = `
          <td>${id}</td>
          <td><input class="table-input" name="fullname" value="${fullname}"></td>
          <td><input class="table-input" name="username" value="${username}"></td>
          <td><input class="table-input" name="email" value="${email}"></td>
          <td>
            <select class="table-select" name="rol">
              <option value="user" ${rol==='user'?'selected':''}>user</option>
              <option value="admin" ${rol==='admin'?'selected':''}>admin</option>
            </select>
          </td>
          <td class="table-actions">
            <button class="btn btn-xs row-save" type="button">Guardar</button>
            <button class="btn btn-xs btn-ghost row-cancel" type="button">Cancelar</button>
          </td>`;
      });
    }

    if (bDel){
      bDel.addEventListener('click', async ()=>{
        const tr = usersTable.querySelector('tbody tr.selected');
        if (!tr) { alert('Selecciona un usuario'); return; }
        const id = tr.dataset.id;
        if (!confirm('¿Eliminar usuario #' + id + '?')) return;
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('admin/users_delete.php', { method:'POST', body: fd, credentials:'same-origin' });
        if (res.ok) loadUsers(currentQ); else alert('Error eliminando usuario');
      });
    }

    // Delegación en tabla para guardar/cancelar desde acciones en fila
    usersTable.addEventListener('click', async (e)=>{
      const btn = e.target.closest('button');
      if (!btn) return;
      const tr = e.target.closest('tr');
      if (!tr) return;
      if (btn.classList.contains('row-save')){
        const mode = tr.getAttribute('data-mode');
        const fullname = tr.querySelector('input[name="fullname"]').value.trim();
        const username = tr.querySelector('input[name="username"]').value.trim();
        const email    = tr.querySelector('input[name="email"]').value.trim();
        const rol      = tr.querySelector('select[name="rol"]').value;
        if (!fullname || !username || !email){ alert('Completa todos los campos'); return; }
        const fd = new FormData();
        fd.append('fullname', fullname); fd.append('username', username); fd.append('email', email); fd.append('rol', rol);
        let url = 'admin/users_create.php';
        if (mode==='edit') { url='admin/users_update.php'; fd.append('id', tr.dataset.id); }
        const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin' });
        if (res.ok) loadUsers(currentQ); else alert('Error guardando usuario');
      }
      if (btn.classList.contains('row-cancel')){
        loadUsers(currentQ);
      }
    });
  }

  // Gestionar productos
  const productsTable = document.getElementById('acc-products-table');
  if (productsTable){
    function loadProducts(q){
      const url = new URL('admin/products_list.php', window.location.href);
      if (q) url.searchParams.set('q', q);
      fetch(url.toString(), { credentials:'same-origin' })
        .then(r => r.json())
        .then(data => {
          const tb = productsTable.querySelector('tbody');
          if (!data || !Array.isArray(data.items)) { tb.innerHTML = '<tr><td colspan="5">No se pudo cargar</td></tr>'; return; }
          if (!data.items.length) { tb.innerHTML = '<tr><td colspan="5">Sin productos</td></tr>'; return; }
          tb.innerHTML = data.items.map(p => `
            <tr data-id="${p.id}" data-name="${p.name||''}" data-price="${p.price||0}" data-image="${p.image||''}" data-category="${p.category||''}">
              <td>${p.id}</td>
              <td>${p.name || ''}</td>
              <td>${p.price != null ? p.price : ''}</td>
              <td>${p.image || ''}</td>
              <td>${p.category || ''}</td>
              
            </tr>
          `).join('');
        })
        .catch(()=>{
          const tb = productsTable.querySelector('tbody');
          tb.innerHTML = '<tr><td colspan="5">Error al cargar</td></tr>';
        });
    }

    let pQ = '';
    loadProducts(pQ);

    // selección visual
    productsTable.addEventListener('click', (e)=>{
      const tr = e.target.closest('tbody tr');
      if(!tr) return;
      productsTable.querySelectorAll('tbody tr').forEach(r=>r.classList.remove('selected'));
      tr.classList.add('selected');
    });

    const pCreate = document.getElementById('p-btn-create');
    const pEdit   = document.getElementById('p-btn-edit');
    const pDel    = document.getElementById('p-btn-delete');
    const pRef    = document.getElementById('p-btn-refresh');
    const pSearchBtn = document.getElementById('p-btn-search');
    const pSearchInp = document.getElementById('p-search');

    if (pRef) pRef.addEventListener('click', ()=>{ pQ=''; if(pSearchInp) pSearchInp.value=''; loadProducts(pQ); });
    if (pSearchBtn) pSearchBtn.addEventListener('click', ()=>{ pQ=(pSearchInp?.value||'').trim(); loadProducts(pQ); });
    if (pSearchInp) pSearchInp.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); pQ=(pSearchInp.value||'').trim(); loadProducts(pQ); }});

    if (pCreate){
      pCreate.addEventListener('click', ()=>{
        const tb = productsTable.querySelector('tbody');
        if (tb.querySelector('tr[data-mode="create"]')) return;
        const row = document.createElement('tr');
        row.setAttribute('data-mode','create');
        row.innerHTML = `
          <td>—</td>
          <td><input class=\"table-input\" name=\"name\" placeholder=\"Nombre\"></td>
          <td><input class=\"table-input\" name=\"price\" placeholder=\"Precio (COP)\"></td>
          <td><input class=\"table-input\" name=\"image\" placeholder=\"Imagen URL\"></td>
          <td><input class=\"table-input\" name=\"category\" placeholder=\"Categoría\"></td>
          <td><div class=\\"table-actions\\" style=\\"gap:6px; display:flex; align-items:center;\\">
            <button class=\"btn btn-xs prow-save\" type=\"button\">Guardar</button>
            <button class=\"btn btn-xs btn-ghost prow-cancel\" type=\"button\">Cancelar</button>
          </div></td>`;
        tb.prepend(row);
      });
    }

    if (pEdit){
      pEdit.addEventListener('click', ()=>{
        const tr = productsTable.querySelector('tbody tr.selected');
        if (!tr) { alert('Selecciona un producto'); return; }
        if (tr.getAttribute('data-mode')==='edit') return;
        tr.setAttribute('data-mode','edit');
        const id = tr.dataset.id;
        const name = tr.dataset.name || '';
        const price = tr.dataset.price || '';
        const image = tr.dataset.image || '';
        const category = tr.dataset.category || '';
        tr.innerHTML = `
          <td>${id}</td>
          <td><input class=\"table-input\" name=\"name\" value=\"${name}\"></td>
          <td><input class=\"table-input\" name=\"price\" value=\"${price}\"></td>
          <td><input class=\"table-input\" name=\"image\" value=\"${image}\"></td>
          <td><input class=\"table-input\" name=\"category\" value=\"${category}\"></td>
          <td><div class=\\"table-actions\\" style=\\"gap:6px; display:flex; align-items:center;\\">
            <button class=\"btn btn-xs prow-save\" type=\"button\">Guardar</button>
            <button class=\"btn btn-xs btn-ghost prow-cancel\" type=\"button\">Cancelar</button>
          </div></td>`;
      });
    }

    if (pDel){
      pDel.addEventListener('click', async ()=>{
        const tr = productsTable.querySelector('tbody tr.selected');
        if (!tr) { alert('Selecciona un producto'); return; }
        const id = tr.dataset.id;
        if (!confirm('¿Eliminar producto #'+id+'?')) return;
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('admin/products_delete.php', { method:'POST', body: fd, credentials:'same-origin' });
        if (res.ok) loadProducts(pQ); else alert('Error eliminando producto');
      });
    }

    // Delegación en tabla de productos (guardar/cancelar)
    productsTable.addEventListener('click', async (e)=>{
      const btn = e.target.closest('button');
      if (!btn) return;
      const tr = e.target.closest('tr');
      if (!tr) return;
      if (btn.classList.contains('prow-save')){
        const mode = tr.getAttribute('data-mode');
        const name = tr.querySelector('input[name="name"]').value.trim();
        const price = parseFloat(tr.querySelector('input[name="price"]').value.trim() || '0');
        const image = tr.querySelector('input[name="image"]').value.trim();
        const category = tr.querySelector('input[name="category"]').value.trim();
        if (!name){ alert('Nombre es requerido'); return; }
        const fd = new FormData();
        fd.append('name', name); fd.append('price', isNaN(price)?0:price); fd.append('image', image); fd.append('category', category);
        let url = 'admin/products_create.php';
        if (mode==='edit') { url='admin/products_update.php'; fd.append('id', tr.dataset.id); }
        const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin' });
        if (res.ok) loadProducts(pQ); else alert('Error guardando producto');
      }
      if (btn.classList.contains('prow-cancel')){
        loadProducts(pQ);
      }
    });
  }
});


