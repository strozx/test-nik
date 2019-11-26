<?php
/**
 * Created by PhpStorm.
 * User: dc7
 * Date: 11/21/2019
 * Time: 10:27 AM
 */
namespace Nik;

require '../../vendor/autoload.php';

use mysqli;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Exception;
use splitbrain\phpcli\Options;

class TestCommand extends CLI
{
    private $conn;
    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     *
     * @throws Exception
     */
    protected function setup(Options $options)
    {
        $this->conn = new mysqli('127.0.0.1', 'root', '', 'test_nik');

        if ($this->conn->connect_error) {
            die("There is a problem with the connection: " . $this->conn->connect_error);
        }

        $options->setHelp('Test Nik');
        $options->registerOption('bestModelPerClient', 'For getting best selling model per client', 'a');
        $options->registerOption('add', 'Adds new vehicle (seller id, buyer id, model id, sale date, buy date)', 'b', true);
        $options->registerOption('bestSelling', 'Returns best three months in a row for ID provided', 'c');
    }


    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param Options $options
     * @return void
     *
     * @throws Exception
     */
    protected function main(Options $options)
    {
        if ($options->getOpt('bestModelPerClient')) {
            $this->bestModelPerClient();
        } else if ($options->getOpt('add')) {
            $this->add($options->getArgs());
        } else if ($options->getOpt('bestSelling')) {
            $this->bestSelling($options->getArgs());
        } else {
            echo $options->help();
        }
    }


    protected function bestModelPerClient()
    {
        $result = $this->conn->query("SELECT b.BuyerID, b.ModelID, MAX(b.counts) AS purchases 
        FROM ( SELECT BuyerID, ModelID, COUNT( ModelID) AS counts FROM vehicle GROUP BY BuyerID, ModelID) b 
        GROUP BY b.BuyerID, b.ModelID ORDER BY `purchases` DESC");
        while ($row = $result->fetch_array()) {
            $bid= $result->fetch_array()['BuyerID'];
            $sql = "SELECT name, surname from client where BuyerID=$bid";
            $resultBuyer = $this->conn->query($sql);
            $res = $resultBuyer->fetch_assoc();
            echo "Name: ".$res['name'] . " ". $res['surname'] . ", Model: " . $row['ModelID'] . ", # of purchases: " . $row['purchases'] . "\n";
        }
    }

    protected function add($args)
    {
        if (count($args) < 5) {
            echo "Function requires 6 arguments\n";
            return;
        }
        $query = $this->conn->prepare("INSERT INTO vehicle (VehicleID, InhouseSellerID, BuyerID, ModelID, SaleDate, BuyDate) VALUES (?, ?, ?, ?, ?, ?)");

        $query->bind_param("ssssss", $args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        $query->execute();

        if ($query->error) {
            echo $query->error . "\n";
        } else {
            echo "Success";
        }
    }


    protected function bestSelling($args)
    {
        $sum = 0;

        if (!$args[0]) {
            echo "Please provide a ModelID\n";
            return;
        }
        $result = $this->conn->query("SELECT YEAR(SaleDate) AS YEAR, MONTH(SaleDate) AS MONTH, COUNT(*) AS SUM
        FROM vehicle
        WHERE ModelID = $args[0]
        GROUP BY YEAR, MONTH
        LIMIT 3");

        $data = $result->fetch_all();
        $bestSellingMonths = "";
        if (count($data) == 0) {
            echo "No ID found for that model\n";
            return;
      }
        $threeMonthSum =0;
        foreach ($data as  $value){
            //print_r($value);
            echo "Best selling for modelID: ". $args[0]." Month and year: ". $value[1]. "/".$value[0]. " SOLD: ".$value[2]."\n";

        }
    }

}

$cli = new TestCommand();
$cli->run();








