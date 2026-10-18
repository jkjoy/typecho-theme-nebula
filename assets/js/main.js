(function () {
  "use strict";

  var root = document.documentElement;
  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var revealObserver = null;
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

  function revealContent() {
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
    }, { threshold: 0, rootMargin: "0px 0px -24px" });
    revealObserver = observer;

    elements.forEach(function (element) {
      revealObserver.observe(element);
    });
  }

  function imageFallbacks() {
    document.querySelectorAll(".post-cover img, .comment-avatar img").forEach(function (image) {
      function removeBrokenImage() {
        image.remove();
      }

      image.addEventListener("error", removeBrokenImage, { once: true });
      if (image.complete && image.naturalWidth === 0) removeBrokenImage();
    });
  }

  function codeBlocks() {
    var blocks = document.querySelectorAll(".article-content pre");
    if (!blocks.length) return;

    function languageName(pre, code) {
      var classes = (code.className + " " + pre.className).trim();
      var match = classes.match(/(?:lang(?:uage)?)-([^\s]+)/i);
      var language = match ? match[1] : code.getAttribute("data-language") || pre.getAttribute("data-language");
      return (language || "text").replace(/[_-]+/g, " ").toUpperCase();
    }

    function legacyCopy(text) {
      var textarea = document.createElement("textarea");
      textarea.value = text;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.select();
      var copied = document.execCommand("copy");
      textarea.remove();
      return copied ? Promise.resolve() : Promise.reject(new Error("copy failed"));
    }

    function copy(text) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      }
      return legacyCopy(text);
    }

    blocks.forEach(function (pre) {
      var code = pre.querySelector("code");
      if (!code || pre.querySelector(".code-copy")) return;

      var language = languageName(pre, code);
      var label = document.createElement("span");
      var button = document.createElement("button");
      var resetTimer = null;

      label.className = "code-language";
      label.textContent = language;
      button.className = "code-copy";
      button.type = "button";
      button.textContent = "复制";
      button.setAttribute("aria-label", "复制 " + language + " 代码");
      button.title = "复制代码";

      button.addEventListener("click", function () {
        if (resetTimer) window.clearTimeout(resetTimer);
        copy(code.textContent).then(function () {
          button.textContent = "已复制";
          button.classList.remove("is-error");
          button.classList.add("is-success");
        }).catch(function () {
          button.textContent = "复制失败";
          button.classList.remove("is-success");
          button.classList.add("is-error");
        }).finally(function () {
          resetTimer = window.setTimeout(function () {
            button.textContent = "复制";
            button.classList.remove("is-success", "is-error");
          }, 1800);
        });
      });

      pre.appendChild(label);
      pre.appendChild(button);
    });
  }

  var articleToc = (function () {
    var panel = document.getElementById("article-toc");
    var list = document.getElementById("article-toc-list");
    var button = document.getElementById("toc-toggle");
    var closeButton = document.getElementById("toc-close");
    var tools = document.getElementById("scroll-tools");
    var headings = [];
    var links = [];
    var activeIndex = -1;
    var frame = 0;
    var observer = null;
    var wideScreen = window.matchMedia("(min-width: 1280px)");

    function close(restoreFocus) {
      if (!panel || !button) return;
      var wasOpen = panel.classList.contains("is-open");
      panel.classList.remove("is-open");
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-label", "打开文章目录");
      if (restoreFocus && wasOpen) button.focus({ preventScroll: true });
    }

    function headingOffset() {
      return (parseFloat(getComputedStyle(root).getPropertyValue("--nav-h")) || 64) + 24;
    }

    function update() {
      frame = 0;
      if (!headings.length) return;
      var index = 0;
      var offset = headingOffset() + 4;
      for (var i = 0; i < headings.length; i++) {
        if (headings[i].getBoundingClientRect().top > offset) break;
        index = i;
      }
      if (window.scrollY > 0 && window.scrollY + root.clientHeight >= root.scrollHeight - 2) {
        index = headings.length - 1;
      }
      if (index === activeIndex) return;
      if (links[activeIndex]) {
        links[activeIndex].classList.remove("is-active");
        links[activeIndex].removeAttribute("aria-current");
      }
      var link = links[index];
      link.classList.add("is-active");
      link.setAttribute("aria-current", "location");
      activeIndex = index;

      var linkRect = link.getBoundingClientRect();
      var listRect = list.getBoundingClientRect();
      if (linkRect.top < listRect.top) list.scrollTop += linkRect.top - listRect.top;
      else if (linkRect.bottom > listRect.bottom) list.scrollTop += linkRect.bottom - listRect.bottom;
    }

    function scheduleUpdate() {
      if (headings.length && !frame) frame = window.requestAnimationFrame(update);
    }

    function unmount() {
      close(false);
      if (frame) window.cancelAnimationFrame(frame);
      if (observer) observer.disconnect();
      frame = 0;
      observer = null;
      headings = [];
      links = [];
      activeIndex = -1;
      if (panel) panel.hidden = true;
      if (button) button.hidden = true;
      if (list) list.replaceChildren();
      if (tools) tools.classList.remove("has-toc");
    }

    function mount() {
      unmount();
      var content = document.querySelector("#main-content [data-toc-content]");
      if (!content || !panel || !list || !button || !tools) return;
      headings = Array.from(content.querySelectorAll("h1, h2, h3, h4, h5, h6")).filter(function (heading) {
        return heading.textContent.trim() !== "";
      });
      if (!headings.length) return;

      var minLevel = Math.min.apply(null, headings.map(function (heading) { return Number(heading.tagName.slice(1)); }));
      var fragment = document.createDocumentFragment();
      headings.forEach(function (heading, index) {
        if (!heading.id || document.getElementById(heading.id) !== heading) {
          var suffix = index + 1;
          var id = "nebula-heading-" + suffix;
          while (document.getElementById(id)) id = "nebula-heading-" + (++suffix);
          heading.id = id;
        }
        var item = document.createElement("li");
        var link = document.createElement("a");
        item.className = "article-toc-item";
        link.className = "article-toc-link";
        link.style.setProperty("--toc-depth", Number(heading.tagName.slice(1)) - minLevel);
        link.href = "#" + encodeURIComponent(heading.id);
        link.textContent = heading.textContent.replace(/\s+/g, " ").trim();
        item.appendChild(link);
        fragment.appendChild(item);
        links.push(link);
      });
      list.appendChild(fragment);
      panel.hidden = false;
      button.hidden = false;
      tools.classList.add("has-toc");
      if ("ResizeObserver" in window) {
        observer = new ResizeObserver(scheduleUpdate);
        observer.observe(content);
      }
      update();
    }

    if (panel && list && button) {
      button.addEventListener("click", function () {
        if (panel.classList.contains("is-open")) {
          close(false);
          return;
        }
        panel.classList.add("is-open");
        button.setAttribute("aria-expanded", "true");
        button.setAttribute("aria-label", "关闭文章目录");
        if (links[activeIndex]) links[activeIndex].focus({ preventScroll: true });
      });
      if (closeButton) closeButton.addEventListener("click", function () { close(true); });
      list.addEventListener("click", function (event) {
        var link = event.target.closest(".article-toc-link");
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var index = links.indexOf(link);
        if (index < 0) return;
        event.preventDefault();
        close(true);
        var hash = link.getAttribute("href");
        var state = window.history.state;
        if (state && state.source === "swup") {
          state = Object.assign({}, state, { url: window.location.pathname + window.location.search + hash });
        }
        window.history.replaceState(state, "", hash);
        window.scrollTo({
          top: headings[index].getBoundingClientRect().top + window.scrollY - headingOffset(),
          behavior: reducedMotion ? "auto" : "smooth"
        });
        scheduleUpdate();
      });
      document.addEventListener("pointerdown", function (event) {
        if (!panel.contains(event.target) && !button.contains(event.target)) close(false);
      });
      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && panel.classList.contains("is-open")) close(true);
      });
      window.addEventListener("scroll", scheduleUpdate, { passive: true });
      window.addEventListener("resize", function () {
        if (wideScreen.matches) close(false);
        scheduleUpdate();
      });
      document.addEventListener("load", scheduleUpdate, true);
    }

    return { mount: mount, unmount: unmount, close: close };
  })();

  var updateScrollTools = (function scrollTools() {
    var tools = document.getElementById("scroll-tools");
    var topButton = document.getElementById("back-top");
    var bottomButton = document.getElementById("back-bottom");
    var progress = document.getElementById("scroll-progress");
    if (!tools || !topButton || !bottomButton || !progress) return function () {};
    var scheduled = false;

    function scrollRange() {
      var scrollingElement = document.scrollingElement || root;
      return Math.max(0, scrollingElement.scrollHeight - root.clientHeight);
    }

    function update() {
      var range = scrollRange();
      var position = Math.min(range, Math.max(0, window.scrollY));
      var percentage = range > 0 ? Math.round(position / range * 100) : 0;
      var text = percentage + "%";

      if (progress.textContent !== text) {
        progress.textContent = text;
        progress.setAttribute("aria-valuenow", String(percentage));
      }
      tools.classList.toggle("show", range > 1 || tools.classList.contains("has-toc"));
      topButton.disabled = position <= 1;
      bottomButton.disabled = range - position <= 1;
      scheduled = false;
    }

    function scheduleUpdate() {
      if (scheduled) return;
      scheduled = true;
      window.requestAnimationFrame(update);
    }

    window.addEventListener("scroll", scheduleUpdate, { passive: true });
    window.addEventListener("resize", scheduleUpdate);
    window.addEventListener("pageshow", scheduleUpdate);
    document.addEventListener("load", scheduleUpdate, true);
    if ("ResizeObserver" in window) {
      var observer = new ResizeObserver(scheduleUpdate);
      observer.observe(document.body);
    }

    topButton.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: reducedMotion ? "auto" : "smooth" });
    });
    bottomButton.addEventListener("click", function () {
      window.scrollTo({ top: scrollRange(), behavior: reducedMotion ? "auto" : "smooth" });
    });

    update();
    return scheduleUpdate;
  })();

  (function starfield() {
    var canvas = document.getElementById("bg-canvas");
    if (!canvas || !canvas.getContext) return;
    var context = canvas.getContext("2d", { alpha: true, desynchronized: true });
    var stars = [];
    var width = 0;
    var height = 0;
    var animationFrame = 0;
    var lastFrameTime = 0;
    var pointer = { x: 0.5, y: 0.5 };
    var coarsePointer = window.matchMedia("(pointer: coarse)").matches;
    var frameInterval = 1000 / (coarsePointer ? 24 : 30);
    var linkDistance = coarsePointer ? 104 : 120;

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
      var ratio = Math.min(window.devicePixelRatio || 1, coarsePointer ? 1 : 1.5);
      width = window.innerWidth;
      height = window.innerHeight;
      canvas.width = Math.round(width * ratio);
      canvas.height = Math.round(height * ratio);
      context.setTransform(ratio, 0, 0, ratio, 0, 0);
      var minimum = coarsePointer ? 20 : 28;
      var maximum = coarsePointer ? 42 : 64;
      var count = Math.min(maximum, Math.max(minimum, Math.round((width * height) / 24000)));
      while (stars.length < count) stars.push(makeStar());
      stars.length = count;
    }

    function draw(move, step) {
      context.clearRect(0, 0, width, height);
      var light = root.dataset.theme === "light";
      var color = light ? "30,36,54" : "232,236,244";
      var parallaxX = move ? (pointer.x - 0.5) * 24 : 0;
      var parallaxY = move ? (pointer.y - 0.5) * 24 : 0;

      stars.forEach(function (star) {
        if (move) {
          star.x += star.velocityX * step;
          star.y += star.velocityY * step;
          star.phase += 0.018 * step;
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

    function animate(timestamp) {
      animationFrame = window.requestAnimationFrame(animate);
      if (!lastFrameTime) {
        lastFrameTime = timestamp;
        draw(true, 1);
        return;
      }

      var elapsed = timestamp - lastFrameTime;
      if (elapsed < frameInterval) return;
      lastFrameTime = timestamp - (elapsed % frameInterval);
      draw(true, Math.min(elapsed / (1000 / 60), 3));
    }

    resize();
    if (reducedMotion) draw(false, 0);
    else animationFrame = window.requestAnimationFrame(animate);

    window.addEventListener("resize", function () {
      resize();
      if (reducedMotion) draw(false, 0);
    });

    if (!coarsePointer) {
      window.addEventListener("pointermove", function (event) {
        pointer.x = event.clientX / Math.max(width, 1);
        pointer.y = event.clientY / Math.max(height, 1);
      }, { passive: true });
    }

    document.addEventListener("visibilitychange", function () {
      if (reducedMotion) return;
      if (document.hidden) {
        window.cancelAnimationFrame(animationFrame);
        animationFrame = 0;
      } else if (!animationFrame) {
        lastFrameTime = 0;
        animationFrame = window.requestAnimationFrame(animate);
      }
    });
  })();

  function closePersistentUi() {
    var menuButton = document.getElementById("menu-toggle");
    var searchButton = document.getElementById("search-toggle");
    var searchPanel = document.getElementById("search-panel");
    articleToc.close(false);
    document.body.classList.remove("nav-open");
    if (menuButton) {
      menuButton.setAttribute("aria-expanded", "false");
      menuButton.setAttribute("aria-label", "打开菜单");
    }
    if (searchButton) {
      searchButton.setAttribute("aria-expanded", "false");
      searchButton.setAttribute("aria-label", "打开搜索");
    }
    if (searchPanel) {
      searchPanel.classList.remove("is-open");
      searchPanel.hidden = true;
    }
  }

  function normalizedPath(value) {
    var path = new URL(value, window.location.origin).pathname.replace(/\/+$/, "");
    return path || "/";
  }

  function updateNavigation() {
    var locationUrl = new URL(window.location.href);
    var currentPath = normalizedPath(locationUrl.href);
    var isSearch = locationUrl.searchParams.has("s");
    document.querySelectorAll("#site-nav a[href]").forEach(function (link) {
      var linkPath = normalizedPath(link.href);
      var isTagArchive = link.dataset.navSlug === "tags" && /\/tag(?:\/|$)/.test(currentPath);
      var active = !isSearch && (currentPath === linkPath || isTagArchive);
      link.classList.toggle("active", active);
      if (active) link.setAttribute("aria-current", "page");
      else link.removeAttribute("aria-current");
    });
  }

  var commentReplyController = {
    response: function () {
      return document.querySelector(".comments .respond");
    },

    inputParent: function (response, coid) {
      var form = response.tagName === "FORM" ? response : response.querySelector("form");
      if (!form) return;

      var input = form.querySelector('input[name="parent"]');
      if (!input && coid) {
        input = document.createElement("input");
        input.type = "hidden";
        input.name = "parent";
        form.appendChild(input);
      }

      if (coid && input) input.value = String(coid);
      else if (input) input.remove();
    },

    getChild: function (rootElement, node) {
      if (!rootElement || !node || !rootElement.contains(node)) return null;
      while (node.parentNode && node.parentNode !== rootElement) node = node.parentNode;
      return node.parentNode === rootElement ? node : null;
    },

    reply: function (htmlId, coid, button) {
      var response = this.response();
      var comment = document.getElementById(htmlId);
      if (!response || !comment || !button) return true;

      var child = this.getChild(comment, button);
      var holderId = response.id + "-holder";
      var holder = document.getElementById(holderId);
      this.inputParent(response, coid);

      if (!holder) {
        holder = document.createElement("div");
        holder.id = holderId;
        response.parentNode.insertBefore(holder, response);
      }

      if (child) comment.insertBefore(response, child.nextSibling);
      else comment.appendChild(response);

      var cancel = response.querySelector("#cancel-comment-reply-link");
      if (cancel) cancel.style.display = "";

      var textarea = response.querySelector('textarea[name="text"]');
      if (textarea) textarea.focus();
      return false;
    },

    cancelReply: function () {
      var response = this.response();
      if (!response) return true;

      var holder = document.getElementById(response.id + "-holder");
      this.inputParent(response, false);
      if (!holder || !holder.parentNode) return true;

      var cancel = response.querySelector("#cancel-comment-reply-link");
      if (cancel) cancel.style.display = "none";
      holder.parentNode.insertBefore(response, holder);
      holder.remove();
      return false;
    }
  };

  function mountCommentReply() {
    if (document.querySelector(".comments .respond")) {
      window.TypechoComment = Object.assign(window.TypechoComment || {}, commentReplyController);
    }
  }

  function unmountPage() {
    articleToc.unmount();
    if (revealObserver) {
      revealObserver.disconnect();
      revealObserver = null;
    }
    if (window.NebulaMemos) window.NebulaMemos.unmount();
  }

  function mountPage(focusMain) {
    revealContent();
    imageFallbacks();
    codeBlocks();
    if (window.NebulaMemos) window.NebulaMemos.mount();
    updateNavigation();
    mountCommentReply();
    updateScrollTools();

    var main = document.getElementById("main-content");
    if (focusMain && main) {
      main.setAttribute("tabindex", "-1");
      main.focus({ preventScroll: true });
      window.setTimeout(function () { main.removeAttribute("tabindex"); }, 0);
    }
  }

  articleToc.mount();
  mountPage(false);

  if (typeof window.Swup === "function") {
    var swup = new window.Swup({
      containers: ["#main-content"],
      animationSelector: "#main-content",
      linkSelector: ".site-header a[href], #main-content a[href]",
      ignoreVisit: function (url, context) {
        var link = context && context.el;
        var path = new URL(url, window.location.origin).pathname;
        if (link && link.closest("[data-no-swup]")) return true;
        if (/\/(?:admin|action)(?:\/|$)/i.test(path)) return true;
        return /\.(?:avif|gif|jpe?g|png|svg|webp|pdf|zip|rar|7z|tar|gz|mp3|mp4|ogg|webm|xml)$/i.test(path);
      },
      skipPopStateHandling: function (event) {
        if (window.NebulaMemos && window.NebulaMemos.handlesPopState()) return true;
        return !event.state || event.state.source !== "swup";
      }
    });

    swup.hooks.on("visit:start", closePersistentUi);
    swup.hooks.before("content:replace", unmountPage);
    swup.hooks.on("content:replace", articleToc.mount);
    swup.hooks.on("page:view", function () {
      mountPage(true);
      window.dispatchEvent(new CustomEvent("nebula:page-view", {
        detail: { url: window.location.href, title: document.title }
      }));
    });
  }
})();
