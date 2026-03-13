<?php
require_once 'conn.php'; // Include your database connection
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve data from the form
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $fullName = $firstName . " " . $middleName . " " . $lastName;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $_SESSION['STATUS'] = "PASSWORD_NOT_SAME";
        header("Location: ../register.php?error=PasswordNotSame");
    }

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $emailCheckQuery = "
        SELECT COUNT(*) FROM admin WHERE email = :email_admin
        UNION ALL
        SELECT COUNT(*) FROM staff_accounts WHERE email = :email_staff
        UNION ALL
        SELECT COUNT(*) FROM students WHERE email = :email_student
    ";
        $emailCheckStmt = $pdo->prepare($emailCheckQuery);
        $emailCheckStmt->execute([
            ':email_admin' => $email,
            ':email_staff' => $email,
            ':email_student' => $email
        ]);


        $emailCount = array_sum($emailCheckStmt->fetchAll(PDO::FETCH_COLUMN));
        if ($emailCount > 0) {
            $_SESSION['STATUS'] = "EMAIL_ALREADY_EXISTS";
            header("Location: ../create_account.php");
            exit();
        }

        // Handle data based on the role
        if ($role === "admin") {
            // Insert into admin table
            $query = "INSERT INTO admin (username, email, password, first_name, middle_name, last_name, date_created, phone_number, gender, fullName) 
                      VALUES (:username, :email, :password, :first_name, :middle_name, :last_name, NOW(), :phone_number, :gender, :full_name)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':username' => $_POST['username'] ?? '',
                ':email' => $email,
                ':password' => $hashed_password,
                ':first_name' => $_POST['firstName'] ?? '',
                ':middle_name' => $_POST['middleName'] ?? '',
                ':last_name' => $_POST['lastName'] ?? '',
                ':phone_number' => $_POST['phone_number'] ?? '',
                ':full_name' => $fullName,
                ':gender' => $_POST['gender'] ?? ''

            ]);

            $fullName = $_POST['first_name'] . ' ' . $_POST['middle_name'] . ' ' . $_POST['last_name'];

            $stmt = $pdo->prepare("
            INSERT INTO admin_notifications (type, title, description, date, link, status)
            VALUES (:type, :title, :description, NOW(), :link, :status)
        ");

            // Define the notification data for "Staff Account Registration"
            $data = [
                ':type' => 'admin', // Change type if needed (e.g., 'success', 'warning', etc.)
                ':title' => 'New Admin Account Registration',
                ':description' => 'A new admin account has registered to the system by the name of: ' . $fullName,
                ':link' => 'admin/admin_management.php', // Update with the relevant URL
                ':status' => 'unread' // Default status is 'unread'
            ];

            $stmt->execute($data);

            $_SESSION['STATUS'] = "REGISTRATION_SUCCESSFUL_ACTIVATION_PLEASE_STAFF";

            header("Location: ../index.php");
        } elseif ($role === "staff") {
            // Insert into staff_accounts table
            $query = "INSERT INTO staff_accounts (first_name, middle_name, last_name, fullName, email, password, department, date_created, phone_number, gender, role) 
                      VALUES (:first_name, :middle_name, :last_name, :fullName, :email, :password, :department, NOW(), :phone_number, :gender, 'Staff')";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':first_name' => $_POST['firstName'] ?? '',
                ':middle_name' => $_POST['middleName'] ?? '',
                ':last_name' => $_POST['lastName'] ?? '',
                ':fullName' => $fullName ?? '',
                ':email' => $email,
                ':password' => $hashed_password,
                ':department' => $_POST['department'] ?? '',
                ':phone_number' => $_POST['phone_number'] ?? '',
                ':gender' => $_POST['gender'] ?? ''
            ]);
            $_SESSION['STATUS'] = "REGISTRATION_SUCCESSFUL_ACTIVATION_PLEASE_STAFF";

            $stmt = $pdo->prepare("
            INSERT INTO admin_notifications (type, title, description, date, link, status)
            VALUES (:type, :title, :description, NOW(), :link, :status)
        ");

            // Define the notification data for "Staff Account Registration"
            $data = [
                ':type' => 'teacher', // Change type if needed (e.g., 'success', 'warning', etc.)
                ':title' => 'New Staff Account Registration',
                ':description' => 'A new staff/teacher account has registered to the system by the name of: ' . $fullName,
                ':link' => 'admin/teacher_management.php', // Update with the relevant URL
                ':status' => 'unread' // Default status is 'unread'
            ];

            $stmt->execute($data);

            // header("Location: ../index.php");
        } elseif ($role === "student") {

            $token = bin2hex(random_bytes(25)); // You can store this in your database for verification

            // Validate ADDU email
            if (!str_ends_with($email, '@addu.edu.ph')) {
                $_SESSION['STATUS'] = "EMAIL_NOT_ADDU";
                header("Location: ../index.php");
                exit();
            }

            $email = $_POST['email'];
            $student_id = $_POST['student_id'];


            // Use regex to extract the first number sequence from the email
            if (preg_match('/\d+/', $email, $matches)) {
                $emailStudentId = trim($matches[0]); // Extracted number from email and trim whitespace
                $student_id = trim($student_id); // Trim whitespace from student_id to avoid mismatch

                echo "Extracted from email: " . $emailStudentId . "<br>";
                echo "Student ID entered: " . $student_id . "<br>";

                // Perform the comparison
                if ($emailStudentId === $student_id) {
                    echo "IDs match. Registration successful.";
                } else {
                    echo "IDs do not match.";
                    $_SESSION['STATUS'] = 'NOT_SAME_ID';
                    header('Location: ../create_account.php');
                    exit(); // Important to prevent further execution after a redirect
                }
            } else {
                echo "Error: No numeric ID found in the email.";
                $_SESSION['STATUS'] = 'INVALID_EMAIL_FORMAT';
                header('Location: ../create_account.php');
                exit(); // Exit after redirect
            }

            $studentIdCheckQuery = "SELECT COUNT(*) FROM students WHERE student_id = :student_id";
            $studentIdCheckStmt = $pdo->prepare($studentIdCheckQuery);
            $studentIdCheckStmt->execute([':student_id' => $_POST['student_id'] ?? '']);
            $studentIdExists = $studentIdCheckStmt->fetchColumn();

            if ($studentIdExists > 0) {
                $_SESSION['STATUS'] = "STUDENT_ID_EXISTS";
                header("Location: ../create_account.php");
                exit();
            }

            // Insert into students table
            $query = "INSERT INTO students (id, first_name, middle_name, last_name, fullName, student_id, gender, course, year_level, email, password, role, activation_token) 
                      VALUES (:id, :first_name, :middle_name, :last_name, :fullName, :student_id,  :gender, :course, :year_level, :email, :password, 'Student', :activation_token)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':id' => $_POST['student_id'] ?? '',
                ':first_name' => $_POST['firstName'] ?? '',
                ':middle_name' => $_POST['middleName'] ?? '',
                ':last_name' => $_POST['lastName'] ?? '',
                ':fullName' => $fullName ?? '',
                ':student_id' => $_POST['student_id'] ?? '',
                ':gender' => $_POST['gender'] ?? '',
                ':course' => $_POST['course'] ?? '',
                ':year_level' => $_POST['year_level'] ?? '',
                ':email' => $email,
                ':password' => $hashed_password,
                ':activation_token' => $token
            ]);



            $stmt = $pdo->prepare("
        INSERT INTO admin_notifications (type, title, description, date, link, status)
        VALUES (:type, :title, :description, NOW(), :link, :status)
    ");

            // Define the notification data for "Staff Account Registration"
            $data = [
                ':type' => 'student', // Change type if needed (e.g., 'success', 'warning', etc.)
                ':title' => 'New Student Account Registration',
                ':description' => 'A new student has registered to the system by the name of: ' . $fullName,
                ':link' => 'admin/class_management.php', // Update with the relevant URL
                ':status' => 'unread' // Default status is 'unread'
            ];

            $stmt->execute($data);



            $_SESSION['STATUS'] = "REGISTRATION_SUCCESSFUL_ACTIVATION_PLEASE";

            header("Location: sendActivationEmail.php?email=$email&name=$fullName&token=$token");
        }
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
