<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookId = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $published_date = $_POST['published_date'];
    $genre_id = $_POST['genre_id']; // Get the genre ID from the form

    // Prepare the statement for updating the book
    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, description=?, published_date=?, genre_id=? WHERE id=?");
    $stmt->bind_param("ssssii", $title, $author, $description, $published_date, $genre_id, $bookId);

    if ($stmt->execute()) {
        // If a new cover image is uploaded, handle the upload
        if (!empty($_FILES['book_cover']['name'])) {
            // Handle the file upload logic
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
            if (move_uploaded_file($bookCover["tmp_name"], $targetFile)) {
                // Update the book's cover image in the database
                $stmt = $conn->prepare("UPDATE books SET cover_image=? WHERE id=?");
                $stmt->bind_param("si", $targetFile, $bookId);
                $stmt->execute();
            }
        }

        header("Location: view_books.php?message=" . urlencode("Book updated successfully!"));
        exit();
    } else {
        die("Error updating book: " . $conn->error);
    }

    $stmt->close();
}

$conn->close();
?>
