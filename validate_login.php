<?php
session_start();
include('db.php'); // Connection to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password'])) {
        $passwordEntered = $_POST['password'];
        $username = $_POST['username'] ?? null;

        if (!$username) {
            header("Location: user_selection.php?error=No+user+selected.");
            exit();
        }

        // Fetch user details
        $userQuery = $conn->prepare("SELECT id, password, user_type FROM users WHERE username = ?");
        if ($userQuery === false) {
            header("Location: user_selection.php?error=Database+error.");
            exit();
        }

        $userQuery->bind_param("s", $username);
        $userQuery->execute();

        if (!$userQuery) {
            header("Location: user_selection.php?error=Database+error.");
            exit();
        }

        $result = $userQuery->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($passwordEntered, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];

                // Redirect based on user role
                if ($user['user_type'] === 'librarian') {
                    header("Location: view_books.php"); // Redirect to librarian dashboard
                } else {
                    header("Location: my_library.php"); // Redirect to non-librarian dashboard
                }
                exit();
            } else {
                header("Location: user_selection.php?error=Invalid+password.");
                exit();
            }
        } else {
            header("Location: user_selection.php?error=User+not+found.");
            exit();
        }
    } else {
        header("Location: user_selection.php?error=Invalid+request.");
        exit();
    }
} else {
    header("Location: user_selection.php");
    exit();
}

// Close the connection
$conn->close();
?>
