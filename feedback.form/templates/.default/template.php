<div id="feedback-form">
    <link rel="stylesheet" href="<?= $templateFolder ?>/style.css" type="text/css" />
  <form id="ajax-feedback">
    <div class="form-group">
      <label for="name">Ваше имя</label>
      <input id="name" type="text" name="name" placeholder="Ваше имя" required>
    </div>

    <div class="form-group">
      <label for="email">Ваш email</label>
      <input id="email" type="email" name="email" placeholder="Ваш email" required>
    </div>

    <div class="form-group">
      <label for="message">Ваш отзыв</label>
      <textarea id="message" name="message" placeholder="Ваш отзыв" required></textarea>
    </div>

    <input type="hidden" name="submit" value="Y">
    <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">

    <button type="submit">Отправить</button>
  </form>

  <div id="feedback-response"></div>

  <h3>Последние отзывы:</h3>
<ul id="feedback-list">
  <?php foreach ($arResult["ITEMS"] as $item): ?>
    <li>
      <b><?= htmlspecialcharsbx($item["NAME"]) ?></b>
      <span class="review-label">Отзыв:</span>
      <span class="review-text"><?= nl2br(htmlspecialcharsbx($item["PREVIEW_TEXT"])) ?></span>
    </li>
  <?php endforeach; ?>
</ul>



<script>
document.getElementById("ajax-feedback").addEventListener("submit", function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch(window.location.href, {
    method: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    const out = document.getElementById("feedback-response");
    const ul = document.getElementById("feedback-list");

    if (data.status === "success") {
      out.innerHTML = "<span style='color: green'>" + data.message + "</span>";

      ul.innerHTML = "";

      data.items.forEach(item => {
        const li = document.createElement("li");
        li.innerHTML = "<b>" + escapeHtml(item.NAME) + "</b>: " + escapeHtml(item.PREVIEW_TEXT);
        ul.appendChild(li);
      });

      form.reset();
    } else {
      out.innerHTML = "<span style='color: red'>" + (data.errors ? data.errors.join('<br>') : data.message) + "</span>";
    }
  })
  .catch(() => {
    document.getElementById("feedback-response").innerHTML = "Ошибка сети";
  });

  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[m];
    });
  }
});
</script>
