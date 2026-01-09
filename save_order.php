<?php
header("Content-Type: application/json");

// DB connection
include("./user/db.php");

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Safety check
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// Prepare SQL
$stmt = $conn->prepare(
  "INSERT INTO orders 
  (payment_id, name, mobile, email, `address`, product, size, quantity, total)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => $conn->error]);
    exit;
}

// Bind parameters
$stmt->bind_param(
  "sssssssid",
  $data['payment_id'],
  $data['name'],
  $data['mobile'],
  $data['email'],
  $data['address'],
  $data['product'],
  $data['size'],
  $data['qty'],
  $data['total']
);

// Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

// Close
$stmt->close();
$conn->close();
