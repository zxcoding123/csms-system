<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['uploadQR']) && $_FILES['uploadQR']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['uploadQR']['name']);

        // Create the upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['uploadQR']['tmp_name'], $uploadFile)) {
            echo "<p>File is valid, and was successfully uploaded.</p>";
            echo "<p><a href='$uploadFile'>View Uploaded QR Code</a></p>";
        } else {
            echo "<p>Possible file upload attack!</p>";
        }
    } else {
        echo "<p>No file uploaded or there was an upload error.</p>";
    }
}
?>
