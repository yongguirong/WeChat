<?php # add_page.php - Script 9.15
// This page both displays and handles the "add a page" form.

// Need the utilities file:
require('includes/utilities.inc.php');

// Redirect if the user doesn't have permission:
if (!$user->canCreatePage()) {
    header("Location:index.php");
    exit;
}
    
// Create a new form:
echo 'please use quickform to create a new form in this page!';
// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form submission
    
    // Validate the form data:
    if ($form->validate()) {
        
        // Insert into the database:
        $q = 'INSERT INTO pages (creatorId, title, content, dateAdded) VALUES (:creatorId, :title, :content, NOW())';
        $stmt = $pdo->prepare($q);
        $r = $stmt->execute(array(':creatorId' => $user->getId(), ':title' => $title->getValue(), ':content' => $content->getValue()));

        // Freeze the form upon success:
        if ($r) {
            $form->toggleFrozen(true);
            $form->removeChild($submit);
        }
                
    } // End of form validation IF.
    
} // End of form submission IF.

// Show the page:
$pageTitle = 'Add a Page';
include('includes/header.inc.php');
include('views/add_page.html');
include('includes/footer.inc.php');
?>
