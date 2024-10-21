<?php
include('db.php');

if (isset($_POST['search_term'])) {
    $search = $conn->real_escape_string($_POST['search_term']);
    $query = "SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%'";
    $result = $conn->query($query);

    if ($result) {
        while ($book = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($book['id']) . '</td>';
            echo '<td>' . htmlspecialchars($book['title']) . '</td>';
            echo '<td>' . htmlspecialchars($book['author']) . '</td>';
            echo '<td>' . htmlspecialchars($book['published_date']) . '</td>';
            echo '<td>';
            echo '<a href="view_book.php?id=' . $book['id'] . '" class="btn btn-info">Read The Book</a>';
            echo '<button type="button" class="btn btn-warning" onclick="showEditBookModal(' . $book['id'] . ')">Edit</button>';
            echo '<a href="?id=' . $book['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this book?\');">Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
    }
}

$conn->close();
?>
