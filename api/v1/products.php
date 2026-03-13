<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add product via GET
if ($method === 'GET' && isset($_GET['name']) && isset($_GET['price']) && !isset($_GET['update'])) {
    $name = $conn->real_escape_string($_GET['name']);
    $price = (float)$_GET['price'];
    $sql = "INSERT INTO products (name, price) VALUES ('$name', $price)";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}


// Partial update product via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['name'])) $fields[] = "name='" . $conn->real_escape_string($_GET['name']) . "'";
    if (isset($_GET['price'])) $fields[] = "price=" . (float)$_GET['price'];
    if (count($fields) > 0) {
        $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete product via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM products WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM products');
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string($data['name']);
        $price = (float)$data['price'];
        $sql = "INSERT INTO products (name, price) VALUES ('$name', $price)";
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
        $price = (float)$data['price'];
        $sql = "UPDATE products SET name='$name', price=$price WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM products WHERE id=$id";
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