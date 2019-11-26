<?php
/**
 * Created by PhpStorm.
 * User: dc7
 * Date: 11/21/2019
 * Time: 11:11 AM
 */
require '../vendor/autoload.php';


$url = fopen("https://admin.b2b-carmarket.com//test/project", "r");
$start = true;
$attributes = array();
$data = array();
$generator = \Nubs\RandomNameGenerator\All::create();
if ($url)
{
    while (($line = fgets($url)) !== false)
    {
        $elements = explode(',', $line);
        if ($start) {
            $attributes = array_map('trim', $elements);
            $start = false;
        } else {
            foreach ($elements as $key => $element)
            {
                $element = trim(str_replace('<br>', '', $element));
                if (!array_key_exists($attributes[$key], $data)) {
                    $data[$attributes[$key]] = array($element);
                } else {
                    array_push($data[$attributes[$key]], $element);
                }


            }
        }
    }

    $conn = new mysqli('127.0.0.1', 'root', '', 'test_nik');
    if ($conn->connect_error) {
        die("There is a problem with the connection: " . $conn->connect_error);
    }


//Insert parsed vehicle data to DB
    $queryVehicles = $conn->prepare("INSERT INTO vehicle (VehicleID, InhouseSellerID, BuyerID, ModelID, SaleDate, BuyDate) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($data['VehicleID']); $i++) {
        $queryVehicles->bind_param("ssssss", $data['VehicleID'][$i], $data['InhouseSellerID'][$i] ,$data['BuyerID'][$i],
            $data['ModelID'][$i],$data['SaleDate'][$i],$data['BuyDate'][$i]);
        $queryVehicles->execute();

    }



    $sql = "SELECT COUNT(DISTINCT (`BuyerID`)) FROM `vehicle`";
    $result = $conn->query($sql);
    $queryNames = $conn->prepare("INSERT INTO client (name, surname, BuyerID) VALUES (?,?,?)");

    //insert user data
    $res = $result->fetch_assoc()['COUNT(DISTINCT (`BuyerID`))'];
    $inDB = [];
    for ($i = 0; $i < $res; $i++) {
        if (!in_array($data['BuyerID'][$i],$inDB)){
            $fullName = $generator->getName();
            $splitName = explode(" ",$fullName);
            //print_r($splitName);
            $queryNames->bind_param("sss", $splitName[0], $splitName[1],$data['BuyerID'][$i]);
            $queryNames->execute();
            array_push($inDB, $data['BuyerID'][$i]);
            print_r($inDB);

        }
        else {
            $res++;
        }
    }
    echo "DB populated!";
    fclose($url);
} else {
    echo "There has been an error";
}



