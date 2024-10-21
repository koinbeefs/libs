<?php
session_start();

include('db.php');

$message = '';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL query to retrieve user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            // Redirect based on user type
            if ($user['user_type'] === 'librarian') {
                header("Location: view_books.php");
            } else {
                header("Location: my_library.php");
            }
            exit();
        } else {
            $message = "Invalid credentials!";
        }
    } else {
        $message = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #b8a38f;
        }
        .container {
            max-width: 400px;
            margin-top: 100px;
            padding: 20px;
            background-color: #78593b;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            width: 100%;
        }
        .alert {
            text-align: center;
        }
        label {
	        color: #FFFFFF;
        }
        p,h2{
            color: white;
        }
        a{
            color: whitesmoke;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #8a653f;">
        <a class="navbar-brand" href="view_books.php">
            <img src="logo.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
        </a>
    
        <a class="navbar-brand" style="font-size: 40px;" href="view_books.php">Books</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <form class="ml-2">
                <a class="btn btn-warning" style="font-size: 20px;" href="login.php">Login</a>
                <a class="btn btn-warning" style="font-size: 20px;" href="register.php">Register</a>
            </form>
            
        </div>
    </nav>
    <div class="container">
        <h2>Login</h2>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
