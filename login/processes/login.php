<?php
// Include database connection file
require 'conn.php';
session_start();

// Check if email and password are submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

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
            // First, check if the user is an admin
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                if ($admin['status'] == 'Inactive') {
                    $_SESSION['STATUS'] = "INACTIVE_ACCOUNT_STAFF";
                    header('Location: ../../login/index.php');
                    exit;
                } else {

                    if ($admin && password_verify($password, $admin['password'])) {
                        // Set session variables for admin
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['user_id'] = $admin['id'];
                        $_SESSION['username'] = $admin['username'];
                        $_SESSION['email'] = $admin['email'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['full_name'] = $admin['fullName'];
                        $_SESSION['name'] = $admin['first_name'] . ' ' . $admin['middle_name'] . ' ' . $admin['last_name'];
                        $_SESSION['STATUS'] = "ADMIN_LOGIN_SUCCESFUL";
                        echo json_encode(['status' => 'success', 'user_type' => 'admin']);
                        header('Location: ../../admin/index.php');
                        exit;
                    } else {
                        $_SESSION['STATUS'] = "LOGIN_ERROR";
                        header('Location: ../../login/index.php');
                    }
                }
            }

            // Check if the user is a staff
            $stmt = $pdo->prepare("SELECT * FROM staff_accounts WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($staff) {
                if ($staff['status'] == 'Inactive') {
                    $_SESSION['STATUS'] = "INACTIVE_ACCOUNT_STAFF";
                    header('Location: ../../login/index.php');
                    exit;
                } elseif (password_verify($password, $staff['password'])) {
                    // Set session variables for staff
                    $_SESSION['user_id'] = $staff['id'];
                    $_SESSION['teacher_id'] = $staff['id'];
                    $_SESSION['teacher_name'] = $staff['fullName'];
                    $_SESSION['email'] = $staff['email'];
                    $_SESSION['user_type'] = 'staff';
                    $_SESSION['name'] = $staff['fullName'];
                    $_SESSION['full_name'] = $staff['fullName'];
                    $_SESSION['STATUS'] = "TEACHER_LOGIN_SUCCESFUL";
                    header('Location: ../../staff/index.php');
                    exit;
                }
            }
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                if ($student['status'] == 'inactive') {
                    $_SESSION['STATUS'] = "INACTIVE_ACCOUNT";
                    header('Location: ../../login/index.php');
                    exit;
                } elseif (password_verify($password, $student['password'])) {
                    // Set session variables for student
                    $_SESSION['user_id'] = $student['student_id'];
                    $_SESSION['fullName'] = $student['fullName'];
                    $_SESSION['name'] = $student['fullName'];
                    $_SESSION['full_name'] = $student['fullName'];
                    $_SESSION['student_id'] = $student['student_id'];
                    $_SESSION['user_type'] = 'student';
                    $_SESSION['course'] = $student['course'];
                    $_SESSION['year_level'] = $student['year_level'];
                    $_SESSION['STATUS'] = "STUDENT_LOGIN_SUCCESFUL";
                    header('Location: ../../students/student_dashboard.php');
                    exit;
                }
            }

        } else {
            $_SESSION['STATUS'] = "EMAIL_NONE_EXISTENCE";
            header("Location: ../../login/index.php");
            exit();
        }

        // If no match is found, return an error
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        $_SESSION['STATUS'] = "LOGIN_ERROR";
        header("Location: ../../login/index.php");
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        $_SESSION['STATUS'] = "UNKNOWN_ERROR";
        header("Location: ../../login/index.php");
    }
}
?>