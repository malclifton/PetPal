document.addEventListener("DOMContentLoaded", function () {
  console.log("🐾 script.js loaded!");

  fetch("php/get_session_user.php")
    .then((res) => {
      if (!res.ok) throw new Error("Not logged in");
      return res.json();
    })
    .then((user) => {
      const userId = user.user_id;
      const role = String(user.role).trim().toLowerCase(); // Normalize role
      console.log("Logged-in user ID:", userId, "| Role:", role, "| Type:", typeof role);

      // Update UI with user role
      const roleElement = document.getElementById('user-role');
      if (roleElement) {
        roleElement.textContent = role === "owner" ? "Owner" : "Sitter";
      }

      fetchNotifications(userId);

      const dashboardLinks = document.querySelectorAll(".dashboard-link");
      let dashboardPath = "./signIn.html"; // Consistent path format

      if (role === "owner") {
        dashboardPath = "./petOwnerDashboard.html";
      } else if (role === "sitter") {
        dashboardPath = "./petSitterDashboard.html";
      }

      console.log("Setting dashboard path to:", dashboardPath);
      dashboardLinks.forEach((link) => {
        link.href = dashboardPath;
      });
    })
    .catch((err) => {
      console.error("Error fetching session user:", err);
      
      if (err.message === "Not logged in") {
        window.location.href = "./signIn.html";
        return;
      }

      const container = document.querySelector(".notifications");
      if (container) {
        container.innerHTML = "<p>Please log in to view notifications.</p>";
      }

      const dashboardLinks = document.querySelectorAll(".dashboard-link");
      dashboardLinks.forEach((link) => {
        link.href = "./signIn.html";
      });
    });
});

function fetchNotifications(userId) {
  console.log("Fetching notifications for user:", userId);

  fetch(`php/getNotifications.php?userId=${encodeURIComponent(userId)}`)
    .then((res) => {
      if (!res.ok) throw new Error("Failed to fetch notifications");
      return res.json();
    })
    .then((notifications) => {
      console.log("Received notifications:", notifications);
      const container = document.querySelector(".notifications");
      
      if (!container) {
        console.warn(".notifications container not found!");
        return;
      }

      container.innerHTML = "";

      if (!notifications || !notifications.length) {
        container.innerHTML = "<p>No recent notifications.</p>";
        return;
      }

      notifications.forEach((notif) => {
        const div = document.createElement("div");
        div.className = `notification ${notif.status}`;
        div.dataset.id = notif.notificationId;

        const date = new Date(notif.sendTime + 'Z'); // Handle UTC dates
        const timeAgo = timeSince(date);

        div.innerHTML = `
          <div class="profile">
            <img src="./img/profile_pic.png" alt="Profile" />
          </div>
          <div class="message">
            <p><b>${escapeHtml(notif.message)}</b></p>
            <small class="text-muted">${timeAgo}</small>
            ${notif.status === "unread" ? '<button class="mark-read-btn">Mark Read</button>' : ''}
          </div>
        `;

        container.appendChild(div);

        // Add event listener properly
        if (notif.status === "unread") {
          div.querySelector('.mark-read-btn').addEventListener('click', () => {
            markAsRead(notif.notificationId);
          });
        }
      });
    })
    .catch((err) => {
      console.error("Notification fetch error:", err);
      const container = document.querySelector(".notifications");
      if (container) {
        container.innerHTML = `<p>Error loading notifications: ${escapeHtml(err.message)}</p>`;
      }
    });
}

function markAsRead(notificationId) {
  console.log("Marking notification as read:", notificationId);
  fetch(`php/markNotificationsRead.php?id=${encodeURIComponent(notificationId)}`, {
    method: "POST",
  })
    .then((res) => {
      if (!res.ok) throw new Error("Failed to mark as read");
      return res.json();
    })
    .then(() => {
      const notificationDiv = document.querySelector(
        `.notification[data-id="${notificationId}"]`
      );
      if (notificationDiv) {
        notificationDiv.classList.remove("unread");
        notificationDiv.classList.add("read");
        const button = notificationDiv.querySelector("button");
        if (button) button.remove();
      }
    })
    .catch((err) => {
      console.error("Error marking as read:", err);
      alert(`Failed to mark notification as read: ${err.message}`);
    });
}

// Utility: Converts timestamp to "X minutes/hours ago"
function timeSince(date) {
  const seconds = Math.floor((new Date() - date) / 1000);
  
  const intervals = [
    { label: "year", seconds: 31536000 },
    { label: "month", seconds: 2592000 },
    { label: "day", seconds: 86400 },
    { label: "hour", seconds: 3600 },
    { label: "minute", seconds: 60 },
    { label: "second", seconds: 1 },
  ];

  for (const interval of intervals) {
    const count = Math.floor(seconds / interval.seconds);
    if (count >= 1) {
      return `${count} ${interval.label}${count !== 1 ? 's' : ''} ago`;
    }
  }

  return "just now";
}

// Basic HTML escaping for security
function escapeHtml(unsafe) {
  return unsafe?.toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;") || '';
}