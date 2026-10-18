(function () {
  "use strict";

  var active = null;
  var turnstilePromise = null;

  function loadTurnstile() {
    if (window.turnstile && window.turnstile.render) return Promise.resolve(window.turnstile);
    if (turnstilePromise) return turnstilePromise;

    turnstilePromise = new Promise(function (resolve, reject) {
      var script = document.querySelector('script[src^="https://challenges.cloudflare.com/turnstile/v0/api.js"]');
      var created = !script;
      if (created) {
        script = document.createElement("script");
        script.src = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
        script.async = true;
      }
      var timer = window.setTimeout(failed, 15000);

      function cleanup() {
        window.clearTimeout(timer);
        script.removeEventListener("load", loaded);
        script.removeEventListener("error", failed);
      }

      function loaded() {
        if (!window.turnstile || !window.turnstile.render) {
          failed();
          return;
        }
        cleanup();
        resolve(window.turnstile);
      }

      function failed() {
        cleanup();
        if (created) script.remove();
        turnstilePromise = null;
        reject(new Error("人机验证加载失败，请刷新页面后重试。"));
      }

      script.addEventListener("load", loaded);
      script.addEventListener("error", failed);
      if (created) document.head.appendChild(script);
    });
    return turnstilePromise;
  }

  function unmount() {
    if (active) active();
    active = null;
  }

  function mount() {
    unmount();
    var form = document.querySelector("[data-link-apply-form]");
    if (!form) return;
    var button = form.querySelector("[data-link-apply-submit]");
    var status = form.querySelector("[data-link-apply-status]");
    var challenge = form.querySelector(".link-apply-challenge");
    var required = form.dataset.turnstileRequired === "true";
    var label = button.textContent;
    var mounted = true;
    var pending = false;
    var controller = null;
    var widgetId = null;
    var challengeApi = null;
    var challengeSize = "";
    var challengeTheme = "";
    var challengeResizeObserver = null;
    var challengeThemeObserver = null;

    function message(text, state) {
      if (!mounted) return;
      status.textContent = text;
      status.dataset.state = state;
      status.hidden = false;
    }

    function renderChallenge() {
      if (!mounted || !challengeApi) return;
      // Flexible widgets need at least 300px; keep narrow screens usable without scaling.
      var size = challenge.clientWidth >= 300 ? "flexible" : "compact";
      var theme = document.documentElement.dataset.theme || "auto";
      if (widgetId !== null && size === challengeSize && theme === challengeTheme) return;
      if (widgetId !== null) challengeApi.remove(widgetId);
      widgetId = null;
      challenge.classList.remove("is-interactive");
      widgetId = challengeApi.render(challenge, {
        sitekey: challenge.dataset.sitekey,
        size: size,
        theme: theme,
        appearance: "interaction-only",
        "before-interactive-callback": function () {
          if (mounted) challenge.classList.add("is-interactive");
        },
        "error-callback": function () {
          if (!pending && status.dataset.state !== "success") message("人机验证暂时不可用，请刷新页面后重试。", "error");
        }
      });
      challengeSize = size;
      challengeTheme = theme;
    }

    function initChallenge() {
      if (!mounted || !required || !challenge) return;
      loadTurnstile().then(function (api) {
        if (!mounted) return;
        challengeApi = api;
        renderChallenge();
        if ("ResizeObserver" in window) {
          challengeResizeObserver = new ResizeObserver(renderChallenge);
          challengeResizeObserver.observe(challenge);
        } else window.addEventListener("resize", renderChallenge);
        challengeThemeObserver = new MutationObserver(renderChallenge);
        challengeThemeObserver.observe(document.documentElement, {
          attributes: true,
          attributeFilter: ["data-theme"]
        });
      }).catch(function (error) {
        if (status.dataset.state !== "success") message(error.message, "error");
      });
    }

    function submit(event) {
      if (!window.fetch) return;
      event.preventDefault();
      if (pending) return;
      ["name", "url", "email", "image", "description"].forEach(function (name) {
        form.elements.namedItem(name).value = form.elements.namedItem(name).value.trim();
      });
      if (!form.reportValidity()) return;

      var data = new FormData(form);
      if (required && !String(data.get("cf-turnstile-response") || "").trim()) {
        message("人机验证尚未完成，请稍候或按提示完成验证后重试。", "error");
        return;
      }
      pending = true;
      button.disabled = true;
      button.textContent = "提交中…";
      form.setAttribute("aria-busy", "true");
      message("正在提交申请…", "info");
      controller = new AbortController();

      fetch(form.action, {
        method: "POST",
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
        body: data,
        signal: controller.signal
      }).then(function (response) {
        return response.json().catch(function () { return null; }).then(function (result) {
          if (!response.ok || !result || result.success !== true) {
            var fallback = response.status === 429 ? "提交过于频繁，请稍后重试。"
              : "提交未完成，请检查填写内容，或刷新页面后重试。";
            throw new Error(result && typeof result.message === "string" ? result.message : fallback);
          }
          if (!mounted) return;
          form.reset();
          message(typeof result.message === "string" ? result.message : "提交成功，等待审核。", "success");
        });
      }).catch(function (error) {
        if (error.name !== "AbortError") {
          message(error instanceof TypeError ? "网络连接失败，请检查网络后重试。" : error.message || "提交失败，请稍后重试。", "error");
        }
      }).finally(function () {
        if (!mounted) return;
        pending = false;
        controller = null;
        button.disabled = false;
        button.textContent = label;
        form.removeAttribute("aria-busy");
        if (widgetId !== null && window.turnstile) {
          challenge.classList.remove("is-interactive");
          window.turnstile.reset(widgetId);
        }
      });
    }

    form.addEventListener("submit", submit);
    if (required) {
      // Reuse a plugin footer script on full loads; Swup visits load it on demand.
      if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initChallenge, { once: true });
      else initChallenge();
    }
    active = function () {
      mounted = false;
      form.removeEventListener("submit", submit);
      document.removeEventListener("DOMContentLoaded", initChallenge);
      if (challengeResizeObserver) challengeResizeObserver.disconnect();
      if (challengeThemeObserver) challengeThemeObserver.disconnect();
      window.removeEventListener("resize", renderChallenge);
      if (controller) controller.abort();
      if (widgetId !== null && window.turnstile) window.turnstile.remove(widgetId);
    };
  }

  window.NebulaLinks = { mount: mount, unmount: unmount };
})();
