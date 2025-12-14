<?php
// index.php – main student feedback form (frontend + backend entry)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduSense — Submit Feedback</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Frontend CSS ni groupmate -->
    <!-- Dahil nasa root si index.php at nasa /frontend ang css, kailangan natin ng path na ito: -->
    <link rel="stylesheet" href="frontend/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm main-nav">
    <div class="container">
        <span class="navbar-brand fw-bold">EduSense</span>
        <!-- ayusin ang path papuntang admin login -->
        <a href="admin/login.php" class="nav-link text-white">Admin</a>
        <!-- kung ang login file mo ay auth.php instead of login.php, palitan mo lang ito:
             <a href="admin/auth.php" class="nav-link text-white">Admin</a>
        -->
    </div>
</nav>

<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold">Help us improve — Submit Feedback</h1>
        <p class="text-muted">Your feedback helps teachers make improvements.</p>
    </div>

    <div class="card shadow border-0 p-4 mx-auto" style="max-width: 650px; border-radius: 20px;">
        <h5 class="mb-3 fw-semibold">Submit Feedback</h5>

        <!-- IMPORTANT: action + method + names -->
        <!-- DITO NA TATAWAG SA submit_feedback.php (same folder) -->
        <form id="feedbackForm" action="submit_feedback.php" method="post" novalidate>
            <div class="mb-3">
                <label for="fbName" class="form-label fw-semibold">Name (optional)</label>
                <input
                    id="fbName"
                    name="name"
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="Anonymous">
            </div>

            <div class="mb-3">
                <label for="fbCourse" class="form-label fw-semibold">Course / Section</label>
                <input
                    id="fbCourse"
                    name="course"
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="e.g., BSIT 2A"
                    required>
            </div>

            <div class="mb-3">
                <label for="fbSubject" class="form-label fw-semibold">Subject</label>
                <input
                    id="fbSubject"
                    name="subject"
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="e.g., Web Dev"
                    required>
            </div>

            <div class="mb-3">
                <label for="fbComment" class="form-label fw-semibold">Your Feedback</label>
                <textarea
                    id="fbComment"
                    name="comment"
                    class="form-control form-control-lg"
                    rows="4"
                    placeholder="What can be improved? What did you like?"
                    required></textarea>
            </div>

            <button class="btn btn-primary btn-lg w-100 submit-btn" type="submit">
                Send Feedback
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
