<?php
require_once 'admin/config.php';
require_once 'admin/functions.php';

// Fetch categories for nav/filter
$categories = getAllCategories();

$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$isCustom = isset($_GET['custom']) ? (bool)$_GET['custom'] : false;
$isGifting = isset($_GET['gifting']) ? (bool)$_GET['gifting'] : false;

// Price Filter Params
$priceRange = isset($_GET['price_range']) ? trim($_GET['price_range']) : '';
$minPrice = null;
$maxPrice = null;

// Parse price range
if ($priceRange === '0-100') {
    $minPrice = 0;
    $maxPrice = 100;
} elseif ($priceRange === '100-500') {
    $minPrice = 100;
    $maxPrice = 500;
} elseif ($priceRange === '500-9999') {
    $minPrice = 500;
    $maxPrice = 9999;
}

// Base query parts
$sql = "SELECT d.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_name, GROUP_CONCAT(c.slug SEPARATOR ', ') AS category_slugs
        FROM designs d
        LEFT JOIN product_categories pc ON d.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id";

$whereClauses = [];
$params = [];

// 1. Add Filter Conditions
if ($isCustom) {
    $whereClauses[] = "d.is_custom = 1";
} elseif ($isGifting) {
    $whereClauses[] = "d.is_gifting = 1";
} elseif ($categorySlug) {
    $whereClauses[] = "c.slug = :slug";
    $params[':slug'] = $categorySlug;
}

// 2. Add Price Conditions
if ($minPrice !== null) {
    $whereClauses[] = "d.price >= :min_price";
    $params[':min_price'] = $minPrice;
}
if ($maxPrice !== null) {
    $whereClauses[] = "d.price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

// 3. Assemble Query
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

$sql .= " GROUP BY d.id ORDER BY d.id DESC";

// 4. Execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();


// Change this to your full site URL (without trailing slash)
$siteURL = "https://yourwebsite.com";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Wedding Cards | <?php echo $categorySlug ? htmlspecialchars(ucfirst(str_replace('-', ' ', $categorySlug))) : 'All Products'; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background-color: #fff7fc;
        color: #4e4351;
    }
    a {
        color: #9a789b;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    a:hover, a.active {
        color: #f9c5d1;
        text-decoration: underline;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }
    .page-header h1 {
        margin: 0;
        font-weight: 600;
        flex: 1;
    }
    .back-home-btn {
        display: inline-block;
        background-color: #f9c5d1;
        color: #4e4351;
        padding: 8px 16px;
        font-weight: 500;
        font-size: 0.95rem;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s ease, transform 0.2s ease;
        white-space: nowrap;
    }
    .back-home-btn:hover {
        background-color: #010006;
        color: white;
        transform: translateY(-2px);
    }
    h1 {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 1.5rem 1rem 3rem;
    }
    .category-links {
        background: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .category-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }
    .category-dropdown-btn {
        background-color: #f9c5d1;
        color: #4e4351;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 1rem;
        border: 2px solid #f9c5d1;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .category-dropdown-btn:hover {
        background-color: #e8aebc;
        border-color: #e8aebc;
    }
    .category-dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 250px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        padding: 12px 0;
        z-index: 1000;
        border-radius: 0.5rem;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: 0.5rem;
    }
    .category-dropdown-content a {
        color: #4e4351;
        padding: 12px 20px;
        text-decoration: none;
        display: block;
        font-weight: 500;
        transition: background 0.2s ease;
    }
    .category-dropdown-content a:hover {
        background-color: #f9c5d1;
    }
    .category-dropdown-content a.active {
        background-color: #f9c5d1;
        font-weight: 700;
    }
    .category-dropdown-wrapper.active .category-dropdown-content {
        display: block;
    }
    .all-products-btn {
        background-color: #f9c5d1;
        color: #4e4351;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 1rem;
        border: 2px solid #f9c5d1;
        border-radius: 20px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    .all-products-btn:hover,
    .all-products-btn.active {
        background-color: #e8aebc;
        border-color: #e8aebc;
    }
    @media (max-width: 600px) {
        .category-links {
            flex-direction: column;
            gap: 0.5rem;
        }
        .category-dropdown-content {
            position: static;
            display: none;
            box-shadow: none;
            width: 100%;
            transform: none;
            margin-top: 0;
        }
        .category-dropdown-wrapper.active .category-dropdown-content {
            display: block;
        }
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 2rem;
        margin-top: 2em;
    }
    .product-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 6px 16px rgb(199 191 199 / 20%);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-6px);
    }
    .product-image {
        width: 100%;
        height: 220px;
        overflow: hidden;
    }
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }
    .product-card:hover img {
        transform: scale(1.05);
    }
    .product-info {
        padding: 1rem 1.5rem 1.5rem;
        text-align: center;
    }
    .product-info h3 {
        font-size: 1.25rem;
        margin-bottom: 0.4rem;
    }
    .categories {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    .category-tag {
        background-color: #f9c5d1;
        color: #4e4351;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-block;
    }
    .product-info span.price {
        font-weight: 700;
        font-size: 1.15rem;
        color: #4e4351;
        display: block;
        margin-bottom: 0.8rem;
    }
    /* WhatsApp Share Button */
    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background-color: #25D366;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    .share-btn:hover {
        background-color: #1ebe5d;
    }
    .share-icon-img {
        width: 16px;
        height: 16px;
    }
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
        .product-image {
            height: 180px;
        }
    }

    /* Price Filter Styles */
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: center;
        justify-content: center;
        margin: 2rem 0;
        background: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .filter-group label {
        font-weight: 600;
        color: #4e4351;
    }
    .price-filter-select {
        padding: 0.6rem 0.8rem;
        border: 2px solid #ddd;
        border-radius: 0.375rem;
        font-family: inherit;
        cursor: pointer;
        background-color: white;
        font-size: 1rem;
        transition: border 0.2s ease;
    }
    .price-filter-select:hover {
        border-color: #f9c5d1;
    }
    .price-filter-select:focus {
        border-color: #f9c5d1;
        outline: none;
        box-shadow: 0 0 5px rgba(249, 197, 209, 0.3);
    }
    .filter-input {
        padding: 0.5rem;
        border: 2px solid #ddd;
        border-radius: 0.375rem;
        width: 100px;
        font-family: inherit;
    }
    .filter-input:focus {
        border-color: #f9c5d1;
        outline: none;
    }
    .btn-filter {
        background-color: #f9c5d1;
        color: #4e4351;
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: 0.375rem;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s;
    }
    .btn-filter:hover {
        background-color: #c6a4b4;
    }
    .btn-reset {
        text-decoration: underline;
        color: #9a789b;
        font-size: 0.9rem;
        background: none;
        border: none;
        cursor: pointer;
    }
    @media (max-width: 600px) {
        .filter-container {
            flex-direction: column;
            gap: 1rem;
        }
        .filter-group {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        .category-dropdown-btn {
            width: 100%;
            text-align: left;
        }
        .category-dropdown-content {
            position: static;
            display: none;
            box-shadow: none;
            width: 100%;
            transform: none;
            margin-top: 0;
        }
        .category-dropdown-wrapper.active .category-dropdown-content {
            display: block;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1>Our Wedding Card Designs</h1>
        <a href="index.php" class="back-home-btn">← Back to Home</a>
    </div>

    <!-- Price Filter -->
    <form class="filter-container" method="GET" action="all-products.php">
        <!-- Preserve existing params -->
        <?php if ($categorySlug): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">
        <?php endif; ?>
        <?php if ($isCustom): ?>
            <input type="hidden" name="custom" value="1">
        <?php endif; ?>
        <?php if ($isGifting): ?>
            <input type="hidden" name="gifting" value="1">
        <?php endif; ?>

        <div class="filter-group">
            <label for="price_range">Price:</label>
            <select id="price_range" name="price_range" class="price-filter-select" onchange="this.form.submit()">
                <option value="">All Prices</option>
                <option value="0-100" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] === '0-100') ? 'selected' : ''; ?>>Below ₹100</option>
                <option value="100-500" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] === '100-500') ? 'selected' : ''; ?>>₹100 - ₹500</option>
                <option value="500-9999" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] === '500-9999') ? 'selected' : ''; ?>>₹500+</option>
            </select>
        </div>

        <!-- Categories in same form -->
        <div class="filter-group">
            <label for="category_select">Category:</label>
            <div class="category-dropdown-wrapper" onclick="toggleCategoryDropdown()">
                <button class="category-dropdown-btn" type="button">Select Category ▼</button>
                <div class="category-dropdown-content">
                    <a href="all-products.php?price_range=<?php echo isset($_GET['price_range']) ? urlencode($_GET['price_range']) : ''; ?>" 
                       class="<?php echo $categorySlug === '' ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="all-products.php?category=<?php echo urlencode($cat['slug']); ?><?php echo (isset($_GET['price_range']) && $_GET['price_range']) ? '&price_range=' . urlencode($_GET['price_range']) : ''; ?>"
                           class="<?php echo ($categorySlug === $cat['slug']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($minPrice !== null || $maxPrice !== null || $categorySlug): ?>
            <a href="all-products.php" class="btn-reset">Reset All</a>
        <?php endif; ?>
    </form>

    <!-- Products Grid -->
    <section class="products-grid">
        <?php if (empty($products)): ?>
            <p style="grid-column: 1/-1; text-align: center; color: #9a789b; font-size: 1.1rem;">
                No products found in this category.
            </p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a href="product.php?id=<?php echo $product['id']; ?>">
                        <div class="product-image">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" loading="lazy" />
                            <?php else: ?>
                                <div style="height: 220px; background: <?php echo $product['gradient'] ?? '#f9c5d1'; ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">
                                    No Image Available
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                        <div class="categories">
                            <?php 
                            $catNames = explode(', ', $product['category_name']);
                            foreach ($catNames as $cat): ?>
                                <span class="category-tag"><?php echo htmlspecialchars(trim($cat)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <span class="price">₹<?php echo number_format($product['price']); ?></span>

                        <?php
                        $shareMessage = "Check out this wedding card: " . $product['title'] . " - " . $siteURL . "/product.php?id=" . $product['id'];
                        $encodedShare = urlencode($shareMessage);
                        ?>
                        <a href="https://wa.me/?text=<?php echo $encodedShare; ?>" target="_blank" class="share-btn">
                            <img src="uploads/whatsappIcon.png" alt="Share" class="share-icon-img"> Share
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<script>
function toggleCategoryDropdown() {
    const dropdown = document.querySelector('.category-dropdown-wrapper');
    dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.category-dropdown-wrapper');
    const btn = document.querySelector('.category-dropdown-btn');
    if (!dropdown.contains(event.target) && event.target !== btn) {
        dropdown.classList.remove('active');
    }
});

// Close dropdown when a category is selected
document.querySelectorAll('.category-dropdown-content a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelector('.category-dropdown-wrapper').classList.remove('active');
    });
});
</script>
</body>
</html>
