<?php
require_once "./controlador/MainController.php";
require_once "./controlador/SessionController.php";

// Iniciar sesión
$sessionController = new SessionController();
$sessionController->startSession();

$vista_actual = isset($_GET['vista']) ? $_GET['vista'] : '';
$sessionController->checkProtectedView($vista_actual);

$controller = new MainController();
$controller->handleRequest();







