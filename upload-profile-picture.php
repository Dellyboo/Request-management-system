<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $userId = $_POST['user_id'];
    $targetDir = "images/";
    $targetFile = $targetDir . basename($_FILES["profile_picture"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate the uploaded file
    if (getimagesize($_FILES["profile_picture"]["tmp_name"]) === false) {
        echo "<script>alert('File is not an image.'); window.history.back();</script>";
        exit;
    }
    if ($_FILES["profile_picture"]["size"] > 5000000) {
        echo "<script>alert('Sorry, your file is too large.'); window.history.back();</script>";
        exit;
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.'); window.history.back();</script>";
        exit;
    }

    // If everything is fine, upload the file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
            $picture = basename($_FILES["profile_picture"]["name"]);

            // Update the database with the new picture name
            $pdo = new PDO("mysql:host=localhost;dbname=konteldb", "root", "");
            $stmt = $pdo->prepare("UPDATE poste SET picture = ? WHERE id = ?");
            $stmt->execute([$picture, $userId]);

            // Show alert and redirect back to the same page
            echo "<script>
                alert('You have successfully changed your profile picture.');
                window.location.href = document.referrer;
            </script>";
            exit;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.'); window.history.back();</script>";
        }
    }
}
?>
