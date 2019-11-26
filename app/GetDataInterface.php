<?php
/**
 * Created by PhpStorm.
 * User: nikst
 * Date: 26/11/2019
 * Time: 17:40
 */

namespace App\Nik;

interface GetDataInterface
{

    public function __construct($url);

    public function parseData();

    public function populateVehicle();

    public function populateBuyers();

    function connect();

}