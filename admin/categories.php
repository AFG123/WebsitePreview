<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
require_once 'functions.php';

$action = $_GET['action'] ?? 'list';
$categoryId = $_GET['id'] ?? null;
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'slug' => sanitizeInput($_POST['slug']),
            'description' => sanitizeInput($_POST['description'])
        ];
        
        // Generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $data['name'])));
        }
        
        // Validate data
        $validationErrors = validateCategory($data);
        $errors = array_merge($errors, $validationErrors);
        
        if (empty($errors)) {
            if ($action === 'add') {
                if (addCategory($data)) {
                    $_SESSION['success_message'] = 'Category added successfully!';
                    header('Location: categories.php');
                    exit();
                } else {
                    $errors[] = 'Failed to add category.';
                }
            } else {
                if (updateCategory($categoryId, $data)) {
                    $_SESSION['success_message'] = 'Category updated successfully!';
                    header('Location: categories.php');
                    exit();
                } else {
                    $errors[] = 'Failed to update category.';
                }
            }
        }
    } elseif ($action === 'delete' && $categoryId) {
        if (deleteCategory($categoryId)) {
            $_SESSION['success_message'] = 'Category deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete category.';
        }
        header('Location: categories.php');
        exit();
    }
}

// Get data for forms
$categories = getAllCategories();
$currentCategory = null;

if ($action === 'edit' && $categoryId) {
    $currentCategory = getCategory($categoryId);
    if (!$currentCategory) {
        $_SESSION['error_message'] = 'Category not found.';
        header('Location: categories.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Admin Panel</title>
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

        .sidebar nav ul {
            list-style: none;
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
        }

        .btn-primary {
            background-color: #f9c5d1;
            color: #4e4351;
        }

        .btn-danger {
            background-color: #dc3545;
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

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4e4351;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #9a789b;
        }

        .categories-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .categories-table th,
        .categories-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .categories-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #4e4351;
        }

        .categories-table tbody tr:hover {
            background-color: #f8f9fa;
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

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
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
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="products.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                    </svg>
                    Products
                </a></li>
                <li><a href="categories.php" class="active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Categories
                </a></li>
                <li><a href="settings.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
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
            <h1>Categories</h1>
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
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Categories List -->
                <div class="page-header">
                    <h2>All Categories</h2>
                    <a href="categories.php?action=add" class="btn btn-primary">Add New Category</a>
                </div>

                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" 
                                           class="btn btn-small btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- Add/Edit Category Form -->
                <div class="page-header">
                    <h2><?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h2>
                    <a href="categories.php" class="btn">Back to Categories</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="name" class="form-label">Category Name *</label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($currentCategory['name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" id="slug" name="slug" class="form-input" 
                                       value="<?php echo htmlspecialchars($currentCategory['slug'] ?? ''); ?>"
                                       placeholder="Leave empty to auto-generate">
                                <small style="color: #666;">URL-friendly version of the name (e.g., traditional, modern)</small>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-textarea"><?php echo htmlspecialchars($currentCategory['description'] ?? ''); ?></textarea>
                            </div>

                            <div style="margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $action === 'add' ? 'Add Category' : 'Update Category'; ?>
                                </button>
                                <a href="categories.php" class="btn" style="margin-left: 1rem;">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>