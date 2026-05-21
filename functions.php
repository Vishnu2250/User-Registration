<?php

require_once 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function redirect($url) {
    header("Location: " . $url); 
    exit();                 
}
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 6. registerUser($username, $email, $password) Function
 *    Handles the process of creating a new user record in the database.
 */
function registerUser($username, $email, $password) {
    global $link; // Access the database connection from config.php

    $hashed_password = hashPassword($password); // Hash the password before storing

    // Prepare an SQL INSERT statement using parameterized queries (for security against SQL injection)
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind parameters to the prepared statement (s = string)
        mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password);

        // Set the actual parameter values
        $param_username = $username;
        $param_email = $email;
        $param_password = $hashed_password;

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            return true; // Registration successful
        } else {
            // Error handling for duplicate entries (username or email must be unique)
            if (mysqli_errno($link) == 1062) { // MySQL error code for duplicate entry
                if (strpos(mysqli_error($link), 'username') !== false) {
                    return "Username already exists.";
                } elseif (strpos(mysqli_error($link), 'email') !== false) {
                    return "Email already registered.";
                }
            }
            return "Something went wrong. Please try again later."; // Generic error for other database issues
        }
        mysqli_stmt_close($stmt); // Close the statement
    }
    return "Database error."; // If preparing the statement failed
}


/**
 * 7. loginUser($identifier, $password) Function
 *    Handles user login by verifying credentials against the database.
 *    The $identifier can be either a username or an email.
 */
function loginUser($identifier, $password) {
    global $link; // Access the database connection

    // Determine if the identifier is an email or username
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Prepare a SELECT statement to fetch user data
    $sql = "SELECT id, username, email, password FROM users WHERE " . $field . " = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_identifier);
        $param_identifier = $identifier;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt); // Store result to check number of rows

            if (mysqli_stmt_num_rows($stmt) == 1) { // If a user with that identifier is found
                // Bind the result columns to PHP variables
                mysqli_stmt_bind_result($stmt, $id, $username, $email, $hashed_password);
                if (mysqli_stmt_fetch($stmt)) { // Fetch the row
                    if (verifyPassword($password, $hashed_password)) {
                        // Password is correct, start a session for the user
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["email"] = $email;
                        return true; // Login successful
                    } else {
                        return "Invalid password."; // Password mismatch
                    }
                }
            } else {
                return "No account found with that " . $field . "."; // User not found
            }
        } else {
            return "Oops! Something went wrong. Please try again later."; // Database execution error
        }
        mysqli_stmt_close($stmt); // Close the statement
    }
    return "Database error."; // If preparing the statement failed
}

/**
 * 8. isLoggedIn() Function
 *    Checks if a user is currently logged in by looking at the session variable.
 */
function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

/**
 * 9. requireLogin() Function
 *    A convenience function to enforce login for protected pages.
 *    If the user is not logged in, it redirects them to the login page.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect("login.php");
    }
}
?>