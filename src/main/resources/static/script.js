const userId = 1;

// Fetch notifications from the backend
const API_BASE_URL = "http://localhost:8080";
const NOTIFICATIONS_API = `${API_BASE_URL}/notifications`;

fetch(`${API_BASE_URL}/api/notifications/user/${userId}`)
  .then(response => response.json())
  .then(data => {
    const notificationsContainer = document.getElementById('notifications');
    data.forEach(notification => {
      const notificationDiv = document.createElement('div');
      notificationDiv.className = `notification ${notification.status}`;
      notificationDiv.dataset.id = notification.notificationId; 
      notificationDiv.innerHTML = `
        <p>${notification.message}</p>
        <small>${new Date(notification.sendTime).toLocaleString()}</small>
        <button onclick="markAsRead(${notification.notificationId})">Mark as Read</button>
      `;
      notificationsContainer.appendChild(notificationDiv);
    });
  })
  .catch(error => {
    console.error('Error fetching notifications:', error);
  });

// Mark a notification as read
function markAsRead(notificationId) {
  fetch(`${NOTIFICATIONS_API}/${notificationId}/read`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    // Update UI without full page reload
    const notificationDiv = document.querySelector(`.notification[data-id="${notificationId}"]`);
    if (notificationDiv) {
      notificationDiv.classList.remove('unread');
      notificationDiv.classList.add('read');
    }
  })
  .catch(error => {
    console.error('Error marking as read:', error);
    alert('Failed to update notification status');
  });
}