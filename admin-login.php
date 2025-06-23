<?php
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin-panel.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Awaiken">
	<!-- Page Title -->
    <title>Admin Login - Hypnotherapy and Cosmic Hub</title>
	<!-- Favicon Icon -->
	<!--<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">-->
	<!-- Google Fonts Css-->
	<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    
	<!-- Bootstrap Css -->
	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- SlickNav Css -->
	<link href="css/slicknav.min.css" rel="stylesheet">
	<!-- Swiper Css -->
	<link rel="stylesheet" href="css/swiper-bundle.min.css">
	<!-- Font Awesome Icon Css-->
	<link href="css/all.min.css" rel="stylesheet" media="screen">
	<!-- Animated Css -->
	<link href="css/animate.css" rel="stylesheet">
    <!-- Magnific Popup Core Css File -->
	<link rel="stylesheet" href="css/magnific-popup.css">
	<!-- Mouse Cursor Css File -->
	<link rel="stylesheet" href="css/mousecursor.css">
	<!-- Main Custom Css -->
	<link href="css/custom.css" rel="stylesheet" media="screen">
</head>
<body>

    <!-- Header Start -->
	<header class="main-header">
		<div class="header-sticky">
			<nav class="navbar navbar-expand-lg">
				<div class="container">
					<!-- Logo Start -->
					<a class="navbar-brand" href="index.html">
						<img style="width: 150px;" src="images/logo.png" alt="Logo">
					</a>
					<!-- Logo End -->

					<!-- Main Menu Start -->
					<div class="collapse navbar-collapse main-menu">
                        <div class="nav-menu-wrapper">
                            <ul class="navbar-nav mr-auto" id="menu"> 
                                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>                                                              
                                <li class="nav-item"><a class="nav-link" href="about.html">About Us</a>
                                <li class="nav-item"><a class="nav-link" href="services.html">Services</a></li>
                                <li class="nav-item"><a class="nav-link" href="contact.html">Contact Us</a></li>
                                <li class="nav-item highlighted-menu"><a class="nav-link" href="book-appointment.html">Book Appointment</a></li>                   
                            </ul>
                        </div>
					</div>
					<!-- Main Menu End -->
					<div class="navbar-toggle"></div>
				</div>
			</nav>
			<div class="responsive-menu"></div>
		</div>
	</header>
	<!-- Header End -->

    <!-- Page Header Start -->
    <div class="page-header parallaxie">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 class="text-anime-style-2" data-cursor="-opaque">Admin Login</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html">home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">admin</li>
                            </ol>
                        </nav>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Admin Login Start -->
    <div class="page-book-appointment">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Admin Login Box Start -->
                    <div class="our-appointment-box">
                        <!-- Login Form Start -->
                        <div class="appointment-form">
                            <div class="section-title text-center mb-4">
                                <h3 class="wow fadeInUp">Admin Panel</h3>
                                <h2 class="text-anime-style-2" data-cursor="-opaque">Login to <span>Dashboard</span></h2>
                            </div>
                            
                            <!-- Admin Login Form Start -->
                            <form id="adminLoginForm" action="admin-auth.php" method="POST" class="wow fadeInUp">
                                <div class="row">
                                    <div class="form-group col-md-12 mb-4">
                                        <input type="text" name="username" class="form-control" id="username" placeholder="Username" required="">
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="form-group col-md-12 mb-4">
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required="">
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="contact-form-btn text-center">
                                            <button type="submit" class="btn-default">Login</button>
                                            <div id="msgSubmit" class="h3 hidden mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- Admin Login Form End -->
                        </div>
                        <!-- Login Form End -->
                    </div>
                    <!-- Admin Login Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Admin Login End -->

    <!-- Footer Main Start -->
    <footer class="footer-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Footer Header Start -->
                    <div class="footer-header">
                        <!-- Footer About Start -->
                        <div class="footer-about">
                            <div class="footer-logo">
                                <a class="navbar-brand" href="index.html" style="font-size: 24px; font-weight: bold; color: #fff; text-decoration: none;">
                                    Hypnotherapy and COSMIC HUB
                                </a>
                            </div>
                        </div>
                        <!-- Footer About End -->
                    </div>
                    <!-- Footer Header End -->
                </div>

                <div class="col-lg-6 col-md-3">
                    <!-- Footer Links Start -->
                    <div class="footer-links">
                        <h3>Quick link</h3>
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="about.html">About us</a></li>
                            <li><a href="services.html">Services</a></li>
                            <li><a href="contact.html">Contact</a></li>
                        </ul>
                    </div>
                    <!-- Footer Links End -->
                </div>

                <div class="col-lg-6 col-md-5">
                    <!-- Footer Contact Links Start -->
                    <div class="footer-links footer-contact-links">
                        <h3>Contact</h3>
                        <ul>
                            <li><a href="tel:9829422484">+(91) 9829422484</a></li>
                            <li>Chandiram Bora Path, Ketekibari, Tezpur, Da-Parbatia Gaon, Assam 784001</li>
                        </ul>
                    </div>
                    <!-- Footer Contact Links End -->
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Main End -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const formData = new FormData(this);

                fetch('admin-auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim(); // Remove any whitespace
                    if (data === 'success') {
                        // Redirect immediately on success
                        window.location.replace('admin-panel.php');
                    } else {
                        const msg = document.getElementById('msgSubmit');
                        msg.innerText = data;
                        msg.classList.remove('hidden');
                        msg.style.color = '#dc3545';

                        // Hide message after 5 seconds
                        setTimeout(() => {
                            msg.classList.add('hidden');
                        }, 5000);
                    }
                })
                .catch(err => {
                    const msg = document.getElementById('msgSubmit');
                    msg.innerText = 'Something went wrong. Please try again.';
                    msg.classList.remove('hidden');
                    msg.style.color = '#dc3545';
                    console.error(err);
                });
            });
        });
    </script>

    <!-- Jquery Library File -->
    <script src="js/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap js file -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Validator js file -->
    <script src="js/validator.min.js"></script>
    <!-- SlickNav js file -->
    <script src="js/jquery.slicknav.js"></script>
    <!-- Swiper js file -->
    <script src="js/swiper-bundle.min.js"></script>
    <!-- Counter js file -->
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <!-- Magnific js file -->
    <script src="js/jquery.magnific-popup.min.js"></script>
    <!-- SmoothScroll -->
    <script src="js/SmoothScroll.js"></script>
    <!-- Parallax js -->
    <script src="js/parallaxie.js"></script>
    <!-- MagicCursor js file -->
    <script src="js/gsap.min.js"></script>
    <script src="js/magiccursor.js"></script>
    <!-- Text Effect js file -->
    <script src="js/SplitText.js"></script>
    <script src="js/ScrollTrigger.min.js"></script>
    <!-- YTPlayer js File -->
    <script src="js/jquery.mb.YTPlayer.min.js"></script>
    <!-- Wow js file -->
    <script src="js/wow.min.js"></script>
    <!-- Main Custom js file -->
    <script src="js/function.js"></script>
</body>
</html> 