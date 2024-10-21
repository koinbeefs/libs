<?php
session_start();
include('db.php');

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userName = isset($_SESSION['username']) ? $_SESSION['username'] : 'users';

// Handle deletion of a book
if (isset($_GET['id'])) {
    $deleteId = $_GET['id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete all associated book contents
        $stmt = $conn->prepare("DELETE FROM book_contents WHERE book_id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();
    
        // Now delete the book from the database
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
    
        if ($stmt->execute()) {
            // Commit transaction if everything was successful
            $conn->commit();
            $message = "Book and associated contents deleted successfully!";
        } else {
            throw new Exception("Error deleting book: " . $conn->error);
        }
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $message = "Error while processing: " . $e->getMessage();
    }
    
    // Redirect to the books page with the message
    header("Location: view_books.php?message=" . urlencode($message));
    exit();
}


// Initialize the search variable
$search = '';

// Check if search term is set in the request
if (isset($_POST['search_term']) || isset($_GET['search_term'])) {
    $search = isset($_POST['search_term']) ? $_POST['search_term'] : $_GET['search_term'];
    $search = $conn->real_escape_string($search); // Escape to prevent SQL injection
}

// Set the number of results per page
$resultsPerPage = 5;

// Get the current page number, default to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL LIMIT clause
$offset = ($currentPage - 1) * $resultsPerPage;

// Query to get the total number of results based on the search
$totalResultsQuery = "SELECT COUNT(*) as total FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%'";
$totalResults = $conn->query($totalResultsQuery);
$row = $totalResults->fetch_assoc();
$totalCount = $row['total'];

// Calculate total pages
$totalPages = ceil($totalCount / $resultsPerPage);

// Fetch the results for the current page based on the search
$query = "SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%' LIMIT $offset, $resultsPerPage";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Books</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        th,td{
            color: black;
        }
        .td{
            font-size: 25px;
        }
    </style>
</head>
<body style="background-color: #b8a38f;">
    <nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #805933;">
    <a class="navbar-brand" href="view_books.php">
            <img src="books.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
        </a>
    
        <a class="navbar-brand" style="font-size: 40px;" href="view_books.php">Books</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <span class="navbar-text mr-3" style="font-size: 20px;"><?php echo htmlspecialchars($userName); ?></span>
            <form action="logout.php" method="post" class="ml-2">
                <button type="submit" class="btn btn-warning" style="font-size: 20px;">Logout</button>
            </form>

        </div>
    </nav>
    <div class="container mt-5">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>
        <div class="row mb-5">
            <div class="col-md-6" >
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBookModal" >
                    Add New Book
                </button>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchTerm" class="form-control" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>" aria-label="Search">
                    <div class="input-group-append">
                        <button id="clearSearch" class="btn btn-outline-secondary" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
<!-- The Modal for Editing Book -->
<div class="modal fade" id="editBookModal" tabindex="-1" role="dialog" aria-labelledby="editBookModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="margin-top: 150px;">
            <div class="modal-header" style="background-color: #8a653f;">
                <h5 class="modal-title" id="editBookModalLabel">Update Book</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBookForm" method="POST" action="process_edit_book.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="editBookId" name="id">
                    <div class="form-group">
                        <label for="editBookTitle">Title</label>
                        <input type="text" class="form-control" id="editBookTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="editBookAuthor">Author</label>
                        <input type="text" class="form-control" id="editBookAuthor" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="editBookDescription">Description</label>
                        <textarea class="form-control" id="editBookDescription" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editBookPublishedDate">Published Date</label>
                        <input type="date" class="form-control" id="editBookPublishedDate" name="published_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editBookGenre">Genre</label>
                        <select class="form-control" id="editBookGenre" name="genre_id" required>
                            <option value="">Select Genre</option>
                            <?php
                            // Fetch genres from the database
                            $genresResult = $conn->query("SELECT * FROM book_genre");
                            while ($genre = $genresResult->fetch_assoc()) {
                                echo '<option value="' . $genre['id'] . '">' . htmlspecialchars($genre['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editBookCover">Book Cover Image (optional)</label>
                        <input type="file" class="form-control-file" id="editBookCover" name="book_cover" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #6c757d;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #dc3545">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


        <!-- The Modal for Adding Book -->
<!-- The Modal for Adding Book -->
<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="margin-top: 150px;">
            <div class="modal-header" style="background-color: #8a653f;">
                <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBookForm" method="POST" action="process_add_book.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bookTitle">Title</label>
                        <input type="text" class="form-control" id="bookTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="bookAuthor">Author</label>
                        <input type="text" class="form-control" id="bookAuthor" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="bookDescription">Description</label>
                        <textarea class="form-control" id="bookDescription" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="bookPublishedDate">Published Date</label>
                        <input type="date" class="form-control" id="bookPublishedDate" name="published_date" required>
                    </div>
                    <div class="form-group">
                        <label for="bookGenre">Genre</label>
                        <select class="form-control" id="bookGenre" name="genre_id" required>
                            <option value="">Select Genre</option>
                            <?php
                            // Fetch genres from the database
                            $genresResult = $conn->query("SELECT * FROM book_genre");
                            while ($genre = $genresResult->fetch_assoc()) {
                                echo '<option value="' . $genre['id'] . '">' . htmlspecialchars($genre['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bookCover">Book Cover Image</label>
                        <input type="file" class="form-control-file" id="bookCover" name="book_cover" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #6c757d;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #dc3545">Close</button>
                    <button type="submit" class="btn btn-primary">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Confirmation Modal for Deleting a Book -->
        <div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="margin-top: 300px;"> 
                    <div class="modal-header" style="background-color: #8a653f;">
                        <h5 class="modal-title" id="deleteBookModalLabel">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this book? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" >Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <table class="table" >
            <thead>
                <tr style="background-color: #8a653f;">
                    
                    <th style="font-size: 30px; text-align: center;">Cover</th>
                    <th style="font-size: 30px; text-align: center;">Title</th>
                    <th style="font-size: 30px; text-align: center;">Author</th>
                    <th style="font-size: 30px; text-align: center;">Published Date</th>
                    <th style="font-size: 30px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="bookResults">
                <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($book['cover_image']): ?>
                            <img src="<?php echo $book['cover_image']; ?>" alt="Book Cover" style="width: 50px; height: auto;">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size: 18px; text-align: center;"><?php echo htmlspecialchars($book['title']); ?></td>
                    <td style="font-size: 18px; text-align: center;"><?php echo htmlspecialchars($book['author']); ?></td>
                    <td style="font-size: 18px; text-align: center;"><?php echo htmlspecialchars($book['published_date']); ?></td>
                    <td style="text-align: center; font-size: 18px;">
                        <a href="view_book.php?id=<?php echo $book['id']; ?>" class="btn btn-info">Read The Book</a>
                        <button type="button" class="btn btn-warning" onclick="showEditBookModal(<?php echo $book['id']; ?>)">Edit</button>
                        <a href="#" class="btn btn-danger" onclick="showDeleteBookModal(<?php echo $book['id']; ?>)">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search_term=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <script>
        let bookIdToDelete = null;

        function showDeleteBookModal(bookId) {
            bookIdToDelete = bookId; // Store the ID of the book to delete
            $('#deleteBookModal').modal('show'); // Show the confirmation modal
        }

        $('#confirmDelete').on('click', function() {
            if (bookIdToDelete) {
                // Redirect to delete the book
                window.location.href = "?id=" + bookIdToDelete;
            }
        });

        $(document).ready(function() {
            // Handle the live search functionality
            $('#searchTerm').on('input', function() {
                const searchTerm = $(this).val();

                $.ajax({
                    url: 'search.php',
                    type: 'GET',
                    data: { search_term: searchTerm },
                    success: function(response) {
                        $('#bookResults').html(response);
                    }
                });
            });

            // Clear search field
            $('#clearSearch').on('click', function() {
                $('#searchTerm').val('');
                location.reload();
            });
        });
        function showEditBookModal(bookId) {
    // Make an AJAX call to fetch book details
    $.ajax({
        url: 'fetch_book.php', // Assuming you have a fetch_book.php file to get book details
        type: 'GET',
        data: { id: bookId },
        success: function(response) {
            const book = JSON.parse(response);

            // Populate the fields in the edit modal
            $('#editBookId').val(book.id);
            $('#editBookTitle').val(book.title);
            $('#editBookAuthor').val(book.author);
            $('#editBookDescription').val(book.description);
            $('#editBookPublishedDate').val(book.published_date);

            // Show the modal
            $('#editBookModal').modal('show');
        },
        error: function() {
            alert("Error fetching book details.");
        }
    });
}

    </script>
</body>
</html>

<?php $conn->close(); ?>
