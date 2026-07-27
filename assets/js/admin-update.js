(function () {
  "use strict";

  var panel = document.querySelector(".nebula-update-panel");
  if (!panel || panel.dataset.ready === "true") return;
  panel.dataset.ready = "true";

  var checkButton = panel.querySelector("[data-check-update]");
  var installButton = panel.querySelector("[data-install-update]");
  var status = panel.querySelector("[data-update-status]");
  var currentVersion = panel.querySelector("[data-current-version]");
  var endpoint = panel.getAttribute("data-endpoint");

  function setStatus(message, state) {
    status.textContent = message;
    status.className = "nebula-update-status" + (state ? " is-" + state : "");
  }

  function setBusy(busy) {
    checkButton.disabled = busy;
    installButton.disabled = busy;
  }

  function request(action) {
    var body = new URLSearchParams();
    body.set("action", action);

    return fetch(endpoint, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: body.toString()
    }).then(function (response) {
      return response.text().then(function (body) {
        var result;

        try {
          result = JSON.parse(body.replace(/^\uFEFF/, ""));
        } catch (error) {
          if (response.redirected && /\/admin\/login\.php(?:[?#]|$)/.test(response.url)) {
            throw new Error("登录状态已失效，请重新登录后再检查更新。");
          }
          throw new Error("服务器没有返回有效的更新结果（HTTP " + response.status + "）。");
        }

        if (!response.ok || !result.success) {
          throw new Error(result.message || "更新请求失败。");
        }
        return result;
      });
    });
  }

  checkButton.addEventListener("click", function () {
    setBusy(true);
    installButton.hidden = true;
    setStatus("正在连接 GitHub 检查版本...", "");

    request("check").then(function (result) {
      if (result.update_available) {
        installButton.hidden = false;
        setStatus("发现新版本 " + result.latest_version + "，可以立即升级。", "update");
      } else {
        setStatus(result.message || "当前已经是最新版本。", "success");
      }
    }).catch(function (error) {
      setStatus(error.message, "error");
    }).finally(function () {
      setBusy(false);
    });
  });

  installButton.addEventListener("click", function () {
    if (!window.confirm("升级将覆盖主题目录中的同名文件，是否继续？")) return;

    setBusy(true);
    setStatus("正在下载、校验并覆盖主题文件，请勿关闭页面...", "");

    request("update").then(function (result) {
      currentVersion.textContent = result.version;
      installButton.hidden = true;
      setStatus("已升级到 " + result.version + "，共更新 " + result.files + " 个文件。", "success");
    }).catch(function (error) {
      setStatus(error.message, "error");
    }).finally(function () {
      setBusy(false);
    });
  });
})();
