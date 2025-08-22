<?php
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: /login.php');
  exit;
}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <title>Резервації столів</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">Резервації</div>
    <div class="spacer"></div>
    <div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></div>
    <a class="logout" href="/logout.php">Вийти</a>
  </header>
  <main class="layout">
    <aside class="sidebar">
      <h3>Дата</h3>
      <input type="date" id="res-date">
      <h3>Столи <span id="table-count" class="badge">0</span></h3>
      <div id="table-list" class="list"></div>
      <h3>Додати стіл</h3>
      <form id="add-table-form">
        <label>Номер <input type="number" name="number" min="1" required></label>
        <label>Місць <input type="number" name="seats" min="1" value="2"></label>
        <label>Форма 
          <select name="shape">
            <option value="circle">Круглий</option>
            <option value="rect">Прямокутний</option>
          </select>
        </label>
        <button type="submit">Додати</button>
      </form>
    </aside>
    <section class="canvas-wrap">
      <div class="canvas-toolbar">
        <span>Флор-план (drag & drop)</span>
        <button id="save-layout">Зберегти розміщення</button>
      </div>
      <svg id="floor-canvas" width="900" height="520"></svg>
    </section>
  </main>
  <section class="reservations">
    <h2>Резервації на <span id="res-date-label"></span></h2>
    <form id="add-res-form" class="res-form">
      <label>Час <input type="time" name="res_time" required></label>
      <label>Прізвище <input type="text" name="guest_lastname" required></label>
      <label>К-сть осіб <input type="number" name="party_size" min="1" value="2"></label>
      <label>Стіл
        <select name="table_id" id="res-table-select"></select>
      </label>
      <label>Нотатки <input type="text" name="notes"></label>
      <button type="submit">Додати резервацію</button>
    </form>
    <table class="res-table">
      <thead>
        <tr>
          <th>Година</th>
          <th>Прізвище</th>
          <th>К-сть осіб</th>
          <th>Стіл</th>
          <th>Нотатки</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="res-tbody"></tbody>
    </table>
  </section>

  <script>window.CSRF='<?= session_id() ?>';</script>
  <script src="/assets/app.js"></script>
</body>
</html>
