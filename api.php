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
                $status = $_POST['status'] ?? '';

                if (!$time) {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Time");
                    echo json_encode($response);
                    exit(); 
                }

                $checkTime = strtotime($time);

                if ($date == "") {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Date");
                    echo json_encode($response);
                    exit(); 
                }

                $date = (date('w', strtotime($date)) + 6) % 7;

                if ($status == "") {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Status");
                    echo json_encode($response);
                    exit(); 
                }

                if (!$status) {
                    http_response_code(400);
                    $response = array("status" => "error", "message" => "No Status");
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

                function getClosedPoints() {
                    global $mysqlDataArray;
                    global $date;
                    global $checkTime;
                
                    $closedPointsOfSale = array();
                
                    return $closedPointsOfSale;
                }

                switch ($status) {
                    case "otevrene":    
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
                                    $dny = array("PO", "ÚT", "ST", "ČT", "PÁ", "SO", "NE");
                                    $oteviraciDny = $dny[$filteredPointsOfSaleValuable["openingHoursFrom"]] . "-" . $dny[$filteredPointsOfSaleValuable["openingHoursTo"]];
    
                                    $time_until_closing = $endTime - $checkTime;
                                    $closingHours = floor($time_until_closing / 3600);
                                    $closingMinutes = floor(($time_until_closing % 3600) / 60);
    
                                    $openPointsOfSale[] = [
                                        "name" => $filteredPointsOfSaleValuable['name'],
                                        "type" => $filteredPointsOfSaleValuable['type'],
                                        "address" => $filteredPointsOfSaleValuable['address'],
                                        "openingHours" => $hoursRange,
                                        "openingDay" => $oteviraciDny,
                                        "closingHours" => $closingHours,
                                        "closingMinutes" => $closingMinutes
                                    ];
                                    break;
                                }
                            }
                        }

                        http_response_code(200);
                        $response = array("status" => "ok", "type" => "opened", "data" => $openPointsOfSale);
                        echo json_encode($response);
                        exit(); 
                    case "zavrene":
                        $filteredPointsOfSale = array();
                        foreach ($mysqlDataArray as $mysqlDataValuable) {
                            $hoursJson = json_decode($mysqlDataValuable["openingHours"]);
                            foreach ($hoursJson as $hoursJsonValuable) {
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
                            }
                        }
        
                        
                        $closedPointsOfSale = array();
                        
                        
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
                        
                                if (!($checkTime >= $startTime && $checkTime <= $endTime)) {
                                    $dny = array("PO", "ÚT", "ST", "ČT", "PÁ", "SO", "NE");
                                    $oteviraciDny = $dny[$filteredPointsOfSaleValuable["openingHoursFrom"]] . "-" . $dny[$filteredPointsOfSaleValuable["openingHoursTo"]];
    
                                    $time_until_closing = $endTime - $checkTime;
                                    $closingHours = floor($time_until_closing / 3600);
                                    $closingMinutes = floor(($time_until_closing % 3600) / 60);
    
                                    $closedPointsOfSale[] = [
                                        "name" => $filteredPointsOfSaleValuable['name'],
                                        "type" => $filteredPointsOfSaleValuable['type'],
                                        "address" => $filteredPointsOfSaleValuable['address'],
                                        "openingHours" => $hoursRange,
                                        "openingDay" => $oteviraciDny,
                                        "closingHours" => $closingHours,
                                        "closingMinutes" => $closingMinutes
                                    ];
                                    break;
                                }
                            }
                        }
                        
                        http_response_code(200);
                        $response = array("status" => "ok", "type" => "closed", "data" => $closedPointsOfSale);
                        echo json_encode($response);
                        exit(); 
                }
            default:
                http_response_code(405);
                $response = array("status" => "error", "message" => "Bad Request Uri");
                echo json_encode($response);
                exit(); 
        }
    default:
        http_response_code(405);
        $response = array("status" => "error", "message" => "Bad Request Method");
        echo json_encode($response);
        exit(); 

}

?>