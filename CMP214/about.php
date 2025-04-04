<?php
$title = "About Us";
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="hero">
    <h1>About Tyne Brew Coffee</h1>
    <p>Crafting excellence in every cup, since the early 1900s.</p>
</header>

<section class="about-container">
    <div class="about-content">
        <img src="IMG/tyne_bridge.png" alt="Tyne Bridge Newcastle" class="about-image">
        <div class="about-text">
            <p>
                Welcome to Tyne Brew Coffee, your trusted supplier of award-winning coffee, rooted in the heart of Newcastle upon Tyne. 
                Established in the early 1900s, we have built a strong tradition of excellence, sourcing premium beans from the best coffee-growing 
                regions worldwide. Our meticulous roasting process ensures each cup delivers the perfect balance of aroma and flavour.
            </p>
            <p>
                What sets us apart? Our unwavering dedication to quality and our community. Over the years, Tyne Brew Coffee has grown beyond a brand - 
                it's a local staple. From humble beginnings on City Road to becoming a recognised name in fine coffee craftsmanship, we remain committed 
                to providing our customers with a rich and authentic coffee experience.
            </p>
        </div>
    </div>
</section>

<section class="founder-container">
    <h2>Our Founder</h2>
    <div class="founder-content">
        <img src="IMG/founder.png" alt="Founder of Tyne Brew Coffee" class="founder-image">
        <div class="founder-text">
            <p>
                Tyne Brew Coffee was founded by Anthony Gibson, a passionate coffee enthusiast with a vision to bring high-quality, flavourful blends 
                to the North East. His dedication to perfecting the craft led him to source the finest beans and develop a signature roasting method that 
                is still used today. 
            </p>
            <p>
                Today, the legacy continues with his grandsons, Ian and Riley Gibson, who have expanded the business while staying true to the core values 
                of quality, authenticity, and customer satisfaction.
            </p>
        </div>
    </div>
</section>

<section class="customer-focus">
    <div class="content-wrapper">
        <h2>Our Dedication to You</h2>
        <p>
            At Tyne Brew Coffee, our customers are at the heart of everything we do. Whether you're a casual coffee lover or a dedicated connoisseur, 
            we offer a diverse range of coffee, tea, and hot chocolate to suit your taste. Our goal is to bring a moment of joy to your day with every sip.
        </p>
    </div>
</section>

</body>
</html>




