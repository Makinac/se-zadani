<?php
header('Content-Type: application/json');
require("db.php");

switch($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        switch ($_SERVER['PATH_INFO']) {
            case "/postPointOfSale":
                global $pdo;

                $time = $_POST['time'] ?? '';

                if (!$time) {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Time");
                    echo json_encode($response);
                    exit(); 
                }
                
                $sql = "SELECT * FROM pointsOfSale";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                
                $openingHoursArray = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $openingHoursArray[] = $row;
                }

                $openPointsOfSale = array();

                $checkTime = strtotime($time);

                foreach ($openingHoursArray as $store) {
                    $hoursArray = json_decode($store['openingHours'], true);
                    $convertedHoursArray = array();

                    foreach ($hoursArray as $array) {
                        $convertedHoursArray[] = $array['hours'];
                    }

                    foreach ($convertedHoursArray as $hoursArray) {
                        $ranges = strpos($hoursArray, ',') !== false ? explode(',', $hoursArray) : [$hoursArray];
                    
                        foreach ($ranges as $range) {
                            $rangeParts = explode('-', $range);
                            
                            if (count($rangeParts) == 2) {
                                list($start, $end) = $rangeParts;
                                $startTime = strtotime($start);
                                $endTime = strtotime($end);
                    
                                if ($checkTime >= $startTime && $checkTime <= $endTime) {
                                    $openPointsOfSale[] = [
                                        "name" => $store['name'],
                                        "type" => $store['type'],
                                        "address" => $store['address'],
                                        "openingHours" => $store['openingHours'],
                                        "raw" => $store
                                    ];
                                    break 2;
                                }
                            }
                        }
                    }
                    
                }  

  
                http_response_code(200);
                $response = array("status" => "ok", "data" => $openPointsOfSale);
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