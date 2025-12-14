let CURRENT_DATA = { feedback: [], training: [] };

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, c =>
    ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c])
  );
}

async function fetchData() {
  const FEEDBACK_URL = "../api/feedback.php";
  const TRAINING_URL = "../api/training.php";

  const [fbRes, trRes] = await Promise.all([
    fetch(FEEDBACK_URL),
    fetch(TRAINING_URL)
  ]);

  const feedback = await fbRes.json();
  const training = await trRes.json();

  return { feedback, training };
}

let sentimentChart = null;

function renderCounts(feedback) {
  const counts = feedback.reduce(
    (acc, f) => {
      const key = f.sentiment || "neutral";
      acc[key] = (acc[key] || 0) + 1;
      return acc;
    },
    { positive: 0, neutral: 0, negative: 0 }
  );

  const pos = document.getElementById("positiveCount");
  const neu = document.getElementById("neutralCount");
  const neg = document.getElementById("negativeCount");

  if (pos) pos.innerText = counts.positive;
  if (neu) neu.innerText = counts.neutral;
  if (neg) neg.innerText = counts.negative;

  return counts;
}

function renderChart(counts) {
  const ctx = document.getElementById("sentimentChart");
  if (!ctx || typeof Chart === "undefined") return;

  const data = [counts.positive, counts.neutral, counts.negative];

  if (sentimentChart) {
    sentimentChart.data.datasets[0].data = data;
    sentimentChart.update();
    return;
  }

  sentimentChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Positive", "Neutral", "Negative"],
      datasets: [
        {
          data,
          backgroundColor: ["#10b981", "#9ca3af", "#ef4444"]
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" }
      }
    }
  });
}

function renderRecentFeedback(feedback) {
  const wrapper = document.getElementById("feedbackTableWrapper");
  if (!wrapper) return;

  wrapper.innerHTML = "";

  if (!feedback.length) {
    wrapper.innerHTML = '<div class="text-muted small p-2">No feedback yet.</div>';
    return;
  }

  feedback.forEach(f => {
    const name = f.student_name || f.student || "Anonymous";
    const subject = f.subject || "";
    const created = f.created_at || "";
    const comment = f.comment || "";
    const sentiment = f.sentiment || "neutral";

    const badgeClass =
      sentiment === "positive"
        ? "success"
        : sentiment === "negative"
        ? "danger"
        : "secondary";

    wrapper.innerHTML += `
      <div class="p-2 border-bottom">
        <div class="d-flex justify-content-between">
          <div>
            <strong>${escapeHtml(name)}</strong>
            <div class="small text-muted">
              ${escapeHtml(subject)} • ${escapeHtml(created)}
            </div>
          </div>
          <div>
            <span class="badge bg-${badgeClass}">
              ${escapeHtml(sentiment)}
            </span>
          </div>
        </div>
        <div class="mt-2 small">${escapeHtml(comment)}</div>
      </div>
    `;
  });
}

function renderTrainingTable(training) {
  const tbody = document.querySelector("#trainingTable tbody");
  if (!tbody) return;

  tbody.innerHTML = "";

  if (!training.length) {
    return;
  }

  training.forEach(r => {
    tbody.innerHTML += `
      <tr>
        <td>${r.id}</td>
        <td>${escapeHtml(r.gender)}</td>
        <td>${escapeHtml(r.income)}</td>
        <td>${escapeHtml(r.interest)}</td>
        <td>${escapeHtml(r.category)}</td>
      </tr>
    `;
  });
}

async function renderDashboard() {
  const data = await fetchData();
  CURRENT_DATA = data;

  const counts = renderCounts(data.feedback);
  renderChart(counts);
  renderRecentFeedback(data.feedback);
  renderTrainingTable(data.training);
}

function exportCSV() {
  const rows = [["ID", "Student", "Subject", "Comment", "Sentiment", "Created At"]];
  CURRENT_DATA.feedback.forEach(f => {
    rows.push([
      f.id,
      f.student_name || f.student || "Anonymous",
      f.subject || "",
      f.comment || "",
      f.sentiment || "",
      f.created_at || ""
    ]);
  });

  const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = "feedback.csv";
  a.click();
  URL.revokeObjectURL(url);
}

document.addEventListener("DOMContentLoaded", () => {
  // Dashboard page only
  if (document.getElementById("sentimentChart")) {
    renderDashboard();

    const refreshBtn = document.getElementById("refreshBtn");
    const downloadBtn = document.getElementById("downloadBtn");

    if (refreshBtn) refreshBtn.addEventListener("click", renderDashboard);
    if (downloadBtn) downloadBtn.addEventListener("click", exportCSV);
  }

  // WALANG event listener para sa feedback form
});
