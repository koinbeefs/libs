<?php
session_start();
include('db.php'); // Connection to your database

// Fetch user roles
$librarianQuery = $conn->query("SELECT * FROM users WHERE user_type = 'librarian'");
$nonLibrarianQuery = $conn->query("SELECT * FROM users WHERE user_type = 'non-librarian'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Selection</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Select User or Add New User</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col">
                <h4>Librarian Accounts:</h4>
                <ul class="list-group">
                    <?php while ($librarian = $librarianQuery->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($librarian['username']); ?>
                            <div>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#librarianModal<?php echo $librarian['id']; ?>">
                                    Login
                                </button>

                                <div class="modal fade" id="librarianModal<?php echo $librarian['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="librarianModalLabel<?php echo $librarian['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Enter Password for Librarian</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="validate_login.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="librarianUserPassword">Password</label>
                                                        <input type="password" class="form-control" name="password" required>
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($librarian['username']); ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Confirmation Modal -->
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirmDeleteModal<?php echo $librarian['id']; ?>">
                                    Delete
                                </button>

                                <div class="modal fade" id="confirmDeleteModal<?php echo $librarian['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel<?php echo $librarian['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this librarian account?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <form action="remove_user.php" method="post" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $librarian['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="col">
                <h4>Non-Librarian Accounts:</h4>
                <ul class="list-group">
                    <?php while ($nonLibrarian = $nonLibrarianQuery->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($nonLibrarian['username']); ?>
                            <div>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#nonLibrarianModal<?php echo $nonLibrarian['id']; ?>">
                                    Login
                                </button>

                                <div class="modal fade" id="nonLibrarianModal<?php echo $nonLibrarian['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="nonLibrarianModalLabel<?php echo $nonLibrarian['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Enter Password for Non-Librarian</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="validate_login.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="nonLibrarianUserPassword">Password</label>
                                                        <input type="password" class="form-control" name="password" required>
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($nonLibrarian['username']); ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Confirmation Modal for Non-Librarian -->
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirmDeleteNonLibrarianModal<?php echo $nonLibrarian['id']; ?>">
                                    Delete
                                </button>

                                <div class="modal fade" id="confirmDeleteNonLibrarianModal<?php echo $nonLibrarian['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteNonLibrarianModalLabel<?php echo $nonLibrarian['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this non-librarian account?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <form action="remove_user.php" method="post" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $nonLibrarian['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <a href="register.php" class="btn btn-secondary mt-3">Create New Account</a>
    </div>
</body>
</html>

<?php
// Close the connection
$librarianQuery->close();
$nonLibrarianQuery->close();
$conn->close();
?>
 