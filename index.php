<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var array<int, array<string, mixed>> $products */

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$user = loggedIn() ? [
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email']
] : null;

$frontendProducts = [];

foreach ($products as $id => $product) {
    $frontendProducts[] = array_merge(
        ['id' => $id],
        $product
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yamaha Guitar Store | Find Your Sound</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&family=Cinzel:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylesheet.css">

    <script>
        window.APP = <?php echo json_encode([
            'csrf' => $_SESSION['csrf'],
            'user' => $user,
            'products' => $frontendProducts
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

    <script src="script.js" defer></script>
</head>

<body data-theme="light">

    <div class="top-bar">
        <i class="bi bi-music-note-beamed"></i>
        YOUR MUSIC. YOUR STORY. YOUR SOUND.
    </div>

    <!-- NAVBAR -->
    <header class="site-header">
        <nav class="navbar navbar-expand-lg container">

            <a href="#home" class="navbar-brand">
                <i class="bi bi-soundwave"></i>
                YAMAHA<span>GUITARS.</span>
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-3">

                <!-- PILIH TEMA -->
                <select id="themeSelect" class="icon-button">
                    <option value="light">☀ Light</option>
                    <option value="dark">☾ Dark</option>
                    <option value="retro">♫ Retro</option>
                </select>

                <!-- TOMBOL AKUN / LOGIN -->
                <button class="icon-button" id="accountBtn" type="button">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-sm-inline ms-1"><?php echo $user ? htmlspecialchars($user['name']) : 'Login / Daftar'; ?></span>
                </button>

                <!-- KERANJANG -->
                <button class="icon-button cart-trigger" id="cartBtn" type="button">
                    <i class="bi bi-bag"></i>
                    <span id="cartCount" class="cart-count">0</span>
                </button>

                <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNav">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="#home" class="nav-link">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a href="#kategori" class="nav-link">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a href="#produk" class="nav-link">Koleksi</a>
                    </li>
                    <li class="nav-item">
                        <a href="#tentang" class="nav-link">Tentang</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>

        <!-- HERO -->
        <section class="hero-section" id="home">
            <div class="container">
                <div class="hero-layout">

                    <div class="hero-content">

                        <span class="hero-badge">
                            <i class="bi bi-stars"></i>
                            YAMAHA GUITAR COLLECTION
                        </span>

                        <h1>
                            Find Your<br>
                            <em>Perfect Sound.</em>
                        </h1>

                        <p>
                            Temukan karakter musikmu lewat koleksi gitar Yamaha.
                            Dari akustik hingga elektrik, pilih instrumen
                            untuk setiap panggung dan setiap cerita.
                        </p>

                        <div class="hero-actions">
                            <a href="#produk" class="primary-button">
                                Jelajahi Koleksi
                                <i class="bi bi-arrow-up-right"></i>
                            </a>

                            <a href="#tentang" class="secondary-button">
                                Tentang Kami
                            </a>
                        </div>

                        <div class="hero-stats">
                            <div>
                                <strong>8+</strong>
                                <span>Produk Pilihan</span>
                            </div>

                            <div>
                                <strong>3</strong>
                                <span>Tema Website</span>
                            </div>

                            <div>
                                <strong>24/7</strong>
                                <span>Explore Anytime</span>
                            </div>
                        </div>

                    </div>

                    <div class="hero-image">
                        <img
                            src="https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=1200&q=85"
                            alt="Gitar akustik"
                        >

                        <div class="hero-floating">
                            <i class="bi bi-music-note-beamed"></i>

                            <div>
                                <strong>Play Your Story</strong>
                                <small>Discover your sound</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- BENEFITS -->
        <section class="benefits">
            <div class="container">
                <div class="row g-4">

                    <div class="col-6 col-lg-3">
                        <div class="benefit">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <strong>Akun Pelanggan</strong>
                                <small>Login & Registrasi</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="benefit">
                            <i class="bi bi-truck"></i>
                            <div>
                                <strong>Pengiriman</strong>
                                <small>Reguler & Express</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="benefit">
                            <i class="bi bi-bag-check"></i>
                            <div>
                                <strong>Belanja Online</strong>
                                <small>Keranjang Tersimpan</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="benefit">
                            <i class="bi bi-qr-code"></i>
                            <div>
                                <strong>Pembayaran</strong>
                                <small>QRIS Demo</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- KATEGORI -->
        <section class="section-padding" id="kategori">
            <div class="container">

                <div class="section-heading">
                    <span class="section-label">EXPLORE COLLECTION</span>
                    <h2>Shop by <em>Category</em></h2>
                    <p>Temukan instrumen sesuai gaya musikmu.</p>
                </div>

                <div class="row g-3">

                    <div class="col-6 col-lg-4">
                        <button class="category-card" data-category="Acoustic">
                            <i class="bi bi-music-note-beamed"></i>
                            <strong>Acoustic</strong>
                            <small>Warm & natural sound</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </button>
                    </div>

                    <div class="col-6 col-lg-4">
                        <button class="category-card" data-category="Electric">
                            <i class="bi bi-lightning-charge"></i>
                            <strong>Electric</strong>
                            <small>Powerful & expressive</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </button>
                    </div>

                    <div class="col-12 col-lg-4">
                        <button class="category-card" data-category="Electro Acoustic">
                            <i class="bi bi-speaker"></i>
                            <strong>Electro Acoustic</strong>
                            <small>Ready for the stage</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <!-- PRODUK -->
        <section class="products-section section-padding" id="produk">
            <div class="container">

                <div class="section-heading">
                    <span class="section-label">OUR COLLECTION</span>
                    <h2>Featured <em>Products</em></h2>
                    <p>Temukan gitar Yamaha favoritmu.</p>
                </div>

                <div class="shop-toolbar">

                    <div class="search-box">
                        <i class="bi bi-search"></i>

                        <input
                            type="search"
                            id="searchInput"
                            placeholder="Cari gitar Yamaha..."
                        >
                    </div>

                    <select id="categoryFilter">
                        <option value="all">Semua Kategori</option>
                        <option value="Acoustic">Acoustic</option>
                        <option value="Electric">Electric</option>
                        <option value="Electro Acoustic">Electro Acoustic</option>
                    </select>

                    <select id="sortFilter">
                        <option value="default">Urutkan Produk</option>
                        <option value="low">Harga Terendah</option>
                        <option value="high">Harga Tertinggi</option>
                        <option value="name">Nama A-Z</option>
                    </select>

                </div>

                <div class="shop-info">
                    <span id="resultCount">0 produk ditemukan</span>

                    <button id="resetFilter">
                        Reset Filter
                    </button>
                </div>

                <div class="row g-4" id="productGrid"></div>

            </div>
        </section>

        <!-- PROMO -->
        <section class="section-padding">
            <div class="container">

                <div class="promo-banner">
                    <div class="promo-content">
                        <span class="section-label">YOUR MUSIC JOURNEY</span>

                        <h2>
                            Make Every<br>
                            <em>Note Count.</em>
                        </h2>

                        <p>
                            Setiap gitar punya cerita.
                            Mulai perjalanan musikmu bersama Yamaha Guitar Store.
                        </p>

                        <a href="#produk" class="primary-button">
                            Belanja Sekarang
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <i class="bi bi-music-note-beamed promo-decoration"></i>
                </div>

            </div>
        </section>

        <!-- TENTANG -->
        <section class="about-section section-padding" id="tentang">
            <div class="container text-center">

                <span class="section-label">ABOUT OUR STORE</span>

                <h2>Yamaha Guitar Store</h2>

                <p>
                    Website e-commerce untuk proyek antarmuka komputer.
                    Dibangun menggunakan HTML, CSS, JavaScript,
                    PHP, MySQL, dan Bootstrap.
                    Website ini bukan toko resmi Yamaha.
                </p>

            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">

            <h4>YAMAHA<span>GUITARS.</span></h4>
            <p>Find Your Perfect Sound.</p>

            <hr>

            <div class="footer-bottom">
                <span>© 2026 Yamaha Guitar Store — Educational Project</span>
                <span>Bukan website resmi Yamaha</span>
            </div>

        </div>
    </footer>

    <!-- KERANJANG -->
    <div class="cart-overlay" id="drawerOverlay"></div>

    <aside class="cart-panel" id="cartDrawer">

        <div class="cart-header">
            <div>
                <h3>Your Cart</h3>
                <p>Keranjang belanja kamu</p>
            </div>

            <button class="close-cart" id="closeCart">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="cartItems" class="cart-items"></div>

        <div class="cart-bottom">
            <div class="total-row">
                <span>Subtotal</span>
                <strong id="cartSubtotal">Rp0</strong>
            </div>

            <button class="primary-button full-width" id="checkoutBtn">
                Checkout <i class="bi bi-arrow-right"></i>
            </button>
        </div>

    </aside>

    <!-- MODAL LOGIN DAN REGISTER -->
    <div class="modal fade" id="authModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <span class="section-label">WELCOME TO YAMAHA GUITARS</span>
                        <h3 class="modal-title" id="authTitle">Login Akun</h3>
                    </div>

                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="loginForm">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="nama@email.com"
                                required
                                autocomplete="email"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Password akun"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <button class="primary-button full-width mt-3" type="submit">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>

                        <p class="text-center mt-3 small">
                            Belum punya akun?
                            <button type="button" class="btn btn-link p-0 text-decoration-none" id="showRegister">
                                Daftar sekarang
                            </button>
                        </p>

                    </form>

                    <form id="registerForm" class="d-none">

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input
                                name="name"
                                class="form-control"
                                minlength="3"
                                maxlength="100"
                                placeholder="Nama Lengkap"
                                required
                                autocomplete="name"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                name="email"
                                type="email"
                                class="form-control"
                                placeholder="Alamat email"
                                required
                                autocomplete="email"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="Minimal 8 karakter"
                                minlength="8"
                                required
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input
                                name="confirm"
                                type="password"
                                class="form-control"
                                placeholder="Ulangi password"
                                minlength="8"
                                required
                                autocomplete="new-password"
                            >
                        </div>

                        <button class="primary-button full-width mt-3" type="submit">
                            <i class="bi bi-person-plus"></i> Buat Akun
                        </button>

                        <p class="text-center mt-3 small">
                            Sudah punya akun?
                            <button type="button" class="btn btn-link p-0 text-decoration-none" id="showLogin">
                                Login di sini
                            </button>
                        </p>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AKUN -->
    <div class="modal fade" id="accountModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h3 class="modal-title">Akun Saya</h3>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background: var(--soft);">
                        <i class="bi bi-person-circle fs-1" style="color: var(--accent);"></i>
                        <div>
                            <strong id="profileName" class="d-block fs-5"></strong>
                            <small id="profileEmail" class="text-muted"></small>
                        </div>
                    </div>

                    <h5>Riwayat Pesanan</h5>
                    <div id="orderHistory" class="my-3"></div>

                    <a href="logout.php" class="secondary-button full-width text-center mt-4 text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CHECKOUT -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h3 class="modal-title">Checkout Pesanan</h3>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">

                        <div class="col-lg-7">

                            <form id="checkoutForm">

                                <h5 class="checkout-heading"><span>1</span> Informasi Penerima</h5>

                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input
                                        name="name"
                                        id="buyerName"
                                        class="form-control"
                                        required
                                        minlength="3"
                                        maxlength="100"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nomor HP</label>
                                    <input
                                        name="phone"
                                        type="tel"
                                        class="form-control"
                                        required
                                        pattern="[0-9+ -]{9,17}"
                                        placeholder="08xxxxxxxxxx"
                                    >
                                </div>

                                <h5 class="checkout-heading"><span>2</span> Alamat Pengiriman</h5>

                                <div class="mb-3">
                                    <label class="form-label">Kota / Kabupaten</label>
                                    <input name="city" class="form-control" required minlength="2">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Kode Pos</label>
                                    <input
                                        name="postal"
                                        class="form-control"
                                        required
                                        pattern="[0-9]{5}"
                                        placeholder="16421"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea
                                        name="address"
                                        class="form-control"
                                        rows="3"
                                        minlength="10"
                                        required
                                        placeholder="Jalan, nomor rumah, RT/RW, kecamatan, provinsi"
                                    ></textarea>
                                </div>

                                <h5 class="checkout-heading"><span>3</span> Pengiriman</h5>

                                <label class="checkout-option w-100">
                                    <input type="radio" name="shipping" value="regular" checked>
                                    <span>
                                        <strong>Reguler</strong>
                                        <small>Estimasi 3–5 hari</small>
                                    </span>
                                    <b>Rp20.000</b>
                                </label>

                                <label class="checkout-option w-100">
                                    <input type="radio" name="shipping" value="express">
                                    <span>
                                        <strong>Express</strong>
                                        <small>Estimasi 1–2 hari</small>
                                    </span>
                                    <b>Rp45.000</b>
                                </label>

                                <h5 class="checkout-heading"><span>4</span> Metode Pembayaran</h5>

                                <label class="checkout-option w-100">
                                    <input type="radio" name="payment" value="qris" checked>
                                    <span>
                                        <strong><i class="bi bi-qr-code"></i> QRIS</strong>
                                        <small>Pembayaran QRIS Simulasi</small>
                                    </span>
                                </label>

                                <label class="checkout-option w-100">
                                    <input type="radio" name="payment" value="transfer">
                                    <span>
                                        <strong><i class="bi bi-bank"></i> Transfer Bank</strong>
                                        <small>Transfer Simulasi</small>
                                    </span>
                                </label>

                            </form>
                        </div>

                        <div class="col-lg-5">
                            <div class="order-summary">

                                <h5>Ringkasan Pesanan</h5>

                                <div id="checkoutItems" class="my-3"></div>

                                <hr>

                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span id="summarySubtotal"></span>
                                </div>

                                <div class="total-row">
                                    <span>Ongkir</span>
                                    <span id="summaryShipping"></span>
                                </div>

                                <div class="total-row grand-total">
                                    <strong>Total Pembayaran</strong>
                                    <strong id="summaryTotal"></strong>
                                </div>

                                <button
                                    type="submit"
                                    form="checkoutForm"
                                    class="primary-button full-width mt-4"
                                >
                                    Buat Pesanan Demo
                                </button>

                                <p class="checkout-note">
                                    Website ini menggunakan pembayaran simulasi.
                                    Tidak ada transaksi uang sungguhan.
                                </p>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h3 class="modal-title">Instruksi Pembayaran</h3>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center" id="paymentContent"></div>

            </div>
        </div>
    </div>

    <div class="custom-toast" id="toastMessage" role="status"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>