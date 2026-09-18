/**
 * Homepage intro — circular load/play ring, muted autoplay loop.
 * Buffers after window load so it does not compete with LCP.
 */
(function () {
  "use strict";

  var root = document.querySelector("[data-alwin-intro]");
  if (!root) return;

  var video = root.querySelector("video");
  var toggle = root.querySelector(".alwin-intro__toggle");
  var bufferedEl = root.querySelector(".alwin-intro__ring--buffered");
  var playedEl = root.querySelector(".alwin-intro__ring--played");
  if (!video || !toggle || !bufferedEl || !playedEl) return;

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var userPaused = false;

  function setPausedUI(paused) {
    root.classList.toggle("is-paused", paused);
    toggle.setAttribute("aria-pressed", paused ? "false" : "true");
    var playLabel = toggle.getAttribute("data-play-label") || "پخش ویدیو";
    var pauseLabel = toggle.getAttribute("data-pause-label") || "توقف ویدیو";
    toggle.setAttribute("aria-label", paused ? playLabel : pauseLabel);
  }

  function bufferedRatio() {
    var duration = video.duration;
    if (!duration || !isFinite(duration) || video.buffered.length === 0) return 0;
    return Math.min(1, video.buffered.end(video.buffered.length - 1) / duration);
  }

  function playedRatio() {
    var duration = video.duration;
    if (!duration || !isFinite(duration)) return 0;
    return Math.min(1, video.currentTime / duration);
  }

  function paintRings() {
    bufferedEl.style.strokeDashoffset = String(100 - bufferedRatio() * 100);
    playedEl.style.strokeDashoffset = String(100 - playedRatio() * 100);
  }

  function tryPlay() {
    if (userPaused || reduceMotion) return;
    var playPromise = video.play();
    if (playPromise && typeof playPromise.catch === "function") {
      playPromise.catch(function () {
        setPausedUI(true);
      });
    }
  }

  function startBuffering() {
    video.preload = "auto";
    if (typeof video.load === "function" && video.readyState < 2) {
      video.load();
    }
    tryPlay();
  }

  toggle.addEventListener("click", function (event) {
    event.preventDefault();
    event.stopPropagation();
    if (video.paused) {
      userPaused = false;
      tryPlay();
      return;
    }
    userPaused = true;
    video.pause();
  });

  video.addEventListener("play", function () {
    setPausedUI(false);
  });
  video.addEventListener("pause", function () {
    setPausedUI(true);
  });
  video.addEventListener("progress", paintRings);
  video.addEventListener("timeupdate", paintRings);
  video.addEventListener("loadedmetadata", paintRings);

  setPausedUI(video.paused || reduceMotion);
  if (reduceMotion) {
    video.pause();
    video.preload = "metadata";
    return;
  }

  if (document.readyState === "complete") startBuffering();
  else window.addEventListener("load", startBuffering, { once: true });
})();
