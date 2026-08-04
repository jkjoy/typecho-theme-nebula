(function () {
  "use strict";

  var root = document.querySelector("[data-memos-endpoint]");
  if (!root || !window.fetch) return;

  var list = root.querySelector("[data-memos-list]");
  var status = root.querySelector("[data-memos-status]");
  var pager = root.querySelector("[data-memos-pager]");
  var pageLabel = root.querySelector("[data-memos-page]");
  var prevButton = root.querySelector("[data-memos-prev]");
  var nextButton = root.querySelector("[data-memos-next]");
  var lightbox = document.querySelector("[data-memo-lightbox]");
  var endpoint = root.dataset.memosEndpoint;
  var limit = Math.min(100, Math.max(1, parseInt(root.dataset.memosLimit, 10) || 20));
  var query = new URLSearchParams(window.location.search);
  var currentPage = Math.max(1, parseInt(query.get("memo-page"), 10) || 1);
  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var controller = null;
  var requestId = 0;
  var gallery = [];
  var galleryIndex = 0;

  function safeUrl(value) {
    if (typeof value !== "string" || !value.trim()) return "";
    try {
      var parsed = new URL(value, window.location.origin);
      return parsed.protocol === "http:" || parsed.protocol === "https:" ? parsed.href : "";
    } catch (error) {
      return "";
    }
  }

  function sanitizeContent(value) {
    var parser = new DOMParser();
    var source = parser.parseFromString(typeof value === "string" ? value : "", "text/html");
    var allowed = ["P", "BR", "STRONG", "B", "EM", "I", "A", "UL", "OL", "LI", "BLOCKQUOTE", "CODE", "PRE", "DEL", "S", "MARK"];
    var discarded = ["SCRIPT", "STYLE", "IFRAME", "OBJECT", "EMBED", "FORM", "INPUT", "BUTTON", "SVG", "MATH", "TEMPLATE"];

    Array.from(source.body.querySelectorAll("*")).reverse().forEach(function (element) {
      if (discarded.indexOf(element.tagName) !== -1) {
        element.remove();
        return;
      }
      if (allowed.indexOf(element.tagName) === -1) {
        element.replaceWith.apply(element, Array.from(element.childNodes));
        return;
      }

      var href = element.tagName === "A" ? safeUrl(element.getAttribute("href")) : "";
      Array.from(element.attributes).forEach(function (attribute) {
        element.removeAttribute(attribute.name);
      });
      if (element.tagName === "A" && href) {
        element.href = href;
        element.target = "_blank";
        element.rel = "noopener noreferrer nofollow";
      } else if (element.tagName === "A") {
        element.replaceWith.apply(element, Array.from(element.childNodes));
      }
    });

    var output = document.createElement("div");
    Array.from(source.body.childNodes).forEach(function (node) {
      output.appendChild(node.cloneNode(true));
    });
    return output;
  }

  function timeParts(value) {
    var raw = typeof value === "string" ? value.trim() : "";
    var normalized = raw.replace(" ", "T");
    var date = new Date(normalized);
    if (!raw || Number.isNaN(date.getTime())) {
      return { date: "此刻", clock: "", datetime: "", title: raw || "时间未知" };
    }
    return {
      date: new Intl.DateTimeFormat("zh-CN", { month: "2-digit", day: "2-digit" }).format(date).replace("/", "."),
      clock: new Intl.DateTimeFormat("zh-CN", { hour: "2-digit", minute: "2-digit", hour12: false }).format(date),
      datetime: normalized,
      title: new Intl.DateTimeFormat("zh-CN", { year: "numeric", month: "long", day: "numeric", hour: "2-digit", minute: "2-digit", hour12: false }).format(date)
    };
  }

  function makeTag(value) {
    var tag = document.createElement("span");
    tag.className = "memo-tag";
    tag.textContent = "# " + String(value);
    return tag;
  }

  function makeLocation(latitude, longitude) {
    var lat = parseFloat(latitude);
    var lon = parseFloat(longitude);
    if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
    var link = document.createElement("a");
    link.className = "memo-location";
    link.href = "https://www.openstreetmap.org/?mlat=" + encodeURIComponent(lat) + "&mlon=" + encodeURIComponent(lon) + "#map=14/" + encodeURIComponent(lat) + "/" + encodeURIComponent(lon);
    link.target = "_blank";
    link.rel = "noopener noreferrer";
    link.title = "在地图中查看位置";
    link.textContent = "位置 " + lat.toFixed(4) + ", " + lon.toFixed(4);
    return link;
  }

  function openLightbox(items, index) {
    if (!lightbox || !items.length) return;
    gallery = items;
    galleryIndex = index;
    updateLightbox();
    if (typeof lightbox.showModal === "function") lightbox.showModal();
    else lightbox.setAttribute("open", "");
  }

  function updateLightbox() {
    if (!lightbox || !gallery.length) return;
    var item = gallery[galleryIndex];
    var image = lightbox.querySelector("[data-lightbox-image]");
    var caption = lightbox.querySelector("[data-lightbox-caption]");
    image.src = item.url;
    image.alt = item.alt;
    caption.textContent = (galleryIndex + 1) + " / " + gallery.length;
    lightbox.querySelector("[data-lightbox-prev]").hidden = gallery.length < 2;
    lightbox.querySelector("[data-lightbox-next]").hidden = gallery.length < 2;
  }

  function moveLightbox(step) {
    if (!gallery.length) return;
    galleryIndex = (galleryIndex + step + gallery.length) % gallery.length;
    updateLightbox();
  }

  function makeMedia(items, memoId) {
    var validItems = Array.isArray(items) ? items.map(function (item, index) {
      return {
        type: String(item && item.type || "").toUpperCase(),
        url: safeUrl(item && item.url),
        alt: "说说 " + memoId + " 的第 " + (index + 1) + " 张配图"
      };
    }).filter(function (item) { return item.url; }) : [];
    if (!validItems.length) return null;

    var media = document.createElement("div");
    media.className = "memo-media";
    media.dataset.count = String(Math.min(validItems.length, 4));
    var photos = validItems.filter(function (item) { return item.type === "PHOTO" || item.type === "IMAGE"; });

    validItems.forEach(function (item) {
      if (item.type === "PHOTO" || item.type === "IMAGE") {
        var button = document.createElement("button");
        var image = document.createElement("img");
        button.type = "button";
        button.className = "memo-photo";
        button.setAttribute("aria-label", "预览图片");
        image.src = item.url;
        image.alt = item.alt;
        image.loading = "lazy";
        image.addEventListener("error", function () {
          button.remove();
          if (!media.children.length) media.remove();
        }, { once: true });
        button.addEventListener("click", function () {
          openLightbox(photos, photos.indexOf(item));
        });
        button.appendChild(image);
        media.appendChild(button);
      } else if (item.type === "VIDEO") {
        var video = document.createElement("video");
        video.src = item.url;
        video.controls = true;
        video.preload = "metadata";
        media.appendChild(video);
      } else if (item.type === "AUDIO") {
        var audio = document.createElement("audio");
        audio.src = item.url;
        audio.controls = true;
        audio.preload = "metadata";
        media.appendChild(audio);
      } else {
        var fileLink = document.createElement("a");
        fileLink.className = "memo-attachment";
        fileLink.href = item.url;
        fileLink.target = "_blank";
        fileLink.rel = "noopener noreferrer";
        fileLink.textContent = "查看附件";
        media.appendChild(fileLink);
      }
    });
    return media;
  }

  function renderMemo(memo, index) {
    var id = String(memo.id || (currentPage + "-" + index));
    var time = timeParts(memo.time);
    var article = document.createElement("article");
    var date = document.createElement("a");
    var card = document.createElement("div");
    var content = sanitizeContent(memo.content);
    var media = makeMedia(memo.media, id);
    var foot = document.createElement("footer");
    var tags = Array.isArray(memo.tags) ? memo.tags.filter(function (tag) { return String(tag).trim(); }) : [];
    var location = makeLocation(memo.latitude, memo.longitude);

    article.className = "memo-item";
    article.id = "memo-" + id.replace(/[^a-zA-Z0-9_-]/g, "-");
    article.style.setProperty("--d", Math.min(index * 0.04, 0.24) + "s");
    date.className = "memo-date";
    date.href = "#" + article.id;
    date.title = time.title;
    date.innerHTML = "<time></time><small></small>";
    date.querySelector("time").dateTime = time.datetime;
    date.querySelector("time").textContent = time.date;
    date.querySelector("small").textContent = time.clock;
    card.className = "memo-card";
    content.className = "memo-content";
    card.appendChild(content);
    if (!content.textContent.trim() && !content.children.length) content.hidden = true;
    if (media) card.appendChild(media);

    if (tags.length || location) {
      foot.className = "memo-foot";
      var tagList = document.createElement("div");
      tagList.className = "memo-tags";
      tags.forEach(function (tag) { tagList.appendChild(makeTag(tag)); });
      if (tags.length) foot.appendChild(tagList);
      if (location) foot.appendChild(location);
      card.appendChild(foot);
    }

    article.appendChild(date);
    article.appendChild(card);
    return article;
  }

  function renderMessage(title, message, retry) {
    var box = document.createElement("div");
    var heading = document.createElement("h2");
    var detail = document.createElement("p");
    box.className = "memos-message";
    heading.textContent = title;
    detail.textContent = message;
    box.appendChild(heading);
    box.appendChild(detail);
    if (retry) {
      var button = document.createElement("button");
      button.className = "btn-ghost";
      button.type = "button";
      button.textContent = "重新加载";
      button.addEventListener("click", function () { loadPage(currentPage, false); });
      box.appendChild(button);
    }
    list.replaceChildren(box);
  }

  function setQueryPage(page, replace) {
    var url = new URL(window.location.href);
    if (page === 1) url.searchParams.delete("memo-page");
    else url.searchParams.set("memo-page", String(page));
    window.history[replace ? "replaceState" : "pushState"]({ memoPage: page }, "", url);
  }

  function setLoading(loading) {
    root.classList.toggle("is-fetching", loading);
    list.classList.toggle("is-loading", loading);
    list.setAttribute("aria-busy", String(loading));
    if (loading) {
      prevButton.disabled = true;
      nextButton.disabled = true;
    }
  }

  function loadPage(page, updateHistory) {
    if (controller) controller.abort();
    controller = "AbortController" in window ? new AbortController() : null;
    requestId += 1;
    var activeRequest = requestId;
    currentPage = Math.max(1, page);
    if (updateHistory) setQueryPage(currentPage, false);
    setLoading(true);
    status.textContent = "正在接收第 " + currentPage + " 页...";
    pager.hidden = true;

    var url = new URL(endpoint, window.location.origin);
    url.searchParams.set("limit", String(limit));
    url.searchParams.set("page", String(currentPage));
    var options = { headers: { Accept: "application/json" }, credentials: "same-origin" };
    if (controller) options.signal = controller.signal;

    fetch(url.href, options).then(function (response) {
      if (!response.ok) throw new Error("HTTP " + response.status);
      return response.json();
    }).then(function (data) {
      if (!Array.isArray(data)) throw new Error("INVALID_RESPONSE");
      var publicMemos = data.filter(function (memo) {
        return memo && (!memo.status || String(memo.status).toLowerCase() === "public");
      });
      var fragment = document.createDocumentFragment();
      publicMemos.forEach(function (memo, index) { fragment.appendChild(renderMemo(memo, index)); });
      list.replaceChildren(fragment);
      if (!publicMemos.length) renderMessage("这一页很安静", currentPage > 1 ? "没有更多说说了，可以返回上一页。" : "新的念头出现后，会在这里与你见面。", false);

      status.textContent = "第 " + currentPage + " 页 · " + publicMemos.length + " 条动态";
      pageLabel.textContent = "第 " + currentPage + " 页";
      prevButton.disabled = currentPage <= 1;
      nextButton.disabled = data.length < limit;
      pager.hidden = currentPage === 1 && data.length < limit;
    }).catch(function (error) {
      if (error && error.name === "AbortError") return;
      renderMessage("暂时无法读取说说", "请检查网络连接或接口 /api/v1/memos 是否可用。", true);
      status.textContent = "加载失败";
      pageLabel.textContent = "第 " + currentPage + " 页";
      prevButton.disabled = currentPage <= 1;
      nextButton.disabled = true;
      pager.hidden = currentPage <= 1;
    }).finally(function () {
      if (activeRequest !== requestId) return;
      setLoading(false);
    });
  }

  prevButton.addEventListener("click", function () {
    if (currentPage <= 1) return;
    loadPage(currentPage - 1, true);
    root.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
  });
  nextButton.addEventListener("click", function () {
    loadPage(currentPage + 1, true);
    root.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
  });
  window.addEventListener("popstate", function () {
    var params = new URLSearchParams(window.location.search);
    loadPage(Math.max(1, parseInt(params.get("memo-page"), 10) || 1), false);
  });

  if (lightbox) {
    lightbox.querySelector("[data-lightbox-close]").addEventListener("click", function () { lightbox.close(); });
    lightbox.querySelector("[data-lightbox-prev]").addEventListener("click", function () { moveLightbox(-1); });
    lightbox.querySelector("[data-lightbox-next]").addEventListener("click", function () { moveLightbox(1); });
    lightbox.addEventListener("click", function (event) {
      if (event.target === lightbox) lightbox.close();
    });
    lightbox.addEventListener("keydown", function (event) {
      if (event.key === "ArrowLeft") moveLightbox(-1);
      if (event.key === "ArrowRight") moveLightbox(1);
    });
  }

  setQueryPage(currentPage, true);
  loadPage(currentPage, false);
})();
