// EduTrack Lite - Main JavaScript
// פונקציות כלליות ו-API calls

const API_BASE = 'api/';

// ======================
// Authentication
// ======================

function checkAuth() {
  return $.ajax({
    url: API_BASE + 'auth.php?action=check',
    method: 'GET',
    dataType: 'json'
  });
}

function login(email, password) {
  return $.ajax({
    url: API_BASE + 'auth.php?action=login',
    method: 'POST',
    data: { email, password },
    dataType: 'json'
  });
}

function register(email, password, full_name) {
  return $.ajax({
    url: API_BASE + 'auth.php?action=register',
    method: 'POST',
    data: { email, password, full_name },
    dataType: 'json'
  });
}

function logout() {
  window.location.href = API_BASE + 'auth.php?action=logout';
}

// ======================
// Courses
// ======================

function getCourses() {
  return $.ajax({
    url: API_BASE + 'courses.php?action=list',
    method: 'GET',
    dataType: 'json'
  });
}

function getCourse(id) {
  return $.ajax({
    url: API_BASE + 'courses.php?action=get&id=' + id,
    method: 'GET',
    dataType: 'json'
  });
}

function createCourse(data) {
  return $.ajax({
    url: API_BASE + 'courses.php?action=create',
    method: 'POST',
    data: data,
    dataType: 'json'
  });
}

function getCourseStats(id) {
  return $.ajax({
    url: API_BASE + 'courses.php?action=stats&id=' + id,
    method: 'GET',
    dataType: 'json'
  });
}

// ======================
// Students
// ======================

function getStudents(courseId) {
  return $.ajax({
    url: API_BASE + 'students.php?action=list&course_id=' + courseId,
    method: 'GET',
    dataType: 'json'
  });
}

function createStudent(data) {
  return $.ajax({
    url: API_BASE + 'students.php?action=create',
    method: 'POST',
    data: data,
    dataType: 'json'
  });
}

function importStudents(courseId, file) {
  const formData = new FormData();
  formData.append('course_id', courseId);
  formData.append('file', file);
  
  return $.ajax({
    url: API_BASE + 'students.php?action=import',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json'
  });
}

function deleteStudent(id) {
  return $.ajax({
    url: API_BASE + 'students.php?action=delete&id=' + id,
    method: 'GET',
    dataType: 'json'
  });
}

// ======================
// Sessions
// ======================

function startSession(courseId) {
  return $.ajax({
    url: API_BASE + 'session.php?action=start',
    method: 'POST',
    data: { course_id: courseId },
    dataType: 'json'
  });
}

function getSession(sessionId) {
  return $.ajax({
    url: API_BASE + 'session.php?action=get&id=' + sessionId,
    method: 'GET',
    dataType: 'json'
  });
}

function closeSession(sessionId) {
  return $.ajax({
    url: API_BASE + 'session.php?action=close',
    method: 'POST',
    data: { session_id: sessionId },
    dataType: 'json'
  });
}

function startLesson(courseId) {
  console.log('🚀 Starting lesson for course:', courseId);
  const url = 'session.html?course=' + courseId;
  console.log('📍 Navigating to:', url);
  window.location.href = url;
}

function getSessionAttendance(sessionId) {
  return $.ajax({
    url: API_BASE + 'session.php?action=attendance&session_id=' + sessionId,
    method: 'GET',
    dataType: 'json'
  });
}

// ======================
// Attendance (Student)
// ======================

function checkSessionToken(token) {
  return $.ajax({
    url: API_BASE + 'attend.php?action=check&token=' + token,
    method: 'GET',
    dataType: 'json'
  });
}

function registerAttendance(token, phone) {
  return $.ajax({
    url: API_BASE + 'attend.php?action=register',
    method: 'POST',
    data: { token, phone },
    dataType: 'json'
  });
}

// ======================
// Utilities
// ======================

function showAlert(message, type = 'info') {
  const alertHtml = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  $('#alertContainer').html(alertHtml);
  
  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    $('.alert').fadeOut();
  }, 5000);
}

function showLoading() {
  return `
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">טוען...</span>
      </div>
      <p class="mt-3 text-muted">טוען...</p>
    </div>
  `;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('he-IL', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString('he-IL', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatDateTime(dateString) {
  return formatDate(dateString) + ' בשעה ' + formatTime(dateString);
}

function normalizePhone(phone) {
  // Remove all non-digit characters
  let cleaned = phone.replace(/\D/g, '');
  
  // Handle +972 prefix
  if (cleaned.startsWith('972')) {
    cleaned = '0' + cleaned.substring(3);
  }
  
  // Ensure 10 digits starting with 0
  if (cleaned.length === 10 && cleaned.startsWith('0')) {
    return cleaned;
  }
  
  // If 9 digits and doesn't start with 0, add 0
  if (cleaned.length === 9 && !cleaned.startsWith('0')) {
    return '0' + cleaned;
  }
  
  return null;
}

function formatPhone(phone) {
  if (!phone || phone.length !== 10) return phone;
  return phone.substring(0, 3) + '-' + phone.substring(3, 6) + '-' + phone.substring(6);
}

function isValidPhone(phone) {
  return normalizePhone(phone) !== null;
}

function calculateTimeRemaining(expiresAt) {
  const now = new Date();
  const expires = new Date(expiresAt);
  const diff = expires - now;
  
  if (diff <= 0) return { minutes: 0, seconds: 0, total: 0 };
  
  const totalSeconds = Math.floor(diff / 1000);
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  
  return { minutes, seconds, total: totalSeconds };
}

function formatTimeRemaining(minutes, seconds) {
  return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}

function getAttendanceColor(percentage) {
  if (percentage >= 80) return 'success';
  if (percentage >= 70) return 'warning';
  return 'danger';
}

function getAttendanceEmoji(percentage) {
  if (percentage >= 80) return '🟢';
  if (percentage >= 70) return '🟡';
  return '🔴';
}

function downloadCSV(filename, content) {
  const blob = new Blob(['\ufeff' + content], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// ======================
// Auto Refresh
// ======================

function setupAutoRefresh(callback, interval = 5000) {
  let intervalId = setInterval(callback, interval);
  
  // Stop on page unload
  $(window).on('beforeunload', function() {
    clearInterval(intervalId);
  });
  
  return intervalId;
}

function stopAutoRefresh(intervalId) {
  clearInterval(intervalId);
}

// ======================
// Protected Page Check
// ======================

function requireAuth(callback) {
  return checkAuth()
    .done(function(response) {
      // התשובה יכולה להיות response.authenticated או response.data.authenticated
      const isAuth = response.authenticated || (response.data && response.data.authenticated);
      const userData = response.user || (response.data && response.data.user);
      
      if (!isAuth) {
        console.log('❌ Not authenticated, redirecting to login');
        window.location.href = 'login.html';
      } else {
        console.log('✅ Authenticated:', userData);
        if (callback) {
          // העבר את הנתונים בפורמט אחיד
          callback({
            authenticated: true,
            user: userData
          });
        }
      }
    })
    .fail(function(xhr, status, error) {
      console.error('❌ Auth check failed:', status, error);
      window.location.href = 'login.html';
    });
}

// ======================
// Initialize
// ======================

$(document).ready(function() {
  // Create alert container if doesn't exist
  if ($('#alertContainer').length === 0) {
    $('body').prepend('<div id="alertContainer" style="position: fixed; top: 20px; left: 20px; right: 20px; z-index: 9999;"></div>');
  }
});
