<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'unauthorized']);
  exit;
}

$action = $_GET['action'] ?? '';

function json_input() {
  $raw = file_get_contents('php://input');
  return $raw ? json_decode($raw, true) : [];
}

try {
  switch ($action) {
    case 'list_tables':
      $area = $_GET['area'] ?? null;
      if ($area) {
        $stmt = $pdo->prepare('SELECT * FROM tables WHERE area=? ORDER BY number');
        $stmt->execute([$area]);
        echo json_encode($stmt->fetchAll());
      } else {
        echo json_encode($pdo->query('SELECT * FROM tables ORDER BY number')->fetchAll());
      }
      break;

    case 'add_table':
      $d = json_input();
      $stmt = $pdo->prepare('INSERT INTO tables(number,seats,shape,width,height,x,y,area) VALUES (?,?,?,?,?,?,?,?)');
      $w = 42; $h = ($d['shape'] ?? 'circle') === 'rect' ? 42 : 42;
      $stmt->execute([ (int)$d['number'], (int)$d['seats'], $d['shape'] ?? 'circle', $w, $h, 60, 60, $d['area'] ?? 'hall' ]);
      echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
      break;

    case 'update_table_pos':
      $d = json_input();
      $stmt = $pdo->prepare('UPDATE tables SET x=?, y=? WHERE id=?');
      $stmt->execute([(int)$d['x'], (int)$d['y'], (int)$d['id']]);
      echo json_encode(['ok'=>true]);
      break;

    case 'delete_table':
      $id = (int)($_GET['id'] ?? 0);
      $pdo->prepare('DELETE FROM tables WHERE id=?')->execute([$id]);
      echo json_encode(['ok'=>true]);
      break;

    case 'list_reservations':
      $date = $_GET['date'] ?? date('Y-m-d');
      $stmt = $pdo->prepare('SELECT r.*, t.number AS table_number FROM reservations r JOIN tables t ON t.id=r.table_id WHERE r.res_date=? AND r.deleted_at IS NULL ORDER BY r.res_time');
      $stmt->execute([$date]);
      echo json_encode($stmt->fetchAll());
      break;

    case 'list_reservations_history':
      $date = $_GET['date'] ?? date('Y-m-d');
      $stmt = $pdo->prepare('SELECT r.*, t.number AS table_number FROM reservations r JOIN tables t ON t.id=r.table_id WHERE r.res_date=? AND r.deleted_at IS NOT NULL ORDER BY r.res_time');
      $stmt->execute([$date]);
      echo json_encode($stmt->fetchAll());
      break;

    case 'add_reservation':
      $d = json_input();
      $stmt = $pdo->prepare('INSERT INTO reservations(table_id,res_date,res_time,guest_lastname,party_size,notes) VALUES (?,?,?,?,?,?)');
      $stmt->execute([ (int)$d['table_id'], $d['res_date'], $d['res_time'], trim($d['guest_lastname']), (int)$d['party_size'], trim($d['notes'] ?? '') ]);
      echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
      break;

    case 'delete_reservation':
      $id = (int)($_GET['id'] ?? 0);
      $pdo->prepare('UPDATE reservations SET deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$id]);
      echo json_encode(['ok'=>true]);
      break;

    case 'table_status':
      $date = $_GET['date'] ?? date('Y-m-d');
      $stmt = $pdo->prepare('SELECT DISTINCT table_id FROM reservations WHERE res_date=? AND deleted_at IS NULL');
      $stmt->execute([$date]);
      $busy = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'table_id'));
      echo json_encode($busy);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error'=>'unknown action']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
