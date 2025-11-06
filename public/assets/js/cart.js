document.addEventListener('DOMContentLoaded', () => {
  function getCart(){ try{return JSON.parse(localStorage.getItem('cartItems')||'[]')}catch{return []} }
  function saveCart(arr){ localStorage.setItem('cartItems', JSON.stringify(arr)); }

  function format(n){
    if (window.ultraFormatPrice) return window.ultraFormatPrice(n);
    try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP'}).format(Number(n)||0); }
    catch{ return '$'+((Number(n)||0).toFixed(0)); }
  }

  const tbody = document.getElementById('cart-tbody');
  const emptyBox = document.getElementById('cart-empty');
  const contentBox = document.getElementById('cart-content');
  const totalEl = document.getElementById('cart-total');
  const countEl = document.getElementById('cart-items-count');
  const clearBtn = document.getElementById('cart-clear');
  const checkoutBtn = document.getElementById('cart-checkout');

  function render(){
    const items = getCart();
    if (!items.length){
      emptyBox.style.display='block';
      contentBox.style.display='none';
      return;
    }
    emptyBox.style.display='none';
    contentBox.style.display='grid';

    tbody.innerHTML = items.map(it => `
      <tr data-id="${it.id}">
        <td>
          <div class="cart-item">
            <img src="${resolveAsset(it.image || 'assets/img/BLACK FRONT.png')}" alt="${it.name}">
            <div>
              <div>${it.name}</div>
            </div>
          </div>
        </td>
        <td>${format(it.price)}</td>
        <td>
          <div class="qty">
            <button class="q-minus" type="button">-</button>
            <span class="q-val">${it.qty}</span>
            <button class="q-plus" type="button">+</button>
          </div>
        </td>
        <td class="row-sub">${format(it.price * it.qty)}</td>
        <td><button class="remove-btn" title="Eliminar" type="button"><i class="bi bi-trash"></i></button></td>
      </tr>
    `).join('');

    const total = items.reduce((s,i)=> s + i.price*i.qty, 0);
    const count = items.reduce((s,i)=> s + i.qty, 0);
    totalEl.textContent = format(total);
    countEl.textContent = count;
  }

  function resolveAsset(url){
    if (!url) return url;
    if (/^https?:/i.test(url) || url.startsWith('/')) return url;
    return url; // cart.html está en root public
  }

  // Delegación de eventos de cantidad y eliminar
  tbody.addEventListener('click', (e)=>{
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = parseInt(tr.getAttribute('data-id'));
    let cart = getCart();
    const item = cart.find(p=> p.id===id);
    if (!item) return;

    if (e.target.closest('.q-plus')){
      item.qty += 1;
    } else if (e.target.closest('.q-minus')){
      item.qty -= 1;
      if (item.qty < 1){ cart = cart.filter(p=> p.id!==id); }
    } else if (e.target.closest('.remove-btn')){
      cart = cart.filter(p=> p.id!==id);
    } else {
      return;
    }
    saveCart(cart);
    render();
  });

  if (clearBtn){
    clearBtn.addEventListener('click', ()=>{
      if (!confirm('¿Vaciar carrito?')) return;
      saveCart([]); render();
    });
  }

  if (checkoutBtn){
    checkoutBtn.addEventListener('click', async ()=>{
      // Validación de sesión antes de checkout
      try {
        const sess = await fetch('php/session-status.php', { credentials: 'same-origin' });
        const sdata = await sess.json().catch(()=>({logged:false}));
        if (!sdata || !sdata.logged){
          if (typeof showToast === 'function') showToast('Debes iniciar sesión o registrarte.');
          else alert('Debes iniciar sesión o registrarte.');
          return;
        }
      } catch (_) {}
      const items = getCart();
      if (!items.length) return;
      const payload = {
        currency: (window.ultraSettings ? window.ultraSettings().currency : 'COP'),
        items: items.map(i=>({ id:i.id, name:i.name, price:i.price, qty:i.qty }))
      };
      try {
        const res = await fetch('php/pay/checkout.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          credentials: 'same-origin'
        });
        const data = await res.json();
        if (res.ok && data && data.url){
          window.location.href = data.url;
        } else {
          alert('No se pudo iniciar el pago');
        }
      } catch (e){
        alert('Error de conexión con la pasarela');
      }
    });
  }

  render();
});
