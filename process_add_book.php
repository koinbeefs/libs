<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $published_date = $_POST['published_date'];
    $genre_id = $_POST['genre_id']; // Get the genre ID from the form

    // Handle the file upload
    $bookCover = $_FILES['book_cover'];
    $targetDir = "uploads/covers/";
    $targetFile = $targetDir . basename($bookCover["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is an image
    $check = getimagesize($bookCover["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Allow only certain formats
    $allowedFormats = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Upload file
    if (!move_uploaded_file($bookCover["tmp_name"], $targetFile)) {
        die("Sorry, there was an error uploading your file.");
    }

    // Insert book data into the database
    $stmt = $conn->prepare("INSERT INTO books (title, author, description, published_date, cover_image, genre_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $author, $description, $published_date, $targetFile, $genre_id);

    if ($stmt->execute()) {
        header("Location: view_books.php?message=" . urlencode("Book added successfully!"));
        exit();
    } else {
        die("Error: " . $conn->error);
    }

    $stmt->close();
}

$conn->close();
?>
