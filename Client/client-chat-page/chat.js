let selectedTherapistId = null;
let pollingInterval = null;
let searchDebounceTimer = null;

const chatUsers = document.getElementById("chatUsers");
const chatMessages = document.getElementById("chatMessages");
const chatHeader = document.getElementById("chatHeader");
const chatInputArea = document.getElementById("chatInputArea");
const messageInput = document.getElementById("messageInput");
const sendBtn = document.getElementById("sendBtn");
const chatSearch = document.getElementById("chatSearch");
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
const fileInput = document.getElementById("fileInput");
const emojiBtn = document.getElementById("emojiBtn");

loadConversations();
initChatInputExtras();

function loadConversations() {
  apiPost({ action: "get_conversations" })
    .then(function (data) {
      if (!Array.isArray(data)) {
        chatUsers.innerHTML = '<p class="no-conv">تعذر تحميل المحادثات</p>';
        showEmptyChat("تعذر تحميل المحادثات");
        return;
      }

      renderConversations(data);

      if (!selectedTherapistId && data.length > 0 && !getSearchQuery()) {
        selectTherapist(
          data[0].therapist_id,
          data[0].therapist_name,
          data[0].therapist_avatar
        );
      }
    })
    .catch(function () {
      chatUsers.innerHTML = '<p class="no-conv">تعذر تحميل المحادثات</p>';
      showEmptyChat("تعذر تحميل المحادثات");
    });
}

function runSearch() {
  const query = getSearchQuery();

  if (query === "") {
    loadConversations();
    return;
  }

  apiPost({ action: "search", query: query })
    .then(function (data) {
      if (!data || typeof data !== "object") {
        chatUsers.innerHTML = '<p class="no-conv">تعذر البحث</p>';
        return;
      }

      const conversations = Array.isArray(data.conversations) ? data.conversations : [];
      const therapists = Array.isArray(data.therapists) ? data.therapists : [];
      renderSearchResults(conversations, therapists, query);
    })
    .catch(function () {
      chatUsers.innerHTML = '<p class="no-conv">تعذر البحث</p>';
    });
}

function renderSearchResults(conversations, therapists, query) {
  if (conversations.length === 0 && therapists.length === 0) {
    chatUsers.innerHTML =
      '<p class="no-conv">لا توجد نتائج لـ "' + escapeHtml(query) + '"</p>';
    return;
  }

  chatUsers.innerHTML = "";

  if (conversations.length > 0) {
    chatUsers.appendChild(createListSection("المحادثات"));
    conversations.forEach(function (conv) {
      chatUsers.appendChild(createConversationItem(conv));
    });
  }

  if (therapists.length > 0) {
    chatUsers.appendChild(createListSection("ابدأ محادثة جديدة"));
    therapists.forEach(function (therapist) {
      chatUsers.appendChild(createNewTherapistItem(therapist));
    });
  }

  highlightActiveConversation();
}

function renderConversations(conversations) {
  if (conversations.length === 0) {
    chatUsers.innerHTML = '<p class="no-conv">لا توجد محادثات</p>';
    if (!getSearchQuery()) {
      showEmptyChat("لا توجد محادثات بعد — ابحث عن معالج لبدء محادثة");
    }
    return;
  }

  chatUsers.innerHTML = "";

  conversations.forEach(function (conv) {
    chatUsers.appendChild(createConversationItem(conv));
  });

  highlightActiveConversation();
}

function createListSection(title) {
  const section = document.createElement("div");
  section.className = "chat-list-section";
  section.textContent = title;
  return section;
}

function createChatHeaderContent(name, avatarUrl) {
  const wrap = document.createElement("div");
  wrap.className = "chat-header-profile";

  const avatar = createAvatarElement(name, avatarUrl);
  avatar.classList.add("chat-header-avatar");
  wrap.appendChild(avatar);

  const title = document.createElement("h3");
  title.textContent = name || "";
  wrap.appendChild(title);

  return wrap;
}

function createAvatarElement(name, avatarUrl) {
  const avatar = document.createElement("div");
  avatar.className = "avatar";
  const displayName = name || "?";

  if (avatarUrl) {
    avatar.classList.add("avatar--image");
    const img = document.createElement("img");
    img.src = avatarUrl;
    img.alt = displayName;
    img.addEventListener("error", function () {
      avatar.classList.remove("avatar--image");
      img.remove();
      avatar.textContent = displayName.charAt(0);
    });
    avatar.appendChild(img);
  } else {
    avatar.textContent = displayName.charAt(0);
  }

  return avatar;
}

function createConversationItem(conv) {
  const div = document.createElement("div");
  div.className = "chat-user";
  div.dataset.therapistId = String(conv.therapist_id);
  div.dataset.therapistName = conv.therapist_name || "";
  div.dataset.therapistAvatar = conv.therapist_avatar || "";

  const unread = Number(conv.unread_count || 0);
  const unreadBadge =
    unread > 0 ? `<span class="unread-badge">${unread}</span>` : "";

  div.appendChild(createAvatarElement(conv.therapist_name, conv.therapist_avatar));

  const text = document.createElement("div");
  text.className = "chat-user-text";
  text.innerHTML = `
    <h4>
      <span>${escapeHtml(conv.therapist_name)}</span>
      ${unreadBadge}
    </h4>
    <p>${escapeHtml(conv.last_message || "")}</p>
  `;
  div.appendChild(text);

  const meta = document.createElement("div");
  meta.className = "conv-meta";
  meta.innerHTML = `<span class="conv-time">${escapeHtml(conv.last_message_time || "")}</span>`;
  div.appendChild(meta);

  div.addEventListener("click", function () {
    selectTherapist(conv.therapist_id, conv.therapist_name, conv.therapist_avatar);
  });

  return div;
}

function createNewTherapistItem(therapist) {
  const div = document.createElement("div");
  div.className = "chat-user new-chat";
  div.dataset.therapistId = String(therapist.therapist_id);
  div.dataset.therapistName = therapist.therapist_name || "";
  div.dataset.therapistAvatar = therapist.therapist_avatar || "";

  div.appendChild(createAvatarElement(therapist.therapist_name, therapist.therapist_avatar));

  const text = document.createElement("div");
  text.className = "conv-info";
  text.innerHTML = `
    <h4>${escapeHtml(therapist.therapist_name)}</h4>
    <p>محادثة جديدة</p>
  `;
  div.appendChild(text);

  div.addEventListener("click", function () {
    selectTherapist(therapist.therapist_id, therapist.therapist_name, therapist.therapist_avatar);
  });

  return div;
}

function highlightActiveConversation() {
  document.querySelectorAll(".chat-user").forEach(function (item) {
    const itemId = Number(item.dataset.therapistId || 0);
    item.classList.toggle("active", itemId === selectedTherapistId);
  });
}

function selectTherapist(therapistId, therapistName, therapistAvatar) {
  selectedTherapistId = therapistId;

  chatHeader.style.display = "";
  chatHeader.innerHTML = "";
  chatHeader.appendChild(createChatHeaderContent(therapistName, therapistAvatar));
  chatInputArea.style.display = "";

  highlightActiveConversation();

  loadMessages();

  clearInterval(pollingInterval);
  pollingInterval = setInterval(function () {
    if (selectedTherapistId) {
      loadMessages();
    }
    refreshConversationList();
    updateChatSidebarBadge();
  }, 5000);
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
      refreshConversationList();
      updateChatSidebarBadge();
      if (typeof window.refreshGlobalBadges === 'function') window.refreshGlobalBadges();
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
    appendMessageBody(div, msg);

    const time = document.createElement("span");
    time.textContent = msg.time || "";
    div.appendChild(time);

    chatMessages.appendChild(div);
  });

  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function messageFileUrl(msg) {
  return msg.file_url || msg.file_path || "";
}

function appendMessageBody(div, msg) {
  const type = String(msg.type || "TEXT").toUpperCase();
  const url = messageFileUrl(msg);
  const label = (msg.content || "").trim() || "ملف مرفق";

  if (type === "IMAGE" && url) {
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener";
    link.className = "msg-image-link";
    const img = document.createElement("img");
    img.src = url;
    img.alt = label;
    img.className = "msg-image";
    link.appendChild(img);
    div.appendChild(link);
    return;
  }

  if (type === "FILE" && url) {
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener";
    link.className = "msg-file";
    link.innerHTML = '<i class="fa-solid fa-file"></i>';
    const nameSpan = document.createElement("span");
    nameSpan.textContent = label;
    link.appendChild(nameSpan);
    div.appendChild(link);
    return;
  }

  if (type === "VOICE" && url) {
    const audio = document.createElement("audio");
    audio.controls = true;
    audio.preload = "metadata";
    audio.className = "msg-voice";
    audio.src = url;
    div.appendChild(audio);
    return;
  }

  div.appendChild(document.createTextNode(msg.content || ""));
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
      if (!data || !data.success) return;

      messageInput.value = "";
      loadMessages();
      refreshConversationList();
      updateChatSidebarBadge();
    })
    .finally(function () {
      sendBtn.disabled = false;
    });
}

function refreshConversationList() {
  if (getSearchQuery()) {
    runSearch();
  } else {
    loadConversations();
  }
}

function getSearchQuery() {
  return chatSearch.value.trim();
}

function updateChatSidebarBadge() {
  if (typeof window.refreshClientChatSidebarBadge === "function") {
    window.refreshClientChatSidebarBadge();
  }
}

function initChatInputExtras() {
  if (emojiBtn && messageInput) {
    emojiBtn.addEventListener("click", function (event) {
      event.stopPropagation();
      toggleEmojiPicker();
    });

    document.addEventListener("click", function () {
      closeEmojiPicker();
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", sendFile);
  }
}

function toggleEmojiPicker() {
  const existing = document.querySelector(".emoji-picker");
  if (existing) {
    existing.remove();
    return;
  }

  const picker = document.createElement("div");
  picker.className = "emoji-picker";
  const emojis = [
    "😀", "😍", "😂", "😢", "😎", "👍", "❤️", "🙌", "🎉", "🤔",
    "😴", "😡", "🤩", "🥳", "😇", "😭",
  ];

  emojis.forEach(function (emoji) {
    const span = document.createElement("span");
    span.textContent = emoji;
    span.addEventListener("click", function (event) {
      event.stopPropagation();
      messageInput.value += emoji;
      closeEmojiPicker();
      messageInput.focus();
    });
    picker.appendChild(span);
  });

  picker.addEventListener("click", function (event) {
    event.stopPropagation();
  });

  const anchor = chatInputArea || emojiBtn;
  if (anchor) {
    anchor.appendChild(picker);
  } else {
    document.body.appendChild(picker);
  }
}

function closeEmojiPicker() {
  const picker = document.querySelector(".emoji-picker");
  if (picker) {
    picker.remove();
  }
}

function sendFile() {
  if (!selectedTherapistId || !fileInput) return;

  const file = fileInput.files && fileInput.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    showToast("حجم الملف يتجاوز 10 ميجا");
    fileInput.value = "";
    return;
  }

  const formData = new FormData();
  formData.append("action", "send_file");
  formData.append("therapistId", String(selectedTherapistId));
  formData.append("file", file);

  if (sendBtn) sendBtn.disabled = true;

  apiPostFormData(formData)
    .then(function (data) {
      if (!data || !data.success) {
        showToast((data && (data.error || data.message)) || "تعذّر رفع الملف");
        return;
      }

      loadMessages();
      refreshConversationList();
      updateChatSidebarBadge();
    })
    .catch(function () {
      showToast("حدث خطأ في الاتصال");
    })
    .finally(function () {
      if (sendBtn) sendBtn.disabled = false;
      fileInput.value = "";
    });
}

function showToast(message) {
  const toast = document.createElement("div");
  toast.style.cssText =
    "position:fixed;top:20px;left:50%;transform:translateX(-50%);" +
    "background:#333;color:#fff;padding:12px 25px;border-radius:8px;" +
    "font-family:Cairo,sans-serif;font-size:14px;box-shadow:0 4px 15px rgba(0,0,0,0.2);" +
    "z-index:3000;animation:chatFadeInOut 2.5s forwards;";
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(function () {
    toast.remove();
  }, 2500);
}

if (!document.getElementById("chat-toast-styles")) {
  const style = document.createElement("style");
  style.id = "chat-toast-styles";
  style.textContent =
    "@keyframes chatFadeInOut{" +
    "0%{opacity:0;transform:translate(-50%,-20px);}" +
    "10%{opacity:1;transform:translate(-50%,0);}" +
    "80%{opacity:1;transform:translate(-50%,0);}" +
    "100%{opacity:0;transform:translate(-50%,-10px);}" +
    "}";
  document.head.appendChild(style);
}

sendBtn.addEventListener("click", sendMessage);

messageInput.addEventListener("keypress", function (event) {
  if (event.key === "Enter") {
    sendMessage();
  }
});

chatSearch.addEventListener("input", function () {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(runSearch, 300);
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

function apiPostFormData(formData) {
  return fetch(CHAT_API_URL, {
    method: "POST",
    body: formData,
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
