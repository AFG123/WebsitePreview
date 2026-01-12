<?php
require_once 'config.php';

// ----------------- AUTHENTICATION -----------------
// Simple admin login - for production, consider storing credentials in the DB and hashing passwords
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function login($username, $password) {
    // Uses constants defined in config.php (still practical for admin panel)
    if ($username === "admin" && $password === "wedding123") {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}

// ----------------- PRODUCTS (CARDS) -----------------
function getAllProducts() {
    global $pdo;
    $sql = "SELECT d.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names, GROUP_CONCAT(c.slug SEPARATOR ', ') AS category_slugs
            FROM designs d 
            LEFT JOIN product_categories pc ON d.id = pc.product_id 
            LEFT JOIN categories c ON pc.category_id = c.id 
            GROUP BY d.id 
            ORDER BY d.created_at DESC";
    return $pdo->query($sql)->fetchAll();
}

function getProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names, GROUP_CONCAT(c.slug SEPARATOR ', ') AS category_slugs
                           FROM designs d 
                           LEFT JOIN product_categories pc ON d.id = pc.product_id 
                           LEFT JOIN categories c ON pc.category_id = c.id 
                           WHERE d.id = ? 
                           GROUP BY d.id");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addProduct($data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO designs 
        (title, description, price, image_url, is_bestseller, is_new_arrival, is_custom, is_gifting) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['price'],
        $data['image_url'],
        !empty($data['is_bestseller']) ? 1 : 0,
        !empty($data['is_new_arrival']) ? 1 : 0,
        !empty($data['is_custom']) ? 1 : 0,
        !empty($data['is_gifting']) ? 1 : 0,
    ]);
    $productId = $pdo->lastInsertId();
    
    // Insert categories
    if (!empty($data['categories'])) {
        $stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
        foreach ($data['categories'] as $catId) {
            $stmt->execute([$productId, $catId]);
        }
    }
    
    return true;
}

function updateProduct($id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE designs SET 
        title = ?, description = ?, price = ?, image_url = ?, 
        is_bestseller = ?, is_new_arrival = ?, is_custom = ?, is_gifting = ?
        WHERE id = ?");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['price'],
        $data['image_url'],
        !empty($data['is_bestseller']) ? 1 : 0,
        !empty($data['is_new_arrival']) ? 1 : 0,
        !empty($data['is_custom']) ? 1 : 0,
        !empty($data['is_gifting']) ? 1 : 0,
        $id
    ]);
    
    // Update categories: first delete existing, then insert new
    $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([$id]);
    if (!empty($data['categories'])) {
        $stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
        foreach ($data['categories'] as $catId) {
            $stmt->execute([$id, $catId]);
        }
    }
    
    return true;
}

function deleteProduct($id) {
    global $pdo;
    // Remove related from product_categories
    $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([(int)$id]);
    // Then delete from designs
    $stmt = $pdo->prepare("DELETE FROM designs WHERE id = ?");
    return $stmt->execute([(int)$id]);
}


// ----------------- CATEGORIES -----------------
function getAllCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

function getCategory($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addCategory($data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    return $stmt->execute([
        $data['name'],
        $data['slug'],
        $data['description']
    ]);
}

function updateCategory($id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
    return $stmt->execute([
        $data['name'],
        $data['slug'],
        $data['description'],
        $id
    ]);
}

function deleteCategory($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

// ----------------- SETTINGS -----------------
// You need a settings table if you want site messages, currency, etc. in DB. Here’s a simple example:
function getSettings() {
    global $pdo;
    // Suppose you have a 'settings' table with simple key-value pairs
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settingsArr = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsArr as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSettings($data) {
    global $pdo;
    foreach ($data as $key => $value) {
        $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
    return true;
}

// ----------------- DASHBOARD -----------------
function getDashboardStats() {
    global $pdo;
    $stats = [
        'total_products'   => $pdo->query("SELECT COUNT(*) FROM designs")->fetchColumn(),
        'published_products' => $pdo->query("SELECT COUNT(*) FROM designs WHERE status = 'published'")->fetchColumn(), // If you add a `status` column
        'draft_products'  => $pdo->query("SELECT COUNT(*) FROM designs WHERE status = 'draft'")->fetchColumn(), // If you add a `status` column
        'total_categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn()
    ];
    return $stats;
}

// ----------------- IMAGE UPLOAD -----------------
function uploadImage($file) {
    $uploadsDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    if (!is_writable($uploadsDir)) {
        return ['success' => false, 'error' => 'Uploads directory is not writable.'];
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size too large. Maximum 5MB allowed.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadsDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename, 'url' => 'uploads/' . $filename];
    }
    return ['success' => false, 'error' => 'Failed to move uploaded file.'];
}

// ----------------- VALIDATION -----------------
function validateProduct($data) {
    $errors = [];
    if (empty($data['title'])) $errors[] = 'Title is required.';
    if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) $errors[] = 'Valid price is required.';
    if (empty($data['categories']) || !is_array($data['categories'])) $errors[] = 'At least one category is required.';
    if (empty($data['description'])) $errors[] = 'Description is required.';
    return $errors;
}

function validateCategory($data) {
    $errors = [];
    if (empty($data['name'])) $errors[] = 'Category name is required.';
    if (empty($data['slug'])) {
        $data['slug'] = strtolower(str_replace(' ', '-', $data['name']));
    }
    // Slug validation (optional)
    if (!preg_match('/^[a-z0-9-_]+$/', $data['slug'])) {
        $errors[] = 'Slug can only contain lowercase letters, numbers, dashes, and underscores.';
    }
    return $errors;
}

// ----------------- (OPTIONAL) PRODUCTS FOR API (JSON OUTPUT) -----------------
function getProductsForAPI($categorySlug = null, $featured = null) {
    global $pdo;
    $sql = "SELECT d.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category FROM designs d 
            LEFT JOIN product_categories pc ON d.id = pc.product_id 
            LEFT JOIN categories c ON pc.category_id = c.id WHERE 1 ";
    $params = [];

    if ($categorySlug) {
        $sql .= "AND c.slug = ? ";
        $params[] = $categorySlug;
    }
    if ($featured === 'bestseller') {
        $sql .= "AND d.is_bestseller = 1 ";
    } elseif ($featured === 'new_arrival') {
        $sql .= "AND d.is_new_arrival = 1 ";
    }
    $sql .= "GROUP BY d.id ORDER BY d.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}


?>
