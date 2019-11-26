<?php
/**
 * Created by PhpStorm.
 * User: dc7
 * Date: 11/21/2019
 * Time: 11:11 AM
 */

namespace App\Nik;

require '../vendor/autoload.php';

use mysqli;

class GetData implements GetDataInterface
{

    protected $data;
    protected $url;
    protected $conn;

    public function __construct($url)
    {
        $this->url = $url;
        $this->data = $this->parseData();
        $this->conn = $this->connect();
    }


    public function parseData()
    {
        $url = fopen($this->url, "r");
        $start = true;
        $attributes = array();
        $dataP = array();
        if ($url) {
            while (($line = fgets($url)) !== false) {
                $elements = explode(',', $line);
                if ($start) {
                    $attributes = array_map('trim', $elements);
                    $start = false;
                } else {
                    foreach ($elements as $key => $element) {
                        $element = trim(str_replace('<br>', '', $element));
                        if (!array_key_exists($attributes[$key], $dataP)) {
                            $dataP[$attributes[$key]] = array($element);
                        } else {
                            array_push($dataP[$attributes[$key]], $element);
                        }
                    }
                }
            }
        }
        fclose($url);
        return $dataP;
    }

    public function populateVehicle()
    {
        $db = $this->conn;
        //Insert parsed vehicle data to DB
        $queryVehicles = $db->prepare("INSERT INTO vehicle (VehicleID, InhouseSellerID, BuyerID, ModelID, SaleDate, BuyDate) VALUES (?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($this->data['VehicleID']); $i++) {
            $queryVehicles->bind_param("ssssss", $this->data['VehicleID'][$i], $this->data['InhouseSellerID'][$i], $this->data['BuyerID'][$i],
                $this->data['ModelID'][$i], $this->data['SaleDate'][$i], $this->data['BuyDate'][$i]);
            $queryVehicles->execute();
        }
    }

    public function populateBuyers()
    {
        $db = $this->conn;
        $generator = \Nubs\RandomNameGenerator\All::create();
        $sql = "SELECT COUNT(DISTINCT (`BuyerID`)) FROM `vehicle`";
        $result = $db->query($sql);
        $queryNames = $db->prepare("INSERT INTO client (name, surname, BuyerID) VALUES (?,?,?)");

        //insert user data
        $res = $result->fetch_assoc()['COUNT(DISTINCT (`BuyerID`))'];
        $inDB = [];
        for ($i = 0; $i < $res; $i++) {
            if (!in_array($this->data['BuyerID'][$i], $inDB)) {
                $fullName = $generator->getName();
                $splitName = explode(" ", $fullName);
                //print_r($splitName);
                $queryNames->bind_param("sss", $splitName[0], $splitName[1], $this->data['BuyerID'][$i]);
                $queryNames->execute();
                array_push($inDB, $this->data['BuyerID'][$i]);
                print_r($inDB);
            } else {
                $res++;
            }
        }
    }

    function connect()
    {
        return new mysqli('127.0.0.1', 'root', '', 'test_nik');
    }
}


$parse = new GetData("https://admin.b2b-carmarket.com//test/project");
$parse->populateVehicle();
$parse->populateBuyers();

