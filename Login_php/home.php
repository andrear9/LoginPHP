<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Homepage</title>
</head>
<body>
<h1>Homepage</h1>
</body>
</html>
