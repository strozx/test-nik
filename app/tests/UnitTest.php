<?php
/**
 * Created by PhpStorm.
 * User: dc7
 * Date: 11/21/2019
 * Time: 2:52 PM
 */
use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    /** @test */
    public function addSuccess()
    {
        $output = 'php TestCommand.php -b 1 34242 11 40 57 2019-01-01 2019-01-02';
        $this->assertRegExp('/Success/', $output);
    }
}