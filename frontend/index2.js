const sideMenu = document.querySelector("aside");
const menuBtn = document.querySelector("#menu-btn");
const closeBtn = document.querySelector("#close-btn");
const themeToggler = document.querySelector(".theme-toggler");

menuBtn.addEventListener("click", () => {
  sideMenu.style.display = "block";
});

closeBtn.addEventListener("click", () => {
  sideMenu.style.display = "none";
});

themeToggler.addEventListener("click", () => {
  document.body.classList.toggle("dark-theme-variables");

  themeToggler.querySelector("span:nth-child(1)").classList.toggle("active");
  themeToggler.querySelector("span:nth-child(2)").classList.toggle("active");
});

/*Change Profile*/
document.addEventListener("DOMContentLoaded", () => {
  fetch("./php/getOwnerProfile.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.name) {
        document.getElementById("owner-name").innerHTML = data.name;
      }
      if (data.profile_image) {
        document.getElementById("profileImage").src =
          data.profile_image + "?t=" + new Date().getTime();
      }
    });

  document
    .getElementById("newProfileImage")
    .addEventListener("change", function () {
      const form = document.getElementById("profileImageForm");
      const formData = new FormData(form);

      fetch("./php/updateProfileImage.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            document.getElementById("profileImage").src =
              data.new_image + "?" + new Date().getTime();
          } else {
            alert("Failed to update image: " + (data.error || "Unknown error"));
          }
        });
    });
});

/*Habit Tracking for Pet Owner Dahboard */
function formatTime(timeStr) {
  const [hour, minute] = timeStr.split(":");
  const date = new Date();
  date.setHours(hour, minute);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function fetchTodaysHabits() {
  const today = new Date().toISOString().split("T")[0];

  fetch(`php/fetchHabits.php?date=${today}`)
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.json();
    })
    .then((data) => {
      const list = document.getElementById("habit-list");
      list.innerHTML = "";

      if (data.length === 0) {
        list.innerHTML = "<li>No habits today!</li>";
        return;
      }

      data.forEach((habit) => {
        const li = document.createElement("li");
        const formattedTime = formatTime(habit.time);
        li.textContent = `${habit.pet_name}: ${habit.habit_type} at ${formattedTime}`;
        list.appendChild(li);
      });
    })
    .catch((error) => {
      console.error("Error fetching habits:", error);
      document.getElementById("habit-list").innerHTML =
        "<li>Error loading habits</li>";
    });
}

fetchTodaysHabits();
setInterval(fetchTodaysHabits, 3000);
