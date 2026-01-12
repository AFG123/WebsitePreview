<?php
require_once 'admin/config.php';
require_once 'admin/functions.php';

// WhatsApp number - in international format without + or leading 0
$whatsappNumber = '916366329292'; // change to owner’s number

// Get product id from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product from DB
$stmt = $pdo->prepare(
    "SELECT d.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_name, GROUP_CONCAT(c.slug SEPARATOR ', ') AS category_slug
     FROM designs d
     LEFT JOIN product_categories pc ON d.id = pc.product_id
     LEFT JOIN categories c ON pc.category_id = c.id
     WHERE d.id = ?
     GROUP BY d.id"
);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found.";
    exit;
}

// Create encoded WhatsApp message
$message = "Hi, I am interested in the design: " . $product['title'] . " (Product ID: " . $product['id'] . ")";
$encodedMessage = urlencode($message);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($product['title']); ?> | House Of Cards</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fff7fc;
        margin: 0;
        color: #4e4351;
    }
    .container {
        max-width: 1200px;
        margin: auto;
        padding: 1.5rem;
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
        margin-bottom: 1.5rem;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .back-home-btn:hover {
        background-color: #e8aebc;
        transform: translateY(-2px);
    }
    .product-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 6px 16px rgba(0,0,0,0.05);
    }
    .product-image img {
        width: 100%;
        height: auto;
        border-radius: 1rem;
        object-fit: cover;
    }
    .product-details h1 {
        margin-top: 0;
        font-weight: 600;
    }
    .categories {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .category-label {
        color: #9a789b;
        font-weight: 500;
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
    .price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4e4351;
        margin-bottom: 1rem;
    }
    .description {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 2rem;
    }
    .whatsapp-share-btn {
    display: inline-flex;
    align-items: center;
    background-color: #25D366;
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

.whatsapp-share-btn:hover {
    background-color: #1ebe5d;
}

.share-icon {
    display: inline-block;
    margin-top: 5px;
}

.share-icon img, .share-icon-img {
    width: 20px;
    height: 24px;
    vertical-align: middle;
}

    .whatsapp-btn {
        display: inline-flex;
        align-items: center;
        background-color: #25D366;
        color: white;
        padding: 12px 20px;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1.1rem;
        transition: background-color 0.3s ease;
    }
    .whatsapp-btn img {
        height: 24px;
        margin-right: 8px;
    }
    .whatsapp-btn:hover {
        background-color: #1ebe5d;
    }
    @media (max-width: 768px) {
        .product-wrapper {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

<div class="container">
    <a href="all-products.php" class="back-home-btn">← Back to All Products</a>

    <div class="product-wrapper">
        <div class="product-image">
            <?php if (!empty($product['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
            <?php else: ?>
                <div style="height:300px;background:<?php echo $product['gradient'] ?? '#f9c5d1'; ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;border-radius:1rem;">
                    No Image Available
                </div>
            <?php endif; ?>
        </div>

        <div class="product-details">
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            <div class="categories">
                <span class="category-label">Categories:</span>
                <?php 
                $catNames = explode(', ', $product['category_name']);
                foreach ($catNames as $cat): ?>
                    <span class="category-tag"><?php echo htmlspecialchars(trim($cat)); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="price">₹<?php echo number_format($product['price']); ?></div>
            <div class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>


            <?php
            $siteURL = "https://yourwebsite.com"; // Change to your real site URL
            $shareMessage = "Check out this wedding card design: " . $product['title'] . " - " . $siteURL . "/product.php?id=" . $product['id'];
            $encodedShare = urlencode($shareMessage);
            ?>
            <a href="https://wa.me/?text=<?php echo $encodedShare; ?>" target="_blank" class="whatsapp-share-btn">
                <img src="uploads/whatsappIcon.png" alt="WhatsApp" class="share-icon-img">
                Share on WhatsApp
            </a>


            <!-- WhatsApp Order Button -->
            <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=<?php echo $encodedMessage; ?>" target="_blank" class="whatsapp-btn">
                <img src="uploads/whatsappIcon.png" alt="WhatsApp">
                Order via WhatsApp
            </a>
        </div>
    </div>
</div>

</body>
</html>
