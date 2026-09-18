/**
 * ALWIN — Contact / calling forms
 * Submits to CMS so the admin panel shows a new order/inquiry.
 */
(function () {
  "use strict";

  function formCopy() {
    var el = document.getElementById("alwin-form-copy");
    if (el) {
      try {
        return JSON.parse(el.textContent || "{}") || {};
      } catch (_) {}
    }
    return {};
  }

  function t(key, fallback) {
    var copy = formCopy();
    var value = copy[key];
    if (value == null || value === "") return fallback;
    if (String(value).indexOf("{phone}") !== -1) {
      return String(value).replace("{phone}", copy.phone || "");
    }
    return value;
  }

  function apiBase() {
    var raw = (window.ALWIN_CONFIG && window.ALWIN_CONFIG.apiUrl) || "/api/v1";
    if (/^https?:\/\//i.test(raw)) return raw.replace(/\/$/, "");
    return raw.replace(/\/$/, "");
  }

  function normalizePhone(value) {
    return String(value || "").replace(/[\s\-()]/g, "");
  }

  function isValidPhone(value) {
    var phone = normalizePhone(value);
    return /^(0?9\d{9}|0?21\d{8}|\+98\d{10})$/.test(phone);
  }

  function isValidEmail(value) {
    if (!value) return true;
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  function setStatus(el, type, text) {
    if (!el) return;
    el.className = el.className.replace(/\bis-(success|error)\b/g, "").trim();
    if (type) el.className += (el.className ? " " : "") + "is-" + type;
    el.textContent = text || "";
  }

  function setInvalid(input, hintId, message) {
    var hint = document.getElementById(hintId);
    if (input) {
      input.classList.toggle("is-invalid", Boolean(message));
      input.setAttribute("aria-invalid", message ? "true" : "false");
    }
    if (hint) hint.textContent = message || "";
  }

  function payloadFromForm(form) {
    var data = new FormData(form);
    return {
      name: String(data.get("name") || "").trim(),
      phone: normalizePhone(data.get("phone")),
      email: String(data.get("email") || "").trim(),
      subject: String(data.get("subject") || "callback"),
      message: String(data.get("message") || "").trim(),
      source: String(data.get("source") || "contact"),
      website: String(data.get("website") || ""),
    };
  }

  async function sendInquiry(body) {
    var res = await fetch(apiBase() + "/contact", {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(body),
    });
    var json = {};
    try {
      json = await res.json();
    } catch (_) {
      json = {};
    }
    if (!res.ok || json.ok === false) {
      throw new Error("fail");
    }
    return json;
  }

  function bindCallingForm() {
    var form = document.getElementById("callingForm");
    if (!form || form.dataset.bound) return;
    form.dataset.bound = "1";
    var statusEl = document.getElementById("callingFormStatus");
    var submitBtn = document.getElementById("callingFormSubmit");
    var nameEl = document.getElementById("calling-name");
    var phoneEl = document.getElementById("calling-phone");

    [nameEl, phoneEl].forEach(function (el) {
      if (!el) return;
      el.addEventListener("input", function () {
        el.classList.remove("is-invalid");
        setStatus(statusEl, "", "");
      });
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var ok = true;
      if (!nameEl || nameEl.value.trim().length < 2) {
        setInvalid(nameEl, "calling-hint-name", t("err_name", "نام را کامل وارد کنید."));
        ok = false;
      } else {
        setInvalid(nameEl, "calling-hint-name", "");
      }
      if (!phoneEl || !isValidPhone(phoneEl.value)) {
        setInvalid(phoneEl, "calling-hint-phone", t("err_phone", "شماره تماس معتبر وارد کنید."));
        ok = false;
      } else {
        setInvalid(phoneEl, "calling-hint-phone", "");
      }
      if (!ok) {
        setStatus(statusEl, "error", t("err_fix", "لطفاً موارد مشخص‌شده را اصلاح کنید."));
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.setAttribute("aria-busy", "true");
      }
      setStatus(statusEl, "", t("submitting", "در حال ثبت..."));

      sendInquiry(payloadFromForm(form))
        .then(function () {
          form.reset();
          setStatus(statusEl, "success", t("success", "درخواست ثبت شد. کارشناسان آلوین به‌زودی تماس می‌گیرند."));
        })
        .catch(function () {
          setStatus(statusEl, "error", t("error", "ثبت نشد. لطفاً دوباره تلاش کنید یا با {phone} تماس بگیرید."));
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.removeAttribute("aria-busy");
          }
        });
    });
  }

  function bindContactForm() {
    var form = document.getElementById("contactForm");
    if (!form || form.dataset.bound) return;
    form.dataset.bound = "1";

    var statusEl = document.getElementById("contactFormStatus");
    var submitBtn = document.getElementById("contactFormSubmit");
    var fields = {
      name: form.querySelector("#contact-name"),
      phone: form.querySelector("#contact-phone"),
      email: form.querySelector("#contact-email"),
      subject: form.querySelector("#contact-subject"),
      message: form.querySelector("#contact-message"),
    };

    function validate() {
      var ok = true;
      if (!fields.name.value.trim() || fields.name.value.trim().length < 2) {
        setInvalid(fields.name, "hint-name", t("err_name", "نام را کامل وارد کنید."));
        ok = false;
      } else {
        setInvalid(fields.name, "hint-name", "");
      }
      if (!isValidPhone(fields.phone.value)) {
        setInvalid(fields.phone, "hint-phone", t("err_phone", "شماره تماس معتبر وارد کنید."));
        ok = false;
      } else {
        setInvalid(fields.phone, "hint-phone", "");
      }
      if (!isValidEmail(fields.email.value.trim())) {
        setInvalid(fields.email, "hint-email", t("err_email", "ایمیل معتبر نیست."));
        ok = false;
      } else {
        setInvalid(fields.email, "hint-email", "");
      }
      if (!fields.subject.value) {
        setInvalid(fields.subject, "hint-subject", t("err_subject", "موضوع را انتخاب کنید."));
        ok = false;
      } else {
        setInvalid(fields.subject, "hint-subject", "");
      }
      if (!fields.message.value.trim() || fields.message.value.trim().length < 10) {
        setInvalid(fields.message, "hint-message", t("err_message", "پیام حداقل ۱۰ کاراکتر باشد."));
        ok = false;
      } else {
        setInvalid(fields.message, "hint-message", "");
      }
      return ok;
    }

    Object.keys(fields).forEach(function (key) {
      var el = fields[key];
      if (!el) return;
      el.addEventListener("input", function () {
        el.classList.remove("is-invalid");
        el.setAttribute("aria-invalid", "false");
        var hint = document.getElementById("hint-" + key);
        if (hint) hint.textContent = "";
        if (statusEl && statusEl.classList.contains("is-error")) setStatus(statusEl, "", "");
      });
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      setStatus(statusEl, "", "");
      if (!validate()) {
        setStatus(statusEl, "error", t("err_fix", "لطفاً موارد مشخص‌شده را اصلاح کنید."));
        var firstInvalid = form.querySelector(".is-invalid");
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.setAttribute("aria-busy", "true");
      }
      var body = payloadFromForm(form);
      body.source = "contact";
      sendInquiry(body)
        .then(function () {
          form.reset();
          setStatus(statusEl, "success", t("success", "پیام شما ثبت شد. کارشناسان آلوین به‌زودی پاسخ می‌دهند."));
        })
        .catch(function () {
          setStatus(statusEl, "error", t("error", "ثبت نشد. لطفاً دوباره تلاش کنید."));
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.removeAttribute("aria-busy");
          }
        });
    });
  }

  function bindPage() {
    bindCallingForm();
    bindContactForm();
  }

  window.ALWIN_PAGE = window.ALWIN_PAGE || {};
  window.ALWIN_PAGE.contact = bindPage;
  bindPage();

  var reveals = document.querySelectorAll(".contact-reveal");
  if ("IntersectionObserver" in window && reveals.length) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    reveals.forEach(function (el) {
      io.observe(el);
    });
  } else {
    reveals.forEach(function (el) {
      el.classList.add("is-visible");
    });
  }
})();
