document.addEventListener('DOMContentLoaded', function(){
  const grid = document.getElementById('products-grid');
  const form = document.getElementById('search-form');
  const searchInput = document.getElementById('search-input');
  const langSelect = document.getElementById('lang-select');
  const currencySelect = document.getElementById('currency-select');

  const rates = { COP: 1, USD: 0.00026, EUR: 0.00024 };
  const symbols = { COP: '$', USD: '$', EUR: '€' };

  let state = {
    lang: localStorage.getItem('lang') || 'ES',
    currency: localStorage.getItem('currency') || 'COP',
    q: ''
  };

  function t(key){
    const dict = {
      ES: { buy: 'Comprar', add: 'Añadir al carrito', search: 'Buscar productos...' },
      EN: { buy: 'Buy', add: 'Add to cart', search: 'Search for products' }
    };
    return (dict[state.lang] || dict.ES)[key] || key;
  }

  function formatPrice(cop){
    const rate = rates[state.currency] || 1;
    const val = cop * rate;
    let tail = state.currency === 'COP' ? ' COP' : '';
    return `${symbols[state.currency] || ''} ${val.toLocaleString(undefined, { maximumFractionDigits: 2 })}${tail}`;
  }

  async function fetchProducts(){
    const params = new URLSearchParams();
    params.set('category', 'T-Shirts');
    if (state.q) params.set('q', state.q);
    const res = await fetch(`forms/products.php?${params.toString()}`);
    const data = await res.json();
    renderProducts(data.items || []);
  }

  function renderProducts(items){
    if (!grid) return;
    grid.innerHTML = '';
    if (!items.length){
      const empty = document.createElement('p');
      empty.style.color = '#fff';
      empty.textContent = state.lang === 'EN' ? 'No products found' : 'No se encontraron productos';
      grid.appendChild(empty);
      return;
    }
    items.forEach(p => {
      const card = document.createElement('div');
      card.className = 'main-grid-item';
      card.innerHTML = `
        <img src="${p.image}" alt="${p.name}">
        <h4>${p.name}</h4>
        <p>${formatPrice(p.price)}</p>
        <button class="main-item-button">${t('buy')}</button>
        <button class="main-item-button2">${t('add')}</button>
      `;
      grid.appendChild(card);
    });
  }

  // Search
  if (form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      state.q = (searchInput?.value || '').trim();
      fetchProducts();
    });
  }

  // Language select
  function applyLang(){
    if (langSelect) langSelect.value = state.lang;
    if (searchInput) searchInput.placeholder = t('search');
  }
  if (langSelect){
    langSelect.value = state.lang;
    langSelect.addEventListener('change', function(){
      state.lang = langSelect.value;
      localStorage.setItem('lang', state.lang);
      applyLang();
      fetchProducts();
    });
  }

  // Currency select
  function applyCurrency(){
    if (currencySelect) currencySelect.value = state.currency;
    fetchProducts();
  }
  if (currencySelect){
    currencySelect.value = state.currency;
    currencySelect.addEventListener('change', function(){
      state.currency = currencySelect.value;
      localStorage.setItem('currency', state.currency);
      applyCurrency();
    });
  }

  // Init
  applyLang();
  if (currencySelect) currencySelect.value = state.currency;
  fetchProducts();
});

