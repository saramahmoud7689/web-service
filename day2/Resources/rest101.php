<?php

require 'vendor/autoload.php';

/*
 * 1- VERB
 * 2- Resource //Products
 * 3- Resource ID
 * 4- Extra Params
 */

$db = new MySQLHandler('products');

$method = $_SERVER['REQUEST_METHOD'];

$urlParts = explode('/', $_SERVER['REQUEST_URI']);

$resource = $urlParts[2] ?? null;

$resourceID = $urlParts[3] ?? null;

if ($resource !== 'products') {
    http_response_code(404);
    echo "Resource $resource doesn't exist";
    exit;
}

header("Content-Type: application/json");

switch ($method) {
    case 'GET':
        if ($resourceID) {
            $data = $db->get_record_by_id($resourceID);
            if ($data && !empty($data)) {
                echo json_encode($data[0]);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Product not found']);
            }
            break;
        }
        $data = $db->get_data();
        echo json_encode($data);
        break;

    case 'POST':
        //validate required product data
        $success = $db->save($_POST);
        if ($success) {
            http_response_code(201);
        }

        break;

    case 'PUT':
        if ($resourceID) {
            $_PUT = json_decode(file_get_contents("php://input"), true);
            $db->update($_PUT, $resourceID);
            break;
        }
        http_response_code(400);
        echo "product id is required";

        break;

    case 'DELETE':
        if ($resourceID) {
            $db->delete($resourceID);
            break;
        }
        http_response_code(400);
        echo "product id is required";

        break;

    default:
        http_response_code(405);
        echo "Method Not Allowed";
        break;
}


