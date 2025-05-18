<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to database (connection.php is in the root directory)
include '../connection.php';

// Fetch specific package from database (using 'packages' table)
$package = [];
$query = "SELECT id, package_name, price, description FROM packages WHERE id = '4'";
$result = mysqli_query($connection, $query);

if ($row = mysqli_fetch_assoc($result)) {
   $package = $row; // Store package data
}

// Set availability (assuming Royal Heritage Room is available for this example)
$packageAvailability = [];
$packageAvailability[$package['package_name']] = 1; // 1 for available, 0 for unavailable
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Royal Heritage Room - Package Details</title>

   <!-- swiper css link -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@11/swiper-bundle.min.css" />

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap">

   <!-- custom css file link (css/style.css is in the root's css/ folder) -->
   <link rel="stylesheet" href="../css/style.css">

   <style>
      .btn-disabled {
         background-color: #cccccc !important;
         cursor: not-allowed !important;
         pointer-events: none !important;
         opacity: 0.7;
      }

      .availed-message {
         color: #ff0000;
         font-weight: bold;
         margin-top: 15px;
         font-size: 14px;
         text-align: center;
         padding: 5px 0;
      }

      .btn-center {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: 10px;
      }

      .swiper-container {
         width: 100%;
         max-width: 800px;
         margin: 20px auto;
      }

      .swiper-slide img {
         width: 100%;
         height: 400px;
         object-fit: cover;
         border-radius: 8px;
      }

      .package-details {
         width: 100%;
         margin: 20px 0;
         padding: 20px;
         background-color: #f9f9f9;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .package-details h3 {
         color: #2c3e50;
         font-size: 28px;
         margin-bottom: 15px;
         border-bottom: 2px solid #3498db;
         padding-bottom: 10px;
      }

      .package-details p {
         color: #34495e;
         font-size: 16px;
         line-height: 1.6;
         margin-bottom: 20px;
      }

      .package-details h2 {
         color: #e74c3c;
         font-size: 24px;
         margin: 20px 0;
      }

      .package-details h4 {
         color: #2980b9;
         font-size: 20px;
         margin-top: 20px;
         margin-bottom: 10px;
      }

      .package-details ul {
         list-style: none;
         padding: 0;
         margin-bottom: 20px;
      }

      .package-details ul li {
         font-size: 16px;
         color: #2c3e50;
         margin-bottom: 10px;
         position: relative;
         padding-left: 25px;
      }

      .package-details ul li:before {
         content: '\f058';
         font-family: 'Font Awesome 5 Free';
         font-weight: 900;
         color: #3498db;
         position: absolute;
         left: 0;
         top: 2px;
      }
   </style>
</head>

<body>
   <!-- header section starts -->
   <section class="header">
      <a href="../index.php" class="logo"><img src="../images/logo.jpg" alt="" style="width:500px;height:100px;"></a>
      <nav class="navbar">
         <a href="../index.php"><i class="fas fa-home"></i> Home</a>
         <a href="../about.php"><i class="fas fa-info-circle"></i> About</a>
         <a href="../package.php"><i class="fas fa-box"></i> Package</a>
         <a href="../book.php"><i class="fas fa-book"></i> Book</a>
      </nav>
      <div id="menu-btn" class="fas fa-bars"></div>
   </section>
   <!-- header section ends -->



   <!-- package details section starts -->
   <section class="home-packages">
      <h1 class="heading-title">Royal Heritage Room Details</h1>
      
      <!-- Image Slider -->
      <?php if (!empty($package)): ?>
         <div class="swiper-container">
            <div class="swiper-wrapper">
               <div class="swiper-slide"><img src="../images/room-4.jpg" alt="Royal Heritage Room"></div>
               <div class="swiper-slide"><img src="../images/antique-1.jpg" alt="Antique Furnishings"></div>
               <div class="swiper-slide"><img src="../images/bathtub-1.jpg" alt="Luxury Bathtub"></div>
               </div>
            <div class="swiper-pagination"></div>
         </div>

         <!-- Package Details -->
         <div class="package-details">
            <div class="btn-close-container">
            <a href="../package.php" class="btn"><</a>
         </div>
            <h3><?php echo htmlspecialchars($package['package_name']); ?></h3>
            <p><?php echo htmlspecialchars($package['description']); ?></p>
            <h2>₱<?php echo number_format($package['price'], 2); ?> </h2>

            <!-- Room Information -->
            <h4>Room Information</h4>
            <ul>
               <li>Bed Type: Four-poster king bed</li>
               <li>Occupancy: 2 adults and 1 child</li>
               <li>Amenities: Antique furnishings, Luxury linens, Bathtub, Cable TV</li>
            </ul>

            <!-- Inclusions and Promotions -->
            <h4>Inclusions and Ongoing Promotions</h4>
            <ul>
               <li>Afternoon tea set</li>
               <li>Cultural dance show access</li>
               <li>Free historical walking tour</li>
            </ul>

            <div class="btn-center">
               <?php if (isset($packageAvailability[$package['package_name']]) && $packageAvailability[$package['package_name']] == 0): ?>
                  <span class="btn btn-disabled">Book Now</span>
                  <div class="availed-message">Package availed</div>
               <?php else: ?>
                  <a href="../book.php?package=<?php echo urlencode($package['package_name']); ?>" class="btn">Book Now</a>
               <?php endif; ?>
            </div>
         </div>
      <?php endif; ?>
   </section>
   <!-- package details section ends -->

   <!-- footer section starts -->
   <section class="footer">
      <div class="box-container">
         <div class="box">
            <h3>quick links</h3>
            <a href="../index.php"> <i class="fas fa-angle-right"></i> home</a>
            <a href="../about.php"> <i class="fas fa-angle-right"></i> about</a>
            <a href="../package.php"> <i class="fas fa-angle-right"></i> package</a>
            <a href="../book.php"> <i class="fas fa-angle-right"></i> book</a>
         </div>
         <div class="box">
            <h3>contact info</h3>
            <a href="#"> <i class="fas fa-phone"></i> +63-931-223-2777 </a>
            <a href="#"> <i class="fas fa-phone"></i> +111-222-3333 </a>
            <a href="#"> <i class="fas fa-envelope"></i> HotelDeLuna@gmail.com </a>
            <a href="#"> <i class="fas fa-map"></i> San Pablo City, Laguna - 4000 </a>
         </div>
         <div class="box">
            <h3>follow us</h3>
            <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
            <a href="#"> <i class="fab fa-twitter"></i> twitter </a>
            <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
            <a href="#"> <i class="fab fa-linkedin"></i> linkedin </a>
         </div>
      </div>
      <div class="credit"> created by <span>Hotel De Luna.</span> | all rights reserved! </div>
   </section>
   <!-- footer section ends -->

   <!-- swiper js link -->
   <script src="https://unpkg.com/swiper@11/swiper-bundle.min.js"></script>

   <!-- custom js file link (js/script.js is in the root's js/ folder) -->
   <script src="../js/script.js"></script>

   <!-- Initialize Swiper -->
   <script>
      var swiper = new Swiper('.swiper-container', {
         loop: true,
         pagination: {
            el: '.swiper-pagination',
            clickable: true,
         },
         autoplay: {
            delay: 3000,
            disableOnInteraction: false,
         },
      });
   </script>
</body>

</html>