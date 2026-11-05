<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var PDO $pdo */
/** @var array<int, array<string, mixed>> $products */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ], 405);
}

if (
    !isset($_POST['csrf']) ||
    !hash_equals(
        (string) ($_SESSION['csrf'] ?? ''),
        (string) $_POST['csrf']
    )
) {
    jsonResponse([
        'success' => false,
        'message' => 'Sesi tidak valid. Refresh halaman.'
    ], 403);
}

$action = $_POST['action'] ?? '';

try {

    // REGISTER
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama harus 3 sampai 100 karakter.'
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            jsonResponse([
                'success' => false,
                'message' => 'Email tidak valid.'
            ], 422);
        }

        if (strlen($password) < 8) {
            jsonResponse([
                'success' => false,
                'message' => 'Password minimal 8 karakter.'
            ], 422);
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            jsonResponse([
                'success' => false,
                'message' => 'Email sudah terdaftar.'
            ], 409);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        jsonResponse([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'user' => [
                'name' => $name,
                'email' => $email
            ]
        ]);
    }

    // LOGIN
    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        jsonResponse([
            'success' => true,
            'message' => 'Login berhasil.',
            'user' => [
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }

    // KERANJANG
    if ($action === 'cart') {
        $userId = requireLogin();

        $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart_items WHERE user_id = ?');
        $stmt->execute([$userId]);

        $items = [];
        $subtotal = 0;

        foreach ($stmt->fetchAll() as $row) {
            $id = (int) $row['product_id'];
            $quantity = (int) $row['quantity'];

            if (!isset($products[$id])) {
                continue;
            }

            $product = $products[$id];

            $items[] = [
                'id' => $id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];

            $subtotal += $product['price'] * $quantity;
        }

        jsonResponse([
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal
        ]);
    }

    // TAMBAH PRODUK
    if ($action === 'add') {
        $userId = requireLogin();
        $productId = (int) ($_POST['product_id'] ?? 0);

        if (!isset($products[$productId])) {
            jsonResponse([
                'success' => false,
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO cart_items (user_id, product_id, quantity)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + 1, 99)'
        );

        $stmt->execute([$userId, $productId]);

        jsonResponse([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.'
        ]);
    }

    // UBAH JUMLAH PRODUK
    if ($action === 'quantity') {
        $userId = requireLogin();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if (!isset($products[$productId]) || $quantity < 0 || $quantity > 99) {
            jsonResponse([
                'success' => false,
                'message' => 'Jumlah produk tidak valid.'
            ], 422);
        }

        if ($quantity === 0) {
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$quantity, $userId, $productId]);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Keranjang diperbarui.'
        ]);
    }

    // CHECKOUT
    if ($action === 'checkout') {
        $userId = requireLogin();

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal = trim($_POST['postal'] ?? '');

        $shipping = $_POST['shipping'] ?? 'regular';
        $payment = $_POST['payment'] ?? 'qris';

        if (
            mb_strlen($name) < 3 ||
            !preg_match('/^[0-9+ -]{9,17}$/', $phone) ||
            mb_strlen($address) < 10 ||
            mb_strlen($city) < 2 ||
            !preg_match('/^[0-9]{5}$/', $postal)
        ) {
            jsonResponse([
                'success' => false,
                'message' => 'Lengkapi data pengiriman dengan benar.'
            ], 422);
        }

        if (!in_array($shipping, ['regular', 'express'], true) || !in_array($payment, ['qris', 'transfer'], true)) {
            jsonResponse([
                'success' => false,
                'message' => 'Metode pengiriman atau pembayaran tidak valid.'
            ], 422);
        }

        $shippingCost = $shipping === 'express' ? 45000 : 20000;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart_items WHERE user_id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            $pdo->rollBack();
            jsonResponse([
                'success' => false,
                'message' => 'Keranjang kosong.'
            ], 422);
        }

        $subtotal = 0;

        foreach ($rows as $row) {
            $id = (int) $row['product_id'];
            $qty = (int) $row['quantity'];

            if (!isset($products[$id]) || $qty < 1 || $qty > 99) {
                throw new RuntimeException('Data produk tidak valid.');
            }

            $subtotal += $products[$id]['price'] * $qty;
        }

        $total = $subtotal + $shippingCost;

        $stmt = $pdo->prepare(
            'INSERT INTO orders (
                user_id, customer_name, phone, address, city, postal_code,
                shipping, payment_method, subtotal, shipping_cost, total
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $userId, $name, $phone, $address, $city, $postal,
            $shipping, $payment, $subtotal, $shippingCost, $total
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
             VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($rows as $row) {
            $id = (int) $row['product_id'];

            $itemStmt->execute([
                $orderId,
                $id,
                $products[$id]['name'],
                $products[$id]['price'],
                (int) $row['quantity']
            ]);
        }

        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$userId]);

        $pdo->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'order_id' => $orderId,
            'total' => $total,
            'payment' => $payment,
            'status' => 'Menunggu Pembayaran'
        ]);
    }

    // RIWAYAT PESANAN
    if ($action === 'orders') {
        $userId = requireLogin();

        $stmt = $pdo->prepare(
            'SELECT id, total, payment_method, shipping, status, created_at
             FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 30'
        );

        $stmt->execute([$userId]);

        jsonResponse([
            'success' => true,
            'orders' => $stmt->fetchAll()
        ]);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Aksi tidak ditemukan.'
    ], 404);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan server.'
    ], 500);
}