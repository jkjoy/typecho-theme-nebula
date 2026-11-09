(function () {
  "use strict";

  var active = null;

  function unmount() {
    if (active) active();
  }

  function mount() {
    unmount();
    var root = document.querySelector("[data-memos]");
    if (!root) return;

    var lightbox = document.querySelector("[data-memo-lightbox]");
    var image = lightbox && lightbox.querySelector("[data-lightbox-image]");
    var caption = lightbox && lightbox.querySelector("[data-lightbox-caption]");
    var previous = lightbox && lightbox.querySelector("[data-lightbox-prev]");
    var next = lightbox && lightbox.querySelector("[data-lightbox-next]");
    var close = lightbox && lightbox.querySelector("[data-lightbox-close]");
    var canOpen = lightbox && typeof lightbox.showModal === "function" && image && caption && previous && next && close;
    var listeners = [];
    var gallery = [];
    var galleryIndex = 0;

    function listen(target, type, handler) {
      target.addEventListener(type, handler);
      listeners.push(function () { target.removeEventListener(type, handler); });
    }

    function closeLightbox() {
      if (lightbox && lightbox.open) lightbox.close();
      if (image) image.removeAttribute("src");
      gallery = [];
    }

    function updateLightbox() {
      var link = gallery[galleryIndex];
      var thumbnail = link.querySelector("img");
      image.src = link.href;
      image.alt = thumbnail ? thumbnail.alt : "瞬间配图";
      caption.textContent = (galleryIndex + 1) + " / " + gallery.length;
      previous.hidden = gallery.length < 2;
      next.hidden = gallery.length < 2;
    }

    function moveLightbox(step) {
      if (!gallery.length) return;
      galleryIndex = (galleryIndex + step + gallery.length) % gallery.length;
      updateLightbox();
    }

    root.querySelectorAll("a.memo-photo[href] img").forEach(function (thumbnail) {
      function showFallback() {
        var link = thumbnail.closest("a.memo-photo");
        thumbnail.hidden = true;
        link.classList.add("memo-photo-unavailable");
        link.setAttribute("aria-label", "图片暂时无法加载，点击打开原图");
      }
      listen(thumbnail, "error", showFallback);
      if (thumbnail.complete && thumbnail.naturalWidth === 0) showFallback();
    });

    if (canOpen) {
      listen(root, "click", function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest("a.memo-photo[href]");
        if (!link || !root.contains(link) || link.classList.contains("memo-photo-unavailable")) return;
        var media = link.closest(".memo-media");
        if (!media) return;
        gallery = Array.from(media.querySelectorAll("a.memo-photo[href]")).filter(function (item) {
          return !item.classList.contains("memo-photo-unavailable");
        });
        galleryIndex = gallery.indexOf(link);
        if (galleryIndex < 0) return;
        event.preventDefault();
        updateLightbox();
        lightbox.showModal();
      });
      listen(close, "click", closeLightbox);
      listen(previous, "click", function () { moveLightbox(-1); });
      listen(next, "click", function () { moveLightbox(1); });
      listen(lightbox, "click", function (event) {
        if (event.target === lightbox) closeLightbox();
      });
      listen(lightbox, "keydown", function (event) {
        if (event.key === "ArrowLeft" || event.key === "ArrowRight") {
          event.preventDefault();
          moveLightbox(event.key === "ArrowLeft" ? -1 : 1);
        }
      });
      listen(lightbox, "close", function () {
        image.removeAttribute("src");
        gallery = [];
      });
    }

    active = function () {
      listeners.forEach(function (remove) { remove(); });
      closeLightbox();
      active = null;
    };
  }

  window.NebulaMemos = { mount: mount, unmount: unmount };
})();
