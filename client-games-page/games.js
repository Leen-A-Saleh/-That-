const dropZone = document.getElementById("dropZone");
const fileInput = document.getElementById("fileInput");
const previewGrid = document.getElementById("previewGrid");
const countLabel = document.getElementById("countLabel");
const uploadBtn = document.getElementById("uploadBtn");
const successMsg = document.getElementById("successMsg");
let selectedFiles = [];

fileInput.addEventListener("change", (e) =>
  handleFiles(Array.from(e.target.files)),
);
dropZone.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropZone.classList.add("drag-over");
});
dropZone.addEventListener("dragleave", () =>
  dropZone.classList.remove("drag-over"),
);
dropZone.addEventListener("drop", (e) => {
  e.preventDefault();
  dropZone.classList.remove("drag-over");
  handleFiles(Array.from(e.dataTransfer.files));
});

function handleFiles(files) {
  files.forEach((file) => {
    if (!selectedFiles.find((f) => f.name === file.name))
      selectedFiles.push(file);
  });
  renderPreviews();
}

function renderPreviews() {
  previewGrid.innerHTML = "";
  selectedFiles.forEach((file, i) => {
    const item = document.createElement("div");
    item.className = "preview-item";

    if (file.type.startsWith("image/")) {
      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      item.appendChild(img);
    } else {
      item.innerHTML = `<div style="width:100%;height:100%;background:#1a1a2e;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;font-size:11px;gap:4px;">▶<span>فيديو</span></div>`;
    }

    const name = document.createElement("div");
    name.className = "file-name";
    name.textContent = file.name;
    item.appendChild(name);

    const rm = document.createElement("button");
    rm.className = "remove-btn";
    rm.textContent = "×";
    rm.onclick = () => {
      selectedFiles.splice(i, 1);
      renderPreviews();
    };
    item.appendChild(rm);

    previewGrid.appendChild(item);
  });

  const n = selectedFiles.length;
  countLabel.textContent =
    n === 0 ? "لم يتم اختيار ملفات بعد" : `تم اختيار ${n} ملف`;
  uploadBtn.disabled = n === 0;
}

uploadBtn.addEventListener("click", async () => {
  uploadBtn.textContent = "⏳ جارٍ الرفع...";
  uploadBtn.disabled = true;

  const formData = new FormData();
  selectedFiles.forEach((file) => formData.append("files", file));
  formData.append("childId", localStorage.getItem("userId"));

  try {
    const res = await fetch("/api/activities/upload", {
      method: "POST",
      headers: {
        Authorization: "Bearer " + localStorage.getItem("token"),
      },
      body: formData,
    });

    if (res.ok) {
      successMsg.style.display = "block";
      selectedFiles = [];
      renderPreviews();
    } else {
      alert("فشل الرفع، حاول مرة ثانية");
    }
  } catch (err) {
    successMsg.style.display = "block";
    selectedFiles = [];
    renderPreviews();
  } finally {
    uploadBtn.textContent = "رفع الملفات";
    uploadBtn.disabled = false;
  }
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
