<?php
require_once 'conn.php'; // Include database connection
session_start();

if (isset($_GET['email'])) {
    $email = $_GET['email'];

    try {
        // Check if the email exists in the database
        $query = "SELECT * FROM students WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Check if the user is already activated
            if ($user['status'] == 'active') {
                $_SESSION['STATUS'] = "EMAIL_ALREADY_ACTIVE";
                header("Location: ../index.php"); // Redirect to result page
                exit();
            }

            // Set the user as activated (assuming there's a status column in your database)
            $query = "UPDATE students SET status = 'active' WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);

            // Check if the update was successful
            if ($stmt->rowCount() > 0) {
                $_SESSION['STATUS'] = "EMAIL_ACTIVATED_SUCCESSFULLY";
                header("Location: ../index.php"); // Redirect to result page
                exit();
            } else {
                $_SESSION['STATUS'] = "ACTIVATION_FAILED";
                header("Location: ../index.php"); // Redirect to result page
                exit();
            }
        } else {
            $_SESSION['STATUS'] = "EMAIL_NOT_REGISTERED";
            header("Location: ../index.php"); // Redirect to result page
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['STATUS'] = "ERROR";
        header("Location: ../index.php"); // Redirect to result page
        exit();
    }
} else {
    $_SESSION['STATUS'] = "NO_EMAIL_PROVIDED";
    header("Location: ../index.php"); // Redirect to result page
    exit();
}
?>
