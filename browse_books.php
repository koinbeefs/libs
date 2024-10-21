<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize search and genre variables
$search = '';
$selectedGenre = '';

// Check if the search form is submitted
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['genre'])) {
    $selectedGenre = $_GET['genre'];
}

// Fetch all genres for the dropdown
$genreQuery = $conn->query("SELECT * FROM book_genre");

// Pagination variables
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Prepare the book query with optional search and genre filtering
$searchQuery = "%" . $conn->real_escape_string($search) . "%";
if ($selectedGenre !== '') {
    // If genre filter is applied
    $bookQuery = $conn->prepare("
        SELECT * FROM books 
        WHERE (title LIKE ? OR author LIKE ?) AND genre_id = ?
        LIMIT ? OFFSET ?
    ");
    $bookQuery->bind_param("ssiii", $searchQuery, $searchQuery, $selectedGenre, $limit, $offset);
} else {
    // No genre filter applied
    $bookQuery = $conn->prepare("
        SELECT * FROM books 
        WHERE title LIKE ? OR author LIKE ?
        LIMIT ? OFFSET ?
    ");
    $bookQuery->bind_param("ssii", $searchQuery, $searchQuery, $limit, $offset);
}

$bookQuery->execute();
$results = $bookQuery->get_result();

// Count total number of books for pagination
if ($selectedGenre !== '') {
    // Count with genre filter
    $countQuery = $conn->prepare("
        SELECT COUNT(*) AS total FROM books 
        WHERE (title LIKE ? OR author LIKE ?) AND genre_id = ?
    ");
    $countQuery->bind_param("ssi", $searchQuery, $searchQuery, $selectedGenre);
} else {
    // Count without genre filter
    $countQuery = $conn->prepare("
        SELECT COUNT(*) AS total FROM books 
        WHERE title LIKE ? OR author LIKE ?
    ");
    $countQuery->bind_param("ss", $searchQuery, $searchQuery);
}

$countQuery->execute();
$countResult = $countQuery->get_result();
$totalBooks = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalBooks / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom style for grid view */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .book-card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            background-color: #b89a7d;
        }
        .book-cover {
            width: 150px;
            height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
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
    <div class="row mb-5">
        <div class="col-md-6">
            <h2 class="mt-6">Browse Books</h2>
        </div>
        <div class="col-md-6">
            <form class="form-inline" id="searchForm" method="get" action="browse_books.php">
                <div class="input-group">
                    <input type="text" id="searchInput" name="search" class="form-control mr-2" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
                    <div class="input-group-append">
                        <button type="button" id="clearSearch" class="btn btn-outline-secondary" style="background-color: #fff;">
                            &times; <!-- X icon -->
                        </button>
                    </div>
                </div>
                <!-- Genre filter -->
                <select name="genre" class="form-control ml-2" id="genreSelect">
                    <option value="">All Genres</option>
                    <?php while ($genre = $genreQuery->fetch_assoc()): ?>
                        <option value="<?php echo $genre['id']; ?>" <?php echo ($selectedGenre == $genre['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary ml-2">Search</button>
            </form>
        </div>
    </div>
    <div class="book-grid">
    <?php if ($results->num_rows > 0): ?>
        <?php while ($book = $results->fetch_assoc()): ?>
            <div class="book-card">
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" class="book-cover">
                <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                <a href="read_book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">Read</a>
                <a href="#" class="btn btn-success add-to-library" data-book-id="<?php echo $book['id']; ?>">Add to Library</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No books found.</p>
    <?php endif; ?>
</div>

    <!-- Pagination Controls -->
    <div class="pagination-container mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($selectedGenre); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($selectedGenre); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($selectedGenre); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
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
                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit" class="btn btn-warning">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Notification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="responseModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Automatically submit form when a genre is selected
    $('#genreSelect').on('change', function() {
        $('#searchForm').submit();
    });

    // Live search functionality
    $('#searchInput').on('input', function() {
        const query = $(this).val();
        const genre = $('select[name="genre"]').val();
        if (query.length > 0) {
            $.ajax({
                url: 'live_search.php',
                method: 'GET',
                data: { search: query, genre: genre },
                success: function(data) {
                    $('.book-grid').html(data); // Update the grid with search results
                }
            });
        } else {
            location.reload(); // Reload the original page
        }
    });

    // Clear button functionality
    $('#clearSearch').on('click', function() {
        $('#searchInput').val(''); // Clear input field
        location.reload(); // Reload the original page
    });
    $('.add-to-library').on('click', function(e) {
        e.preventDefault(); // Prevent default anchor behavior
        const bookId = $(this).data('book-id');

        $.ajax({
            url: 'add_to_library.php',
            method: 'GET',
            data: { book_id: bookId },
            dataType: 'json',
            success: function(response) {
                $('#responseModalLabel').text('Notification');
                $('#responseModalBody').text(response.message);
                $('#responseModal').modal('show');
            },
            error: function() {
                $('#responseModalLabel').text('Notice');
                $('#responseModalBody').text('You already have this book.');
                $('#responseModal').modal('show');
            }
        });
    });
});
</script>
</body>
</html>

<?php
// Close the database connection
$bookQuery->close();
$countQuery->close();
$conn->close();
?>
