document.addEventListener("DOMContentLoaded", () => {

    const grid = document.getElementById("products-grid");
    const searchForm = document.getElementById("search-form");
    const searchInput = document.getElementById("search-input");

    const wishlistCount = document.getElementById("wishlist-count");
    const wishlistMenu = document.getElementById("wishlist-menu");
    const wishlistContainer = document.querySelector(".dropdown-wishlist");
    const wishlistToggle = document.querySelector(".dropdown-wishlist .whishlist");
    // Toasts para feedback de usuario
    const toastContainer = (() => {
        const el = document.createElement("div");
        el.className = "toast-container";
        document.body.appendChild(el);
        return el;
    })();

    function showToast(text) {
        if (!toastContainer) return;
        const div = document.createElement("div");
        div.className = "toast-message";
        div.textContent = text;
        toastContainer.appendChild(div);
        requestAnimationFrame(() => div.classList.add("show"));
        setTimeout(() => {
            div.classList.remove("show");
            div.classList.add("hide");
            div.addEventListener("transitionend", () => div.remove(), { once: true });
        }, 2000);
    }

    // Preferencias (idioma y moneda)
    const langSelect = document.getElementById('lang-select');
    const currencySelect = document.getElementById('currency-select');

    const CURRENCY_RATES = {
        COP: 1,
        USD: 0.00025,
        EUR: 0.00023
    };

    function getSettings() {
        return {
            lang: localStorage.getItem('ultra_lang') || 'ES',
            currency: localStorage.getItem('ultra_currency') || 'COP'
        };
    }

    function setSettings(partial) {
        const cur = getSettings();
        const next = { ...cur, ...partial };
        localStorage.setItem('ultra_lang', next.lang);
        localStorage.setItem('ultra_currency', next.currency);
        return next;
    }

function applySettingsToUI() {
        const s = getSettings();
        if (langSelect) langSelect.value = s.lang;
        if (currencySelect) currencySelect.value = s.currency;
        const ph = s.lang === 'EN' ? 'Search for products' : 'Buscar productos';
        if (searchInput) searchInput.placeholder = ph;
}

// Traducciones simples para UI
function t(key) {
    const lang = getSettings().lang;
    const dict = {
        buy: { ES: 'Comprar', EN: 'Buy' },
            add_to_cart: { ES: 'Añadir al carrito', EN: 'Add to cart' },
        remove: { ES: 'Quitar', EN: 'Remove' },
        pay: { ES: 'Pagar', EN: 'Checkout' },
        go_to_cart: { ES: 'Ir al carrito de compras', EN: 'Go to cart' },
        empty_cart: { ES: 'Tu carrito está vacío', EN: 'Your cart is empty' },
        empty_wishlist: { ES: 'No tienes favoritos', EN: 'No favorites yet' },
        total: { ES: 'Total', EN: 'Total' }
    };
    return (dict[key] && dict[key][lang]) || (dict[key] && dict[key].ES) || key;
}

// Exponer helper de traducciones
window.ultraT = t;

    function formatPrice(value) {
        const s = getSettings();
        const rate = CURRENCY_RATES[s.currency] || 1;
        const converted = (Number(value) || 0) * rate;
        try {
            const fmt = new Intl.NumberFormat(s.lang === 'EN' ? 'en-US' : 'es-CO', {
                style: 'currency',
                currency: s.currency
            });
            return fmt.format(converted);
        } catch {
            const symbol = s.currency === 'USD' ? '$' : (s.currency === 'EUR' ? '€' : '$');
            return symbol + converted.toFixed(2);
        }
    }

    // Exponer helpers globales para páginas de categoría
    window.ultraFormatPrice = formatPrice;
    window.ultraApplySettings = applySettingsToUI;
    window.ultraSettings = getSettings;

    // Listeners de selects
    if (langSelect) {
        langSelect.addEventListener('change', () => {
            setSettings({ lang: langSelect.value });
            applySettingsToUI();
            // Re-render para aplicar textos
            if (typeof window.ultraRefetchCategory === 'function') {
                window.ultraRefetchCategory();
            } else {
                fetchProducts(searchInput ? searchInput.value.trim() : '');
            }
            renderWishlistDropdown();
            renderCartDropdown();
        });
    }
    if (currencySelect) {
        currencySelect.addEventListener('change', () => {
            setSettings({ currency: currencySelect.value });
            // Re-render dinámico
            if (typeof window.ultraRefetchCategory === 'function') {
                window.ultraRefetchCategory();
            } else {
                fetchProducts(searchInput ? searchInput.value.trim() : '');
            }
            // Actualizar dropdowns
            renderWishlistDropdown();
            renderCartDropdown();
        });
    }

    // Sincronizar selects al cargar
    applySettingsToUI();

    // Cache de detalles de favoritos (id -> {id,name,price,image})
    function getFavDetails() {
        return JSON.parse(localStorage.getItem("favDetails") || "{}");
    }

    function saveFavDetails(obj) {
        localStorage.setItem("favDetails", JSON.stringify(obj));
    }

    function getFavs() {
        return JSON.parse(localStorage.getItem("favs") || "[]");
    }

    function saveFavs(arr) {
        localStorage.setItem("favs", JSON.stringify(arr));
    }

    function isFav(id) {
        return getFavs().includes(id);
    }

    function updateFavCounter() {
        if (wishlistCount) {
            wishlistCount.textContent = getFavs().length;
        }
    }

    function toggleFav(id, heartBtn) {
        let favs = getFavs();

        if (favs.includes(id)) {
            favs = favs.filter(x => x !== id);
            heartBtn.classList.remove("active");
            heartBtn.querySelector("i").className = "bi bi-heart";

            // limpiar detalle guardado
            const details = getFavDetails();
            if (details[id]) {
                delete details[id];
                saveFavDetails(details);
            }
            showToast("Eliminado de favoritos");
        } else {
            favs.push(id);
            heartBtn.classList.add("active");
            heartBtn.querySelector("i").className = "bi bi-heart-fill";
            heartBtn.classList.add("pop");
            setTimeout(() => heartBtn.classList.remove("pop"), 250);
            showToast("Agregado a favoritos");

            // Capturar detalles visibles en la card para el dropdown
            const card = heartBtn.closest(".main-grid-item");
            if (card) {
                const name = card.querySelector("h4")?.textContent?.trim() || "";
                const abs = card.querySelector("img")?.src || "";
                const originPrefix = location.origin + "/";
                const img = abs.startsWith(originPrefix) ? abs.slice(originPrefix.length) : abs;
                const priceText = card.querySelector("p")?.textContent || "0";
                const price = parseFloat(priceText.replace(/[^0-9.]/g, "")) || 0;
                const details = getFavDetails();
                details[id] = { id, name, price, image: img };
                saveFavDetails(details);
            }
        }

        saveFavs(favs);
        updateFavCounter();

        // Si el dropdown está abierto, volver a renderizarlo
        if (wishlistContainer && wishlistContainer.classList.contains("open")) {
            renderWishlistDropdown();
        }
    }

    const cartCount = document.getElementById("cart-count");
    const cartMenu = document.getElementById("cart-menu");
    const cartContainer = document.querySelector(".dropdown-cartbox");
    const cartToggle = document.querySelector(".dropdown-cartbox .dropdown-cart");

    function getCart() {
        return JSON.parse(localStorage.getItem("cartItems") || "[]");
    }

    function saveCart(arr) {
        localStorage.setItem("cartItems", JSON.stringify(arr));
    }

    function updateCartCounter() {
        if (!cartCount) return;
        const items = getCart();
        const total = items.reduce((sum, item) => sum + item.qty, 0);
        cartCount.textContent = total;
    }

    function addToCart(product) {
        let cart = getCart();
        const found = cart.find(p => p.id === product.id);

        if (found) {
            found.qty += 1;
        } else {
            cart.push({ ...product, qty: 1 });
        }

        saveCart(cart);
        updateCartCounter();
        showToast("Producto agregado al carrito");
        if (cartContainer && cartContainer.classList.contains("open")) {
            renderCartDropdown();
        }
    }

    // Render del dropdown del carrito
    function renderCartDropdown() {
        if (!cartMenu) return;
        const items = getCart();
        if (!items.length) {
            cartMenu.innerHTML = '<div class="wishlist-empty">Tu carrito está vacío</div>';
            return;
        }

        const listHtml = items.map(it => `
            <div class="cart-item" data-id="${it.id}">
                <img class="cart-item-img" src="${resolveAsset(it.image || 'assets/img/BLACK FRONT.png')}" alt="${it.name}">
                <div class="cart-item-info">
                    <div class="cart-item-name">${it.name}</div>
                    <div class="cart-item-price">${formatPrice(it.price)}</div>
                </div>
                <div class="cart-qty-controls">
                    <button class="cart-qty-btn cart-qty-minus" data-id="${it.id}">-</button>
                    <span class="cart-qty-value">${it.qty}</span>
                    <button class="cart-qty-btn cart-qty-plus" data-id="${it.id}">+</button>
                </div>
            </div>
        `).join("");

        const total = items.reduce((sum, it) => sum + (it.price * it.qty), 0);

        const footer = `
            <div class="cart-menu-footer">
                <div class="cart-total">
                    <span>${t('total')}</span>
                    <span class="cart-total-value">${formatPrice(total)}</span>
                </div>
                <button class="cart-pay-btn">${t('pay')}</button>
                <button class="cart-goto-btn">${t('go_to_cart')}</button>
            </div>
        `;

        cartMenu.innerHTML = listHtml + footer;
    }

    // Render del dropdown de favoritos
    function renderWishlistDropdown() {
        if (!wishlistMenu) return;
        const ids = getFavs();
        const details = getFavDetails();

        if (!ids.length) {
            wishlistMenu.innerHTML = `<div class="wishlist-empty">${t('empty_wishlist')}</div>`;
            return;
        }

        const html = ids.map(id => {
            const d = details[id] || {};
            const img = resolveAsset(d.image || "assets/img/BLACK FRONT.png");
            const name = (d.name || `Producto ${id}`);
            const price = (typeof d.price === 'number' ? d.price : 0);
            return `
                <div class="wishlist-item" data-id="${id}">
                    <img class="wishlist-item-img" src="${img}" alt="${name}">
                    <div class="wishlist-item-info">
                        <div class="wishlist-item-name">${name}</div>
                        <div class="wishlist-item-price">${formatPrice(price)}</div>
                    </div>
                    <div class="wishlist-item-actions">
                        <button class="wishlist-remove-btn" data-id="${id}">${t('remove')}</button>
                        <button class="wishlist-addcart-btn" data-id="${id}">${t('add_to_cart')}</button>
                    </div>
                </div>
            `;
        }).join("");

        wishlistMenu.innerHTML = html;
    }

    function getPhpBase() {
        return window.location.pathname.includes('/categorias/') ? '../' : '';
    }

    function resolveAsset(url) {
        if (!url) return url;
        if (/^https?:/i.test(url) || url.startsWith('/')) return url;
        const isCategory = window.location.pathname.includes('/categorias/');
        const prefix = isCategory ? '../' : '';
        return `${prefix}${url}`;
    }

    async function fetchProducts(query = "") {
        try {
            const base = getPhpBase();
            const res = await fetch(`${base}php/forms/products.php?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            renderProducts(data.items || []);
        } catch (error) {
            console.error("Error cargando productos:", error);
        }
    }

    function renderProducts(items) {
        grid.innerHTML = "";

        if (!items.length) {
            grid.innerHTML = `<p>No se encontraron productos</p>`;
            return;
        }

        items.forEach(p => {
            const div = document.createElement("div");
            div.classList.add("main-grid-item");

            const favActive = isFav(p.id) ? "active" : "";
            const icon = isFav(p.id) ? "bi-heart-fill" : "bi-heart";

            div.innerHTML = `
                <button class="favorite-btn ${favActive}" data-id="${p.id}">
                    <i class="bi ${icon}"></i>
                </button>

                <img src="${resolveAsset(p.image)}" alt="${p.name}">
                <h4>${p.name}</h4>
                <p>${formatPrice(p.price)}</p>

                <button class="main-item-button">${t('buy')}</button>

                <!-- ✅ Se agrega id, nombre y precio -->
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

    grid.addEventListener("click", e => {
        const btn = e.target.closest(".favorite-btn");
        if (btn) {
            const id = parseInt(btn.dataset.id);
            toggleFav(id, btn);
            return;
        }

        const cartBtn = e.target.closest(".add-cart-btn");
        if (cartBtn) {
            const product = {
                id: parseInt(cartBtn.dataset.id),
                name: cartBtn.dataset.name,
                price: parseFloat(cartBtn.dataset.price),
                image: cartBtn.dataset.image
            };

            addToCart(product);
        }
    });

    // Toggle del dropdown de favoritos
    if (wishlistToggle && wishlistContainer) {
        wishlistToggle.addEventListener("click", (e) => {
            e.preventDefault();
            const isOpen = wishlistContainer.classList.contains("open");
            if (isOpen) {
                wishlistContainer.classList.remove("open");
            } else {
                renderWishlistDropdown();
                wishlistContainer.classList.add("open");
            }
        });

        // Cerrar al hacer click fuera
        document.addEventListener("click", (e) => {
            if (!wishlistContainer.classList.contains("open")) return;
            if (!wishlistContainer.contains(e.target)) {
                wishlistContainer.classList.remove("open");
            }
        });

        // Delegación de eventos dentro del menú
        if (wishlistMenu) {
            wishlistMenu.addEventListener("click", (e) => {
                const removeBtn = e.target.closest(".wishlist-remove-btn");
                if (removeBtn) {
                    const id = parseInt(removeBtn.dataset.id);
                    // quitar de favs
                    let favs = getFavs().filter(x => x !== id);
                    saveFavs(favs);
                    updateFavCounter();
                    // limpiar detalle
                    const details = getFavDetails();
                    if (details[id]) { delete details[id]; saveFavDetails(details); }
                    // actualizar UI
                    renderWishlistDropdown();
                    // Si la card está visible, actualizar ícono
                    const favBtnInGrid = document.querySelector(`.favorite-btn[data-id="${id}"]`);
                    if (favBtnInGrid) {
                        favBtnInGrid.classList.remove("active");
                        favBtnInGrid.querySelector("i").className = "bi bi-heart";
                    }
                    showToast("Eliminado de favoritos");
                    return;
                }

                const addBtn = e.target.closest(".wishlist-addcart-btn");
                if (addBtn) {
                    const id = parseInt(addBtn.dataset.id);
                    const details = getFavDetails();
                    const prod = details[id];
                    if (prod) {
                        addToCart({ id: prod.id, name: prod.name, price: prod.price, image: prod.image });
                    }
                }
            });
        }
    }

    // Toggle del dropdown del carrito
    if (cartToggle && cartContainer) {
        cartToggle.addEventListener("click", (e) => {
            e.preventDefault();
            const isOpen = cartContainer.classList.contains("open");
            if (isOpen) {
                cartContainer.classList.remove("open");
            } else {
                renderCartDropdown();
                cartContainer.classList.add("open");
            }
        });

        // Cerrar al hacer click fuera (sin interferir con wishlist)
        document.addEventListener("click", (e) => {
            if (!cartContainer.classList.contains("open")) return;
            if (!cartContainer.contains(e.target)) {
                cartContainer.classList.remove("open");
            }
        });

        // Delegación de eventos para +/- dentro del menú
        if (cartMenu) {
            cartMenu.addEventListener("click", (e) => {
                // Evita que el click burbujee al document y cierre el dropdown
                e.stopPropagation();
                const plus = e.target.closest(".cart-qty-plus");
                const minus = e.target.closest(".cart-qty-minus");
                if (!plus && !minus) return;

                const id = parseInt((plus || minus).dataset.id);
                let cart = getCart();
                const found = cart.find(p => p.id === id);
                if (!found) return;

                if (plus) {
                    found.qty += 1;
                } else if (minus) {
                    found.qty -= 1;
                    if (found.qty < 1) {
                        cart = cart.filter(p => p.id !== id);
                        showToast("Producto eliminado del carrito");
                    }
                }

                saveCart(cart);
                updateCartCounter();
                renderCartDropdown();
            });
        }
    }

    if (searchForm) {
        searchForm.addEventListener("submit", e => {
            e.preventDefault();
            const text = searchInput.value.trim();
            fetchProducts(text);
        });
    }

    // Mostrar/ocultar opciones de cuenta según sesión
    async function updateAccountMenuVisibility() {
        try {
            const base = getPhpBase();
            const res = await fetch(`${base}php/session-status.php`, { credentials: 'same-origin' });
            const data = await res.json();
            const logged = !!(data && data.logged);

            const allMenus = document.querySelectorAll('.dropdown-account-menu');
            allMenus.forEach(menu => {
                const links = menu.querySelectorAll('a');
                links.forEach(a => {
                    const href = a.href || '';
                    const li = a.closest('li') || a;
                    // Ocultar "Cerrar sesion" si NO hay sesión
                    if (href.endsWith('/php/logout.php')) {
                        li.style.display = logged ? '' : 'none';
                    }
                    // Ocultar "Registrarse" si SÍ hay sesión
                    if (href.endsWith('/php/forms/register.php')) {
                        li.style.display = logged ? 'none' : '';
                    }
                    // Ocultar "Iniciar sesion" si SÍ hay sesión
                    if (href.endsWith('/php/forms/login.php')) {
                        li.style.display = logged ? 'none' : '';
                    }
                });
            });
        } catch (e) {
            // Si falla, no romper UI
        }
    }

    updateAccountMenuVisibility();

    // Interceptar click en "Mi cuenta" para mostrar aviso previo si no hay sesión
    try {
        const accountLinks = document.querySelectorAll('.dropdown-account-menu a[href$="MyAccount.php"]');
        accountLinks.forEach(a => {
            a.addEventListener('click', async (ev) => {
                // Evitar navegación inmediata
                ev.preventDefault();
                const base = getPhpBase();
                try {
                    const res = await fetch(`${base}php/session-status.php`, { credentials: 'same-origin' });
                    const data = await res.json();
                    if (data && data.logged) {
                        window.location.href = `${base}php/MyAccount.php`;
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('Debes iniciar sesión. Si no tienes cuenta, regístrate.');
                        }
                        setTimeout(() => {
                            window.location.href = `${base}php/forms/login.php?info=login_required`;
                        }, 1500);
                    }
                } catch (e) {
                    // En caso de error, degradar a redirección normal
                    window.location.href = `${base}php/forms/login.php?info=login_required`;
                }
            });
        });
    } catch {}

    updateFavCounter();
    updateCartCounter();  
    fetchProducts();
});

