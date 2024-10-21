<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized access');
}

// Get the search query
$searchQuery = $_POST['query'] ?? '';

// Prepare and execute the search query
$searchQuery = '%' . $conn->real_escape_string($searchQuery) . '%';
$stmt = $conn->prepare("
    SELECT b.id, b.title, b.cover_image 
    FROM books b 
    JOIN user_library ul ON b.id = ul.book_id 
    WHERE ul.user_id = ? AND (b.title LIKE ? OR b.author LIKE ?)
");
$user_id = $_SESSION['user_id'];
$stmt->bind_param("iss", $user_id, $searchQuery, $searchQuery);
$stmt->execute();
$results = $stmt->get_result();

// Generate HTML for the search results
if ($results->num_rows > 0) {
    while ($book = $results->fetch_assoc()) {
        echo '<div class="search-result-item" style="padding: 10px; border-bottom: 1px solid #ccc;">';
        echo '<a href="read_book.php?id=' . $book['id'] . '">';
        echo '<img src="' . htmlspecialchars($book['cover_image']) . '" alt="Book Cover" style="width: 50px; height: 75px; margin-right: 10px; vertical-align: middle;">';
        echo htmlspecialchars($book['title']);
        echo '</a>';
        echo '</div>';
    }
} else {
    echo '<div class="search-result-item" style="padding: 10px;">No results found.</div>';
}

$stmt->close();
$conn->close();
?>
