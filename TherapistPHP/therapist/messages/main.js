document.addEventListener("DOMContentLoaded", function () {

  const sendBtn       = document.getElementById("sendBtn");
  const messageInput  = document.getElementById("messageInput");
  const chatMessages  = document.getElementById("chatMessages");
  const fileInput     = document.getElementById("fileInput");
  const emojiBtn      = document.querySelector(".emoji");
  const favoriteBtn   = document.querySelector(".favorite-btn");
  const menuBtn       = document.getElementById("menuBtn");
  const dropdownMenu  = document.getElementById("dropdownMenu");
  const chatSearch    = document.getElementById("chatSearch");

  if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function showToast(message) {
    const toast = document.createElement("div");
    toast.style.cssText = `
      position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
      background-color: #333; color: white; padding: 12px 25px;
      border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 14px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 2000;
      animation: fadeInOut 2.5s forwards;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
  }

  if (!document.getElementById("chat-toast-styles")) {
    const style = document.createElement("style");
    style.id = "chat-toast-styles";
    style.innerHTML = `
      @keyframes fadeInOut {
        0% { opacity: 0; transform: translate(-50%, -20px); }
        10% { opacity: 1; transform: translate(-50%, 0); }
        80% { opacity: 1; transform: translate(-50%, 0); }
        100% { opacity: 0; transform: translate(-50%, -10px); }
      }
    `;
    document.head.appendChild(style);
  }

  if (favoriteBtn) {
    favoriteBtn.addEventListener("click", () => {
      favoriteBtn.classList.toggle("active");
      showToast(
        favoriteBtn.classList.contains("active")
          ? "تمت الإضافة إلى المفضلة"
          : "تمت الإزالة من المفضلة"
      );
    });
  }

  if (menuBtn && dropdownMenu) {
    menuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
    });
    document.addEventListener("click", () => dropdownMenu.classList.remove("show"));
  }

  function sendMessage() {
    if (!messageInput || !chatMessages) return;
    const text = messageInput.value.trim();
    if (!text) return;

    const receiverId = chatMessages.dataset.receiver;
    if (!receiverId) return;

    const fd = new FormData();
    fd.append("action", "send_message");
    fd.append("receiver_id", receiverId);
    fd.append("content", text);

    sendBtn.disabled = true;

    fetch("index.php", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: fd
    })
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          showToast(data.message || "تعذّر إرسال الرسالة");
          return;
        }

        const placeholder = chatMessages.querySelector(".no-messages");
        if (placeholder) placeholder.remove();

        const div = document.createElement("div");
        div.classList.add("message", "me");
        div.innerHTML = "";
        div.appendChild(document.createTextNode(text));
        const span = document.createElement("span");
        span.textContent = data.sent_at;
        div.appendChild(span);

        chatMessages.appendChild(div);
        messageInput.value = "";
        chatMessages.scrollTop = chatMessages.scrollHeight;
      })
      .catch(() => showToast("حدث خطأ في الاتصال"))
      .finally(() => { sendBtn.disabled = false; });
  }

  if (sendBtn)      sendBtn.addEventListener("click", sendMessage);
  if (messageInput) messageInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") sendMessage();
  });

  if (emojiBtn && messageInput) {
    emojiBtn.addEventListener("click", () => {
      const existing = document.querySelector(".emoji-picker");
      if (existing) { existing.remove(); return; }

      const picker = document.createElement("div");
      picker.classList.add("emoji-picker");
      picker.style.cssText = `
        position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%);
        background: #fff; border: 1px solid #ddd; padding: 10px;
        border-radius: 8px; display: flex; flex-wrap: wrap; gap: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 100;
      `;

      const emojis = ["😀","😍","😂","😢","😎","👍","❤️","🙌","🎉","🤔","😴","😡","🤩","🥳","😇","😭"];
      emojis.forEach(e => {
        const span = document.createElement("span");
        span.textContent = e;
        span.style.cssText = "cursor: pointer; font-size: 20px;";
        span.onclick = () => {
          messageInput.value += e;
          picker.remove();
          messageInput.focus();
        };
        picker.appendChild(span);
      });
      document.body.appendChild(picker);
    });
  }

  if (fileInput && chatMessages) {
    fileInput.addEventListener("change", () => {
      const file = fileInput.files[0];
      if (!file) return;

      const receiverId = chatMessages.dataset.receiver;
      if (!receiverId) return;

      if (file.size > 10 * 1024 * 1024) {
        showToast("حجم الملف يتجاوز 10 ميجا");
        fileInput.value = "";
        return;
      }

      const fd = new FormData();
      fd.append("action", "send_file");
      fd.append("receiver_id", receiverId);
      fd.append("file", file);

      fetch("index.php", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: fd
      })
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            showToast(data.message || "تعذّر رفع الملف");
            return;
          }

          const placeholder = chatMessages.querySelector(".no-messages");
          if (placeholder) placeholder.remove();

          const div = document.createElement("div");
          div.classList.add("message", "me");

          if (data.type === "IMAGE") {
            const link = document.createElement("a");
            link.href = data.file_path;
            link.target = "_blank";
            link.className = "msg-image-link";
            const img = document.createElement("img");
            img.src = data.file_path;
            img.alt = data.file_name;
            img.className = "msg-image";
            link.appendChild(img);
            div.appendChild(link);
          } else {
            const link = document.createElement("a");
            link.href = data.file_path;
            link.download = "";
            link.className = "msg-file";
            link.innerHTML = '<i class="fa-solid fa-file"></i>';
            const span = document.createElement("span");
            span.textContent = data.file_name;
            link.appendChild(span);
            div.appendChild(link);
          }

          const timeSpan = document.createElement("span");
          timeSpan.textContent = data.sent_at;
          div.appendChild(timeSpan);

          chatMessages.appendChild(div);
          chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(() => showToast("حدث خطأ في الاتصال"))
        .finally(() => { fileInput.value = ""; });
    });
  }

  if (chatSearch) {
    chatSearch.addEventListener("input", () => {
      const q = chatSearch.value.trim().toLowerCase();
      document.querySelectorAll(".chat-users .chat-user").forEach(el => {
        const name = (el.dataset.name || "").toLowerCase();
        el.style.display = !q || name.includes(q) ? "" : "none";
      });
    });
  }

});
