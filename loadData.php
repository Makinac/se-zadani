<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("db.php");

$pidFile = file_get_contents('https://data.pid.cz/pointsOfSale/json/pointsOfSale.json');

if ($pidFile === false) {
    die('Nepodařilo se načíst data.');
}

$jsonData = json_decode($pidFile, true);
if ($jsonData === null) {
    die('Chyba při zpracování JSON dat.');
}

foreach ($jsonData as $sp) {
    $id = $sp['id'];
    $sql = "SELECT id FROM pointsOfSale";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($existingIds as $existingId) {
        if (!in_array($existingId, array_column($jsonData, 'id'))) {
            $deleteSql = "DELETE FROM pointsOfSale WHERE id = :id";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindParam(':id', $existingId);
            $deleteStmt->execute();
        }
    }

    $type = $sp['type'];
    $name = $sp['name'];
    $address = $sp['address'];
    $openingHours = json_encode($sp['openingHours']);
    $lat = $sp['lat'];
    $lon = $sp['lon'];
    $services = $sp['services'];
    $payMethods = $sp['payMethods'];


    $sql = "SELECT id FROM pointsOfSale WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $insertSql = "INSERT INTO pointsOfSale (id, type, name, address, openingHours, lat, lon, services, payMethods) VALUES (:id, :type, :name, :address, :openingHours, :lat, :lon, :services, :payMethods)";
        $stmt = $pdo->prepare($insertSql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':openingHours', $openingHours);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lon', $lon);
        $stmt->bindParam(':services', $services);
        $stmt->bindParam(':payMethods', $payMethods);
        $stmt->execute();
    }
}


?>
