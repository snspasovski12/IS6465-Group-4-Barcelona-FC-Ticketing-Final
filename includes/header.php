<?php
/*
 * header.php: opens every page. Set $pageTitle (and optionally $pageRole)
 * before including this file.
 */
require_once __DIR__ . "/init.php";
$pageTitle = $pageTitle ?? "Home";
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | FC Barcelona Ticketing</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require __DIR__ . "/nav.php"; ?>
    <main class="container py-4 flex-grow-1">
