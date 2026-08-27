<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$json_file = 'local_users.json';

// Jika file json lokal belum ada, ambil otomatis dari link jsonplaceholder
if (!file_exists($json_file)) {
    $api_url = "https://jsonplaceholder.typicode.com/users";
    $api_data = file_get_contents($api_url);
    if ($api_data) {
        file_put_contents($json_file, $api_data);
    }
}

// Fungsi helper baca data
function getLocalData() {
    global $json_file;
    if (!file_exists($json_file)) return [];
    return json_decode(file_get_contents($json_file), true) ?: [];
}

// Fungsi helper simpan data
function saveLocalData($data) {
    global $json_file;
    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 1. READ: Ambil data, urutkan berdasarkan ID dari yang terkecil ke terbesar
    $data = getLocalData();
    
    usort($data, function($a, $b) {
        return $a['id'] - $b['id'];
    });

    // Tampilkan dengan format JSON yang rapi ke bawah (Pretty Print)
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} elseif ($method === 'POST') {
    // 2. CREATE (Add) atau UPDATE (Edit)
    $input = json_decode(file_get_contents("php://input"), true);
    $data = getLocalData();
    $action = $input['action'] ?? '';

    if ($action === 'add') {
        // Buat ID baru otomatis (ID terbesar + 1)
        $new_id = count($data) > 0 ? max(array_column($data, 'id')) + 1 : 1;

        $new_user = [
            "id" => $new_id,
            "name" => $input['name'],
            "username" => $input['username'] ?? "user_" . $new_id,
            "email" => $input['email'],
            "phone" => $input['phone'] ?? "",
            "address" => [
                "street" => $input['street'],
                "suite" => $input['suite'],
                "city" => $input['city'],
                "zipcode" => $input['zipcode']
            ]
        ];

        $data[] = $new_user;
        saveLocalData($data);
        echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan!"]);

    } elseif ($action === 'edit') {
        // Edit data berdasarkan ID
        $id = $input['id'];
        $updated = false;

        foreach ($data as &$user) {
            if ($user['id'] == $id) {
                $user['name'] = $input['name'];
                $user['email'] = $input['email'];
                $user['phone'] = $input['phone'] ?? "";
                $user['address']['street'] = $input['street'];
                $user['address']['suite'] = $input['suite'];
                $user['address']['city'] = $input['city'];
                $user['address']['zipcode'] = $input['zipcode'];
                $updated = true;
                break;
            }
        }

        if ($updated) {
            saveLocalData($data);
            echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Data tidak ditemukan!"]);
        }
    }

} elseif ($method === 'DELETE') {
    // 3. DELETE: Menghapus data berdasarkan ID dari parameter URL (?id=X)
    $id = $_GET['id'] ?? null;
    if ($id) {
        $data = getLocalData();
        $filtered = array_filter($data, function($user) use ($id) {
            return $user['id'] != $id;
        });

        saveLocalData(array_values($filtered));
        echo json_encode(["status" => "success", "message" => "Data berhasil dihapus!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ID tidak valid!"]);
    }
}
?>