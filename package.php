<?php
// Connect to database
include 'connection.php';

// Start session
session_start();

// Fetch all packages from database
$query = "SELECT package_name, description, price FROM packages";
$result = mysqli_query($connection, $query);
$packages = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Check if user is logged in and fetch their bookings if they are
$userBookings = [];
$hasPending = false;
if (isset($_SESSION['user_id'])) {
   $userId = $_SESSION['user_id'];
   $bookingQuery = "SELECT package, arrivals, leaving, status FROM booking WHERE user_id = '$userId' ORDER BY created_at DESC";
   $bookingResult = mysqli_query($connection, $bookingQuery);

   while ($row = mysqli_fetch_assoc($bookingResult)) {
      $userBookings[] = $row;
      if ($row['status'] == 'Pending') {
         $hasPending = true;
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>CHOOSE YOUR PACKAGE</title>

   <!-- swiper css link  -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@11/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .btn-center {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: 10px;
      }

      /* Booking status floating component styles */
      .booking-status-container {
         position: fixed;
         right: 20px;
         bottom: 20px;
         z-index: 1000;
      }

      .booking-status-btn {
         background-color: <?php echo $hasPending ? '#f39c12' : '#3a86ff'; ?>;
         color: white;
         width: 60px;
         height: 60px;
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         cursor: pointer;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
         font-size: 24px;
         transition: all 0.3s ease;
      }

      .booking-status-btn:hover {
         transform: scale(1.1);
      }

      .booking-status-badge {
         position: absolute;
         top: -5px;
         right: -5px;
         background-color: #e74c3c;
         color: white;
         border-radius: 50%;
         width: 20px;
         height: 20px;
         display: flex;
         justify-content: center;
         align-items: center;
         font-size: 12px;
      }

      .booking-modal {
         display: none;
         position: fixed;
         z-index: 1001;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
      }

      .booking-modal-content {
         background-color: #fefefe;
         margin: 10% auto;
         padding: 20px;
         border-radius: 10px;
         width: 80%;
         max-width: 600px;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }

      .close-modal {
         color: #aaa;
         float: right;
         font-size: 28px;
         font-weight: bold;
         cursor: pointer;
      }

      .close-modal:hover {
         color: black;
      }

      .booking-item {
   padding: 10px;
   border-bottom: 1px solid #eee;
   display: flex;
   align-items: center; /* Vertically center items */
   justify-content: space-between; /* Maintain spacing between elements */
   gap: 10px; /* Add spacing between elements */
}

.booking-item span {
   flex: 1; /* Make each span take equal space */
   text-align: center; /* Center text within each span */
}

.booking-item span:nth-child(2), 
.booking-item span:nth-child(3) {
   flex: 0 0 auto; /* Prevent arrival and departure from stretching too much */
   min-width: 100px; /* Ensure consistent width for dates */
}


      .status-pending {
         color: #f39c12;
         font-weight: bold;
      }

      .status-paid {
         color: #27ae60;
         font-weight: bold;
      }

      .status-completed {
         color: #3498db;
         font-weight: bold;
      }

      .status-cancelled {
         color: #e74c3c;
         font-weight: bold;
      }

      .booking-title {
         font-size: 24px;
         margin-bottom: 20px;
         color: #333;
         text-align: center;
      }

   </style>
</head>

<body>

   <!-- header section starts  -->
   <section class="header">
      <a href="index.php" class="logo"><img src="images/logo.jpg" alt="" style="width:500px;height:100px;"></a>
      <nav class="navbar">
         <a href="index.php"><i class="fas fa-home"></i> Home</a>
         <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
         <a href="package.php"><i class="fas fa-box"></i> Package</a>
         <a href="book.php"><i class="fas fa-book"></i> Book</a>
      </nav>
      <div id="menu-btn" class="fas fa-bars"></div>
   </section>
   <!-- header section ends -->

   <div class="heading" style="background:url(images/header-bg-2.png) no-repeat">
      <h1>Packages</h1>
   </div>

   <!-- packages section starts  -->
   <section class="home-packages">
   <h1 class="heading-title">Choose your Luxury</h1>
   <div class="box-container">
      <?php
      $imageIndex = 1;
      foreach ($packages as $package):
         $imageSrc = "images/img-" . $imageIndex . ".jpg";
         $packageNumber = $imageIndex; // Capture current index for package number
         $imageIndex++;
         if ($imageIndex > 15) $imageIndex = 1; // Reset index after 15
      ?>
         <div class="box">
            <div class="image">
               <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($package['package_name']); ?>">
            </div>
            <div class="content">
               <h3><?php echo htmlspecialchars($package['package_name']); ?></h3>
               <p><?php echo htmlspecialchars($package['description']); ?></p>
               <h2>₱<?php echo number_format($package['price'], 2); ?> </h2>
               <div class="btn-center">
                  <a href="packages/package<?php echo $packageNumber; ?>.php" class="btn">View</a>
                  <?php if (isset($packageAvailability[$package['package_name']]) && $packageAvailability[$package['package_name']] == 0): ?>
                     <div class="availed-message">Package availed</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      <?php endforeach; ?>
   </div>
</section>
   <!-- packages section ends -->
 
   <!-- Booking status floating component -->
   <?php if (isset($_SESSION['user_id'])): ?>
      <div class="booking-status-container">
         <div class="booking-status-btn" id="bookingStatusBtn">
            <i class="fas fa-calendar-check"></i>
            <?php if ($hasPending): ?>
               <div class="booking-status-badge">!</div>
            <?php endif; ?>
         </div>
      </div>

      <!-- Booking status modal -->
      <div id="bookingModal" class="booking-modal">
         <div class="booking-modal-content">
            <span class="close-modal">&times;</span>
            <h2 class="booking-title">Your Bookings</h2>
            <?php if (!empty($userBookings)): ?>
               <?php foreach ($userBookings as $booking): ?>
                  <div class="booking-item">
                     <span><?php echo htmlspecialchars($booking['package']); ?></span>
                     <span><?php echo htmlspecialchars($booking['arrivals']); ?></span>
                     <span><?php echo htmlspecialchars($booking['leaving']); ?></span>
                     <span class="status-<?php echo strtolower($booking['status']); ?>">
                        <?php echo htmlspecialchars($booking['status']); ?>
                     </span>
                  </div>
               <?php endforeach; ?>
            <?php else: ?>
               <div class="no-bookings" style="text-align: center; padding: 20px;">
                  <p style="font-size: 18px; color: #555; margin-bottom: 20px;">
                     THERE ARE NO BOOKINGS MADE, BOOK NOW!
                  </p>
                  <a href="package.php" class="btn" style="display: inline-block;">Book a Package</a>
               </div>
            <?php endif; ?>
         </div>
      </div>
   <?php endif; ?>

   <!-- footer section starts  -->

  <section class="footer">

    <div class="box-container">

      <div class="box">
        <h3>quick links</h3>
        <a href="index.php"> <i class="fas fa-angle-right"></i> home</a>
        <a href="about.php"> <i class="fas fa-angle-right"></i> about</a>
        <a href="package.php"> <i class="fas fa-angle-right"></i> package</a>
        <a href="book.php"> <i class="fas fa-angle-right"></i> book</a>
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
   <!-- swiper js link  -->
   <script src="https://unpkg.com/swiper@11/swiper-bundle.min.js"></script>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

   <!-- booking_status modal -->
   <script src="js/booking_status.js"></script>

</body>

</html>