<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>회원관리 시스템</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">MemberSystem</a>
        <div class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="nav-link text-white" href="mypage.php">마이페이지</a>
                <a class="nav-link text-white" href="logout.php">로그아웃</a>
            <?php else: ?>
                <a class="nav-link text-white" href="login.php">로그인</a>
                <a class="nav-link text-white" href="signup.php">회원가입</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">