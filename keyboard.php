<?php
    // Initialize database connection
    $mysqli = new mysqli("localhost", "root", "", "appyogi");

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $userId = $_GET['user'];

    if (isset($_POST['action']) && ($_POST['action'] === 'acquireControl' || $_POST['action'] === 'releaseControl')) {
        //Fetch Keyboard state
        $query = "SELECT user, keyboardstate, timeout FROM keyboard WHERE id=1";
        $keyBoardState = $mysqli->query($query)->fetch_assoc();

        //if($keyBoardState['user'] == 0 && $keyBoardState['keyboardstate'] == 0) {
            $mysqli->query("UPDATE keyboard SET user = " . $userId . ", keyboardstate =" . $_POST['keyboardstate'] . ", timeout = " . $_POST['timeout'] . " WHERE id = 1");
        //}
        
    } else if(isset($_POST['action']) && ($_POST['action'] == "updateKeyboard")) {
        $mysqli->query("UPDATE keyboard SET user = " . $userId . ", keyboardstate = 0, timeout = 1 WHERE id = 1");
        
        
        $query2 = "SELECT user, userkeys FROM userkeys where user = " . $userId . " and userkeys = " . $_POST['key'] . " and status = 1";
        $keysState = $mysqli->query($query2)->fetch_assoc();
        if(isset($keysState['user'])){
            $mysqli->query("UPDATE userkeys SET status = 0 WHERE user = " . $userId . " and userkeys = " . $_POST['key'] . "");
        } else {
            $mysqli->query("UPDATE userkeys SET status = 1 WHERE user = " . $userId . " and userkeys = " . $_POST['key'] . "");
        }

        
    }

    $query = "SELECT user, keyboardstate, timeout FROM keyboard WHERE id=1";
    $keyBoardState['userkeyboard'] = $mysqli->query($query)->fetch_assoc();

    $query2 = "SELECT user, userkeys FROM userkeys where status = 1";
    //$query2 = "SELECT user, CASE WHEN status = 1 THEN userkeys ELSE '' END as userkeys FROM userkeys";
    $result = $mysqli->query($query2)->fetch_all();
    $finalArr = [];
    //echo "<pre/>";
    //print_r($result); exit;

    foreach ($result as $key => $value) {
        if($value[1]){
            $finalArr[$value[0]][] = intval($value[1]);
        }
    }
    if(empty($finalArr)) {
        $finalArr = [["user" => 1, "keys" => []], ["user" => 2, "keys" => []]];
    } else {
       
        function formatArr($vl, $k){
            return array("user" => $k, "keys" => $vl);
        }
        $finalArr = array_map('formatArr', $finalArr, array_keys($finalArr));
    }

    foreach($finalArr as $k2 => $vl2){
        if(count($finalArr) == 1){
            if($finalArr[$k2]['user'] == 1){
                $finalArr = [["user" => $finalArr[$k2]['user'], "keys" => $finalArr[$k2]['keys']], ["user" => 2, "keys" => []]];
            } else if($finalArr[$k2]['user'] == 2){
                $finalArr = [["user" => 1, "keys" => []], ["user" => $finalArr[$k2]['user'], "keys" => $finalArr[$k2]['keys']]];
            }
        }
    }
    //print_r($finalArr); exit;
    
    $userKey['userkeys'] = $finalArr;
    $finalArr = [$keyBoardState, $userKey];
    echo json_encode($finalArr); 

    $mysqli->close();