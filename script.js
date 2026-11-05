// ========================================
// YAMAHA GUITAR STORE
// FRONTEND + PHP MYSQL BACKEND
// ========================================

const $ = id => document.getElementById(id);

const products = window.APP.products;
let currentUser = window.APP.user;
let cartItems = [];
let cartSubtotal = 0;
let busy = false;

const money = value => new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0
}).format(value);

function escapeHTML(value) {
    return String(value).replace(/[&<>"']/g, char => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
    })[char]);
}

// NOTIFIKASI
let toastTimer;

function notify(message) {
    const toast = $("toastMessage");
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 3000);
}

// API
async function api(action, data = {}) {
    const body = new URLSearchParams();

    body.set("action", action);
    body.set("csrf", window.APP.csrf);

    Object.entries(data).forEach(([key, value]) => {
        body.set(key, String(value));
    });

    const response = await fetch("api.php", {
        method: "POST",
        body,
        credentials: "same-origin"
    });

    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error("Server tidak mengembalikan JSON. Periksa PHP dan MySQL.");
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Permintaan gagal.");
    }

    return result;
}

async function runAction(callback) {
    if (busy) return;
    busy = true;

    try {
        await callback();
    } catch (error) {
        notify(error.message);
    } finally {
        busy = false;
    }
}

// ========================================
// TEMA
// ========================================

const themes = ["light", "dark", "retro"];

function applyTheme(theme) {
    if (!themes.includes(theme)) theme = "light";

    document.body.dataset.theme = theme;
    const themeSelect = $("themeSelect");
    if (themeSelect) {
        themeSelect.value = theme;
    }

    localStorage.setItem("yamaha-theme", theme);
}

const savedTheme = localStorage.getItem("yamaha-theme") || "light";
applyTheme(savedTheme);

const themeSelectEl = $("themeSelect");
if (themeSelectEl) {
    themeSelectEl.addEventListener("change", event => {
        applyTheme(event.target.value);
    });
}

// ========================================
// MODAL & AUTENTIKASI
// ========================================

const authModalEl = $("authModal");
const accountModalEl = $("accountModal");
const checkoutModalEl = $("checkoutModal");
const paymentModalEl = $("paymentModal");

const authModal = authModalEl ? bootstrap.Modal.getOrCreateInstance(authModalEl) : null;
const accountModal = accountModalEl ? bootstrap.Modal.getOrCreateInstance(accountModalEl) : null;
const checkoutModal = checkoutModalEl ? bootstrap.Modal.getOrCreateInstance(checkoutModalEl) : null;
const paymentModal = paymentModalEl ? bootstrap.Modal.getOrCreateInstance(paymentModalEl) : null;

function showLogin() {
    if ($("loginForm")) $("loginForm").classList.remove("d-none");
    if ($("registerForm")) $("registerForm").classList.add("d-none");
    if ($("authTitle")) $("authTitle").textContent = "Login Akun";
    if (authModal) authModal.show();
}

function showRegister() {
    if ($("loginForm")) $("loginForm").classList.add("d-none");
    if ($("registerForm")) $("registerForm").classList.remove("d-none");
    if ($("authTitle")) $("authTitle").textContent = "Daftar Akun";
    if (authModal) authModal.show();
}

if ($("showRegister")) $("showRegister").addEventListener("click", showRegister);
if ($("showLogin")) $("showLogin").addEventListener("click", showLogin);

if ($("accountBtn")) {
    $("accountBtn").addEventListener("click", () => {
        if (!currentUser) {
            showLogin();
            return;
        }

        if ($("profileName")) $("profileName").textContent = currentUser.name;
        if ($("profileEmail")) $("profileEmail").textContent = currentUser.email;

        if (accountModal) accountModal.show();
        loadOrders();
    });
}

// REGISTER SUBMIT
if ($("registerForm")) {
    $("registerForm").addEventListener("submit", event => {
        event.preventDefault();

        runAction(async () => {
            const form = event.currentTarget;
            const data = Object.fromEntries(new FormData(form));

            if (data.password !== data.confirm) {
                throw new Error("Konfirmasi password tidak sama.");
            }

            const result = await api("register", {
                name: data.name,
                email: data.email,
                password: data.password
            });

            currentUser = result.user;

            form.reset();
            if (authModal) authModal.hide();

            await loadCart();
            notify("Akun berhasil dibuat. Selamat datang!");
        });
    });
}

// LOGIN SUBMIT
if ($("loginForm")) {
    $("loginForm").addEventListener("submit", event => {
        event.preventDefault();

        runAction(async () => {
            const form = event.currentTarget;
            const data = Object.fromEntries(new FormData(form));

            const result = await api("login", {
                email: data.email,
                password: data.password
            });

            currentUser = result.user;

            form.reset();
            if (authModal) authModal.hide();

            await loadCart();
            notify(`Selamat datang, ${currentUser.name}!`);
        });
    });
}

// ========================================
// PRODUK
// ========================================

function productHTML(product) {
    return `
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <article class="product-card">
                <div class="product-photo">
                    <img
                        src="${product.image}"
                        alt="Ilustrasi ${escapeHTML(product.name)}"
                        loading="lazy"
                    >
                    <span class="product-badge">YAMAHA COLLECTION</span>
                </div>

                <div class="product-body">
                    <span class="product-category">
                        ${escapeHTML(product.category)}
                    </span>

                    <h5>${escapeHTML(product.name)}</h5>
                    <p class="product-description">${escapeHTML(product.description)}</p>

                    <div class="product-price">
                        ${money(product.price)}
                    </div>

                    <button class="add-cart-btn" data-add="${product.id}">
                        <i class="bi bi-bag-plus"></i>
                        Tambah ke Keranjang
                    </button>
                </div>
            </article>
        </div>
    `;
}

function renderProducts() {
    const searchInput = $("searchInput");
    const categoryFilter = $("categoryFilter");
    const sortFilter = $("sortFilter");
    const resultCount = $("resultCount");
    const productGrid = $("productGrid");

    if (!productGrid) return;

    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const category = categoryFilter ? categoryFilter.value : "all";
    const sort = sortFilter ? sortFilter.value : "default";

    let filtered = products.filter(product => {
        const text = (
            product.name + " " +
            product.category + " " +
            product.description
        ).toLowerCase();

        return text.includes(keyword) &&
            (category === "all" || product.category === category);
    });

    if (sort === "low") filtered.sort((a, b) => a.price - b.price);
    if (sort === "high") filtered.sort((a, b) => b.price - a.price);
    if (sort === "name") filtered.sort((a, b) => a.name.localeCompare(b.name));

    if (resultCount) {
        resultCount.textContent = `${filtered.length} produk ditemukan`;
    }

    productGrid.innerHTML = filtered.length
        ? filtered.map(productHTML).join("")
        : `<div class="col-12 text-center py-5">
               <h4>Produk tidak ditemukan</h4>
               <p>Coba kata kunci lainnya.</p>
           </div>`;
}

if ($("searchInput")) $("searchInput").addEventListener("input", renderProducts);
if ($("categoryFilter")) $("categoryFilter").addEventListener("change", renderProducts);
if ($("sortFilter")) $("sortFilter").addEventListener("change", renderProducts);

if ($("resetFilter")) {
    $("resetFilter").addEventListener("click", () => {
        if ($("searchInput")) $("searchInput").value = "";
        if ($("categoryFilter")) $("categoryFilter").value = "all";
        if ($("sortFilter")) $("sortFilter").value = "default";
        renderProducts();
    });
}

document.querySelectorAll("[data-category]").forEach(button => {
    button.addEventListener("click", () => {
        if ($("categoryFilter")) $("categoryFilter").value = button.dataset.category;
        if ($("searchInput")) $("searchInput").value = "";
        renderProducts();
        const produkSec = $("produk");
        if (produkSec) produkSec.scrollIntoView({ behavior: "smooth" });
    });
});

if ($("productGrid")) {
    $("productGrid").addEventListener("click", event => {
        const button = event.target.closest("[data-add]");
        if (!button) return;

        if (!currentUser) {
            notify("Login terlebih dahulu untuk berbelanja.");
            showLogin();
            return;
        }

        runAction(async () => {
            await api("add", { product_id: button.dataset.add });
            await loadCart();
            notify("Produk berhasil ditambahkan ke keranjang.");
        });
    });
}

// ========================================
// KERANJANG
// ========================================

function openCart() {
    if ($("cartDrawer")) $("cartDrawer").classList.add("active");
    if ($("drawerOverlay")) $("drawerOverlay").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeCart() {
    if ($("cartDrawer")) $("cartDrawer").classList.remove("active");
    if ($("drawerOverlay")) $("drawerOverlay").classList.remove("active");
    document.body.style.overflow = "";
}

if ($("cartBtn")) {
    $("cartBtn").addEventListener("click", () => {
        if (!currentUser) {
            showLogin();
            return;
        }

        loadCart();
        openCart();
    });
}

if ($("closeCart")) $("closeCart").addEventListener("click", closeCart);
if ($("drawerOverlay")) $("drawerOverlay").addEventListener("click", closeCart);

document.addEventListener("keydown", event => {
    if (event.key === "Escape") closeCart();
});

async function loadCart() {
    if (!currentUser) {
        cartItems = [];
        cartSubtotal = 0;
        renderCart();
        return;
    }

    const result = await api("cart");
    cartItems = result.items;
    cartSubtotal = result.subtotal;

    renderCart();
}

function renderCart() {
    const cartCount = $("cartCount");
    const cartSubtotalEl = $("cartSubtotal");
    const cartItemsEl = $("cartItems");

    if (!cartItemsEl) return;

    const count = cartItems.reduce((total, item) => total + item.quantity, 0);

    if (cartCount) cartCount.textContent = count;
    if (cartSubtotalEl) cartSubtotalEl.textContent = money(cartSubtotal);

    if (!cartItems.length) {
        cartItemsEl.innerHTML = `
            <div class="cart-empty text-center py-5">
                <i class="bi bi-bag-x fs-1"></i>
                <h5 class="mt-3">Keranjang kosong</h5>
                <p>Belum ada produk yang dipilih.</p>
            </div>
        `;
        return;
    }

    cartItemsEl.innerHTML = cartItems.map(item => `
        <div class="cart-item">
            <img src="${item.image}" alt="${escapeHTML(item.name)}">

            <div class="cart-item-info">
                <strong>${escapeHTML(item.name)}</strong>
                <small>${money(item.price)}</small>

                <div class="quantity-control">
                    <button data-qty="${item.id}" data-value="${item.quantity - 1}">−</button>
                    <span>${item.quantity}</span>
                    <button data-qty="${item.id}" data-value="${item.quantity + 1}">+</button>
                    <button class="remove-item" data-qty="${item.id}" data-value="0" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join("");
}

if ($("cartItems")) {
    $("cartItems").addEventListener("click", event => {
        const button = event.target.closest("[data-qty]");
        if (!button) return;

        runAction(async () => {
            await api("quantity", {
                product_id: button.dataset.qty,
                quantity: button.dataset.value
            });

            await loadCart();
        });
    });
}

// ========================================
// CHECKOUT & PESANAN
// ========================================

function shippingCost() {
    const el = document.querySelector('input[name="shipping"]:checked');
    return el && el.value === "express" ? 45000 : 20000;
}

function updateCheckout() {
    const checkoutItems = $("checkoutItems");
    if (!checkoutItems) return;

    checkoutItems.innerHTML = cartItems.map(item => `
        <div class="order-item">
            <span>${escapeHTML(item.name)} × ${item.quantity}</span>
            <strong>${money(item.price * item.quantity)}</strong>
        </div>
    `).join("");

    if ($("summarySubtotal")) $("summarySubtotal").textContent = money(cartSubtotal);
    if ($("summaryShipping")) $("summaryShipping").textContent = money(shippingCost());
    if ($("summaryTotal")) $("summaryTotal").textContent = money(cartSubtotal + shippingCost());
}

document.querySelectorAll('input[name="shipping"]').forEach(input => {
    input.addEventListener("change", updateCheckout);
});

if ($("checkoutBtn")) {
    $("checkoutBtn").addEventListener("click", () => {
        if (!currentUser) {
            closeCart();
            showLogin();
            return;
        }

        if (!cartItems.length) {
            notify("Keranjang masih kosong.");
            return;
        }

        if ($("buyerName")) $("buyerName").value = currentUser.name;

        updateCheckout();
        closeCart();
        if (checkoutModal) checkoutModal.show();
    });
}

if ($("checkoutForm")) {
    $("checkoutForm").addEventListener("submit", event => {
        event.preventDefault();

        runAction(async () => {
            const form = event.currentTarget;
            if (!form.reportValidity()) return;

            const data = Object.fromEntries(new FormData(form));
            const result = await api("checkout", data);

            const method = result.payment === "qris" ? "QRIS" : "Transfer Bank";
            const icon = result.payment === "qris" ? "bi-qr-code" : "bi-bank";

            if ($("paymentContent")) {
                $("paymentContent").innerHTML = `
                    <span class="demo-badge">PESANAN BERHASIL DIBUAT</span>

                    <h4 class="mt-3">Pesanan #${result.order_id}</h4>
                    <h2 class="my-3">${money(result.total)}</h2>

                    <div class="fake-qr">
                        <i class="bi ${icon}"></i>
                        <small>${method} DEMO</small>
                        <small>TIDAK UNTUK PEMBAYARAN</small>
                    </div>

                    <p>Status: <strong>Menunggu Pembayaran</strong></p>

                    <p class="checkout-note">
                        Pesanan telah tersimpan di database MySQL.
                        Ini hanya simulasi checkout.
                    </p>

                    <button class="primary-button full-width mt-3" data-bs-dismiss="modal">
                        Selesai
                    </button>
                `;
            }

            cartItems = [];
            cartSubtotal = 0;
            renderCart();
            form.reset();

            if (checkoutModalEl) {
                checkoutModalEl.addEventListener(
                    "hidden.bs.modal",
                    () => {
                        if (paymentModal) paymentModal.show();
                    },
                    { once: true }
                );
            }

            if (checkoutModal) checkoutModal.hide();
            notify("Pesanan berhasil disimpan ke database.");
        });
    });
}

async function loadOrders() {
    const orderHistory = $("orderHistory");
    if (!orderHistory) return;

    orderHistory.textContent = "Memuat riwayat...";

    try {
        const result = await api("orders");

        if (!result.orders.length) {
            orderHistory.textContent = "Belum ada riwayat pesanan.";
            return;
        }

        orderHistory.innerHTML = result.orders.map(order => `
            <div class="p-3 mb-2 rounded border" style="background: var(--surface);">
                <strong>Pesanan #${order.id}</strong>
                <small class="d-block text-muted">${escapeHTML(order.created_at)}</small>

                <div class="d-flex justify-content-between mt-2">
                    <span>${escapeHTML(order.status)}</span>
                    <strong>${money(order.total)}</strong>
                </div>

                <small class="text-muted d-block mt-1">
                    Pembayaran: ${escapeHTML(order.payment_method.toUpperCase())}
                </small>
            </div>
        `).join("");

    } catch (error) {
        orderHistory.textContent = error.message;
    }
}

// INITIALIZATION
renderProducts();
renderCart();

if (currentUser) {
    loadCart().catch(error => notify(error.message));
}