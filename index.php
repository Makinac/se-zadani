<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300); 
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time = $_POST['time'] ?? '';

    if ($time == '') {
        $errorMessage = "Zadej čas";
    } else {
        $apiUrl = 'http://49.13.93.232/PID/api.php/postPointOfSale';
        $data = [
            'time' => $time
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PID</title>
</head>
<body>
    <h2>PID Time</h2>
    <form action="index.php" method="post">
        <label for="time">Čas:</label><br>
        <input type="time" id="time" name="time"><br><br>
        <input type="submit" value="Odeslat">
    </form>

    <?php if (!empty($errorMessage)): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <?php if (!empty($responseData)): ?>
        <table>
            <tr>
                <th>Jméno 📜</th>
                <th>Type 🔧</th>
                <th>Adresa 🗺️</th>
                <th>Otevírací Doba ⌛</th>
            </tr>
            <?php 
                foreach ($responseData["data"] as $point){
                    $hours_array = json_decode($point["openingHours"], true);
                    $hours_string = '';

                    foreach ($hours_array as $item) {
                        $hours_string .= $item['hours'] . ', ';
                    }
                    $hours_string = rtrim($hours_string, ', ');
                    ?>
                        <tr>
                            <td><?php echo $point["name"]; ?></td>
                            <td><?php echo $point["type"]; ?></td>
                            <td><?php echo $point["address"]; ?></td>
                            <td><?php echo $hours_string; ?></td>
                        </tr>
                    <?php 
                }
            ?>
        </table>
        <br>
        <h2>Raw response</h2>
        <?php print_r("<pre>"); print_r($responseData); print_r("</pre>")?>
    <?php endif; ?>
    <br>
</body>
</html>
