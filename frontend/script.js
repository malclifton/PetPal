document.addEventListener("DOMContentLoaded", function () {
  // TODO: have the userId from the sign in be set here, I put userId=3 because that's the only userId i have in my own DB
  const userId = 3;
  /*
        This is the script for the notifications. Right now, you have to manually put in the userId for the notifcations you want to see
        In the future, this'll be handled automatically, but for now, it successfully displays notifications that the user has been sent

    */
  function fetchNotifications() {
    fetch(
      `https://codd.cs.gsu.edu/~mclifton6/petpal/backend/api/notifications/user/${userId}`
    ) //This should point to the domain that the backend is runnning on
      .then((response) => response.json())
      .then((notifications) => {
        const container = document.querySelector(
          ".recent-notifications .notifications"
        );
        container.innerHTML = "";

        notifications.forEach((notification) => {
          const notificationDiv = document.createElement("div");
          notificationDiv.className = `notification ${notification.status}`;
          notificationDiv.dataset.id = notification.notificationId;

          notificationDiv.innerHTML = `
                        <div class="profile">
                            <img src="./img/profile_pic.png">
                        </div>
                        <div class="message">
                            <p><b>${notification.message}</b></p>
                            <small class="text-muted">${new Date(
                              notification.sendTime
                            ).toLocaleString()}</small>
                            ${
                              notification.status === "unread"
                                ? `<button onclick="markAsRead(${notification.notificationId})">Mark Read</button>`
                                : ""
                            }
                        </div>
                    `;

          container.appendChild(notificationDiv);
        });
      })
      .catch((error) => console.error("Error:", error));
  }

  // Initial fetch
  fetchNotifications();

  // Refresh every 30 seconds
  setInterval(fetchNotifications, 30000);
});

// Mark as read function
function markAsRead(notificationId) {
  fetch(
    `https://codd.cs.gsu.edu/~mclifton6/petpal/backend/api/notifications/${notificationId}/read`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
    }
  )
    .then((response) => {
      if (!response.ok) throw new Error("Failed to mark as read");
      return response.json();
    })
    .then((updatedNotification) => {
      const notificationDiv = document.querySelector(
        `.notification[data-id="${notificationId}"]`
      );
      if (notificationDiv) {
        notificationDiv.classList.remove("unread");
        notificationDiv.classList.add("read");
        // Remove the mark as read button
        const button = notificationDiv.querySelector("button");
        if (button) button.remove();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Failed to update notification status");
    });
}
