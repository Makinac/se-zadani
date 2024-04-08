<?php
header('Content-Type: application/json');
require("db.php");

switch($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        switch ($_SERVER['PATH_INFO']) {
            case "/postPointOfSale":
                global $pdo;

                $time = $_POST['time'] ?? '';
                $date = $_POST['date'] ?? '';

                $checkTime = strtotime($time);
                $date = (date('w', strtotime($date)) + 6) % 7;

                if (!$time) {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Time");
                    echo json_encode($response);
                    exit(); 
                }

                if ($date == "") {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Date");
                    echo json_encode($response);
                    exit(); 
                }
                
                $sql = "SELECT * FROM pointsOfSale";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                
                $mysqlDataArray = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $mysqlDataArray[] = $row;
                }

                $filteredPointsOfSale = array();
                foreach ($mysqlDataArray as $mysqlDataValuable) {
                    $hoursJson = json_decode($mysqlDataValuable["openingHours"]);
                    foreach ($hoursJson as $hoursJsonValuable) {
                        if ($date >= $hoursJsonValuable->from && $date <= $hoursJsonValuable->to) {
                            $filteredPointsOfSale[] = [
                                "id" => $mysqlDataValuable['id'],
                                "type" => $mysqlDataValuable['type'],
                                "name" => $mysqlDataValuable['name'],
                                "address" => $mysqlDataValuable['address'],
                                "openingHoursFrom" => $hoursJsonValuable->from,
                                "openingHoursTo" => $hoursJsonValuable->to,
                                "openingHoursHours" => $hoursJsonValuable->hours,
                                "lat" => $mysqlDataValuable['lat'],
                                "lon" => $mysqlDataValuable['lon'],
                                "services" => $mysqlDataValuable['services'],
                                "payMethods" => $mysqlDataValuable['payMethods']
                            ]; 
                            break;           
                        }
                    }
                }

                
                $openPointsOfSale = array();
                
                foreach ($filteredPointsOfSale as $filteredPointsOfSaleValuable) {
                    $openingHours = $filteredPointsOfSaleValuable["openingHoursHours"];
                    
                    if (strpos($openingHours, ',') !== false) {
                        $hoursArray = explode(',', $openingHours);
                    } else {
                        $hoursArray = [$openingHours];
                    }
                    
                    foreach ($hoursArray as $hoursRange) {
                        $Hours = explode('-', $hoursRange);
                        list($start, $end) = $Hours;
                        $startTime = strtotime($start);
                        $endTime = strtotime($end);
                
                        if ($checkTime >= $startTime && $checkTime <= $endTime) {
                            $openPointsOfSale[] = [
                                "name" => $filteredPointsOfSaleValuable['name'],
                                "type" => $filteredPointsOfSaleValuable['type'],
                                "address" => $filteredPointsOfSaleValuable['address'],
                                "openingHours" => $filteredPointsOfSaleValuable['openingHoursHours'],
                                "raw" => $filteredPointsOfSaleValuable
                            ];
                            break;
                        }
                    }
                }
  
                http_response_code(200);
                $response = array("status" => "ok", "data" => $filteredPointsOfSale);
                echo json_encode($response);
                exit(); 
            default:
                http_response_code(405);
                $response = array("status" => "error", "message" => "Bad Request Uri");
                echo json_encode($response);
                exit(); 
        }
        break;
    default:
        http_response_code(405);
        $response = array("status" => "error", "message" => "Bad Request Method");
        echo json_encode($response);
        exit(); 

}

?>