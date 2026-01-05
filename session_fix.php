<?php
// session_fix.php - Untuk debugging session
session_start();

echo "<h1>Session Debug</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Actions:</h2>";
echo "<a href='?action=clear'>Clear Session</a> | ";
echo "<a href='?action=destroy'>Destroy Session</a> | ";
echo "<a href='login.php'>Go to Login</a>";

if(isset($_GET['action'])) {
    if($_GET['action'] == 'clear') {
        $_SESSION = array();
        echo "<p>Session cleared!</p>";
    } elseif($_GET['action'] == 'destroy') {
        session_destroy();
        echo "<p>Session destroyed!</p>";
    }
}
?>