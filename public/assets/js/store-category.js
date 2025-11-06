document.addEventListener("DOMContentLoaded", () => {
  const grid = document.getElementById("products-grid");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");
  const category = (document.body.dataset.category || "").trim();

  function getFavs() {
    try { return JSON.parse(localStorage.getItem("favs") || "[]"); } catch { return []; }
  }

  function isFav(id) { return getFavs().includes(id); }

  function render(items) {
    if (!grid) return;
    grid.innerHTML = "";
    if (!items || !items.length) {
      grid.innerHTML = `<p>No se encontraron productos</p>`;
      return;
    }

    items.forEach(p => {
      const div = document.createElement("div");
      div.classList.add("main-grid-item");

      const favActive = isFav(p.id) ? "active" : "";
      const icon = isFav(p.id) ? "bi-heart-fill" : "bi-heart";

      const t = (window.ultraT ? window.ultraT : (k => ({ buy:'Comprar', add_to_cart:'Añadir al carrito' }[k] || k)));
      div.innerHTML = `
        <button class="favorite-btn ${favActive}" data-id="${p.id}">
            <i class="bi ${icon}"></i>
        </button>

        <img src="${(window.location.pathname.includes('/categorias/') ? '../' : '') + p.image}" alt="${p.name}">
        <h4>${p.name}</h4>
        <p>${(window.ultraFormatPrice ? window.ultraFormatPrice(p.price) : ('$' + p.price))}</p>

        <button class="main-item-button">${t('buy')}</button>

        <button class="main-item-button2 add-cart-btn"
            data-id="${p.id}"
            data-name="${p.name}"
            data-price="${p.price}"
            data-image="${p.image}">
            ${t('add_to_cart')}
        </button>
      `;

      grid.appendChild(div);
    });
  }

  function getPhpBase() {
    return window.location.pathname.includes('/categorias/') ? '../' : '';
  }

  async function fetchCategory(query = "") {
    try {
      const params = new URLSearchParams();
      if (category) params.set("category", category);
      if (query) params.set("q", query);
      const base = getPhpBase();
      const res = await fetch(`${base}php/forms/products.php?${params.toString()}`);
      const data = await res.json();
      render(data.items || []);
    } catch (e) {
      console.error("Error cargando productos por categoría:", e);
    }
  }

  // Anular la búsqueda de store.js para mantener el filtro por categoría
  if (searchForm) {
    searchForm.addEventListener("submit", (e) => {
      // Captura y evita que otros listeners manejen el submit
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation?.();
      const text = (searchInput?.value || "").trim();
      fetchCategory(text);
    }, true);
  }

  // Cargar categoría al iniciar (puede sobrescribir el render inicial de store.js)
  fetchCategory("");

  // Exponer refetch para que store.js pueda re-renderizar al cambiar moneda
  window.ultraRefetchCategory = function() {
    const q = (searchInput?.value || "").trim();
    fetchCategory(q);
  };
});
