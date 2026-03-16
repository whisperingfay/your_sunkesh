<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add inventory via GET
if ($method === 'GET' && isset($_GET['product_id']) && isset($_GET['stock']) && !isset($_GET['update'])) {
    $product_id = (int)$_GET['product_id'];
    $stock = (int)$_GET['stock'];
    $sql = "INSERT INTO inventory (product_id, stock) VALUES ($product_id, $stock)";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Partial update inventory via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['product_id'])) $fields[] = "product_id=" . (int)$_GET['product_id'];
    if (isset($_GET['stock'])) $fields[] = "stock=" . (int)$_GET['stock'];
    if (count($fields) > 0) {
        $sql = "UPDATE inventory SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete inventory via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM inventory WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM inventory');
        if (!$result) {
            echo json_encode(['error' => $conn->error]);
            exit;
        }
        $inventory = [];
        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
        echo json_encode($inventory);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = (int)$data['product_id'];
        $stock = (int)$data['stock'];
        $sql = "INSERT INTO inventory (product_id, stock) VALUES ($product_id, $stock)";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $product_id = (int)$data['product_id'];
        $stock = (int)$data['stock'];
        $sql = "UPDATE inventory SET product_id=$product_id, stock=$stock WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM inventory WHERE id=$id";
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
