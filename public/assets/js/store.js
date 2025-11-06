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
            heartBtn.classList.add("pop");   // animación suave
            setTimeout(() => heartBtn.classList.remove("pop"), 250);
        }

        saveFavs(favs);
        updateFavCounter();
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
                <button class="main-item-button2">Añadir al carrito</button>
            `;

            grid.appendChild(div);
        });
    }

    /* ---------------------------
       ✅ EVENTOS DE FAVORITOS
    ---------------------------- */
    grid.addEventListener("click", e => {
        const btn = e.target.closest(".favorite-btn");
        if (!btn) return;

        const id = parseInt(btn.dataset.id);
        toggleFav(id, btn);
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
    fetchProducts();
});
