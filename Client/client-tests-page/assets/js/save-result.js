/**
 * Save a completed test result to the database via AJAX POST.
 *
 * @param {number}      assessmentId  - The assessment_id from the assessments table
 * @param {Array}       rawAnswers    - Array of {q, value, label} answer objects
 * @param {number}      traitScore    - Calculated total score
 * @param {string|null} level         - Severity level enum: MINIMAL, LOW, MEDIUM, HIGH, SEVERE
 * @param {Object|null} rawResult     - Structured result object (levels, subscales, etc.) or null
 * @returns {Promise<Object>}         - Server response { success, result_id } or { success: false, error }
 */
function saveResult(assessmentId, rawAnswers, traitScore, level, rawResult) {
  if (arguments.length === 4 && level && typeof level === 'object') {
    rawResult = level;
    level = null;
  }

  const saveUrl = './tests-database.php';

  return fetch(saveUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      assessment_id: assessmentId,
      raw_answers: rawAnswers,
      trait_score: traitScore,
      level: level,
      raw_result: rawResult,
    }),
  })
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (data.success) {
        showSaveStatus('تم حفظ نتيجتك بنجاح ✓', 'success');
      } else {
        showSaveStatus('تعذّر حفظ النتيجة', 'error');
      }
      return data;
    })
    .catch(function (err) {
      console.error('Save error:', err);
      showSaveStatus('تعذّر حفظ النتيجة', 'error');
    });
}

/**
 * Display a small status message below the result box.
 */
function showSaveStatus(message, type) {
  // Remove any existing status
  var existing = document.getElementById('saveStatus');
  if (existing) existing.remove();

  var el = document.createElement('div');
  el.id = 'saveStatus';
  el.textContent = message;
  el.style.cssText =
    'text-align:center;padding:10px 20px;margin:15px auto;border-radius:8px;' +
    'font-size:14px;max-width:400px;font-family:Cairo,sans-serif;';

  if (type === 'success') {
    el.style.background = '#ecfdf5';
    el.style.color = '#065f46';
    el.style.border = '1px solid #a7f3d0';
  } else {
    el.style.background = '#fef2f2';
    el.style.color = '#991b1b';
    el.style.border = '1px solid #fecaca';
  }

  // Try to append after the result container
  var container = document.getElementById('questionContainer');
  if (container && container.parentNode) {
    container.parentNode.appendChild(el);
  } else {
    document.body.appendChild(el);
  }
}
