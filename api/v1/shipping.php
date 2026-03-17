<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add shipping via GET
if ($method === 'GET' && isset($_GET['order_id']) && isset($_GET['tracking_number']) && isset($_GET['carrier']) && !isset($_GET['update'])) {
    $order_id = (int)$_GET['order_id'];
    $tracking_number = $conn->real_escape_string($_GET['tracking_number']);
    $carrier = $conn->real_escape_string($_GET['carrier']);
    $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'pending';
    $shipped_date = isset($_GET['shipped_date']) ? $conn->real_escape_string($_GET['shipped_date']) : date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO shipping (order_id, tracking_number, carrier, status, shipped_date) VALUES ($order_id, '$tracking_number', '$carrier', '$status', '$shipped_date')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id, 'tracking_number' => $tracking_number]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Update shipping via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['status'])) $fields[] = "status='" . $conn->real_escape_string($_GET['status']) . "'";
    if (isset($_GET['tracking_number'])) $fields[] = "tracking_number='" . $conn->real_escape_string($_GET['tracking_number']) . "'";
    if (isset($_GET['carrier'])) $fields[] = "carrier='" . $conn->real_escape_string($_GET['carrier']) . "'";
    if (isset($_GET['delivery_date'])) $fields[] = "delivery_date='" . $conn->real_escape_string($_GET['delivery_date']) . "'";
    
    if (count($fields) > 0) {
        $sql = "UPDATE shipping SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete shipping via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM shipping WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Get all shipping or specific order
switch ($method) {
    case 'GET':
        if (isset($_GET['order_id'])) {
            $order_id = (int)$_GET['order_id'];
            $result = $conn->query("SELECT * FROM shipping WHERE order_id = $order_id");
        } else {
            $result = $conn->query("SELECT * FROM shipping");
        }
        $shipments = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $shipments[] = $row;
            }
        }
        echo json_encode($shipments);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = (int)$data['order_id'];
        $tracking_number = $conn->real_escape_string($data['tracking_number']);
        $carrier = $conn->real_escape_string($data['carrier']);
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'pending';
        $shipped_date = isset($data['shipped_date']) ? $conn->real_escape_string($data['shipped_date']) : date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO shipping (order_id, tracking_number, carrier, status, shipped_date) VALUES ($order_id, '$tracking_number', '$carrier', '$status', '$shipped_date')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $fields = [];
        if (isset($data['status'])) $fields[] = "status='" . $conn->real_escape_string($data['status']) . "'";
        if (isset($data['tracking_number'])) $fields[] = "tracking_number='" . $conn->real_escape_string($data['tracking_number']) . "'";
        if (isset($data['carrier'])) $fields[] = "carrier='" . $conn->real_escape_string($data['carrier']) . "'";
        if (isset($data['delivery_date'])) $fields[] = "delivery_date='" . $conn->real_escape_string($data['delivery_date']) . "'";
        
        if (count($fields) > 0) {
            $sql = "UPDATE shipping SET " . implode(", ", $fields) . " WHERE id=$id";
            if ($conn->query($sql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM shipping WHERE id=$id";
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
