<?php
header("Content-Type: application/json; charset=UTF-8");
$conn = new mysqli("localhost", "root", "", "data_api");

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['action'])) {
    $name = $data['name'];
    $username = $data['username'] ?? 'user';
    $email = $data['email'];
    $street = $data['street'];
    $suite = $data['suite'];
    $city = $data['city'];
    $zipcode = $data['zipcode'];

    if ($data['action'] == 'add') {
        // Tambah Data Baru
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, street, suite, city, zipcode) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $username, $email, $street, $suite, $city, $zipcode);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Berhasil menambah data"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menambah data"]);
        }
    } elseif ($data['action'] == 'edit') {
        // Edit Data
        $id = $data['id'];
        $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, street=?, suite=?, city=?, zipcode=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $username, $email, $street, $suite, $city, $zipcode, $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Berhasil memperbarui data"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal memperbarui data"]);
        }
    }
}
$conn->close();
?>