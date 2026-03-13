<?php
require '../db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];


// Add order via GET (with customer_name and product_name)
if ($method === 'GET' && isset($_GET['customer_id']) && isset($_GET['product_id']) && isset($_GET['quantity']) && !isset($_GET['update'])) {
    $customer_id = (int)$_GET['customer_id'];
    $product_id = (int)$_GET['product_id'];
    $quantity = (int)$_GET['quantity'];
    $order_date = isset($_GET['order_date']) ? $conn->real_escape_string($_GET['order_date']) : date('Y-m-d H:i:s');
    $customer_name = isset($_GET['customer_name']) ? $conn->real_escape_string($_GET['customer_name']) : '';
    $product_name = isset($_GET['product_name']) ? $conn->real_escape_string($_GET['product_name']) : '';
    // If customer_name or product_name is empty, fetch from DB
    if ($customer_name === '') {
        $res = $conn->query("SELECT name FROM customers WHERE id=$customer_id LIMIT 1");
        if ($row = $res->fetch_assoc()) $customer_name = $row['name'];
    }
    if ($product_name === '') {
        $res = $conn->query("SELECT name FROM products WHERE id=$product_id LIMIT 1");
        if ($row = $res->fetch_assoc()) $product_name = $row['name'];
    }
    $sql = "INSERT INTO orders (customer_id, customer_name, product_id, product_name, quantity, order_date) VALUES ($customer_id, '$customer_name', $product_id, '$product_name', $quantity, '$order_date')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}



// Partial update order via GET (with customer_name and product_name)
if ($method === 'GET' && isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $fields = [];
    if (isset($_GET['customer_id'])) {
        $fields[] = "customer_id=" . (int)$_GET['customer_id'];
        // auto-update customer_name if not set
        if (!isset($_GET['customer_name'])) {
            $res = $conn->query("SELECT name FROM customers WHERE id=" . (int)$_GET['customer_id'] . " LIMIT 1");
            if ($row = $res->fetch_assoc()) $fields[] = "customer_name='" . $conn->real_escape_string($row['name']) . "'";
        }
    }
    if (isset($_GET['customer_name'])) $fields[] = "customer_name='" . $conn->real_escape_string($_GET['customer_name']) . "'";
    if (isset($_GET['product_id'])) {
        $fields[] = "product_id=" . (int)$_GET['product_id'];
        // auto-update product_name if not set
        if (!isset($_GET['product_name'])) {
            $res = $conn->query("SELECT name FROM products WHERE id=" . (int)$_GET['product_id'] . " LIMIT 1");
            if ($row = $res->fetch_assoc()) $fields[] = "product_name='" . $conn->real_escape_string($row['name']) . "'";
        }
    }
    if (isset($_GET['product_name'])) $fields[] = "product_name='" . $conn->real_escape_string($_GET['product_name']) . "'";
    if (isset($_GET['quantity'])) $fields[] = "quantity=" . (int)$_GET['quantity'];
    if (isset($_GET['order_date'])) $fields[] = "order_date='" . $conn->real_escape_string($_GET['order_date']) . "'";
    if (count($fields) > 0) {
        $sql = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id=$id";
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

// Delete order via GET
if ($method === 'GET' && isset($_GET['delete']) && $_GET['delete'] == 1 && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM orders WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT o.id, o.customer_id, c.name AS customer_name, o.product_id, p.name AS product_name, o.quantity, o.order_date FROM orders o JOIN customers c ON o.customer_id = c.id JOIN products p ON o.product_id = p.id');
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode($orders);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = (int)$data['customer_id'];
        $product_id = (int)$data['product_id'];
        $quantity = (int)$data['quantity'];
        $order_date = isset($data['order_date']) ? $conn->real_escape_string($data['order_date']) : date('Y-m-d H:i:s');
        $customer_name = isset($data['customer_name']) ? $conn->real_escape_string($data['customer_name']) : '';
        $product_name = isset($data['product_name']) ? $conn->real_escape_string($data['product_name']) : '';
        // If customer_name or product_name is empty, fetch from DB
        if ($customer_name === '') {
            $res = $conn->query("SELECT name FROM customers WHERE id=$customer_id LIMIT 1");
            if ($row = $res->fetch_assoc()) $customer_name = $row['name'];
        }
        if ($product_name === '') {
            $res = $conn->query("SELECT name FROM products WHERE id=$product_id LIMIT 1");
            if ($row = $res->fetch_assoc()) $product_name = $row['name'];
        }
        $sql = "INSERT INTO orders (customer_id, customer_name, product_id, product_name, quantity, order_date) VALUES ($customer_id, '$customer_name', $product_id, '$product_name', $quantity, '$order_date')";
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
        if (isset($data['customer_id'])) {
            $fields[] = "customer_id=" . (int)$data['customer_id'];
            if (!isset($data['customer_name'])) {
                $res = $conn->query("SELECT name FROM customers WHERE id=" . (int)$data['customer_id'] . " LIMIT 1");
                if ($row = $res->fetch_assoc()) $fields[] = "customer_name='" . $conn->real_escape_string($row['name']) . "'";
            }
        }
        if (isset($data['customer_name'])) $fields[] = "customer_name='" . $conn->real_escape_string($data['customer_name']) . "'";
        if (isset($data['product_id'])) {
            $fields[] = "product_id=" . (int)$data['product_id'];
            if (!isset($data['product_name'])) {
                $res = $conn->query("SELECT name FROM products WHERE id=" . (int)$data['product_id'] . " LIMIT 1");
                if ($row = $res->fetch_assoc()) $fields[] = "product_name='" . $conn->real_escape_string($row['name']) . "'";
            }
        }
        if (isset($data['product_name'])) $fields[] = "product_name='" . $conn->real_escape_string($data['product_name']) . "'";
        if (isset($data['quantity'])) $fields[] = "quantity=" . (int)$data['quantity'];
        if (isset($data['order_date'])) $fields[] = "order_date='" . $conn->real_escape_string($data['order_date']) . "'";
        if (count($fields) > 0) {
            $sql = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id=$id";
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
        $sql = "DELETE FROM orders WHERE id=$id";
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