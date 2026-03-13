<?php
session_Start();
$host = 'localhost'; 
$dbname = 'u351448361_csms_system'; 
$username = 'u351448361_csms_admin'; 
$password = 'WmsuDatabase009988'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_name = $_SESSION['teacher_name'];
    $note_title = $_POST['note_title'];
    $note_content = $_POST['note_content'];

    try {
        // Insert the new note into the teacher_notes table
        $sql = "INSERT INTO teacher_notes (teacher_name, note_title, note_content) 
                VALUES (:teacher_name, :note_title, :note_content)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':teacher_name', $teacher_name, PDO::PARAM_STR);
        $stmt->bindParam(':note_title', $note_title, PDO::PARAM_STR);
        $stmt->bindParam(':note_content', $note_content, PDO::PARAM_STR);
        $stmt->execute();

        // Redirect back to the page or give a success response
        $_SESSION['STATUS'] = "ADD_NOTES_SUCCESS";
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
header('Location: ../../../index.php');
