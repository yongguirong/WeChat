<?php # login.php - 9.11
// This page both displays and handles the login form.

// Need the utilities file:
require('includes/utilities.inc.php');

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form submission
    
    // Validate the form data:
    if ($form->validate()) {
        
        // Check against the database:
        $q = 'SELECT id, userType, username, email FROM users WHERE email=:email AND pass=SHA1(:pass)';
        $stmt = $pdo->prepare($q);
        $r = $stmt->execute(array(':email' => $email->getValue(), ':pass' => $password->getValue()));

        // Try to fetch the results:
        if ($r) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
            $user = $stmt->fetch();
        }
        
        // Store the user in the session and redirect:
        if ($user) {
    
            // Store in a session:
            $_SESSION['user'] = $user;
    
            // Redirect:
            header("Location:index.php");
            exit;
    
        }
        
    } // End of form validation IF.
    
} // End of form submission IF.

// Show the login page:
$pageTitle = 'Login';
include('includes/header.inc.php');
include('views/login.html');
include('includes/footer.inc.php');
?>