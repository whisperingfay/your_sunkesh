<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Add review via GET
if ($method === 'GET' && isset($_GET['product_id']) && isset($_GET['customer_id']) && isset($_GET['rating']) && isset($_GET['comment']) && !isset($_GET['update'])) {
    $product_id = (int)$_GET['product_id'];
    $customer_id = (int)$_GET['customer_id'];
    $rating = (int)$_GET['rating'];
    $comment = $conn->real_escape_string($_GET['comment']);
    $sql = "INSERT INTO reviews (product_id, customer_id, rating, comment) VALUES ($product_id, $customer_id, $rating, '$comment')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Partial update review via GET
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['product_id'])) $fields[] = "product_id=" . (int)$_GET['product_id'];
    if (isset($_GET['customer_id'])) $fields[] = "customer_id=" . (int)$_GET['customer_id'];
    if (isset($_GET['rating'])) $fields[] = "rating=" . (int)$_GET['rating'];
    if (isset($_GET['comment'])) $fields[] = "comment='" . $conn->real_escape_string($_GET['comment']) . "'";
    if (count($fields) > 0) {
        $sql = "UPDATE reviews SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete review via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM reviews WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM reviews');
        if (!$result) {
            echo json_encode(['error' => $conn->error]);
            exit;
        }
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        echo json_encode($reviews);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = (int)$data['product_id'];
        $customer_id = (int)$data['customer_id'];
        $rating = (int)$data['rating'];
        $comment = $conn->real_escape_string($data['comment']);
        $sql = "INSERT INTO reviews (product_id, customer_id, rating, comment) VALUES ($product_id, $customer_id, $rating, '$comment')";
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
        $customer_id = (int)$data['customer_id'];
        $rating = (int)$data['rating'];
        $comment = $conn->real_escape_string($data['comment']);
        $sql = "UPDATE reviews SET product_id=$product_id, customer_id=$customer_id, rating=$rating, comment='$comment' WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        $sql = "DELETE FROM reviews WHERE id=$id";
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
