<?php
session_start();
include('processes/conn.php');

$stmt = $pdo->prepare("
    UPDATE classes_meetings
    SET status = 'Finished'
    WHERE STR_TO_DATE(CONCAT(CURDATE(), ' ', end_time), '%Y-%m-%d %h:%i %p') < NOW()
");
$stmt->execute();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ADNU - CCS | Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="external/css/login.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="external/img/favicon-32x32.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</head>

<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid d-flex">
            <a class="navbar-brand" href="../index.php">
                <img src="external/img/ADNU_Logo.png" class="img-fluid logo">
            </a>
            <div class="mx-auto">
                Student Management System
            </div>
        </div>
    </nav>
</header>

<body>
    <div class="container-fluid login-container">
        <div class="row d-flex justify-content-center align-items-center  min-vh-100 px-3">
            <div class="col-lg-6 col-md-8 col-sm-12 p-4">
                <div data-aos="fade-right">
                    <div class="d-flex justify-content-center align-items-center">
                           <small><a href="../index.php" class="gb"><i class="bi bi-arrow-left-circle-fill"></i> Go back</a></small>
                    </div>
                 
                    <div class="d-flex mb-3 justify-content-center align-items-center">
                        <img src="external/img/ADNU_Logo.png" class="img-fluid big-logo me-2" alt="ADNU Logo">
                        <img src="external/img/ADNU_CCS_Logo.png" class="img-fluid big-logo" alt="CCS Logo">
                    </div>
                <h3 class="text-center bold">Welcome to AdNU - CCS</h3>
                                    <p class="text-center text-muted">Login your account by filling out the form below</p>
                    <form action="processes/login.php" method="POST" class="login-container-with-input">
                        <label class="bold">EMAIL</label>

                        <input class="input-control" name="email" type="email" placeholder="Email" required>
                        <br> <br>
                        <label class="bold">PASSWORD</label>


                        <!-- password box -->
                        <input type="password"
                            id="password"
                            name="password"
                            class="input-control mb-2"
                            placeholder="Create a password"
                            required>

                        <!-- animated toggle switch -->
                        <div class="form-check form-switch mb-3 text-left">
                            <input class="form-check-input toggle-switch"
                                type="checkbox"
                                id="showPassword"
                                onclick="togglePasswordVisibility()">
                            <label class="form-check-label ms-2" for="showPassword">
                                Show Password
                            </label>
                        </div>

                        <div class=" d-flex justify-content-between mb-3">
                            <a href="create_account.php" class="gb-link me-auto">Create an Account</a>
                            <a data-bs-toggle="modal" data-bs-target="#resetPasswordModal" class="gb-link">Forgot your password?</a>
                        </div>

                        <input type="submit" value="Login" class="login-btn w-100">
                    </form>
                </div>
            </div>

            <!-- Right Column: Image -->
            <div class="col-lg d-none d-lg-flex justify-content-center align-items-center m-20">
                <div data-aos="fade-left">
                    <img src=" external/img/login.png" class="img-fluid w-100" alt="ADNU Login Banner" style="border-radius: 5%;">
                </div>
            </div>
        </div>
    </div>

    <style>
        @media screen and (max-width: 500px) {
            .login-container-with-input {
                width: 100% !important;
            }

            .button-linkers {
                justify-content: center !important;
                text-align: center !important;
                font-size: 12px !important;
                gap: 2rem;
            }

            .d-flex .big-logo {
                width: 100px;
                height: 100px;
            }

            .modal-dialog {
                max-width: 95%;
                margin: 0 auto;
            }

            .modal-content {
                padding: 10px;
            }

            .modal-title {
                font-size: 1.2rem;
            }

            .modal-footer button {
                padding: 5px 10px;
                font-size: 0.9rem;
            }

            .modal-body label {
                font-size: 0.9rem;
            }

            .modal-body input {
                font-size: 0.9rem;
                padding: 5px;
            }
        }
    </style>

    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm" action="processes/reset.php" method="POST">
                        <div class="mb-3">
                            <label for="emailInput" class="form-label">Email Address</label>
                            <input type="email" class="input-control" id="emailInput" name="email"
                                placeholder="Enter your email address" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" form="resetPasswordForm" id="submitReset">Send Reset
                        Link</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        document.getElementById('submitReset').addEventListener('click', function() {
            // Show SweetAlert2 modal
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your request.',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading(); // Show loading indicator
                    // Submit the form after showing the alert
                    document.getElementById('resetPasswordForm').submit();
                }
            });
        });
    </script>
</body>

<script>
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    }
    AOS.init();
</script>

<?php
include('processes/alerts.php');
?>

</html>