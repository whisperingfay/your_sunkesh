<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add discount via GET
if ($method === 'GET' && isset($_GET['code']) && isset($_GET['percentage']) && !isset($_GET['update'])) {
    $code = strtoupper($conn->real_escape_string($_GET['code']));
    $percentage = (int)$_GET['percentage'];
    $expiry = isset($_GET['expiry']) ? $conn->real_escape_string($_GET['expiry']) : date('Y-m-d', strtotime('+30 days'));
    $description = isset($_GET['description']) ? $conn->real_escape_string($_GET['description']) : '';
    
    $sql = "INSERT INTO discounts (code, percentage, expiry, description) VALUES ('$code', $percentage, '$expiry', '$description')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id, 'code' => $code]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Update discount via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['percentage'])) $fields[] = "percentage=" . (int)$_GET['percentage'];
    if (isset($_GET['expiry'])) $fields[] = "expiry='" . $conn->real_escape_string($_GET['expiry']) . "'";
    if (isset($_GET['description'])) $fields[] = "description='" . $conn->real_escape_string($_GET['description']) . "'";
    
    if (count($fields) > 0) {
        $sql = "UPDATE discounts SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete discount via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM discounts WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Get all discounts or specific code
switch ($method) {
    case 'GET':
        if (isset($_GET['code'])) {
            $code = strtoupper($conn->real_escape_string($_GET['code']));
            $result = $conn->query("SELECT * FROM discounts WHERE code = '$code'");
            $discount = $result ? $result->fetch_assoc() : null;
            echo json_encode($discount ? $discount : ['error' => 'Discount not found']);
        } else {
            $result = $conn->query("SELECT * FROM discounts");
            $discounts = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $discounts[] = $row;
                }
            }
            echo json_encode($discounts);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $code = strtoupper($conn->real_escape_string($data['code']));
        $percentage = (int)$data['percentage'];
        $expiry = isset($data['expiry']) ? $conn->real_escape_string($data['expiry']) : date('Y-m-d', strtotime('+30 days'));
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
        
        $sql = "INSERT INTO discounts (code, percentage, expiry, description) VALUES ('$code', $percentage, '$expiry', '$description')";
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
        if (isset($data['percentage'])) $fields[] = "percentage=" . (int)$data['percentage'];
        if (isset($data['expiry'])) $fields[] = "expiry='" . $conn->real_escape_string($data['expiry']) . "'";
        if (isset($data['description'])) $fields[] = "description='" . $conn->real_escape_string($data['description']) . "'";
        
        if (count($fields) > 0) {
            $sql = "UPDATE discounts SET " . implode(", ", $fields) . " WHERE id=$id";
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
        $sql = "DELETE FROM discounts WHERE id=$id";
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
