<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add return via GET
if ($method === 'GET' && isset($_GET['order_id']) && isset($_GET['reason']) && !isset($_GET['update'])) {
    $order_id = (int)$_GET['order_id'];
    $reason = $conn->real_escape_string($_GET['reason']);
    $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'pending';
    $return_date = isset($_GET['return_date']) ? $conn->real_escape_string($_GET['return_date']) : date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO returns (order_id, reason, status, return_date) VALUES ($order_id, '$reason', '$status', '$return_date')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Update return via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['status'])) $fields[] = "status='" . $conn->real_escape_string($_GET['status']) . "'";
    if (isset($_GET['reason'])) $fields[] = "reason='" . $conn->real_escape_string($_GET['reason']) . "'";
    if (isset($_GET['refund_date'])) $fields[] = "refund_date='" . $conn->real_escape_string($_GET['refund_date']) . "'";
    
    if (count($fields) > 0) {
        $sql = "UPDATE returns SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete return via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM returns WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Get all returns or specific order
switch ($method) {
    case 'GET':
        if (isset($_GET['order_id'])) {
            $order_id = (int)$_GET['order_id'];
            $result = $conn->query("SELECT * FROM returns WHERE order_id = $order_id");
        } else {
            $result = $conn->query("SELECT * FROM returns");
        }
        $returns = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $returns[] = $row;
            }
        }
        echo json_encode($returns);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = (int)$data['order_id'];
        $reason = $conn->real_escape_string($data['reason']);
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'pending';
        $return_date = isset($data['return_date']) ? $conn->real_escape_string($data['return_date']) : date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO returns (order_id, reason, status, return_date) VALUES ($order_id, '$reason', '$status', '$return_date')";
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
        if (isset($data['reason'])) $fields[] = "reason='" . $conn->real_escape_string($data['reason']) . "'";
        if (isset($data['refund_date'])) $fields[] = "refund_date='" . $conn->real_escape_string($data['refund_date']) . "'";
        
        if (count($fields) > 0) {
            $sql = "UPDATE returns SET " . implode(", ", $fields) . " WHERE id=$id";
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
        $sql = "DELETE FROM returns WHERE id=$id";
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
