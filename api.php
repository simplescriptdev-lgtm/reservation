<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'unauthorized']);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function json_input() {
  $raw = file_get_contents('php://input');
  return $raw ? json_decode($raw, true) : [];
}

try {
  switch ($action) {
    case 'list_tables':
      $rows = $pdo->query('SELECT * FROM tables ORDER BY number')->fetchAll();
      echo json_encode($rows);
      break;

    case 'add_table':
      $data = json_input();
      $stmt = $pdo->prepare('INSERT INTO tables(number,seats,shape,width,height,x,y) VALUES (?,?,?,?,?,?,?)');
      $stmt->execute([ (int)$data['number'], (int)$data['seats'], $data['shape'] ?? 'circle', 60, 60, 60, 60 ]);
      echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
      break;

    case 'update_table_pos':
      $data = json_input();
      $stmt = $pdo->prepare('UPDATE tables SET x=?, y=? WHERE id=?');
      $stmt->execute([(int)$data['x'], (int)$data['y'], (int)$data['id']]);
      echo json_encode(['ok'=>true]);
      break;

    case 'delete_table':
      $id = (int)($_GET['id'] ?? 0);
      $pdo->prepare('DELETE FROM tables WHERE id=?')->execute([$id]);
      echo json_encode(['ok'=>true]);
      break;

    case 'list_reservations':
      $date = $_GET['date'] ?? date('Y-m-d');
      $stmt = $pdo->prepare('SELECT r.*, t.number AS table_number FROM reservations r JOIN tables t ON t.id=r.table_id WHERE res_date=? ORDER BY res_time');
      $stmt->execute([$date]);
      echo json_encode($stmt->fetchAll());
      break;

    case 'add_reservation':
      $data = json_input();
      $stmt = $pdo->prepare('INSERT INTO reservations(table_id,res_date,res_time,guest_lastname,party_size,notes) VALUES (?,?,?,?,?,?)');
      $stmt->execute([ (int)$data['table_id'], $data['res_date'], $data['res_time'], trim($data['guest_lastname']), (int)$data['party_size'], trim($data['notes'] ?? '') ]);
      echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
      break;

    case 'delete_reservation':
      $id = (int)($_GET['id'] ?? 0);
      $pdo->prepare('DELETE FROM reservations WHERE id=?')->execute([$id]);
      echo json_encode(['ok'=>true]);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error'=>'unknown action']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
