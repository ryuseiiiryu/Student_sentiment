<?php
// api/feedback.php
require_once __DIR__ . '/../config.php';
if (!isset($conn) || !$conn) die('DB connection missing');

// simple local sentiment fallback same as dashboard (copy)
function simple_sentiment_from_text($text) {
    $t = strtolower($text);
    $pos = ['good','great','nice','excellent','helpful','loved','love','easy','clear','best','awesome','amazing','well'];
    $neg = ['bad','poor','hate','difficult','hard','boring','slow','terrible','worse','awful','confusing','too poor','don\'t understand','can\'t understand'];
    $pcount = 0; $ncount = 0;
    foreach ($pos as $w) if (strpos($t, $w) !== false) $pcount++;
    foreach ($neg as $w) if (strpos($t, $w) !== false) $ncount++;
    if ($pcount > $ncount && ($pcount+$ncount)>0) return 'Positive';
    if ($ncount > $pcount && ($pcount+$ncount)>0) return 'Negative';
    if (($pcount+$ncount) > 0 && $pcount == $ncount) return 'Neutral';
    return 'Unknown';
}

$res = $conn->query("SELECT id, student_name, course, subject, comment, IFNULL(sentiment,'') AS sentiment, created_at FROM feedback ORDER BY id DESC");
$rows = [];
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>All Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f6f8fa; font-family:Inter, system-ui, sans-serif; }
    aside { width:240px; position:fixed; top:0; left:0; bottom:0; background:#fff; border-right:1px solid #eee; padding:28px; }
    main { margin-left:260px; padding:28px; }
    .nav-link.active { background:#eef5ff; border-radius:8px; font-weight:600; color:#0b5ed7; }
    .badge-positive { background:#11a76b; color:#fff; }
    .badge-neutral { background:#8f99a6; color:#fff; }
    .badge-negative { background:#e04b4b; color:#fff; }
  </style>
</head>
<body>
  <aside>
    <h4>EduSense<br><small class="text-muted">Admin Panel</small></h4>
    <nav class="nav flex-column mt-4">
      <a class="nav-link" href="../admin/dashboard.php">Dashboard</a>
      <a class="nav-link active" href="../api/feedback.php">Feedback</a>
      <a class="nav-link" href="../frontend/feedback.html">Add Feedback</a>
    </nav>
    <div class="mt-4"><a class="btn btn-outline-secondary" href="../admin/logout.php">Logout</a></div>
  </aside>

  <main>
    <h1>All Feedback</h1>
    <p><a href="../admin/dashboard.php">Back to Dashboard</a></p>

    <table class="table table-striped bg-white">
      <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Course</th><th>Subject</th><th>Comment</th><th>Sentiment</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): 
            $dbSent = trim($r['sentiment']);
            $label = $dbSent !== '' && strtolower($dbSent) !== 'unknown' ? $dbSent : simple_sentiment_from_text($r['comment'] ?? '');
            $lc = strtolower($label);
            $badgeClass = 'badge-neutral';
            if ($lc === 'positive') $badgeClass = 'badge-positive';
            if ($lc === 'negative') $badgeClass = 'badge-negative';
        ?>
          <tr>
            <td><?php echo htmlspecialchars($r['id']); ?></td>
            <td><?php echo htmlspecialchars($r['student_name'] ?: 'Anonymous'); ?></td>
            <td><?php echo htmlspecialchars($r['course']); ?></td>
            <td><?php echo htmlspecialchars($r['subject']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($r['comment'])); ?></td>
            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($label)); ?></span></td>
            <td><?php echo htmlspecialchars($r['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
