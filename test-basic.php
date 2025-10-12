<?php
echo "Basic test - Server is working!";
echo "\nTime: " . date('Y-m-d H:i:s');

if (isset($_GET['test'])) {
    echo "\nTest parameter: " . $_GET['test'];
}
?>