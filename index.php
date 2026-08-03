<?php
/**
 * Cooking Blog Homepage – Single-File Version
 * 
 * Setup:
 * 1. Create a MySQL database named 'cooking_blog'
 * 2. Run the SQL schema (see comment at the bottom of this file)
 * 3. Update database credentials below if needed
 * 4. Place this file in your web server root (e.g., htdocs)
 * 5. Add recipe images in an 'images/' subfolder (or use placeholders)
 */

// ========== DATABASE CONFIGURATION ==========
$host = 'localhost';
$dbname = 'cooking_blog';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ========== FETCH DATA ==========
// 1. Hero: one random featured post
$heroStmt = $pdo->prepare("SELECT * FROM posts WHERE featured = 1 ORDER BY RAND() LIMIT 1");
$heroStmt->execute();
$hero = $heroStmt->fetch(PDO::FETCH_ASSOC);

// 2. Featured posts (up to 3)
$featuredStmt = $pdo->prepare("SELECT * FROM posts WHERE featured = 1 ORDER BY created_at DESC LIMIT 3");
$featuredStmt->execute();
$featuredPosts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Latest posts (6 most recent)
$latestStmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
$latestStmt->execute();
$latestPosts = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Categories
$catStmt = $pdo->query("SELECT * FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cooking Blog - Home</title>
    <style>
        /* ===== RESET & BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        a {
            text-decoration: none;
            color: #e67e22;
        }
        a:hover {
            color: #d35400;
        }

        /* ===== HEADER ===== */
        header {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .logo h1 {
            font-size: 1.8rem;
        }
        .logo a {
            color: #333;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        nav ul li a {
            font-weight: 600;
            color: #555;
        }

        /* ===== HERO ===== */
        .hero {
            background-size: cover;
            background-position: center;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
        }
        .hero-content {
            position: relative;
            z-index: 1;
            padding: 20px;
        }
        .hero-content h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background: #e67e22;
            color: #fff;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn:hover {
            background: #d35400;
            color: #fff;
        }

        /* ===== SECTIONS ===== */
        section {
            padding: 60px 0;
        }
        section h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 40px;
            position: relative;
        }
        section h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #e67e22;
            margin: 10px auto 0;
        }

        /* ===== POST GRID ===== */
        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        .post-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .post-card:hover {
            transform: translateY(-5px);
        }
        .post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post-card h3 {
            padding: 15px 15px 5px;
            font-size: 1.2rem;
        }
        .post-card h3 a {
            color: #333;
        }
        .post-card p {
            padding: 0 15px 10px;
            color: #666;
        }
        .post-card .meta {
            display: block;
            padding: 0 15px 15px;
            font-size: 0.9rem;
            color: #999;
        }

        /* ===== CATEGORIES ===== */
        .category-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            list-style: none;
        }
        .category-list li a {
            display: inline-block;
            background: #fff;
            padding: 10px 25px;
            border-radius: 30px;
            border: 1px solid #ddd;
            transition: 0.3s;
            color: #555;
        }
        .category-list li a:hover {
            background: #e67e22;
            color: #fff;
            border-color: #e67e22;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        footer p {
            margin: 0;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                gap: 15px;
            }
            .hero-content h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
    <div class="container">
        <div class="logo">
            <h1><a href="index.php">🍳 Cooking Blog</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Recipes</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>

<!-- ===== HERO SECTION ===== -->
<section class="hero" style="background-image: url('images/<?php echo $hero ? htmlspecialchars($hero['image']) : 'default-hero.jpg'; ?>');">
    <div class="hero-content">
        <?php if ($hero): ?>
            <h2><?php echo htmlspecialchars($hero['title']); ?></h2>
            <p><?php echo htmlspecialchars($hero['excerpt']); ?></p>
            <a href="post.php?slug=<?php echo $hero['slug']; ?>" class="btn">Read More</a>
        <?php else: ?>
            <h2>Welcome to Our Cooking Blog</h2>
            <p>Discover delicious recipes from around the world.</p>
            <a href="#" class="btn">Explore Recipes</a>
        <?php endif; ?>
    </div>
</section>

<!-- ===== FEATURED RECIPES ===== -->
<section class="featured">
    <div class="container">
        <h2>Featured Recipes</h2>
        <div class="post-grid">
            <?php foreach ($featuredPosts as $post): ?>
                <div class="post-card">
                    <img src="images/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <h3><a href="post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <span class="meta">By <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== LATEST RECIPES ===== -->
<section class="latest">
    <div class="container">
        <h2>Latest Recipes</h2>
        <div class="post-grid">
            <?php foreach ($latestPosts as $post): ?>
                <div class="post-card">
                    <img src="images/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <h3><a href="post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <span class="meta">By <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="categories">
    <div class="container">
        <h2>Browse by Category</h2>
        <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
                <li><a href="category.php?slug=<?php echo $cat['slug']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

</main>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Cooking Blog. All rights reserved.</p>
    </div>
</footer>

</body>
</html>

<?php
/*
 * ===== DATABASE SCHEMA (run this once) =====
 *
 * CREATE DATABASE cooking_blog;
 * USE cooking_blog;
 *
 * CREATE TABLE categories (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     name VARCHAR(50) NOT NULL,
 *     slug VARCHAR(50) NOT NULL UNIQUE
 * );
 *
 * CREATE TABLE posts (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     title VARCHAR(200) NOT NULL,
 *     slug VARCHAR(200) NOT NULL UNIQUE,
 *     excerpt TEXT,
 *     content TEXT,
 *     image VARCHAR(255),
 *     author VARCHAR(100),
 *     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     category_id INT,
 *     featured BOOLEAN DEFAULT FALSE,
 *     FOREIGN KEY (category_id) REFERENCES categories(id)
 * );
 *
 * -- Sample data
 * INSERT INTO categories (name, slug) VALUES
 * ('Breakfast', 'breakfast'),
 * ('Lunch', 'lunch'),
 * ('Dinner', 'dinner'),
 * ('Dessert', 'dessert');
 *
 * INSERT INTO posts (title, slug, excerpt, image, author, category_id, featured) VALUES
 * ('Fluffy Pancakes', 'fluffy-pancakes', 'Start your day with these light and airy pancakes.', 'pancakes.jpg', 'Chef Maria', 1, 1),
 * ('Grilled Chicken Salad', 'grilled-chicken-salad', 'A healthy and delicious salad for lunch.', 'salad.jpg', 'Chef John', 2, 0),
 * ('Spaghetti Carbonara', 'spaghetti-carbonara', 'Classic Italian pasta with egg, cheese, and bacon.', 'carbonara.jpg', 'Chef Luigi', 3, 1),
 * ('Chocolate Lava Cake', 'chocolate-lava-cake', 'Decadent molten chocolate cake for dessert lovers.', 'lava-cake.jpg', 'Chef Anna', 4, 0);
 */
?>
