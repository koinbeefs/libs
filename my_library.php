<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's library
$userLibraryQuery = $conn->prepare("
    SELECT b.* 
    FROM books b 
    JOIN user_library ul ON b.id = ul.book_id 
    WHERE ul.user_id = ?
");

$userLibraryQuery->bind_param("i", $user_id);
$userLibraryQuery->execute();
$results = $userLibraryQuery->get_result();

// You may want to check if the logged-in user is a librarian
$isLibrarian = isset($_SESSION['role']) && $_SESSION['role'] === 'librarian';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Library</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom style for the 'My Library' title in navigation bar */
        .navbar-brand {
            font-size: 2rem; /* Adjust size as needed */
        }

        /* Grid layout for the library items */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 books per row */
            gap: 20px; /* Space between items */
            margin-top: 30px;
        }

        .book-grid-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #b89a7d;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
        }

        .book-cover {
            width: 150px;
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }

        .book-buttons {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .book-buttons a {
            width: 48%; /* Adjust the width of the buttons */
        }
        
    </style>
</head>
<body style="background-color: #b8a38f;">

<nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #8a653f;">
    <a class="navbar-brand" href="my_library.php">
        <img src="books.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
    </a>
    <a class="navbar-brand" style="font-size:40px" href="my_library.php">Library</a>
    
    <div class="collapse navbar-collapse">     
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="browse_books.php" style="font-size:20px">Browse Books</a>
            </li>
        </ul>
        <!-- Logout Button that triggers the confirmation modal -->
        <button type="button" class="btn btn-warning ml-2" style="font-size:20px" data-toggle="modal" data-target="#logoutConfirmationModal">Logout</button>
    </div>
</nav>

<div class="container mt-5">
    <?php if ($results->num_rows > 0): ?>
        <div class="book-grid">
            <?php while ($book = $results->fetch_assoc()): ?>
                <div class="book-grid-item">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" class="book-cover">
                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                    <div class="book-buttons">
                        <a href="read_book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">Read</a>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#confirmRemoveModal" data-book-id="<?php echo $book['id']; ?>">Remove</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have not added any books to your library yet.</p>
    <?php endif; ?>
</div>

<!-- Confirmation Modal for Removing a Book -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1" role="dialog" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmRemoveModalLabel">Confirm Removal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove this book from your library?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmRemoveButton" class="btn btn-danger">Remove</a>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="logoutConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmationModalLabel">Confirm Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="logout.php" method="post" class="ml-2">
                    <button type="submit" class="btn btn-warning">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#confirmRemoveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var bookId = button.data('book-id'); // Extract info from data-* attributes
            var removeUrl = 'remove_from_library.php?book_id=' + bookId; // Construct the URL
            var modal = $(this);
            modal.find('#confirmRemoveButton').attr('href', removeUrl); // Update the button's href
        });
    });
</script>
</body>
</html>

<?php
// Close the database connection
$userLibraryQuery->close();
$conn->close();
?>
