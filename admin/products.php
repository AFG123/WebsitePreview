<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
require_once 'functions.php';

$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;
$errors = [];
$success = '';

// Handle delete action (GET request) OUTSIDE the POST block
if (($action === 'delete' || $action === 'remove') && $productId) {
    if (deleteProduct($productId)) {
        $_SESSION['success_message'] = 'Product deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to delete product.';
    }
    header('Location: products.php');
    exit();
}

// Handle form submissions (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'price' => (float)$_POST['price'],
            'description' => sanitizeInput($_POST['description']),
            'categories' => isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : [],
            'status' => $_POST['status'] ?? 'draft',
            'is_bestseller' => isset($_POST['is_bestseller']),
            'is_new_arrival' => isset($_POST['is_new_arrival']),
            'rating' => (float)($_POST['rating'] ?? 4.5),
            'rating_count' => (int)($_POST['rating_count'] ?? 50),
            'gradient' => sanitizeInput($_POST['gradient']),
            'features' => sanitizeInput($_POST['features']),
            'is_custom' => isset($_POST['is_custom']),
            'is_gifting' => isset($_POST['is_gifting'])
        ];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image_url'] = $uploadResult['url'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        // Validate data
        $validationErrors = validateProduct($data);
        $errors = array_merge($errors, $validationErrors);
        
        if (empty($errors)) {
            if ($action === 'add') {
                if (addProduct($data)) {
                    $_SESSION['success_message'] = 'Product added successfully!';
                    header('Location: products.php');
                    exit();
                } else {
                    $errors[] = 'Failed to add product.';
                }
            } else {
                if (updateProduct($productId, $data)) {
                    $_SESSION['success_message'] = 'Product updated successfully!';
                    header('Location: products.php');
                    exit();
                } else {
                    $errors[] = 'Failed to update product.';
                }
            }
        }
    }
}

// Get data for forms
$categories = getAllCategories();
$products = getAllProducts();
$currentProduct = null;

if ($action === 'edit' && $productId) {
    $currentProduct = getProduct($productId);
    if (!$currentProduct) {
        $_SESSION['error_message'] = 'Product not found.';
        header('Location: products.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #9a789b 0%, #4e4351 100%);
            color: white;
            padding: 2rem 0;
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            padding: 0 1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }

        .sidebar .logo h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar .logo p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }

        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav ul li {
            margin-bottom: 0.5rem;
        }

        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background-color: rgba(255,255,255,0.1);
            border-right: 3px solid #f9c5d1;
        }

        .sidebar nav ul li a svg {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #4e4351;
            font-size: 1.8rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu .btn-logout {
            padding: 0.5rem 1rem;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .user-menu .btn-logout:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .content {
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #9a789b;
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #4e4351;
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: #f9c5d1;
            color: #4e4351;
        }

        .btn-primary:hover {
            background-color: #f2d4e1;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e1e5e9;
            background-color: #f8f9fa;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4e4351;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #9a789b;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .categories-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            border: 2px solid #e1e5e9;
            border-radius: 0.375rem;
            padding: 0.75rem;
        }

        .category-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 400;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .products-table th,
        .products-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .products-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #4e4351;
        }

        .products-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .product-image {
            width: 60px;
            height: 45px;
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
            font-weight: 500;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-published {
            background-color: #d4edda;
            color: #155724;
        }

        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-list {
            list-style-type: disc;
            margin-left: 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                padding: 1rem;
            }

            .content {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .products-table {
                font-size: 0.875rem;
            }

            .products-table th,
            .products-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>Admin Panel</h2>
            <p>Wedding Cards Manager</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="products.php" class="active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                    </svg>
                    Products
                </a></li>
                <li><a href="categories.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Categories
                </a></li>
                <li><a href="settings.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Products</h1>
            <div class="user-menu">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Products List -->
                <div class="page-header">
                    <h2>All Products</h2>
                    <a href="products.php?action=add" class="btn btn-primary">Add New Product</a>
                </div>

                <?php if (empty($products)): ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 3rem;">
                            <h3>No products found</h3>
                            <p>Start by adding your first wedding invitation design.</p>
                            <a href="products.php?action=add" class="btn btn-primary">Add First Product</a>
                        </div>
                    </div>
                <?php else: ?>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-image">
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                            <?php else: ?>
                                                <div class="product-placeholder" style="background: <?php echo $product['gradient'] ?? 'linear-gradient(135deg, #f9c5d1 0%, #c6a4b4 100%)'; ?>">
                                                    IMG
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                        <?php if (isset($product['is_bestseller']) && $product['is_bestseller']): ?>
                                            <span style="color: #d9b08c; font-size: 0.8rem;">⭐ Bestseller</span>
                                        <?php endif; ?>
                                        <?php if (isset($product['is_new_arrival']) && $product['is_new_arrival']): ?>
                                            <span style="color: #9a789b; font-size: 0.8rem;">🆕 New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatCurrency($product['price']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_names'] ?? ''); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo ($product['status'] ?? 'draft') === 'published' ? 'status-published' : 'status-draft'; ?>">
                                            <?php echo ucfirst($product['status'] ?? 'draft'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-small">Edit</a>
                                            <?php echo "Product ID: " . $product['id'] . " | Title: " . htmlspecialchars($product['title']); ?>
                                           <a href="products.php?action=remove&id=<?php echo $product['id']; ?>" 
                                            class="btn btn-small btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this product?')">Delete
                                        </a>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php else: ?>
                <!-- Add/Edit Product Form -->
                <div class="page-header">
                    <h2><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h2>
                    <a href="products.php" class="btn">Back to Products</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="title" class="form-label">Product Title *</label>
                                    <input type="text" id="title" name="title" class="form-input" 
                                           value="<?php echo htmlspecialchars($currentProduct['title'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="price" class="form-label">Price (₹) *</label>
                                    <input type="number" id="price" name="price" class="form-input" step="0.01" min="0"
                                           value="<?php echo $currentProduct['price'] ?? ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Categories *</label>
                                    <div class="categories-checkboxes">
    <?php 
    $currentCategories = [];
    if ($currentProduct) {
        $stmt = $pdo->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
        $stmt->execute([$currentProduct['id']]);
        $currentCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    foreach ($categories as $category): ?>
        <label class="category-checkbox">
            <input type="checkbox" name="categories[]" value="<?php echo (int)$category['id']; ?>"
                   <?php echo in_array($category['id'], $currentCategories) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($category['name']); ?>
        </label>
    <?php endforeach; ?>
                                    </div>
                                    <small style="color: #666;">Select one or more categories.</small>
                                </div>

                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="draft" <?php echo (($currentProduct['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo (($currentProduct['status'] ?? 'draft') === 'published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>

                                <div class="form-group full-width">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea id="description" name="description" class="form-textarea" required><?php echo htmlspecialchars($currentProduct['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" id="image" name="image" class="form-input" accept="image/*">
                                    <div id="image-preview">
                                        <?php if (!empty($currentProduct['image_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($currentProduct['image_url']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem; margin-top: 0.5rem;">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="gradient" class="form-label">Gradient Background (CSS)</label>
                                    <input type="text" id="gradient" name="gradient" class="form-input" 
                                           value="<?php echo htmlspecialchars($currentProduct['gradient'] ?? 'linear-gradient(135deg, #f9c5d1 0%, #c6a4b4 100%)'); ?>"
                                           placeholder="linear-gradient(135deg, #f9c5d1 0%, #c6a4b4 100%)">
                                </div>

                                <div class="form-group">
                                    <label for="rating" class="form-label">Rating (1-5)</label>
                                    <input type="number" id="rating" name="rating" class="form-input" step="0.1" min="1" max="5"
                                           value="<?php echo $currentProduct['rating'] ?? '4.5'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="rating_count" class="form-label">Rating Count</label>
                                    <input type="number" id="rating_count" name="rating_count" class="form-input" min="0"
                                           value="<?php echo $currentProduct['rating_count'] ?? '50'; ?>">
                                </div>

                                <div class="form-group full-width">
                                    <label for="features" class="form-label">Features (one per line)</label>
                                    <textarea id="features" name="features" class="form-textarea" placeholder="Premium gold foil printing&#10;High-quality cardstock&#10;Custom text personalization"><?php echo htmlspecialchars($currentProduct['features'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <div class="form-checkbox">
                                        <input type="checkbox" id="is_bestseller" name="is_bestseller" 
                                               <?php echo (isset($currentProduct['is_bestseller']) && $currentProduct['is_bestseller']) ? 'checked' : ''; ?>>
                                        <label for="is_bestseller">Mark as Bestseller</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-checkbox">
                                        <input type="checkbox" id="is_new_arrival" name="is_new_arrival" 
                                               <?php echo (isset($currentProduct['is_new_arrival']) && $currentProduct['is_new_arrival']) ? 'checked' : ''; ?>>
                                        <label for="is_new_arrival">Mark as New Arrival</label>
                                    </div>
                                </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="is_custom" value="1" <?php echo (!empty($currentProduct['is_custom'])) ? 'checked' : ''; ?>>
                                            Custom Invitation
                                        </label>
                                        </div>

                                        <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="is_gifting" value="1" <?php echo (!empty($currentProduct['is_gifting'])) ? 'checked' : ''; ?>>
                                            Gifting Accessories
                                        </label>
                                    </div>

                            </div>

                            <div style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                                </button>
                                <a href="products.php" class="btn" style="margin-left: 1rem;">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('image-preview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem; margin-top: 0.5rem;">';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<?php if (!empty($currentProduct['image_url'])): ?><img src="../<?php echo htmlspecialchars($currentProduct['image_url']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem; margin-top: 0.5rem;"><?php endif; ?>';
            }
        });
    </script>
</body>
</html>