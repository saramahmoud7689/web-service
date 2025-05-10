<?php
header("Content-Type: application/json");

$allowed_methods = ['GET', 'POST', 'PUT', 'DELETE'];
$method = $_SERVER['REQUEST_METHOD'];

if (!in_array($method, $allowed_methods)) {
    http_response_code(405);
    echo json_encode(["error" => "method not allowed!"]);
    exit;
}

require_once("Resources/config.php");
require_once("Resources/Models/MySQLHandler.php");

try {
    $db = new MySQLHandler("items");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "internal server error!"]);
    exit;
}

$urlParts = explode("/", $_SERVER['PATH_INFO']);
var_dump($urlParts);

$resource = $urlParts[1] ?? null;  
$resourceID = $urlParts[2] ?? null; 

echo "Resource: $resource, ResourceID: $resourceID";


if ($resource !== "items") {
    http_response_code(404);
    echo json_encode(["error" => "Resource doesn't exist"]);
    exit;
}

if ($method === 'GET') {
    if ($resourceID) {
        $result = $db->get_record_by_id($resourceID);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Resource doesn't exist"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Bad request"]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id']) || !isset($data['product_name']) || count($data) != 2) {
        http_response_code(400);
        echo json_encode(["error" => "Bad request"]);
        exit;
    }

    if ($db->save($data)) {
        http_response_code(201);
        echo json_encode(["status" => "Resource was added successfully!"]);
    }
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$db->get_record_by_id($resourceID)) {
        http_response_code(404);
        echo json_encode(["error" => "Resource not found!"]);
        exit;
    }

    if (!isset($data['product_name']) || count($data) != 1) {
        http_response_code(400);
        echo json_encode(["error" => "Bad request"]);
        exit;
    }

    $update = $db->update($resourceID, $data);
    if ($update) {
        echo json_encode($db->get_record_by_id($resourceID));
    }
}

if ($method === 'DELETE') {
    if ($db->get_record_by_id($resourceID)) {
        $db->delete($resourceID);
        echo json_encode(["status" => "Resource was deleted successfully!"]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Resource not found!"]);
    }
}
?>