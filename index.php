<?php
require_once 'admin/config.php';
require_once 'admin/functions.php';

// Fetch random bestsellers for testimonials
$testimonials = [];

// Array of possible reviews
$possibleReviews = [
    '“The card design was stunning and just perfect for our big day!”',
    '“Unique and modern invites! All our guests were impressed.”',
    '“Beautiful craftsmanship and excellent quality. Highly recommend!”',
    '“Our wedding cards turned out even better than we imagined!”',
    '“Fast delivery and amazing customer service. Loved it!”',
    '“Elegant and classy designs that matched our theme perfectly.”',
    '“Worth every penny! The details were exquisite.”',
    '“Professional and creative – exactly what we needed.”'
];

$possibleUsers = [
    '— Happy Couple',
    '— Satisfied Customer',
    '— Newlyweds',
    '— Bride & Groom',
    '— Wedding Planner',
    '— Event Organizer'
];

// Fetch random bestsellers
$stmt = $pdo->query("SELECT title, image_url FROM designs WHERE is_bestseller = 1 AND image_url IS NOT NULL ORDER BY RAND() LIMIT 4");
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $testimonials[] = [
        'image' => $row['image_url'],
        'title' => $row['title'],
        'review' => $possibleReviews[array_rand($possibleReviews)],
        'user' => $possibleUsers[array_rand($possibleUsers)]
    ];
}

// If less than 4, fill with new arrivals
if (count($testimonials) < 4) {
    $needed = 4 - count($testimonials);
    $stmt2 = $pdo->query("SELECT title, image_url FROM designs WHERE is_new_arrival = 1 AND image_url IS NOT NULL ORDER BY RAND() LIMIT $needed");
    $rows2 = $stmt2->fetchAll();
    foreach ($rows2 as $row) {
        $testimonials[] = [
            'image' => $row['image_url'],
            'title' => $row['title'],
            'review' => $possibleReviews[array_rand($possibleReviews)],
            'user' => $possibleUsers[array_rand($possibleUsers)]
        ];
    }
}

// Shuffle to vary order
shuffle($testimonials);

// Limit to 4 testimonials
$testimonials = array_slice($testimonials, 0, 4);


// Get settings
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Browse our elegant wedding card collection featuring traditional, modern, and luxury designs for your special day.">
  <title><?php echo htmlspecialchars($settings['site_title'] ?? 'HouseOfCards'); ?> | Premium Wedding Invitation Collection</title>
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      /* Pastel Color Palette */
      --color-primary: #f9c5d1;
      --color-primary-light: #f2d4e1;
      --color-secondary: #c6a4b4;
      --color-accent: #9a789b;
      --color-text: #4e4351;
      --color-text-light: #8a7f8d;
      --color-background: #fff9fb;
      --color-light: #fff;
      --color-dark: #2c2831;
      --color-gold: #d9b08c;
      
      /* Typography */
      --font-heading: 'Playfair Display', serif;
      --font-body: 'Poppins', sans-serif;
    }

    /* Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-body);
      color: var(--color-text);
      background-color: var(--color-background);
      line-height: 1.6;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: var(--font-heading);
      font-weight: 600;
      color: var(--color-dark);
      margin-bottom: 1rem;
    }

    h1 {
      font-size: 2.5rem;
    }

    h2 {
      font-size: 2rem;
    }

    h3 {
      font-size: 1.5rem;
    }

    a {
      text-decoration: none;
      color: var(--color-accent);
      transition: color 0.3s ease;
    }

    a:hover {
      color: var(--color-primary);
    }

    img {
      max-width: 100%;
      height: auto;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* Header */
    header {
      background-color: var(--color-light);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1rem 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-family: var(--font-heading);
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--color-accent);
      flex-shrink: 0;
    }

    .nav-links {
      display: flex;
      list-style: none;
      align-items: center;
      margin: 0 auto;
    }

    .nav-links li {
      margin: 0 1.5rem;
      position: relative;
    }

    .nav-icons {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .nav-icons button {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 0.25rem;
      transition: background-color 0.3s ease;
    }

    .nav-icons button:hover {
      background-color: var(--color-primary-light);
    }

    .nav-links a {
      /* position: relative;
      font-weight: 500;
      color: var(--color-text);
      display: flex;
      align-items: center;
      padding: 0.5rem 0;
      white-space: nowrap;     /* prevent "Wedding" and "Invitations" from breaking */
  /*display: inline-flex;    /* keeps text & arrow side-by-side */
/*  align-items: center;     /* vertical centering */
 /* gap: 6px; */
 position: relative;
  font-weight: 500;
  color: var(--color-text);
  display: inline-flex;  /* ensures text + arrow are in same row */
  align-items: center;
  white-space: nowrap;   /* prevents breaking to next line */
  gap: 6px;              /* space between text and arrow */
  padding: 0.5rem 0;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 0;
      height: 2px;
      background-color: var(--color-primary);
      transition: width 0.3s ease;
    }

    .nav-links a:hover::after,
    .nav-links a.active::after {
      width: 100%;
    }

    /* Dropdown Styles */
    .dropdown {
      position: relative;
    }

    .dropdown-toggle {
      cursor: pointer;
    }

    .dropdown-toggle::after {
      content: '▼';
      font-size: 0.7rem;
      margin-left: 0.5rem;
      transition: transform 0.3s ease;
    }

    .dropdown.active .dropdown-toggle::after {
      transform: rotate(180deg);
    }

    .dropdown-menu {
      position: absolute;
      top: 100%;
      left: 0;
      background-color: var(--color-light);
      min-width: 200px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border-radius: 0.5rem;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .dropdown.active .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-menu a {
      display: block;
      padding: 0.75rem 1rem;
      color: var(--color-text);
      font-size: 0.9rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-menu a:last-child {
      border-bottom: none;
    }

    .dropdown-menu a:hover {
      background-color: var(--color-primary-light);
      color: var(--color-dark);
    }

    .dropdown-menu a::after {
      display: none;
    }
    /* Mobile Navigation Toggle */
    .mobile-menu-toggle {
      display: none;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0.5rem;
      z-index: 1001;
      width: 40px;
      height: 40px;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 6px;
    }

    .mobile-menu-toggle span {
      width: 24px;
      height: 2px;
      background-color: var(--color-dark);
      transition: all 0.3s ease;
      display: block;
    }

    .mobile-menu-toggle.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 6px);
    }
    .mobile-menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    .mobile-menu-toggle.active span:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -6px);
    }

    /* Responsive Styles */
    @media (max-width: 968px) {
      .mobile-menu-toggle {
        display: flex;
      }

      .nav-links {
        position: fixed;
        top: 0;
        right: -100%;
        height: 100vh;
        width: 80%;
        max-width: 400px;
        background-color: var(--color-light);
        flex-direction: column;
        align-items: flex-start;
        padding: 5rem 2rem 2rem;
        box-shadow: -5px 0 15px rgba(0,0,0,0.1);
        transition: right 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
      }

      .nav-links.active {
        right: 0;
        display: flex;
      }

      .nav-links li {
        width: 100%;
        margin: 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
      }

      .nav-links a {
        display: flex;
        justify-content: space-between;
        width: 100%;
        padding: 1rem 0;
        font-size: 1.1rem;
      }
      
      .nav-links a::after {
        display: none;
      }

      /* Mobile Dropdown */
      .dropdown-menu {
        position: static;
        box-shadow: none;
        transform: none;
        opacity: 1;
        visibility: visible;
        display: none;
        background-color: #f9f9f9;
        margin-left: -2rem;
        margin-right: -2rem;
        padding-left: 3rem;
        padding-right: 2rem;
        width: auto;
        min-width: unset;
        border-radius: 0;
      }

      .dropdown.active .dropdown-menu {
        display: block;
        animation: slideDown 0.3s ease;
      }
      
      .dropdown.active .dropdown-toggle::after {
          transform: rotate(180deg);
      }

      @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
      }
    }


    /* Tablet/Smaller Desktop Adjustments */
    @media (max-width: 768px) {
      .hero {
         min-height: 60vh;
         height: auto;
         padding-top: 4rem; /* For fixed header */
      }
      
      .hero h1 {
        font-size: 2rem;
      }

      .scroll-btn {
        display: none; /* Hide scroll buttons on mobile */
      }
      
      .card-scroll-wrapper {
        padding-right: 20px; /* Add breathing room for scroll */
      }
    }

    /* Hero Section */
    .hero {
      height: 76vh;
      position: relative;
      overflow: hidden;
    }

    .hero-slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 1s ease-in-out;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      background-size: cover;
      background-position: center;
       z-index: 0;       /* base layer for slide */
  overflow: hidden; /* clip overlay */
  /* establish stacking context */
  isolation: isolate;
    }

    .hero-slide.active {
      opacity: 1;
    }

    .hero-slide::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.2);
      z-index: 0;              /* put overlay behind .hero-content */
  pointer-events: none;    /* allow clicks to pass through */
    }

    .hero-content {
      position: relative;
      z-index: 1;
      color: white;
      max-width: 800px;
      padding: 0 20px;
    }

    .hero h1 {
      font-size: 3rem;
      margin-bottom: 1.5rem;
      color: white;
    }

    .hero p {
      font-size: 1.25rem;
      margin-bottom: 2rem;
    }

    .btn {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      border-radius: 0.375rem;
      font-weight: 500;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background-color: var(--color-primary);
      color: var(--color-dark);
    }

    .btn-primary:hover {
      background-color: var(--color-accent);
      color: white;
    }

    /* Card Sections */
    .cards-section {
      padding: 4rem 0;
    }

    .section-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    /* Loading and Error States */
    .loading {
      text-align: center;
      padding: 3rem;
      color: var(--color-text-light);
    }

    .error-message {
      text-align: center;
      padding: 3rem;
      color: var(--color-text-light);
    }

    /* Admin Link */
    .admin-link {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: var(--color-accent);
      color: white;
      padding: 10px 15px;
      border-radius: 25px;
      font-size: 0.9rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .admin-link:hover {
      background-color: var(--color-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    /* Horizontal Scrollable Card Container */
    .card-scroll-container {
      position: relative;
      margin-bottom: 4rem;
    }

    .card-scroll-wrapper {
      overflow-x: auto;
      overflow-y: hidden;
      scrollbar-width: none;
      -ms-overflow-style: none;
      padding: 1rem 0;
    }

    .card-scroll-wrapper::-webkit-scrollbar {
      display: none;
    }

    .card-grid-horizontal {
      display: flex;
      gap: 2rem;
      padding: 0 1rem;
      min-width: max-content;
    }

    .card {
      background: white;
      border-radius: 0.5rem;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      min-width: 280px;
      max-width: 280px;
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .card-image {
      position: relative;
      padding-top: 75%; /* 4:3 aspect ratio */
      overflow: hidden;
    }

    .card-image img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .card:hover .card-image img {
      transform: scale(1.05);
    }

    .card-content {
      padding: 1.5rem;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0.75rem;
    }

    .card-title {
      font-size: 1.25rem;
      margin-bottom: 0;
    }

    .card-price {
      color: var(--color-gold);
      font-weight: 600;
    }

    .card-description {
      color: var(--color-text-light);
      font-size: 0.875rem;
      margin-bottom: 1rem;
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .rating {
      display: flex;
      align-items: center;
    }

    .star {
      color: #f6c416;
      font-size: 0.875rem;
    }

    .rating-text {
      margin-left: 0.25rem;
      font-size: 0.75rem;
      color: var(--color-text-light);
    }

    .card-category {
      font-size: 0.75rem;
      padding: 0.25rem 0.75rem;
      background-color: var(--color-primary-light);
      color: var(--color-text);
      border-radius: 9999px;
    }

    /* Scroll Navigation Buttons */
    .scroll-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: var(--color-light);
      border: 1px solid var(--color-secondary);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .scroll-btn:hover {
      background-color: var(--color-primary);
      border-color: var(--color-primary);
    }

    .scroll-btn-left {
      left: -20px;
    }

    .scroll-btn-right {
      right: -20px;
    }

    .scroll-btn svg {
      width: 16px;
      height: 16px;
      stroke: var(--color-text);
    }

    /* Contact Section */
    .contact {
      background-color: var(--color-light);
      padding: 4rem 0;
    }

    .contact-content {
      max-width: 600px;
      margin: 0 auto;
      background: white;
      padding: 3rem;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--color-dark);
    }

    .form-input,
    .form-textarea {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid var(--color-secondary);
      border-radius: 0.375rem;
      font-family: var(--font-body);
      background: white;
      transition: border-color 0.3s ease;
    }

    .form-input:focus,
    .form-textarea:focus {
      border-color: var(--color-accent);
      outline: none;
    }

    .form-textarea {
      min-height: 120px;
      resize: vertical;
    }
    .btn-submit {
  flex: 1 1 200px; /* Responsive width */
  margin: 0;       /* Remove default margin */
  min-width: 140px;
  }


    .btn-submit {
      background-color: var(--color-accent);
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 0.375rem;
      cursor: pointer;
      font-weight: 500;
      font-size: 1rem;
      transition: all 0.3s ease;
      width: 100%;
    }

    .btn-submit:hover {
      background-color: var(--color-dark);
      transform: translateY(-2px);
    }

    .testimonials-slider {
      position: relative;
      width: 100%;
      min-height: 350px; /* Increased slightly for mobile text wrapping */
    }
    
    @media (max-width: 768px) {
        .testimonials-slider {
            min-height: 400px; /* More height for stacked content on mobile */
        }
    }
.testimonial {
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  animation: fadeIn 0.7s;
}
.testimonial.active {
  display: flex;
}
.testimonial-img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  margin: 0 auto 1.2rem;
  box-shadow: 0 4px 18px rgba(0,0,0,0.09);
}
.testimonial-text p {
  font-size: 1.08rem;
  color: #9a789b;
  margin-bottom: 0.75rem;
}
.testimonial-text h4 {
  font-size: 1rem;
  color: #4e4351;
  font-weight: 600;
}
.testimonial-nav {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 1.2rem;
}
.nav-btn {
  width: 13px;
  height: 13px;
  border-radius: 50%;
  background: #f9c5d1;
  border: none;
  cursor: pointer;
  transition: background 0.3s;
}
.nav-btn.active,
.nav-btn:hover {
  background: #c6a4b4;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.97);}
  to { opacity: 1; transform: scale(1);}
}

    /* Footer */
    footer {
      background-color: var(--color-dark);
      color: white;
      text-align: center;
      padding: 2rem 0;
    }
    /* Footer Styling */
footer {
  background-color: var(--color-dark);
  color: white;
  padding-top: 3rem;
  margin-top: 3rem;
}

.footer-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 2rem;
  padding: 0 1.5rem;
}

.footer-column h3 {
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.footer-column p, 
.footer-column ul li a {
  color: #ddd;
  font-size: 0.95rem;
  line-height: 1.5;
}

.footer-column ul {
  list-style: none;
  padding: 0;
}

.footer-column ul li {
  margin-bottom: 0.5rem;
}

.footer-column ul li a:hover {
  color: var(--color-primary);
}

.social-icons a img {
  width: 28px;
  height: 28px;
  margin-right: 10px;
  transition: transform 0.3s ease;
}

.social-icons a img:hover {
  transform: scale(1.1);
}

.footer-bottom {
  text-align: center;
  padding: 1rem;
  margin-top: 2rem;
  border-top: 1px solid rgba(255,255,255,0.1);
  font-size: 0.85rem;
  color: #aaa;
}

@media (max-width: 768px) {
  .footer-container {
    text-align: center;
  }
  .social-icons {
    justify-content: center;
  }
}



    /* Responsive Design */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .contact-content {
        padding: 2rem;
        margin: 0 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <!--<header>
    <div class="container">
      <nav class="nav-container">-->
        <!-- <div class="logo"><?php echo htmlspecialchars($settings['site_title'] ?? 'House Of Cards'); ?></div> -->
         <!--<div class="logo">
  <a href="index.php">
    <img src="Cards/logo2.png" alt="House of Cards Logo" style="height:80px; display:block;">
  </a>
</div>
        <ul class="nav-links">
          <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
          <li><a href="#home" class="active">Home</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
              <span class="dropdown-text">Wedding Invitations</span>
              <span class="dropdown-icon">▼</span>
            </a>
            <div class="dropdown-menu">
              <a href="all-products.php?category=hindu">Hindu Wedding Invitations</a>
              <a href="all-products.php?category=muslim">Muslim Wedding Invitations</a>
              <a href="all-products.php?category=christian">Christian Wedding Invitations</a>
              <a href="all-products.php?category=general">General Wedding Invitation</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
              <span class="dropdown-text">By Occassion</span>
              <span class="dropdown-icon">▼</span>
            </a>
            <div class="dropdown-menu">
              <a href="all-products.php?category=wedding-byocc">Wedding</a>
              <a href="all-products.php?category=engagement-byocc">Engagement</a>
              <a href="all-products.php?category=birthaybyocc">Birthday</a>
              <a href="all-products.php?category=housewarmingbyocc">Housewarming</a>
              <a href="all-products.php?category=brahmopadesham-byocc">Brahmopadesham/Upanayanom</a>
              <a href="all-products.php?category=naming-ceremony">Naming Ceremony</a>
              <a href="all-products.php?category=baptism-byocc">Holy Communion & Baptism</a>
            </div>
          </li>
           <li class="dropdown">
            <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
              <span class="dropdown-text">Types of Designs</span>
              <span class="dropdown-icon">▼</span>
            </a>
            <div class="dropdown-menu">
              <a href="all-products.php?category=economy-bydes">Economy</a>
              <a href="all-products.php?category=mdfbydes">MDF</a>
              <a href="all-products.php?category=hardbackbydes">Hardback/Padded</a>
              <a href="all-products.php?category=lasercoat-bydes">Lasercot</a>
              <a href="all-products.php?category=acrylic-bydes">Acrylic </a>
              <a href="all-products.php?category=vellum-paperbydes">Vellum Paper</a>
              <a href="all-products.php?category=scrollcards-bydes">Scroll Cards</a>
              <a href="all-products.php?category=semibox-bydes">Semi-Box Invitation</a>
              <a href="all-products.php?category=single-bydes">Single Cards</a>
              <a href="all-products.php?category=premium-invitation-bydes">Premium Invitations</a>
            </div>
          </li>
          <li><a href="all-products.php?category=custom">Custom Invitations</a></li>
          <li><a href="all-products.php?category=gifting">Gifting Accessories</a></li>
        </ul>
      </nav>
    </div>
  </header>-->

  <header>
  <div class="container">
    <nav class="nav-container">
      <div class="logo">
        <a href="index.php">
          <img src="Cards/logo2.png" alt="House of Cards Logo" style="height:80px; display:block;">
        </a>
      </div>

      <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <ul class="nav-links">
        <li><a href="#home" class="active">Home</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
            <span class="dropdown-text">Wedding Invitations</span>
            <span class="dropdown-icon">▼</span>
          </a>
          <div class="dropdown-menu">
            <a href="all-products.php?category=hindu">Hindu Wedding Invitations</a>
            <a href="all-products.php?category=muslim">Muslim Wedding Invitations</a>
            <a href="all-products.php?category=christian">Christian Wedding Invitations</a>
            <a href="all-products.php?category=general">General Wedding Invitation</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
            <span class="dropdown-text">By Occassion</span>
            <span class="dropdown-icon">▼</span>
          </a>
          <div class="dropdown-menu">
            <a href="all-products.php?category=wedding-byocc">Wedding</a>
            <a href="all-products.php?category=engagement-byocc">Engagement</a>
            <a href="all-products.php?category=birthaybyocc">Birthday</a>
            <a href="all-products.php?category=housewarmingbyocc">Housewarming</a>
            <a href="all-products.php?category=brahmopadesham-byocc">Brahmopadesham/Upanayanom</a>
            <a href="all-products.php?category=naming-ceremony">Naming Ceremony</a>
            <a href="all-products.php?category=baptism-byocc">Holy Communion & Baptism</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
            <span class="dropdown-text">Types of Designs</span>
            <span class="dropdown-icon">▼</span>
          </a>
          <div class="dropdown-menu">
            <a href="all-products.php?category=economy-bydes">Economy</a>
            <a href="all-products.php?category=mdfbydes">MDF</a>
            <a href="all-products.php?category=hardbackbydes">Hardback/Padded</a>
            <a href="all-products.php?category=lasercoat-bydes">Lasercot</a>
            <a href="all-products.php?category=acrylic-bydes">Acrylic </a>
            <a href="all-products.php?category=vellum-paperbydes">Vellum Paper</a>
            <a href="all-products.php?category=scrollcards-bydes">Scroll Cards</a>
            <a href="all-products.php?category=semibox-bydes">Semi-Box Invitation</a>
            <a href="all-products.php?category=single-bydes">Single Cards</a>
            <a href="all-products.php?category=premium-invitation-bydes">Premium Invitations</a>
          </div>
        </li>
       <li>
  <a href="all-products.php?custom=1" class="<?php echo (isset($_GET['custom']) && $_GET['custom'] == 1) ? 'active' : ''; ?>">Custom Invitations</a>
</li>
<li>
  <a href="all-products.php?gifting=1" class="<?php echo (isset($_GET['gifting']) && $_GET['gifting'] == 1) ? 'active' : ''; ?>">Gifting Accessories</a>
</li>
      </ul>
    </nav>
  </div>
</header>


  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="hero-slide active" style="background-image: url('Cards/HomePage.jpg');
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;">

  <!-- linear-gradient(135deg, #f9c5d1 0%, #9a789b 100%), -->
      <div class="hero-content">
        <h1>Elegant Wedding Invitations</h1>
        <p>Create the perfect first impression for your special day with our stunning collection of wedding cards</p>
        <!-- <a href="all-products.php" class="btn btn-primary">Click here to view all Products</a> -->
      </div>
    </div>
    <div class="hero-slide" style="background-image: url('Cards/Hero2.jpg');
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;">
      <div class="hero-content">
        <h1>Luxury Designs</h1>
        <p>Handcrafted with premium materials and attention to detail for your once-in-a-lifetime celebration</p>
        <!-- <a href="all-products.php?category=Luxury" class="btn btn-primary">Click here to view all Products</a> -->
      </div>
    </div>
    <div class="hero-slide" style="background-image: url('Cards/Hero3.jpg');
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;">
      <div class="hero-content">
        <h1>Custom Solutions</h1>
        <p>Personalize every detail to match your unique style and wedding theme</p>
        <!-- <a href="all-products.php" class="btn btn-primary">Click here to view all Products</a> -->
      </div>
    </div>
  </section>

  <!-- Cards Sections -->
  <main class="cards-section">
    <div class="container">
      <!-- Bestsellers Section -->
      <section class="card-scroll-container">
        <div class="section-header">
          <h2>Bestsellers</h2>
          <p>Our most popular wedding invitation designs</p>
        </div>
        <div class="card-scroll-wrapper" id="bestsellers-scroll">
          <div id="bestsellers-cards" class="card-grid-horizontal">
            <!-- Cards will be loaded dynamically -->
            <div class="loading">Loading bestsellers...</div>
          </div>
        </div>
        <button class="scroll-btn scroll-btn-left" onclick="scrollCards('bestsellers', 'left')">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button class="scroll-btn scroll-btn-right" onclick="scrollCards('bestsellers', 'right')">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </section>

      <!-- New Arrivals Section -->
      <section class="card-scroll-container">
        <div class="section-header">
          <h2>New Arrivals</h2>
          <p>Fresh designs just added to our collection</p>
        </div>
        <div class="card-scroll-wrapper" id="new-arrivals-scroll">
          <div id="new-arrivals-cards" class="card-grid-horizontal">
            <!-- Cards will be loaded dynamically -->
            <div class="loading">Loading new arrivals...</div>
          </div>
        </div>
        <button class="scroll-btn scroll-btn-left" onclick="scrollCards('new-arrivals', 'left')">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button class="scroll-btn scroll-btn-right" onclick="scrollCards('new-arrivals', 'right')">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </section>
    </div> 
  </main>

  <!-- Contact Section -->
  <!-- Contact Section -->
<!-- <section id="contact" class="contact">
  <div class="container">
    <div class="contact-content">
      <div class="section-header">
        <h2>Get in Touch</h2>
        <p>Let's create the perfect invitation for your special day</p>
      </div>
      <form id="contact-form" method="POST" action="send_email.php" autocomplete="off">
        <div class="form-group">
          <label for="name" class="form-label">Your Name</label>
          <input type="text" id="name" name="name" class="form-input" required maxlength="50">
        </div>
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-input" required maxlength="80">
        </div>
        <div class="form-group">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" id="phone" name="phone" class="form-input" required maxlength="16" pattern="[0-9+\-() ]{8,16}">
        </div>
        <div class="form-group">
          <label for="message" class="form-label">Message</label>
          <textarea id="message" name="message" class="form-textarea" minlength="10" maxlength="500" placeholder="Tell us about your wedding theme, preferred style, or any specific requirements..." required></textarea>
        </div>

      <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button type="button" class="btn-submit" id="send-whatsapp">Contact via WhatsApp</button>
        <button type="submit" class="btn-submit" id="send-email">Send Message via Email</button>
      </div>

      </form>
      <div id="form-feedback" style="margin-top: 1rem;"></div>
    </div>
  </div>
</section>-->
<!-- Testimonials Section -->
<section id="testimonials" class="contact">
  <div class="container">
    <div class="contact-content">
      <div class="section-header">
        <h2>Customer Testimonials</h2>
        <p>What our couples say about our cards</p>
      </div>
      <div class="testimonials-slider">
        <?php foreach ($testimonials as $i => $t): ?>
        <div class="testimonial<?php if($i===0) echo ' active'; ?>">
          <img src="<?php echo htmlspecialchars($t['image']); ?>" alt="Wedding Card" class="testimonial-img">
          <div class="testimonial-text">
            <p><?php echo htmlspecialchars($t['review']); ?></p>
            <h4><?php echo htmlspecialchars($t['user']); ?></h4>
            <div style="font-size:0.93em;color:#9a789b;"><?php echo htmlspecialchars($t['title']); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="testimonial-nav">
        <?php foreach ($testimonials as $i => $t): ?>
        <button onclick="showTestimonial(<?php echo $i; ?>)" class="nav-btn"></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


  <!-- Footer -->
  <footer>
  <div class="footer-container">
    <div class="footer-column">
      <h3>About Us</h3>
      <p>House of Cards – Wedding Cards & Invitations
Based in Bangalore, House of Cards offers 3000+ wedding invitation designs starting at just ₹7. From traditional to modern styles, we specialize in custom printing, gold foiling, embossing, and laser-cut designs. Serving clients across India and worldwide, we’re your one-stop destination for wedding cards, stationery, and custom gifting solutions.</p>
    </div>

    <div class="footer-column">
      <h3>Quick Links</h3>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="all-products.php">All Products</a></li>
        <li><a href="all-products.php?custom=1">Custom Invitations</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Contact Info</h3>
      <p>Email: hello@houseofcards.in</p>
      <p>Phone: +91 6366329292</p>
      <p>Location: <br>Swanky Inc ( House of  Cards)
19 Anjinappa complex Hennur Bagalur Road Kothanur Post (Above TVS Showroom) Bengaluru 560077-6366329292</p>
    </div>

    <div class="footer-column">
      <h3>Follow Us</h3>
      <div class="social-icons">
        <a href="https://wa.me/916366329292" target="_blank">
          <img src="Cards/whatsapp.png" alt="WhatsApp">
        </a>
        <a href="https://www.instagram.com/houseofcards_india/" target="_blank">
          <img src="Cards/insta.jpg" alt="Instagram">
        </a>
        <a href="https://www.facebook.com/share/1B2HmVYfrC/" target="_blank">
          <img src="Cards/facebook.png" alt="Facebook">
        </a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; 2024 <?php echo htmlspecialchars($settings['site_title'] ?? 'House Of Cards'); ?>. All rights reserved.</p>
  </div>
</footer>


  <!-- Admin Link -->
      <?php if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
    <a href="admin/" class="admin-link">Admin Panel</a>
    <?php endif; ?>

  <script src="script.js"></script>
  <script>

    // Additional navigation functions
    function toggleSearch() {
      // You can implement search functionality here
      alert('Search functionality - to be implemented');
    }

    function showLoginModal() {
      // You can implement user login here
      alert('User login - to be implemented');
    }

    function showCart() {
      // You can implement cart functionality here
      alert('Shopping cart - to be implemented');
    }
  </script>
  <script>
    // Load products from PHP backend
    async function loadProducts() {
      try {
        const response = await fetch('api/products.php');
        const data = await response.json();
        
        if (data.success) {
          displayCards('bestsellers', data.products.filter(p => p.is_bestseller));
          displayCards('new-arrivals', data.products.filter(p => p.is_new_arrival));
          // displayCards('traditional', data.products.filter(p => p.category === 'Traditional'));
        } else {
          showError('Failed to load products');
        }
      } catch (error) {
        console.error('Error loading products:', error);
        showError('Error connecting to server');
      }
    }

    function displayCards(sectionId, products) {
      const container = document.getElementById(`${sectionId}-cards`);
      
      if (products.length === 0) {
        container.innerHTML = '<div class="error-message">No products available in this category</div>';
        return;
      }

      container.innerHTML = products.map(product => `
        <a href="product.php?id=${product.id}" class="card" style="text-decoration:none;color:inherit;">
          <div class="card-image">
            ${product.image_url ? 
              `<img src="${product.image_url}" alt="${product.title}" loading="lazy">` :
              `<div style="background: ${product.gradient || 'linear-gradient(135deg, #f9c5d1 0%, #c6a4b4 100%)'}; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-family: var(--font-heading); font-size: 1.2rem;">${product.title}</div>`
            }
          </div>
          <div class="card-content">
            <div class="card-header">
              <h3 class="card-title">${product.title}</h3>
              <span class="card-price"><?php echo $settings['currency'] ?? '₹'; ?>${product.price}</span>
            </div>
            <p class="card-description">${product.description}</p>
            <div class="card-footer">
              <div class="rating">
                <span class="star">★★★★${product.rating >= 5 ? '★' : '☆'}</span>
                <span class="rating-text">(${product.rating_count || 0})</span>
              </div>
              <span class="card-category">${product.category}</span>
            </div>
          </div>
        </a>
      `).join('');
    }

    function showError(message) {
      const sections = ['bestsellers', 'new-arrivals', 'traditional'];
      sections.forEach(section => {
        const container = document.getElementById(`${section}-cards`);
        container.innerHTML = `<div class="error-message">${message}</div>`;
      });
    }

  document.addEventListener('DOMContentLoaded', function() {
        loadProducts(); // ✅ runs when page finishes loading

  const whatsappBtn = document.getElementById('send-whatsapp');
  whatsappBtn.addEventListener('click', function() {
    const name    = document.getElementById('name').value.trim();
    const email   = document.getElementById('email').value.trim();
    const phone   = document.getElementById('phone').value.trim();
    const message = document.getElementById('message').value.trim();

    if (!name || !email || !phone || !message) {
      alert('Please fill all fields.');
      return;
    }

    const whatsappNumber = '9082676956'; // REPLACE with real number!
    const whatsappMsg =
      `Hi! My name is ${name}. Email: ${email}, Phone: ${phone}. Message: ${message}`;
    const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMsg)}`;
    window.open(whatsappURL, '_blank');
  });
});

function toggleMobileMenu() {
    document.querySelector('.nav-links').classList.toggle('active');
    document.querySelector('.mobile-menu-toggle').classList.toggle('active');
}

function toggleDropdown(event) {
    event.preventDefault();
    const dropdown = event.target.closest('.dropdown');
    dropdown.classList.toggle('active');
}

// Close mobile menu when clicking a link (excluding dropdown toggles)
document.addEventListener('DOMContentLoaded', function() {
    // Select all links EXCEPT the dropdown toggles
    const navLinks = document.querySelectorAll('.nav-links a:not(.dropdown-toggle)');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Close the menu
            document.querySelector('.nav-links').classList.remove('active');
            
            // Also reset the hamburger icon animation
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            if (toggleBtn) {
                toggleBtn.classList.remove('active');
            }
        });
    });
});


  </script>

  <script>
let currentTestimonial = 0;
const testimonials = document.querySelectorAll('.testimonial');
const navBtns = document.querySelectorAll('.nav-btn');
let testimonialInterval = null;

function showTestimonial(idx) {
  testimonials.forEach((t, i) => {
    t.classList.toggle('active', i === idx);
    navBtns[i].classList.toggle('active', i === idx);
  });
  currentTestimonial = idx;
}

function nextTestimonial() {
  showTestimonial((currentTestimonial + 1) % testimonials.length);
}

testimonialInterval = setInterval(nextTestimonial, 4000); // Change slide every 4s

navBtns.forEach((btn, idx) => {
  btn.addEventListener('click', () => {
    showTestimonial(idx);
    clearInterval(testimonialInterval);
    testimonialInterval = setInterval(nextTestimonial, 4000);
  });
});

// Initialize first testimonial
showTestimonial(0);
</script>
</body>
</html>