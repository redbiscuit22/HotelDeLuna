<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'book.php';
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_package = $_GET['package'] ?? '';

// Fetch user details
$user_query = $connection->prepare("SELECT name, email FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Fetch all booked dates for the selected package
$booked_dates = [];
if (!empty($selected_package)) {
    $date_query = $connection->prepare("SELECT arrivals, leaving FROM booking WHERE package = ?");
    $date_query->bind_param("s", $selected_package);
    $date_query->execute();
    $date_result = $date_query->get_result();
    
    while ($row = $date_result->fetch_assoc()) {
        $booked_dates[] = [
            'from' => $row['arrivals'],
            'to' => $row['leaving']
        ];
    }
}

// Calculate package price if package is selected
$package_price = 0;
if (!empty($selected_package)) {
    $price_query = $connection->prepare("SELECT price FROM packages WHERE package_name = ?");
    $price_query->bind_param("s", $selected_package);
    $price_query->execute();
    $price_result = $price_query->get_result();
    if ($price_row = $price_result->fetch_assoc()) {
        $package_price = $price_row['price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BOOK NOW!</title>

   <!-- swiper css link -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap">

   <!-- flatpickr (calendar) -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
   <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .flatpickr-day.booked {
         background: #ff4444;
         color: white;
         border-color: #ff4444;
      }
      .flatpickr-day.booked:hover {
         background: #ff2222;
      }
      .flatpickr-day.booked.startRange, 
      .flatpickr-day.booked.endRange {
         background: #ff0000;
      }
      .flatpickr-day.booked.inRange {
         background: rgba(255, 68, 68, 0.2);
         box-shadow: -5px 0 0 rgba(255, 68, 68, 0.2), 5px 0 0 rgba(255, 68, 68, 0.2);
      }
      .date-legend {
         display: flex;
         justify-content: center;
         margin: 15px 0;
         gap: 20px;
         flex-wrap: wrap;
      }
      .legend-item {
         display: flex;
         align-items: center;
         font-size: 14px;
         margin: 5px 0;
      }
      .legend-color {
         width: 20px;
         height: 20px;
         margin-right: 8px;
         border-radius: 3px;
      }
      .booking-form-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 20px;
      }
      .flatpickr-input {
         background-color: #f9f9f9;
         border: 1px solid #ddd;
         padding: 12px 15px;
         border-radius: 5px;
         font-size: 16px;
         width: 100%;
      }
      .flatpickr-calendar {
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         border-radius: 8px;
      }
      
      /* Payment Form Styles */
      .payment-section {
         background: #f9f9f9;
         border-radius: 10px;
         padding: 25px;
         margin-top: 30px;
         box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      }
      .payment-section h2 {
         margin-bottom: 20px;
         color: #333;
         text-align: center;
      }
      .payment-methods {
         display: flex;
         flex-wrap: wrap;
         gap: 15px;
         margin-bottom: 20px;
      }
      .payment-method {
         flex: 1;
         min-width: 200px;
      }
      .payment-method input[type="radio"] {
         display: none;
      }
      .payment-method label {
         display: block;
         padding: 15px;
         background: white;
         border: 2px solid #ddd;
         border-radius: 8px;
         cursor: pointer;
         transition: all 0.3s ease;
         text-align: center;
      }
      .payment-method input[type="radio"]:checked + label {
         border-color:rgb(110, 137, 180);
         background: #f0f7ff;
         box-shadow: 0 0 0 2px rgba(58, 134, 255, 0.2);
      }
      .payment-method img {
         height: 40px;
         margin-bottom: 10px;
      }
      .payment-details {
         display: none;
         background: white;
         padding: 20px;
         border-radius: 8px;
         margin-top: 15px;
         border: 1px solid #eee;
      }
      .payment-method.active .payment-details {
         display: block;
      }
      .form-group {
         margin-bottom: 15px;
      }
      .form-group label {
         display: block;
         margin-bottom: 5px;
         font-weight: 600;
      }
      .form-group input {
         width: 100%;
         padding: 10px;
         border: 1px solid #ddd;
         border-radius: 5px;
         font-size: 16px;
      }
      .price-summary {
         background: white;
         padding: 20px;
         border-radius: 8px;
         margin-top: 20px;
         border: 1px solid #eee;
      }
      .price-row {
         display: flex;
         justify-content: space-between;
         margin-bottom: 10px;
      }
      .price-row.total {
         font-weight: bold;
         font-size: 18px;
         border-top: 1px solid #eee;
         padding-top: 10px;
         margin-top: 10px;
      }
      .btn-center {
         text-align: center;
         margin-top: 20px;
      }
      .btn {
         display: inline-block;
         padding: 12px 30px;
         background: #A68A64;
         color: white;
         font-size: 18px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         transition: background 0.3s;
      }
      .btn:hover {
         background: #A68A64;
      }
      .btn:disabled {
         background: #cccccc;
         cursor: not-allowed;
      }
   </style>
</head>
<body>
   
<!-- header section starts -->
<section class="header">
   <a href="index.php" class="logo"><img src="images/logo.jpg" alt="" style="width:450px;height:100px;"></a>
   <nav class="navbar">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
      <a href="package.php"><i class="fas fa-box"></i> Package</a>
      <a href="book.php"><i class="fas fa-book"></i> Book</a>
      <?php if (isset($_SESSION['user_id'])): ?>
         <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <?php else: ?>
         <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
      <?php endif; ?>
   </nav>
   <div id="menu-btn" class="fas fa-bars"></div>
</section>
<!-- header section ends -->

<div class="heading" style="background:url(images/header-bg-3.png) no-repeat">
   <h1>Book Now</h1>
</div>

<!-- booking section starts -->
<div class="booking-form-container">
   <section class="booking">  
      <h1 class="heading-title">Book your Indulgement!</h1>

      <?php if (isset($_SESSION['user_id'])): ?>
      <form action="book_form.php" method="post" class="book-form" id="bookingForm">
         <div class="flex">
            <div class="inputBox">
               <span>Name :</span>
               <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
            </div>
            <div class="inputBox">
               <span>Email :</span>
               <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            <div class="inputBox">
               <span>Phone :</span>
               <input type="text" name="phone" placeholder="Enter your number" required maxlength="11" pattern="^09\d{9}$" inputmode="numeric" title="Phone number must start with 09 and be exactly 11 digits">
            </div>
            <div class="inputBox">
               <span>Address :</span>
               <input type="text" placeholder="Enter your address" name="address" required>
            </div>
            <div class="inputBox">
               <span>Package :</span>
               <input type="text" name="package" value="<?php echo htmlspecialchars($selected_package); ?>" readonly>
            </div>
            <div class="inputBox">
               <span>Number of guests:</span>
               <input type="number" placeholder="Enter number of guests" name="guests" required min="1" id="guests">
            </div>
            <div class="inputBox">
               <span>Arrival Date :</span>
               <input type="text" class="flatpickr-input" name="arrivals" id="arrivals" placeholder="Select arrival date" required readonly>
            </div>
            <div class="inputBox">
               <span>Departure Date :</span>
               <input type="text" class="flatpickr-input" name="leaving" id="leaving" placeholder="Select departure date" required readonly>
            </div>
         </div>

         <!-- Payment Section -->
         <div class="payment-section">
            <h2>Payment Method</h2>
            
            <div class="payment-methods">
               <div class="payment-method" id="gcashMethod">
                  <input type="radio" name="payment_method" value="GCash" id="gcash" required>
                  <label for="gcash">
                     <img src="images/GCash_logo.png" alt="GCash">
                     <div>GCash</div>
                  </label>
                  <div class="payment-details">
                     <div class="form-group">
                        <label for="gcash_number">GCash Mobile Number</label>
                        <input type="text" name="gcash_number" id="gcash_number" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" style="text-align: center;">
                     </div>
                     <div class="form-group">
                        <label for="gcash_name">Account Name</label>
                        <input type="text" name="gcash_name" id="gcash_name" placeholder="GCash Account" style="text-align: center;">
                     </div>
                  </div>
               </div>
               
               <div class="payment-method" id="mayaMethod">
                  <input type="radio" name="payment_method" value="Maya" id="maya">
                  <label for="maya">
                     <img src="images/Paymaya_logo.png" alt="Maya">
                     <div>Maya</div>
                  </label>
                  <div class="payment-details">
                     <div class="form-group">
                        <label for="maya_number">Maya Mobile Number</label>
                        <input type="text" name="maya_number" id="maya_number" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" style="text-align: center;">
                     </div>
                     <div class="form-group">
                        <label for="maya_name">Account Name</label>
                        <input type="text" name="maya_name" id="maya_name" placeholder="Maya Account" style="text-align: center;">
                     </div>
                  </div>
               </div>
               
               <div class="payment-method" id="paypalMethod">
                  <input type="radio" name="payment_method" value="PayPal" id="paypal">
                  <label for="paypal">
                     <img src="images/paypal-3.svg" alt="PayPal">
                     <div>PayPal</div>
                  </label>
                  <div class="payment-details">
                     <div class="form-group">
                        <label for="paypal_email">PayPal Email</label>
                        <input type="email" name="paypal_email" id="paypal_email" placeholder="Your PayPal email" style="text-align: center;">
                     </div>
                     <div class="form-group">
                        <label for="paypal_name">Account Name</label>
                        <input type="text" name="paypal_name" id="paypal_name" placeholder="PayPal Account" style="text-align: center;">
                     </div>
                  </div>
               </div>
            </div>
            
            <div class="price-summary">
               <h3>Price Summary</h3>
               <div class="price-row">
                  <span>Package Price:</span>
                  <span id="packagePrice">₱<?php echo number_format($package_price, 2); ?></span>
               </div>
               <div class="price-row">
                  <span>Number of Guests:</span>
                  <span id="guestsCount">1</span>
               </div>
               <div class="price-row total">
                  <span>Total Amount:</span>
                  <span id="totalAmount">₱<?php echo number_format($package_price, 2); ?></span>
               </div>
            </div>
            
            <div class="form-group">
               <label for="reference_number">Payment Reference Number (Optional)</label>
               <input type="text" name="reference_number" id="reference_number" placeholder="Enter reference number if already paid">
            </div>
         </div>

         <div class="btn-center">
            <input type="submit" value="Submit Booking" class="btn" name="send" id="submitBtn" disabled>
         </div>  
      </form>
      <?php else: ?>
      <div style="text-align:center; margin-top: 20px;">
         <p style="font-size: 18px;">You must be logged in to book a trip.</p>
         <a href="login.php" class="btn">Login</a>
         <a href="register.php" class="btn">Register</a>
      </div>
      <?php endif; ?>
   </section>
</div>
<!-- booking section ends -->

<!-- footer section starts -->
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

<!-- swiper js link -->
<script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

<!-- flatpickr js -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookedRanges = <?php echo json_encode($booked_dates); ?>;
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate());

    // Format dates for flatpickr
    const disabledRanges = bookedRanges.map(range => ({
        from: range.from,
        to: range.to
    }));

    // Function to style booked dates
    function styleBookedDates() {
        document.querySelectorAll('.flatpickr-day').forEach(day => {
            const date = new Date(day.dateObj);
            day.classList.remove('booked', 'startRange', 'endRange', 'inRange');
            
            // Check if date falls in any booked range
            bookedRanges.forEach(range => {
                const start = new Date(range.from);
                const end = new Date(range.to);
                
                if (date >= start && date <= end) {
                    day.classList.add('booked');
                    if (date.toDateString() === start.toDateString()) {
                        day.classList.add('startRange');
                    } else if (date.toDateString() === end.toDateString()) {
                        day.classList.add('endRange');
                    } else {
                        day.classList.add('inRange');
                    }
                }
            });
        });
    }

    // Initialize arrival date picker
    const arrivalsPicker = flatpickr("#arrivals", {
        minDate: tomorrow,
        dateFormat: "Y-m-d",
        disable: disabledRanges,
        onReady: function(selectedDates, dateStr, instance) {
            styleBookedDates();
        },
        onMonthChange: function(selectedDates, dateStr, instance) {
            setTimeout(styleBookedDates, 10);
        },
        onChange: function(selectedDates, dateStr, instance) {
            styleBookedDates();
            if (selectedDates.length) {
                leavingPicker.set('minDate', selectedDates[0]);
                if (leavingPicker.selectedDates[0] && 
                    leavingPicker.selectedDates[0] < selectedDates[0]) {
                    leavingPicker.clear();
                }
            }
        }
    });

    // Initialize leaving date picker
    const leavingPicker = flatpickr("#leaving", {
        minDate: tomorrow,
        dateFormat: "Y-m-d",
        disable: disabledRanges,
        onReady: function(selectedDates, dateStr, instance) {
            styleBookedDates();
        },
        onMonthChange: function(selectedDates, dateStr, instance) {
            setTimeout(styleBookedDates, 10);
        },
        onChange: function(selectedDates, dateStr, instance) {
            styleBookedDates();
            if (selectedDates.length && arrivalsPicker.selectedDates.length) {
                const arrival = arrivalsPicker.selectedDates[0];
                const leaving = selectedDates[0];
                
                const isRangeValid = !isRangeBooked(arrival, leaving);
                if (!isRangeValid) {
                    alert("Your selected dates include booked periods. Please choose different dates.");
                    leavingPicker.clear();
                }
            }
        }
    });

    // Check if any date in range is booked
    function isRangeBooked(startDate, endDate) {
        return bookedRanges.some(range => {
            const rangeStart = new Date(range.from);
            const rangeEnd = new Date(range.to);
            return (startDate <= rangeEnd && endDate >= rangeStart);
        });
    }

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        const radio = method.querySelector('input[type="radio"]');
        const label = method.querySelector('label');
        
        label.addEventListener('click', () => {
            paymentMethods.forEach(m => m.classList.remove('active'));
            method.classList.add('active');
            validateForm();
        });
    });

    // Update total price when guests change
    const guestsInput = document.getElementById('guests');
    const packagePrice = <?php echo $package_price; ?>;
    
    guestsInput.addEventListener('change', updateTotalPrice);
    
    function updateTotalPrice() {
        const guests = parseInt(guestsInput.value) || 1;
        const total = packagePrice * guests;
        
        document.getElementById('guestsCount').textContent = guests;
        document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    }

    // Form validation
    function validateForm() {
        const form = document.getElementById('bookingForm');
        const submitBtn = document.getElementById('submitBtn');
        let isValid = true;
        
        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
            }
        });
        
        // Check payment method details if selected
        const selectedMethod = form.querySelector('input[name="payment_method"]:checked');
        if (selectedMethod) {
            const methodId = selectedMethod.id;
            
            if (methodId === 'gcash') {
                const gcashNumber = document.getElementById('gcash_number').value;
                const gcashName = document.getElementById('gcash_name').value;
                if (!gcashNumber || !gcashName) isValid = false;
            } else if (methodId === 'maya') {
                const mayaNumber = document.getElementById('maya_number').value;
                const mayaName = document.getElementById('maya_name').value;
                if (!mayaNumber || !mayaName) isValid = false;
            } else if (methodId === 'paypal') {
                const paypalEmail = document.getElementById('paypal_email').value;
                const paypalName = document.getElementById('paypal_name').value;
                if (!paypalEmail || !paypalName) isValid = false;
            }
        } else {
            isValid = false;
        }
        
        // Check date selection
        if (!arrivalsPicker.selectedDates.length || !leavingPicker.selectedDates.length) {
            isValid = false;
        }
        
        // Enable/disable submit button
        submitBtn.disabled = !isValid;
    }
    
    // Add event listeners for form validation
    document.querySelectorAll('#bookingForm input').forEach(input => {
        input.addEventListener('input', validateForm);
        input.addEventListener('change', validateForm);
    });
    
    // Initial validation
    validateForm();

    // Form submission validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!arrivalsPicker.selectedDates.length || !leavingPicker.selectedDates.length) {
            e.preventDefault();
            alert('Please select both arrival and departure dates');
            return;
        }
        
        const arrival = arrivalsPicker.selectedDates[0];
        const leaving = leavingPicker.selectedDates[0];
        
        if (leaving < arrival) {
            e.preventDefault();
            alert('Departure date cannot be before arrival date');
            return;
        }
        
        if (isRangeBooked(arrival, leaving)) {
            e.preventDefault();
            alert('The selected dates include booked periods. Please choose different dates.');
            return;
        }
        
        // Validate payment method details
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod) {
            e.preventDefault();
            alert('Please select a payment method');
            return;
        }
        
        const methodId = selectedMethod.id;
        if (methodId === 'gcash') {
            const gcashNumber = document.getElementById('gcash_number').value;
            if (!/^09\d{9}$/.test(gcashNumber)) {
                e.preventDefault();
                alert('Please enter a valid GCash mobile number (09XXXXXXXXX)');
                return;
            }
        } else if (methodId === 'maya') {
            const mayaNumber = document.getElementById('maya_number').value;
            if (!/^09\d{9}$/.test(mayaNumber)) {
                e.preventDefault();
                alert('Please enter a valid Maya mobile number (09XXXXXXXXX)');
                return;
            }
        } else if (methodId === 'paypal') {
            const paypalEmail = document.getElementById('paypal_email').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(paypalEmail)) {
                e.preventDefault();
                alert('Please enter a valid PayPal email address');
                return;
            }
        }
    });
});
</script>

<!-- custom js file link -->
<script src="js/script.js"></script>

</body>
</html>