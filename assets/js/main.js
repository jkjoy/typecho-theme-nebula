(function () {
  "use strict";

  var root = document.documentElement;
  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  root.classList.add("js");

  (function themeToggle() {
    var button = document.getElementById("theme-toggle");
    if (!button) return;

    button.addEventListener("click", function () {
      var next = root.dataset.theme === "light" ? "dark" : "light";
      root.dataset.theme = next;
      try {
        localStorage.setItem("nebula-theme", next);
      } catch (error) {
        // Storage may be unavailable in private browsing.
      }
    });
  })();

  (function mobileMenu() {
    var button = document.getElementById("menu-toggle");
    var nav = document.getElementById("site-nav");
    if (!button || !nav) return;

    function close() {
      document.body.classList.remove("nav-open");
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-label", "打开菜单");
    }

    button.addEventListener("click", function () {
      var open = document.body.classList.toggle("nav-open");
      button.setAttribute("aria-expanded", String(open));
      button.setAttribute("aria-label", open ? "关闭菜单" : "打开菜单");
    });

    nav.addEventListener("click", function (event) {
      if (event.target.closest("a")) close();
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 920) close();
    });
  })();

  (function searchPanel() {
    var button = document.getElementById("search-toggle");
    var panel = document.getElementById("search-panel");
    var input = document.getElementById("search-input");
    var closeTimer = null;
    if (!button || !panel || !input) return;

    function open() {
      if (closeTimer) window.clearTimeout(closeTimer);
      panel.hidden = false;
      button.setAttribute("aria-expanded", "true");
      button.setAttribute("aria-label", "关闭搜索");
      window.requestAnimationFrame(function () {
        panel.classList.add("is-open");
        input.focus({ preventScroll: true });
      });
    }

    function close() {
      panel.classList.remove("is-open");
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-label", "打开搜索");
      closeTimer = window.setTimeout(function () {
        panel.hidden = true;
      }, reducedMotion ? 0 : 180);
    }

    button.addEventListener("click", function () {
      if (panel.hidden) open(); else close();
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !panel.hidden) {
        close();
        button.focus({ preventScroll: true });
      }
    });
  })();

  (function revealContent() {
    var elements = document.querySelectorAll(".reveal");
    if (!elements.length) return;

    if (reducedMotion || !("IntersectionObserver" in window)) {
      elements.forEach(function (element) {
        element.classList.add("visible");
      });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("visible");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: "0px 0px -24px" });

    elements.forEach(function (element) {
      observer.observe(element);
    });
  })();

  (function imageFallbacks() {
    document.querySelectorAll(".post-cover img, .comment-avatar img").forEach(function (image) {
      function removeBrokenImage() {
        image.remove();
      }

      image.addEventListener("error", removeBrokenImage, { once: true });
      if (image.complete && image.naturalWidth === 0) removeBrokenImage();
    });
  })();

  (function backToTop() {
    var button = document.getElementById("back-top");
    if (!button) return;
    var scheduled = false;

    function update() {
      button.classList.toggle("show", window.scrollY > 420);
      scheduled = false;
    }

    window.addEventListener("scroll", function () {
      if (scheduled) return;
      scheduled = true;
      window.requestAnimationFrame(update);
    }, { passive: true });

    button.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: reducedMotion ? "auto" : "smooth" });
    });

    update();
  })();

  (function starfield() {
    var canvas = document.getElementById("bg-canvas");
    if (!canvas || !canvas.getContext) return;
    var context = canvas.getContext("2d");
    var stars = [];
    var width = 0;
    var height = 0;
    var animationFrame = 0;
    var pointer = { x: 0.5, y: 0.5 };
    var linkDistance = 128;

    function makeStar() {
      return {
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 1.45 + 0.35,
        velocityX: (Math.random() - 0.5) * 0.2,
        velocityY: (Math.random() - 0.5) * 0.2,
        depth: Math.random() * 0.75 + 0.25,
        phase: Math.random() * Math.PI * 2
      };
    }

    function resize() {
      var ratio = Math.min(window.devicePixelRatio || 1, 2);
      width = window.innerWidth;
      height = window.innerHeight;
      canvas.width = Math.round(width * ratio);
      canvas.height = Math.round(height * ratio);
      context.setTransform(ratio, 0, 0, ratio, 0, 0);
      var count = Math.min(86, Math.max(28, Math.round((width * height) / 18000)));
      while (stars.length < count) stars.push(makeStar());
      stars.length = count;
    }

    function draw(move) {
      context.clearRect(0, 0, width, height);
      var light = root.dataset.theme === "light";
      var color = light ? "30,36,54" : "232,236,244";
      var parallaxX = move ? (pointer.x - 0.5) * 24 : 0;
      var parallaxY = move ? (pointer.y - 0.5) * 24 : 0;

      stars.forEach(function (star) {
        if (move) {
          star.x += star.velocityX;
          star.y += star.velocityY;
          star.phase += 0.018;
          if (star.x < -8) star.x = width + 8;
          if (star.x > width + 8) star.x = -8;
          if (star.y < -8) star.y = height + 8;
          if (star.y > height + 8) star.y = -8;
        }

        star.drawX = star.x + parallaxX * star.depth;
        star.drawY = star.y + parallaxY * star.depth;
        var alpha = move ? 0.32 + 0.42 * Math.abs(Math.sin(star.phase)) : 0.58;
        context.beginPath();
        context.arc(star.drawX, star.drawY, star.radius, 0, Math.PI * 2);
        context.fillStyle = "rgba(" + color + "," + alpha.toFixed(2) + ")";
        context.fill();
      });

      for (var first = 0; first < stars.length; first += 1) {
        for (var second = first + 1; second < stars.length; second += 1) {
          var deltaX = stars[first].drawX - stars[second].drawX;
          var deltaY = stars[first].drawY - stars[second].drawY;
          var distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
          if (distance >= linkDistance) continue;
          var alpha = (1 - distance / linkDistance) * (light ? 0.07 : 0.12);
          context.beginPath();
          context.moveTo(stars[first].drawX, stars[first].drawY);
          context.lineTo(stars[second].drawX, stars[second].drawY);
          context.strokeStyle = "rgba(" + color + "," + alpha.toFixed(3) + ")";
          context.lineWidth = 0.6;
          context.stroke();
        }
      }
    }

    function animate() {
      draw(true);
      animationFrame = window.requestAnimationFrame(animate);
    }

    resize();
    if (reducedMotion) draw(false); else animate();

    window.addEventListener("resize", function () {
      resize();
      if (reducedMotion) draw(false);
    });

    window.addEventListener("pointermove", function (event) {
      pointer.x = event.clientX / Math.max(width, 1);
      pointer.y = event.clientY / Math.max(height, 1);
    }, { passive: true });

    document.addEventListener("visibilitychange", function () {
      if (reducedMotion) return;
      if (document.hidden) {
        window.cancelAnimationFrame(animationFrame);
        animationFrame = 0;
      } else if (!animationFrame) {
        animate();
      }
    });
  })();
})();
