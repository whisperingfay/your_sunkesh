<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add customer via GET
if ($method === 'GET' && isset($_GET['name']) && isset($_GET['email']) && !isset($_GET['update'])) {
    $name = $conn->real_escape_string($_GET['name']);
    $email = $conn->real_escape_string($_GET['email']);
    $sql = "INSERT INTO customers (name, email) VALUES ('$name', '$email')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}


// Partial update customer via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['name'])) $fields[] = "name='" . $conn->real_escape_string($_GET['name']) . "'";
    if (isset($_GET['email'])) $fields[] = "email='" . $conn->real_escape_string($_GET['email']) . "'";
    if (count($fields) > 0) {
        $sql = "UPDATE customers SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete customer via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM customers WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM customers');
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        echo json_encode($customers);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);
        $sql = "INSERT INTO customers (name, email) VALUES ('$name', '$email')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);
        $sql = "UPDATE customers SET name='$name', email='$email' WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM customers WHERE id=$id";
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