let selectedTherapistId = null;
let pollingInterval = null;

const chatUsers = document.getElementById("chatUsers");
const chatMessages = document.getElementById("chatMessages");
const chatHeader = document.getElementById("chatHeader");
const chatInputArea = document.getElementById("chatInputArea");
const messageInput = document.getElementById("messageInput");
const sendBtn = document.getElementById("sendBtn");
const chatSearch = document.getElementById("chatSearch");
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

loadConversations();

function loadConversations() {
  apiPost({ action: "get_conversations" })
    .then(function (data) {
      if (!Array.isArray(data)) {
        chatUsers.innerHTML = '<p class="no-conv">تعذر تحميل المحادثات</p>';
        showEmptyChat("تعذر تحميل المحادثات");
        return;
      }

      renderConversations(data);

      if (!selectedTherapistId && data.length > 0) {
        selectTherapist(data[0].therapist_id, data[0].therapist_name);
      }
    })
    .catch(function () {
      chatUsers.innerHTML = '<p class="no-conv">تعذر تحميل المحادثات</p>';
      showEmptyChat("تعذر تحميل المحادثات");
    });
}

function renderConversations(conversations) {
  if (conversations.length === 0) {
    chatUsers.innerHTML = '<p class="no-conv">لا توجد محادثات</p>';
    showEmptyChat("لا توجد محادثات بعد");
    return;
  }

  chatUsers.innerHTML = "";

  conversations.forEach(function (conv) {
    const div = document.createElement("div");
    div.className = "chat-user";
    div.dataset.name = conv.therapist_name || "";

    if (conv.therapist_id === selectedTherapistId) {
      div.classList.add("active");
    }

    const letter = (conv.therapist_name || "?").charAt(0);
    const unread = Number(conv.unread_count || 0);
    const unreadBadge = unread > 0 ? `<span class="unread-badge">${unread}</span>` : "";

    div.innerHTML = `
      <div class="avatar">${escapeHtml(letter)}</div>
      <div class="conv-info">
        <h4>${escapeHtml(conv.therapist_name)}</h4>
        <p>${escapeHtml(conv.last_message || "")}</p>
      </div>
      <div class="conv-meta">
        <span class="conv-time">${escapeHtml(conv.last_message_time || "")}</span>
        ${unreadBadge}
      </div>
    `;

    div.addEventListener("click", function () {
      selectTherapist(conv.therapist_id, conv.therapist_name);
    });

    chatUsers.appendChild(div);
  });
}

function selectTherapist(therapistId, therapistName) {
  selectedTherapistId = therapistId;

  chatHeader.style.display = "";
  chatHeader.innerHTML = `<h3>${escapeHtml(therapistName)}</h3>`;
  chatInputArea.style.display = "";

  document.querySelectorAll(".chat-user").forEach(function (item) {
    item.classList.remove("active");
    if (item.dataset.name === therapistName) {
      item.classList.add("active");
    }
  });

  loadMessages();

  clearInterval(pollingInterval);
  pollingInterval = setInterval(loadMessages, 5000);
}

function showEmptyChat(message) {
  selectedTherapistId = null;
  clearInterval(pollingInterval);
  pollingInterval = null;

  chatHeader.innerHTML = "";
  chatHeader.style.display = "none";
  chatInputArea.style.display = "none";
  chatMessages.innerHTML = `
    <div class="empty-chat">
      <i class="fa-regular fa-comments"></i>
      <p>${escapeHtml(message)}</p>
    </div>
  `;
}

function loadMessages() {
  if (!selectedTherapistId) return;

  apiPost({ action: "get_messages", therapistId: selectedTherapistId })
    .then(function (data) {
      if (!Array.isArray(data)) return;
      renderMessages(data);
    })
    .catch(function () {});
}

function renderMessages(messages) {
  chatMessages.innerHTML = "";

  if (messages.length === 0) {
    chatMessages.innerHTML = '<p class="no-messages">ابدأ المحادثة بإرسال رسالة</p>';
    return;
  }

  messages.forEach(function (msg) {
    const div = document.createElement("div");
    div.className = msg.isMe ? "message me" : "message other";

    if (msg.type === "IMAGE" && msg.file_path) {
      const img = document.createElement("img");
      img.src = msg.file_path;
      img.style.maxWidth = "200px";
      img.style.borderRadius = "8px";
      div.appendChild(img);
    } else if (msg.type === "FILE" && msg.file_path) {
      const link = document.createElement("a");
      link.href = msg.file_path;
      link.target = "_blank";
      link.textContent = "ملف مرفق";
      div.appendChild(link);
    } else {
      div.appendChild(document.createTextNode(msg.content || ""));
    }

    const time = document.createElement("span");
    time.textContent = msg.time || "";
    div.appendChild(time);

    chatMessages.appendChild(div);
  });

  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sendMessage() {
  if (!selectedTherapistId) return;

  const content = messageInput.value.trim();
  if (content === "") return;

  sendBtn.disabled = true;

  apiPost({
    action: "send_message",
    therapistId: selectedTherapistId,
    content: content,
  })
    .then(function (data) {
      if (!data.success) return;

      messageInput.value = "";
      loadMessages();
      loadConversations();
    })
    .finally(function () {
      sendBtn.disabled = false;
    });
}

sendBtn.addEventListener("click", sendMessage);

messageInput.addEventListener("keypress", function (event) {
  if (event.key === "Enter") {
    sendMessage();
  }
});

chatSearch.addEventListener("input", function () {
  const query = chatSearch.value.trim().toLowerCase();

  document.querySelectorAll("#chatUsers .chat-user").forEach(function (item) {
    const name = (item.dataset.name || "").toLowerCase();
    item.style.display = name.includes(query) ? "" : "none";
  });
});

let overlay = document.querySelector(".sidebar-overlay");
if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

menuBtn.addEventListener("click", function () {
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", function () {
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

function apiPost(params) {
  return fetch(CHAT_API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams(params),
  }).then(function (response) {
    return response.json();
  });
}

function escapeHtml(value) {
  return String(value || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}
