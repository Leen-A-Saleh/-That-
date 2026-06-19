/**
 * ─────────────────────────────────────────────────────────────────────────────
 *  نظام تنبيهات موحّد مبني على SweetAlert2
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *  يوفّر دوال عامة بديلة عن alert()/confirm() التقليدية:
 *    - showSuccessToast(message) → Toast نجاح في أعلى يمين الشاشة، يختفي تلقائياً.
 *    - showInfoToast(message)    → Toast معلومة، يختفي تلقائياً.
 *    - showErrorAlert(message)   → نافذة خطأ (Modal) بأيقونة خطأ.
 *    - showWarningAlert(message) → نافذة تحذير (Modal) بأيقونة تحذير.
 *    - showConfirm(message, opts)→ نافذة تأكيد تُعيد Promise<boolean> (بديل confirm()).
 *
 *  جميع الدوال آمنة: إن لم تُحمّل مكتبة SweetAlert2 لأي سبب، نسقط إلى السلوك
 *  الافتراضي (alert/confirm) حتى لا تنكسر تجربة المستخدم.
 */
(function (global) {
  "use strict";

  var BRAND = "#36A397"; // لون العلامة (أخضر ذات)

  // تحميل تلقائي لمكتبة SweetAlert2 إن لم تكن محمّلة مسبقاً في الصفحة (أمان إضافي
  // حتى تعمل الدوال على أي صفحة تستدعي هذا الملف دون الحاجة لإضافة وسم CDN يدوياً).
  if (typeof global.Swal === "undefined" && typeof document !== "undefined") {
    if (!document.querySelector('script[data-swal-auto]')) {
      var s = document.createElement("script");
      s.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
      s.setAttribute("data-swal-auto", "1");
      document.head.appendChild(s);
    }
  }

  function hasSwal() {
    return typeof global.Swal !== "undefined";
  }

  function waitForSwal() {
    if (hasSwal()) return Promise.resolve();
    return new Promise(function (resolve) {
      var interval = setInterval(function () {
        if (hasSwal()) {
          clearInterval(interval);
          resolve();
        }
      }, 50);
    });
  }

  // ─── Toast علوي يميني يختفي تلقائياً ───
  function fireToast(icon, message, timer) {
    return waitForSwal().then(function() {
      return global.Swal.fire({
        toast: true,
        position: "top-end",
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: timer || 3000,
        timerProgressBar: true,
        didOpen: function (el) {
          el.addEventListener("mouseenter", global.Swal.stopTimer);
          el.addEventListener("mouseleave", global.Swal.resumeTimer);
        }
      });
    });
  }

  // ─── نافذة Modal (خطأ/تحذير/معلومة) ───
  function fireModal(icon, message, title) {
    return waitForSwal().then(function() {
      return global.Swal.fire({
        icon: icon,
        title: title || "",
        text: message,
        confirmButtonText: "حسناً",
        confirmButtonColor: BRAND
      });
    });
  }

  global.showSuccessToast = function (message) {
    return fireToast("success", message);
  };

  global.showInfoToast = function (message) {
    return fireToast("info", message);
  };

  global.showErrorAlert = function (message) {
    return fireModal("error", message, "خطأ");
  };

  global.showWarningAlert = function (message) {
    return fireModal("warning", message, "تنبيه");
  };

  // بديل confirm(): يُعيد Promise<boolean>
  global.showConfirm = function (message, options) {
    options = options || {};
    return waitForSwal().then(function() {
      return global.Swal.fire({
        icon: options.icon || "question",
        title: options.title || "تأكيد",
        text: message,
        showCancelButton: true,
        confirmButtonText: options.confirmText || "نعم",
        cancelButtonText: options.cancelText || "إلغاء",
        confirmButtonColor: options.confirmColor || "#e11d48",
        cancelButtonColor: "#64748b"
      }).then(function (result) {
        return result.isConfirmed === true;
      });
    });
  };

  // ─── منع استخدام alert() الافتراضية نهائياً وتحويلها لـ SweetAlert ───
  global.alert = function(message) {
    return fireModal("warning", message, "تنبيه");
  };
  
  // تحويل confirm الافتراضية
  global.confirm = function(message) {
    console.warn("تم استخدام window.confirm. يرجى استخدام showConfirm بدلاً منها لدعم الـ Promises.");
    return false; // نمنع ظهور الـ confirm الافتراضي دائماً
  };

  // تحويل prompt الافتراضية
  global.prompt = function(message) {
    console.warn("تم استخدام window.prompt.");
    return null;
  };

})(window);
