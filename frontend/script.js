document.addEventListener("DOMContentLoaded", function () {
  console.log("🐾 script.js loaded!");

  // Step 1: Get the logged-in user
  fetch("php/get_session_user.php")
    .then((res) => {
      if (!res.ok) throw new Error("Not logged in");
      return res.json();
    })
    .then((user) => {
      const userId = user.user_id;
      console.log("Logged-in user ID:", userId);

      // Step 2: Fetch notifications for that user
      fetchNotifications(userId);
    })
    .catch((err) => {
      console.error("Error fetching session user:", err);
      const container = document.querySelector(".notifications");
      if (container) {
        container.innerHTML = "<p>Please log in to view notifications.</p>";
      }
    });
});

function fetchNotifications(userId) {
  console.log("📩 Fetching notifications for user:", userId);

  fetch(`php/getNotifications.php?userId=${userId}`)
    .then((res) => res.json())
    .then((notifications) => {
      console.log("✅ Received notifications:", notifications);

      const container = document.querySelector(".notifications");
      console.log("📦 Notification container:", container);

      if (!container) {
        console.warn("⚠️ .notifications container not found!");
        return;
      }

      container.innerHTML = "";

      if (!notifications.length) {
        container.innerHTML = "<p>No recent notifications.</p>";
        return;
      }

      notifications.forEach((notif) => {
        const div = document.createElement("div");
        div.className = `notification ${notif.status}`;
        div.dataset.id = notif.notificationId;

        const date = new Date(notif.sendTime);
        const timeAgo = timeSince(date);

        div.innerHTML = `
          <div class="profile">
            <img src="./img/profile_pic.png">
          </div>
          <div class="message">
            <p><b>${notif.message}</b></p>
            <small class="text-muted">${timeAgo}</small>
            ${
              notif.status === "unread"
                ? `<button onclick="markAsRead(${notif.notificationId})">Mark Read</button>`
                : ""
            }
          </div>
        `;

        container.appendChild(div);
      });
    })
    .catch((err) => {
      console.error(" Notification fetch error:", err);
    });
}

function markAsRead(notificationId) {
  fetch(`php/markNotificationsRead.php?id=${notificationId}`, {
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
      console.error("⚠️ Error marking as read:", err);
      alert("Failed to mark notification as read.");
    });
}

// Utility: Converts timestamp to "X minutes/hours ago"
function timeSince(date) {
  const seconds = Math.floor((new Date() - date) / 1000);
  const units = [
    { label: "year", seconds: 31536000 },
    { label: "month", seconds: 2592000 },
    { label: "day", seconds: 86400 },
    { label: "hour", seconds: 3600 },
    { label: "minute", seconds: 60 },
    { label: "second", seconds: 1 },
  ];

  for (let unit of units) {
    const interval = Math.floor(seconds / unit.seconds);
    if (interval >= 1) {
      return `${interval} ${unit.label}${interval !== 1 ? "s" : ""} ago`;
    }
  }

  return "just now";
}
