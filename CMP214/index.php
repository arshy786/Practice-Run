homepage.php is: <?php
require_once 'DB.php';
require_once 'product.php';
require_once 'navbar.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$db = new DB;
$conn = $db->connect();

$query = $conn->query("SELECT * FROM tbl_products");

$products = [];
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $products[] = new Product($row['id'], $row['name'], $row['image'], $row['category'], $row['price'], $row['info']);
}

$category = isset($_GET['category']) ? htmlspecialchars($_GET['category'], ENT_QUOTES, 'UTF-8') : '';

$filtered = $category ? array_filter($products, function ($product) use ($category) {
    return $product->getCategory() === $category;
}) : $products;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM tbl_products WHERE id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $_SESSION['basket'][] = [
                    'id' => $id,
                    'name' => $row['name'],
                    'image' => $row['image'],
                    'price' => $row['price'],
                    'quantity' => 1,
                    'info' => $row['info']
                ];
            }
            header('Location: homepage.php');
            exit();
        }
    } else {
        die("Invalid CSRF token.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tyne Brew Coffee - Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="hero">
    <h1>Welcome to Tyne Brew Coffee</h1>
    <p>Your one-stop shop for premium coffee, tea, and more.</p>
</header>

<section class="category-filters">
    <h2>Browse by Category</h2>
    <div class="category-buttons">
        <a href="homepage.php"><button>All</button></a>
        <a href="homepage.php?category=Coffee"><button>Coffee</button></a>
        <a href="homepage.php?category=Tea"><button>Tea</button></a>
        <a href="homepage.php?category=HotChocolate"><button>Hot Chocolate</button></a>
        <a href="homepage.php?category=Biscuits"><button>Biscuits</button></a>
        <a href="homepage.php?category=Mugs"><button>Mugs</button></a>
    </div>
</section>

<section class="products-container">
    <h2>Our Products</h2>
    <div class="products-grid">
        <?php if (count($filtered) > 0): ?>
            <?php foreach ($filtered as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product->getImage(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
                    <h3 class="product-name"><?= htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="product-price">£<?= number_format($product->getPrice(), 2); ?></p>
                    <p class="product-info"><?= nl2br(htmlspecialchars($product->getInfo(), ENT_QUOTES, 'UTF-8')); ?></p>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id" value="<?= $product->getId(); ?>">
                        <button type="submit" name="add" class="add-to-basket">Add To Basket</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>
</section>

</body>
</html>






