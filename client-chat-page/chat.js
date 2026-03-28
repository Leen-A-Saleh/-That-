const sendBtn = document.getElementById("sendBtn");
const messageInput = document.getElementById("messageInput");
const chatMessages = document.getElementById("chatMessages");
const fileInput = document.getElementById("fileInput");
const emojiBtn = document.querySelector(".emoji");

sendBtn.onclick = sendMessage;
messageInput.addEventListener("keypress", (e) => {
  if (e.key === "Enter") sendMessage();
});

function sendMessage() {
  const text = messageInput.value.trim();
  if (!text) return;

  const div = document.createElement("div");
  div.classList.add("message", "me");

  const time = new Date().toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });

  div.innerHTML = `${text}<span>${time}</span>`;
  chatMessages.appendChild(div);

  messageInput.value = "";
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

emojiBtn.onclick = () => {
  const oldPicker = document.querySelector(".emoji-picker");
  if (oldPicker) oldPicker.remove();

  const picker = document.createElement("div");
  picker.classList.add("emoji-picker");

  picker.style.position = "absolute";
  picker.style.bottom = "60px";
  picker.style.left = "50%";
  picker.style.transform = "translateX(-50%)";
  picker.style.background = "#fff";
  picker.style.border = "1px solid #ddd";
  picker.style.padding = "10px";
  picker.style.borderRadius = "8px";
  picker.style.display = "flex";
  picker.style.flexWrap = "wrap";
  picker.style.gap = "10px";
  picker.style.boxShadow = "0 4px 10px rgba(0,0,0,0.1)";

  const emojis = [
    "😀",
    "😍",
    "😂",
    "😢",
    "😎",
    "👍",
    "❤️",
    "🙌",
    "🎉",
    "🤔",
    "😴",
    "😡",
    "🤩",
    "🥳",
    "😇",
    "😭",
  ];
  emojis.forEach((e) => {
    const span = document.createElement("span");
    span.textContent = e;
    span.style.cursor = "pointer";
    span.style.fontSize = "20px";
    span.onclick = () => {
      messageInput.value += e;
      picker.remove();
    };
    picker.appendChild(span);
  });

  document.body.appendChild(picker);
};

fileInput.addEventListener("change", () => {
  const file = fileInput.files[0];
  if (!file) return;

  const div = document.createElement("div");
  div.classList.add("message", "me");

  const time = new Date().toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });

  if (file.type.startsWith("image")) {
    const img = document.createElement("img");
    img.src = URL.createObjectURL(file);
    img.style.maxWidth = "200px";
    img.style.borderRadius = "8px";

    div.appendChild(img);
  } else {
    const link = document.createElement("a");
    link.href = URL.createObjectURL(file);
    link.download = file.name;
    link.innerText = "" + file.name;

    div.appendChild(link);
  }

  const span = document.createElement("span");
  span.innerText = time;
  div.appendChild(span);

  chatMessages.appendChild(div);

  chatMessages.scrollTop = chatMessages.scrollHeight;
});

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
let overlay = document.querySelector(".sidebar-overlay");
if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");

    window.location.href = "login.html";
  });
}
