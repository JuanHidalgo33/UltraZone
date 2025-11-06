document.addEventListener("DOMContentLoaded", () => {

    const grid = document.getElementById("products-grid");
    const searchForm = document.getElementById("search-form");
    const searchInput = document.getElementById("search-input");

    const wishlistCount = document.getElementById("wishlist-count");

    /* ---------------------------
       ✅ FAVORITOS (localStorage)
    ---------------------------- */
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
        } else {
            favs.push(id);
            heartBtn.classList.add("active");
            heartBtn.querySelector("i").className = "bi bi-heart-fill";
            heartBtn.classList.add("pop");
            setTimeout(() => heartBtn.classList.remove("pop"), 250);
        }

        saveFavs(favs);
        updateFavCounter();
    }

    /* ---------------------------
       ✅ CARRITO (localStorage)
    ---------------------------- */

    const cartCount = document.getElementById("cart-count");

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
    }

    /* ---------------------------
       ✅ CARGAR PRODUCTOS
    ---------------------------- */
    async function fetchProducts(query = "") {
        try {
            const res = await fetch(`forms/products.php?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            renderProducts(data.items || []);
        } catch (error) {
            console.error("Error cargando productos:", error);
        }
    }

    /* ---------------------------
       ✅ MOSTRAR PRODUCTOS
    ---------------------------- */
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

                <img src="${p.image}" alt="${p.name}">
                <h4>${p.name}</h4>
                <p>$${p.price}</p>

                <button class="main-item-button">Comprar</button>

                <!-- ✅ Se agrega id, nombre y precio -->
                <button class="main-item-button2 add-cart-btn"
                    data-id="${p.id}"
                    data-name="${p.name}"
                    data-price="${p.price}"
                    data-image="${p.image}">
                    Añadir al carrito
                </button>
            `;

            grid.appendChild(div);
        });
    }

    /* ---------------------------
       ✅ EVENTOS DE FAVORITOS
    ---------------------------- */
    grid.addEventListener("click", e => {
        const btn = e.target.closest(".favorite-btn");
        if (btn) {
            const id = parseInt(btn.dataset.id);
            toggleFav(id, btn);
            return;
        }

        /* ---------------------------
           ✅ EVENTO AÑADIR AL CARRITO
        ---------------------------- */
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

    /* ---------------------------
       ✅ BUSCADOR
    ---------------------------- */
    if (searchForm) {
        searchForm.addEventListener("submit", e => {
            e.preventDefault();
            const text = searchInput.value.trim();
            fetchProducts(text);
        });
    }

    /* ---------------------------
       ✅ INICIALIZACIÓN
    ---------------------------- */
    updateFavCounter();
    updateCartCounter();  // ✅ Ahora inicia con el número correcto
    fetchProducts();
});
