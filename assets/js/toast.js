/**
 * Toast Notification System
 * Usage:
 *   showToast("Saved successfully!", "success");
 *   showToast("Something went wrong!", "error");
 *   showToast("Loading...", "info");
 */

function showToast(message, type = "info", duration = 3000) {
  // Create container if it doesn’t exist
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  // Create toast element
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  // Append toast
  container.appendChild(toast);

  // Trigger animation
  setTimeout(() => toast.classList.add("show"), 100);

  // Auto remove after duration
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, duration);
}
