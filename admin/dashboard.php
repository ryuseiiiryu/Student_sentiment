<?php
// admin/dashboard.php
session_start();

require_once __DIR__ . '/../config.php'; 

// 1. Error Reporting (Temporary: remove after fixing)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($conn) || !$conn) {
    die("Database connection not available.");
}

// --- HELPER FUNCTIONS ---

// Copy of the logic from your working api/feedback.php
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

function normalize_sentiment($s) {
    if (!is_string($s)) return 'unknown';
    $t = trim(strtolower($s));
    
    $positive = ['positive','pos','+','p','good'];
    $neutral  = ['neutral','neu','n','0','okay','ok'];
    $negative = ['negative','neg','-','bad'];

    if (in_array($t, $positive, true)) return 'positive';
    if (in_array($t, $neutral, true)) return 'neutral';
    if (in_array($t, $negative, true)) return 'negative';
    return 'unknown';
}

// --- DATA FETCHING ---

$positive = $neutral = $negative = 0;
$recent = [];

// 1. FETCH ALL FEEDBACK TO CALCULATE COUNTS (Fixes the "0" issue)
// We fetch everything because we need to run PHP logic on comments if sentiment is missing
$res = $conn->query("SELECT sentiment, comment FROM feedback");

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $dbSent = $r['sentiment'] ?? '';
        $comment = $r['comment'] ?? '';

        // LOGIC: If DB sentiment is missing/unknown, calculate it from comment
        if (empty($dbSent) || strtolower($dbSent) === 'unknown') {
            $effectiveSentiment = simple_sentiment_from_text($comment);
        } else {
            $effectiveSentiment = $dbSent;
        }

        // Now normalize and count
        $norm = normalize_sentiment($effectiveSentiment);
        if ($norm === 'positive') $positive++;
        elseif ($norm === 'neutral') $neutral++;
        elseif ($norm === 'negative') $negative++;
    }
}

// 2. RECENT FEEDBACK (With the same fix applied)
$res2 = $conn->query("SELECT student_name, course, subject, comment, sentiment, created_at FROM feedback ORDER BY created_at DESC LIMIT 6");

if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        $dbSent = $r['sentiment'] ?? '';
        
        // Apply same fallback logic for display
        if (empty($dbSent) || strtolower($dbSent) === 'unknown') {
            $r['effective_sentiment'] = simple_sentiment_from_text($r['comment']);
            $r['is_calculated'] = true; // Mark as calculated so we know
        } else {
            $r['effective_sentiment'] = $dbSent;
            $r['is_calculated'] = false;
        }
        
        $r['sentiment_norm'] = normalize_sentiment($r['effective_sentiment']);
        $recent[] = $r;
    }
}

$total_count = $positive + $neutral + $negative;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EduSense — Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{--sidebar-w:240px;}
    body { font-family: Inter, system-ui, -apple-system, sans-serif; background:#f3f5f7; }
    .sidebar { width:var(--sidebar-w); position:fixed; left:0; top:0; bottom:0; background:#fff; border-right:1px solid rgba(0,0,0,0.04); padding:28px 18px; }
    .content { margin-left:var(--sidebar-w); padding:28px; }
    .card-stats { display:flex; gap:18px; }
    .stat-card { flex:1; padding:24px; border-radius:10px; background:#fff; box-shadow:0 1px 0 rgba(0,0,0,0.03); text-align:center; }
    .stat-card .num { font-size:28px; font-weight:600; margin-top:8px; }
    .recent-badge { display:inline-block; padding:6px 10px; border-radius:999px; font-size:13px; color:#fff; }
    .badge-positive { background:#0ea57f; }
    .badge-neutral { background:#8b8f95; }
    .badge-negative { background:#e24b4b; }
    .badge-unknown { background:#7a7f85; color:#fff; }
    .chart-card { background:#fff; padding:18px; border-radius:8px; box-shadow:0 1px 0 rgba(0,0,0,0.03); }
    .nav-link.active { background: rgba(3,102,214,0.06); border-radius:6px; color:#0366d6!important; font-weight:600; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <h4 class="mb-1">EduSense</h4>
    <small class="text-muted">Admin Panel</small>
    <nav class="nav flex-column mt-4">
      <a class="nav-link active" href="dashboard.php">Dashboard</a>
      <a class="nav-link" href="../api/feedback.php">Feedback</a>
      <a class="nav-link" href="../frontend/feedback.html">Add Feedback</a>
      <div class="mt-4"><a class="btn btn-outline-secondary w-100" href="logout.php">Logout</a></div>
    </nav>
  </aside>

  <main class="content">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h1>Dashboard</h1>
        <p class="text-muted">Overview of collected feedback</p>
      </div>
      <div class="text-end">
        <div class="small text-muted">Signed in as <strong>admin</strong></div>
        <div class="mt-2">
            <a class="btn btn-primary btn-sm" href="dashboard.php">Refresh Data</a>
        </div>
      </div>
    </div>

    <div class="mt-3 card-stats">
      <div class="stat-card">
        <div class="text-muted">Positive</div>
        <div class="num text-success"><?php echo (int)$positive; ?></div>
      </div>
      <div class="stat-card">
        <div class="text-muted">Neutral</div>
        <div class="num text-secondary"><?php echo (int)$neutral; ?></div>
      </div>
      <div class="stat-card">
        <div class="text-muted">Negative</div>
        <div class="num text-danger"><?php echo (int)$negative; ?></div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-8">
        <div class="chart-card">
          <h5>Sentiment Distribution</h5>
          <div style="height:300px; display:flex; justify-content:center; align-items:center;">
             <?php if ($total_count === 0): ?>
               <div class="text-muted">No sentiment data detected.</div>
             <?php else: ?>
               <canvas id="sentimentChart"></canvas>
             <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card chart-card">
          <h6>Recent Feedback</h6>
          <div class="list-group list-group-flush">
            <?php foreach ($recent as $item):
              $s_norm = $item['sentiment_norm'];
              $label = ucfirst($item['effective_sentiment']);
              
              if ($s_norm === 'positive') $badgeClass = 'badge-positive';
              elseif ($s_norm === 'neutral') $badgeClass = 'badge-neutral';
              elseif ($s_norm === 'negative') $badgeClass = 'badge-negative';
              else $badgeClass = 'badge-unknown';
            ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                    <strong><?php echo htmlspecialchars($item['student_name'] ?: 'Anonymous'); ?></strong>
                    <span class="recent-badge <?php echo $badgeClass; ?>" style="font-size:10px; padding:4px 8px;">
                        <?php echo $label; ?>
                        <?php if(!empty($item['is_calculated'])) echo "*"; ?> 
                    </span>
                </div>
                <small class="text-muted"><?php echo htmlspecialchars($item['subject']); ?></small>
                <div class="mt-1 small text-dark"><?php echo nl2br(htmlspecialchars($item['comment'])); ?></div>
              </div>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
              <div class="p-3 text-muted">No recent feedback.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 card chart-card">
      <h6>Training Data (Sample)</h6>
      <p class="text-muted small">This is a static placeholder. To make this work, you need a 'training_data' table in your DB.</p>
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Text Input</th><th>Label</th><th>Status</th></tr></thead>
        <tbody>
           <tr><td>101</td><td>"The teacher was great"</td><td>Positive</td><td><span class="badge bg-success">Verified</span></td></tr>
           <tr><td>102</td><td>"I am confused"</td><td>Negative</td><td><span class="badge bg-warning text-dark">Pending</span></td></tr>
        </tbody>
      </table>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const counts = {
      positive: <?php echo $positive; ?>,
      neutral:  <?php echo $neutral; ?>,
      negative: <?php echo $negative; ?>
    };
    
    // Only render chart if we have data
    if ((counts.positive + counts.neutral + counts.negative) > 0) {
        const ctx = document.getElementById('sentimentChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [counts.positive, counts.neutral, counts.negative],
                    backgroundColor: ['#0ea57f', '#8b8f95', '#e24b4b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }
  </script>
</body>
</html>