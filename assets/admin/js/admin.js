(function () {
  "use strict";

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  const sidebar = document.getElementById("sidebar");
  const scrim = document.querySelector("[data-sidebar-scrim]");
  function closeSidebar() {
    sidebar?.classList.remove("open");
    if (scrim) {
      scrim.hidden = true;
      scrim.classList.remove("show");
    }
  }
  function openSidebar() {
    sidebar?.classList.add("open");
    if (scrim) {
      scrim.hidden = false;
      scrim.classList.add("show");
    }
  }
  document.querySelectorAll("[data-sidebar-toggle]").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (sidebar?.classList.contains("open")) closeSidebar();
      else openSidebar();
    });
  });
  scrim?.addEventListener("click", closeSidebar);

  document.querySelectorAll("[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!window.confirm(form.getAttribute("data-confirm"))) e.preventDefault();
    });
  });

  function slugify(value) {
    return String(value)
      .trim()
      .replace(/\s+/g, "-")
      .replace(/[^\u0600-\u06FF\w-]/g, "")
      .replace(/-+/g, "-");
  }

  document.querySelectorAll("[data-slug-source]").forEach((source) => {
    const form = source.closest("form");
    const target = form?.querySelector("[data-slug-target]");
    if (!target) return;
    let touched = Boolean(target.value);
    target.addEventListener("input", () => { touched = true; });
    source.addEventListener("input", () => {
      if (!touched) target.value = slugify(source.value);
    });
  });

  document.querySelectorAll("select[name$=_id], select[name=image_id], select[name=image_close_id], select[name=image_open_id]").forEach((select) => {
    if (select.closest("[data-media-picker]")) return;
    const preview = select.parentElement.querySelector(".preview");
    const sync = () => {
      const url = select.selectedOptions[0]?.getAttribute("data-preview");
      if (preview && url) preview.src = url;
    };
    select.addEventListener("change", sync);
    sync();
  });

  document.querySelectorAll("form[data-unsaved]").forEach((form) => {
    let dirty = false;
    form.addEventListener("input", () => { dirty = true; });
    form.addEventListener("submit", () => { dirty = false; });
    window.addEventListener("beforeunload", (e) => {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = "";
    });
  });

  document.querySelectorAll("[data-sortable]").forEach((list) => {
    let dragged;
    list.querySelectorAll("[draggable=true]").forEach((item) => {
      item.addEventListener("dragstart", () => { dragged = item; item.style.opacity = "0.5"; });
      item.addEventListener("dragend", () => { item.style.opacity = ""; });
      item.addEventListener("dragover", (e) => {
        e.preventDefault();
        const over = e.currentTarget;
        if (!dragged || dragged === over) return;
        const rect = over.getBoundingClientRect();
        const after = e.clientY > rect.top + rect.height / 2;
        over.parentNode.insertBefore(dragged, after ? over.nextSibling : over);
      });
    });
  });

  document.querySelectorAll("textarea.rich").forEach((textarea) => {
    const wrap = document.createElement("div");
    wrap.className = "rich-wrap";
    const bar = document.createElement("div");
    bar.className = "rich-toolbar";
    const editor = document.createElement("div");
    editor.className = "rich-editor";
    editor.contentEditable = "true";
    editor.innerHTML = textarea.value;
    textarea.style.display = "none";
    textarea.parentNode.insertBefore(wrap, textarea);
    wrap.appendChild(bar);
    wrap.appendChild(editor);
    wrap.appendChild(textarea);
    [
      ["bold", "پررنگ"],
      ["italic", "کج"],
      ["insertUnorderedList", "فهرست"],
      ["insertOrderedList", "شماره"],
      ["undo", "بازگشت"],
      ["redo", "جلو"],
    ].forEach(([cmd, label]) => {
      const b = document.createElement("button");
      b.type = "button";
      b.textContent = label;
      b.addEventListener("click", () => document.execCommand(cmd, false, null));
      bar.appendChild(b);
    });
    const h = document.createElement("button");
    h.type = "button";
    h.textContent = "عنوان";
    h.addEventListener("click", () => document.execCommand("formatBlock", false, "h3"));
    bar.appendChild(h);
    const link = document.createElement("button");
    link.type = "button";
    link.textContent = "پیوند";
    link.addEventListener("click", () => {
      const url = window.prompt("نشانی پیوند");
      if (url) document.execCommand("createLink", false, url);
    });
    bar.appendChild(link);
    const sync = () => { textarea.value = editor.innerHTML; };
    editor.addEventListener("input", sync);
    textarea.form?.addEventListener("submit", sync);
  });

  document.querySelectorAll("form[data-upload]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      const file = form.querySelector('input[type="file"]')?.files?.[0];
      if (!file || !window.XMLHttpRequest) return;
      e.preventDefault();
      const bar = form.querySelector(".upload-progress");
      const fill = bar?.querySelector("span");
      if (bar) bar.hidden = false;
      const data = new FormData(form);
      const xhr = new XMLHttpRequest();
      xhr.open("POST", form.action);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      if (csrf) xhr.setRequestHeader("X-CSRF-TOKEN", csrf);
      xhr.upload.onprogress = (ev) => {
        if (!ev.lengthComputable || !fill) return;
        fill.style.width = Math.round((ev.loaded / ev.total) * 100) + "%";
      };
      xhr.onload = () => { window.location.reload(); };
      xhr.onerror = () => {
        if (bar) bar.hidden = true;
        window.alert("بارگذاری ناموفق بود. دوباره تلاش کنید.");
      };
      xhr.send(data);
    });
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  function setDragState(el, on) {
    el.classList.toggle("is-dragover", on);
  }

  function bindDropTarget(el, onFiles) {
    ["dragenter", "dragover", "dragleave", "drop"].forEach((ev) => {
      el.addEventListener(ev, preventDefaults);
    });
    el.addEventListener("dragenter", () => setDragState(el, true));
    el.addEventListener("dragover", () => setDragState(el, true));
    el.addEventListener("dragleave", (e) => {
      if (!el.contains(e.relatedTarget)) setDragState(el, false);
    });
    el.addEventListener("drop", (e) => {
      setDragState(el, false);
      const files = e.dataTransfer?.files;
      if (files && files.length) onFiles(files);
    });
  }

  function assignFileInput(input, file) {
    try {
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      input.dispatchEvent(new Event("change", { bubbles: true }));
      return true;
    } catch (err) {
      return false;
    }
  }

  document.querySelectorAll("[data-dropzone]").forEach((zone) => {
    const input = zone.querySelector("[data-dropzone-input]");
    const nameEl = zone.querySelector("[data-dropzone-name]");
    if (!input) return;

    const showName = () => {
      const file = input.files?.[0];
      if (!nameEl) return;
      if (!file) {
        nameEl.hidden = true;
        nameEl.textContent = "";
        return;
      }
      nameEl.hidden = false;
      nameEl.textContent = file.name;
    };

    zone.addEventListener("click", (e) => {
      if (e.target.closest("button, a, input:not([type=file])")) return;
      input.click();
    });
    zone.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        input.click();
      }
    });
    if (!zone.hasAttribute("tabindex")) zone.setAttribute("tabindex", "0");

    bindDropTarget(zone, (files) => {
      const file = files[0];
      if (!file) return;
      assignFileInput(input, file);
      showName();
    });
    input.addEventListener("change", showName);
  });

  const mediaCatalog = Array.isArray(window.__ADMIN_MEDIA__) ? window.__ADMIN_MEDIA__.slice() : [];
  const modal = document.getElementById("media-library-modal");
  const modalGrid = modal?.querySelector("[data-media-library-grid]");
  const modalEmpty = modal?.querySelector("[data-media-library-empty]");
  const modalSearch = modal?.querySelector("[data-media-library-search]");
  let activePicker = null;

  function upsertCatalog(item) {
    if (!item || !item.id) return;
    const idx = mediaCatalog.findIndex((m) => String(m.id) === String(item.id));
    const row = {
      id: item.id,
      url: item.url,
      thumb_url: item.thumb_url || item.url,
      name: item.filename || item.name || ("media-" + item.id),
      kind: item.kind || "image",
      alt: item.alt || "",
    };
    if (idx >= 0) mediaCatalog[idx] = row;
    else mediaCatalog.unshift(row);
    window.__ADMIN_MEDIA__ = mediaCatalog;
  }

  function closeLibrary() {
    if (!modal) return;
    modal.hidden = true;
    modal.setAttribute("aria-hidden", "true");
    activePicker = null;
    document.body.style.overflow = "";
  }

  function renderLibrary(query) {
    if (!modalGrid) return;
    const q = String(query || "").trim().toLowerCase();
    const items = mediaCatalog.filter((m) => {
      if ((m.kind || "image") !== "image") return false;
      if (!q) return true;
      return String(m.name || "").toLowerCase().includes(q) || String(m.alt || "").toLowerCase().includes(q);
    });
    modalGrid.innerHTML = "";
    if (modalEmpty) modalEmpty.hidden = items.length > 0;
    items.forEach((item) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "media-modal__item";
      btn.innerHTML = '<img class="media-modal__thumb" alt=""><span></span>';
      btn.querySelector("img").src = item.thumb_url || item.url;
      btn.querySelector("span").textContent = item.name;
      btn.addEventListener("click", () => {
        if (activePicker) activePicker.selectMedia(item);
        closeLibrary();
      });
      modalGrid.appendChild(btn);
    });
  }

  function openLibrary(picker) {
    if (!modal) return;
    activePicker = picker;
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    if (modalSearch) modalSearch.value = "";
    renderLibrary("");
    modalSearch?.focus();
  }

  modal?.querySelectorAll("[data-media-modal-close]").forEach((el) => {
    el.addEventListener("click", closeLibrary);
  });
  modalSearch?.addEventListener("input", () => renderLibrary(modalSearch.value));
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal && !modal.hidden) closeLibrary();
  });

  function createPicker(root) {
    const valueInput = root.querySelector("[data-media-value]");
    const fileInput = root.querySelector("[data-media-file]");
    const stage = root.querySelector("[data-media-stage]");
    const empty = root.querySelector("[data-media-empty]");
    const filled = root.querySelector("[data-media-filled]");
    const preview = root.querySelector("[data-media-preview]");
    const nameEl = root.querySelector("[data-media-name]");
    const progress = root.querySelector("[data-media-progress]");
    const progressFill = progress?.querySelector("span");
    const errorEl = root.querySelector("[data-media-error]");
    const uploadUrl = root.getAttribute("data-upload-url") || window.__ADMIN_MEDIA_UPLOAD__;

    const api = {
      selectMedia(item) {
        if (!item) return;
        valueInput.value = item.id;
        if (preview) preview.src = item.url || "";
        if (nameEl) nameEl.textContent = item.filename || item.name || "تصویر انتخاب‌شده";
        stage.classList.add("has-file");
        if (empty) empty.hidden = true;
        if (filled) filled.hidden = false;
        setError("");
        valueInput.dispatchEvent(new Event("input", { bubbles: true }));
        upsertCatalog(item);
      },
      clear() {
        valueInput.value = "";
        if (preview) preview.removeAttribute("src");
        if (nameEl) nameEl.textContent = "";
        stage.classList.remove("has-file");
        if (empty) empty.hidden = false;
        if (filled) filled.hidden = true;
        if (fileInput) fileInput.value = "";
        setError("");
        valueInput.dispatchEvent(new Event("input", { bubbles: true }));
      },
    };

    function setError(msg) {
      if (!errorEl) return;
      if (!msg) {
        errorEl.hidden = true;
        errorEl.textContent = "";
        return;
      }
      errorEl.hidden = false;
      errorEl.textContent = msg;
    }

    function setProgress(pct, show) {
      if (!progress) return;
      progress.hidden = !show;
      if (progressFill) progressFill.style.width = (pct || 0) + "%";
    }

    function uploadFile(file) {
      if (!file || !uploadUrl) return;
      setError("");
      setProgress(0, true);
      const data = new FormData();
      data.append("file", file);
      data.append("alt", file.name.replace(/\.[^.]+$/, ""));
      const collection = root.getAttribute("data-collection");
      if (collection) data.append("collection", collection);
      const xhr = new XMLHttpRequest();
      xhr.open("POST", uploadUrl);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.setRequestHeader("Accept", "application/json");
      if (csrf) xhr.setRequestHeader("X-CSRF-TOKEN", csrf);
      xhr.upload.onprogress = (ev) => {
        if (!ev.lengthComputable) return;
        setProgress(Math.round((ev.loaded / ev.total) * 100), true);
      };
      xhr.onload = () => {
        setProgress(100, false);
        let payload = null;
        try { payload = JSON.parse(xhr.responseText); } catch (err) {}
        if (xhr.status >= 200 && xhr.status < 300 && payload && payload.media) {
          api.selectMedia(payload.media);
          return;
        }
        const msg = payload?.message
          || payload?.errors?.file?.[0]
          || "بارگذاری ناموفق بود.";
        setError(msg);
      };
      xhr.onerror = () => {
        setProgress(0, false);
        setError("ارتباط با سرور برقرار نشد.");
      };
      xhr.send(data);
    }

    root.querySelectorAll("[data-media-browse-file]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        fileInput?.click();
      });
    });
    root.querySelectorAll("[data-media-open-library]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLibrary(api);
      });
    });
    root.querySelectorAll("[data-media-clear]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        api.clear();
      });
    });

    stage?.addEventListener("click", (e) => {
      if (e.target.closest("button, a, input")) return;
      if (stage.classList.contains("has-file")) return;
      fileInput?.click();
    });

    fileInput?.addEventListener("change", () => {
      const file = fileInput.files?.[0];
      if (file) uploadFile(file);
    });

    if (stage) {
      bindDropTarget(stage, (files) => {
        const file = files[0];
        if (file) uploadFile(file);
      });
    }

    return api;
  }

  document.querySelectorAll("[data-media-picker]").forEach(createPicker);

  function clip(text, max) {
    const s = String(text || "").trim();
    return s.length <= max ? s : s.slice(0, max - 1).trim();
  }
  function plainText(html) {
    const d = document.createElement("div");
    d.innerHTML = html || "";
    return (d.textContent || "").replace(/\s+/g, " ").trim();
  }
  function seoTitleFrom(name) {
    const n = String(name || "").trim();
    if (!n) return "";
    if (/آلوین|alwin/i.test(n)) return clip(n, 70);
    return clip(n + " | آلوین", 70);
  }
  function typeKeyFrom(label) {
    const t = String(label || "");
    if (/توری/.test(t)) return "screen";
    if (/کرتین/.test(t)) return "curtain_wall";
    if (/بالکن/.test(t)) return "balcony";
    if (/در/.test(t)) return "door";
    return "upvc";
  }

  function bindAutofill(root) {
    const title = root.querySelector("[data-fill-source='title']");
    const summary = root.querySelector("[data-fill-source='summary']");
    const category = root.querySelector("[data-fill-source='category']");
    const typeLabel = root.querySelector("[data-fill-source='type-label']");

    function fill(selector, value, force) {
      root.querySelectorAll(selector).forEach((el) => {
        if (!force && el.dataset.fillLocked === "1") return;
        if (value === undefined || value === null) return;
        if (el.value === String(value)) return;
        el.value = value;
      });
    }

    root.querySelectorAll("[data-autofill]").forEach((el) => {
      if (el.value) el.dataset.fillLocked = "1";
      el.addEventListener("input", (e) => {
        if (e.isTrusted) el.dataset.fillLocked = "1";
      });
    });

    const apply = () => {
      const name = title?.value || "";
      fill("[data-autofill='seo-title']", seoTitleFrom(name));
      fill("[data-autofill='alt']", name);
      fill("[data-autofill='slug']", slugify(name));
      const desc = clip(plainText(summary?.value || "") || name, 160);
      fill("[data-autofill='seo-desc']", desc);
      if (category) {
        const type = category.selectedOptions[0]?.getAttribute("data-product-type");
        if (type) fill("[data-autofill='product-type']", type);
      }
      if (typeLabel) fill("[data-autofill='type-key']", typeKeyFrom(typeLabel.value));

      const serpTitle = root.querySelector("[data-serp-title]");
      const serpDesc = root.querySelector("[data-serp-desc]");
      const titleEl = root.querySelector("[data-serp-bind='title']");
      const descEl = root.querySelector("[data-serp-bind='desc']");
      if (serpTitle) serpTitle.textContent = titleEl?.value || seoTitleFrom(name) || "عنوان | آلوین";
      if (serpDesc) serpDesc.textContent = descEl?.value || desc || "توضیح اینجا دیده می‌شود.";
      root.querySelectorAll("[data-count]").forEach((el) => el.dispatchEvent(new Event("recount")));
    };

    [title, summary, category, typeLabel].forEach((el) => {
      el?.addEventListener("input", apply);
      el?.addEventListener("change", apply);
    });
    root.querySelectorAll("[data-serp-bind]").forEach((el) => el.addEventListener("input", apply));
    apply();
  }

  document.querySelectorAll("[data-easy-form], form").forEach((form) => {
    if (form.querySelector("[data-fill-source], [data-autofill], [data-serp]")) bindAutofill(form);
  });

  document.querySelectorAll("[data-status-switch]").forEach((box) => {
    const input = box.closest("label, .switch-card, form")?.querySelector("[data-status-input]");
    if (!input) return;
    const sync = () => { input.value = box.checked ? "published" : "draft"; };
    box.addEventListener("change", sync);
    sync();
  });

  document.querySelectorAll("[data-count]").forEach((el) => {
    const out = el.parentElement?.querySelector("[data-count-out]");
    const max = Number(el.getAttribute("maxlength") || 0);
    const paint = () => {
      if (!out || !max) return;
      const n = (el.value || "").length;
      out.textContent = n + " از " + max + " حرف";
      out.classList.toggle("is-warn", n > max * 0.92);
    };
    el.addEventListener("input", paint);
    el.addEventListener("recount", paint);
    paint();
  });

  document.querySelectorAll("[data-save-dock]").forEach((dock) => {
    const form = dock.closest("form");
    const hint = dock.querySelector("[data-save-hint]");
    if (!form || !hint) return;
    form.addEventListener("input", () => {
      dock.classList.add("is-dirty");
      hint.textContent = "تغییرات ذخیره نشده — یک ذخیره کافی است.";
    });
    form.addEventListener("submit", () => dock.classList.remove("is-dirty"));
  });

  document.querySelectorAll("[data-nav-page]").forEach((sel) => {
    const url = sel.closest("form")?.querySelector('[name="url"]');
    if (!url) return;
    sel.addEventListener("change", () => {
      if (sel.value && sel.value !== "custom") url.value = sel.value;
      if (sel.value === "custom") url.focus();
    });
  });

  const phone = document.querySelector('[data-setting-key="phone"]');
  const phoneDisplay = document.querySelector('[data-setting-key="phone_display"]');
  if (phone && phoneDisplay) {
    let locked = Boolean(phoneDisplay.value && phoneDisplay.value !== phone.value);
    phone.addEventListener("input", () => {
      if (!locked) phoneDisplay.value = phone.value;
    });
    phoneDisplay.addEventListener("input", () => { locked = true; });
  }

  document.querySelectorAll("[data-dropzone]").forEach((zone) => {
    const form = zone.closest("form");
    const alt = form?.querySelector("[data-media-alt]");
    const input = zone.querySelector("[data-dropzone-input]");
    if (!alt || !input) return;
    input.addEventListener("change", () => {
      const file = input.files?.[0];
      if (file && !alt.value) alt.value = file.name.replace(/\.[^.]+$/, "");
    });
  });

  document.querySelectorAll("[data-auto-dismiss]").forEach((toast) => {
    setTimeout(() => {
      toast.style.transition = "opacity 0.35s ease, transform 0.35s ease";
      toast.style.opacity = "0";
      toast.style.transform = "translateY(-8px)";
      setTimeout(() => toast.remove(), 380);
    }, 4200);
  });

  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    document.querySelectorAll(".fly-stage > *:not(.fly-item)").forEach((el, i) => {
      if (el.classList.contains("toast")) return;
      el.classList.add("fly-item");
      if (!el.style.getPropertyValue("--i")) el.style.setProperty("--i", String(Math.min(i, 8)));
    });
  }
})();
