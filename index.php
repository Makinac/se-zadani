<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300); 
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time = $_POST['time'] ?? '';
    $date = $_POST['date'] ?? '';
    $status = $_POST['status'] ?? '';

    $errorMessage = "";

    if ($status == '') {
        $errorMessage = "Vyber status";
    }

    if ($status != "vsechno") {
        if ($date == '') {
            $errorMessage = "Zadej datum";
        }
    
        if ($time == '') {
            $errorMessage = "Zadej čas";
        }
    }

    if ($errorMessage == "") {
        $apiUrl = 'http://49.13.93.232/PID/api.php/postPointOfSale';

        $data = [
            'time' => $time,
            'date' => $date,
            'status' => $status
        ];
    
        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    
        $response = curl_exec($curl);
    
        if ($response === false) {
            $errorMessage = 'Chyba při volání API: ' . curl_error($curl);
        } else {
            $errorMessage = '';
            $responseData = json_decode($response, true);
        }
    
        curl_close($curl);
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PID Pobočky</title>
</head>
<body>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: "Inter", sans-serif;
    }
    .formbold-mb-5 {
        margin-bottom: 20px;
    }
    .formbold-pt-3 {
        padding-top: 12px;
    }
    .formbold-main-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px;
    }
    
    .formbold-form-wrapper {
        margin: 0 auto;
        max-width: 550px;
        width: 100%;
        background: white;
    }
    .formbold-form-label {
        display: block;
        font-weight: 500;
        font-size: 16px;
        color: #07074d;
        margin-bottom: 12px;
    }
    .formbold-form-label-2 {
        font-weight: 600;
        font-size: 20px;
        margin-bottom: 20px;
    }
    
    .formbold-form-input {
        width: 100%;
        padding: 12px 24px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: white;
        font-weight: 500;
        font-size: 16px;
        color: #6b7280;
        outline: none;
        resize: none;
    }
    .formbold-form-input:focus {
        border-color: #6a64f1;
        box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.05);
    }
    
    .formbold-btn {
        text-align: center;
        font-size: 16px;
        border-radius: 6px;
        padding: 14px 32px;
        border: none;
        font-weight: 600;
        background-color: #6a64f1;
        color: white;
        cursor: pointer;
    }
    .formbold-btn:hover {
        box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.05);
    }
    
    .formbold--mx-3 {
        margin-left: -12px;
        margin-right: -12px;
    }
    .formbold-px-3 {
        padding-left: 12px;
        padding-right: 12px;
    }
    .flex {
        display: flex;
    }
    .flex-wrap {
        flex-wrap: wrap;
    }
    .w-full {
        width: 100%;
    }
    .formbold-radio {
        width: 20px;
        height: 20px;
    }
    .formbold-radio-label {
        font-weight: 500;
        font-size: 16px;
        padding-left: 12px;
        color: #070707;
        padding-right: 20px;
    }
    @media (min-width: 540px) {
        .sm\:w-half {
        width: 50%;
        }
    }

    table {
        margin: 20px auto;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }
    </style>
    <div class="formbold-main-wrapper">
        <div class="formbold-form-wrapper">
            <form action="index.php" method="post">   
                <div class="formbold-mb-5 w-full">
                    <label for="status" class="formbold-form-label">
                        Status
                    </label>
                        <select id="status" name="status" onchange="inputStatusChange()" class="formbold-form-input" required>
                            <option value="otevrene">🟢 | Otevřené Pobočky</option>
                            <option value="zavrene">🔴 | Zavřené Pobočky</option>
                        </select>
                </div>
        
                <div class="flex flex-wrap formbold--mx-3">
                    <div class="w-full sm:w-half formbold-px-3">
                        <div class="formbold-mb-5 w-full">
                        <label for="date" id="dateLabel"  class="formbold-form-label"> Datum </label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            class="formbold-form-input"
                            required
                        />
                        </div>
                    </div>
                    <div class="w-full sm:w-half formbold-px-3">
                        <div class="formbold-mb-5">
                        <label for="time" id="timeLabel" class="formbold-form-label"> Čas </label>
                        <input
                            type="time"
                            name="time"
                            id="time"
                            class="formbold-form-input"
                            required
                        />
                        </div>
                    </div>
                    
                </div>

                <div>
                    <input class="formbold-btn" type="submit" value="Zobrazit pobočky">
                </div>
                <?php if (!empty($errorMessage)): ?>
                    <br><p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php 
        if (!empty($responseData)) {
            switch($responseData["type"]) {
                case "opened":
                    echo '<table>';
                    echo '<tr>';
                    echo '<th>Jméno 📜</th>';
                    echo '<th>Adresa 🗺️</th>';
                    echo '<th>Otevírací Doba ⌛</th>';
                    echo '<th>Zavírací Doba ⏱️</th>';
                    echo '</tr>';
                    foreach ($responseData["data"] as $point) {
                        echo '<tr>';
                        echo '<td>' . $point["name"] . '</td>';
                        echo '<td>' . $point["address"] . '</td>';
                        echo '<td>' . $point["openingDay"] . ' ' . $point["openingHours"] . '</td>';
                        echo '<td>';
                        if ($point["closingHours"] == "0") {
                            if ($point["closingMinutes"] == 0) {
                                echo "Teď";  
                            } elseif ($point["closingMinutes"] <= 4) {
                                echo $point["closingMinutes"] . " Minuty";  
                            } else {
                                echo $point["closingMinutes"] . " Minut";
                            }
                        } else {
                            if ($point["closingHours"] == 1) {
                                echo $point["closingHours"] . " Hodinu ";  
                            } elseif ($point["closingHours"] <= 4) {
                                echo $point["closingHours"] . " Hodiny ";  
                            } else {
                                echo $point["closingHours"] . " Hodin ";
                            }
                            if ($point["closingMinutes"] == 0) {
                            } elseif ($point["closingMinutes"] <= 4) {
                                echo "a " . $point["closingMinutes"] . " Minuty";  
                            } else {
                                echo "a " . $point["closingMinutes"] . " Minut";
                            }
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    break;
                
                case "closed":
                    $pointsData = array();
                    foreach ($responseData["data"] as $point) {
                        $name = $point["name"];
                        $address = $point["address"];
                        $openingData = $point["openingDay"] . ' ' . $point["openingHours"];
                    
                        if (array_key_exists($name, $pointsData)) {
                            $pointsData[$name]['openingHours'][] = $openingData;
                        } else { 
                            $pointsData[$name] = array('address' => $address, 'openingHours' => array($openingData));
                        }
                    }

                    echo '<table>';
                    echo '<tr>';
                    echo '<th>Jméno 📜</th>';
                    echo '<th>Adresa 🗺️</th>';
                    echo '<th>Otevírací Doba ⌛</th>';
                    echo '</tr>';
                    foreach ($pointsData as $name => $data) {
                        echo '<tr>';
                        echo '<td>' . $name . '</td>';
                        echo '<td>' . $data['address'] . '</td>';
                        echo '<td>' . implode('<br>', $data['openingHours']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    break;
            }
        }
    ?>

    <script>
        function setCurrentDateTime() {
            var now = new Date();
            var day = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
            var time = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
            document.getElementById('date').value = day;
            document.getElementById('time').value = time;
        }

        window.onload = setCurrentDateTime;
    </script>
</body>
</html>
