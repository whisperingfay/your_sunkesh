<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add payment via GET
if ($method === 'GET' && isset($_GET['order_id']) && isset($_GET['amount']) && isset($_GET['method']) && !isset($_GET['update'])) {
    $order_id = (int)$_GET['order_id'];
    $amount = (float)$_GET['amount'];
    $method_payment = $conn->real_escape_string($_GET['method']);
    $sql = "INSERT INTO payments (order_id, amount, method) VALUES ($order_id, $amount, '$method_payment')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Partial update payment via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['order_id'])) $fields[] = "order_id=" . (int)$_GET['order_id'];
    if (isset($_GET['amount'])) $fields[] = "amount=" . (float)$_GET['amount'];
    if (isset($_GET['method'])) $fields[] = "method='" . $conn->real_escape_string($_GET['method']) . "'";
    if (count($fields) > 0) {
        $sql = "UPDATE payments SET " . implode(", ", $fields) . " WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'updated_id' => $id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
    }
    exit;
}

// Delete payment via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM payments WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM payments');
        if (!$result) {
            echo json_encode(['error' => $conn->error]);
            exit;
        }
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        echo json_encode($payments);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = (int)$data['order_id'];
        $amount = (float)$data['amount'];
        $method_payment = $conn->real_escape_string($data['method']);
        $sql = "INSERT INTO payments (order_id, amount, method) VALUES ($order_id, $amount, '$method_payment')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $order_id = (int)$data['order_id'];
        $amount = (float)$data['amount'];
        $method_payment = $conn->real_escape_string($data['method']);
        $sql = "UPDATE payments SET order_id=$order_id, amount=$amount, method='$method_payment' WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM payments WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    default:
        echo json_encode(['error' => 'Invalid request']);
        break;
}
?>
