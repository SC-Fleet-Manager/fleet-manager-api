<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class DashboardControllerTest extends PantherTestCase
{
    use ReloadDatabaseTrait;

    private $client;

    public function setUp(): void
    {
        $this->client = Client::createSeleniumClient('http://selenium-hub:4444/wd/hub', null, 'http://apache-test');
    }

    public function testIndexSuccessResponse(): void
    {
        // connect to a RSI linked user
        $this->client->request('GET', '/_test_login/d92e229e-e743-4583-905a-e02c57eacfe0');

        // My Fleet
        $this->client->wait()->until(static function (WebDriver $driver) {
            return count($driver->findElements(WebDriverBy::className('js-card-ship'))) > 0;
        });
        $this->assertSame('Cutlass Black', $this->client->findElement(WebDriverBy::cssSelector('.card-title'))->getText());
        $this->assertContains('Manufacturer: Drake', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('LTI: Yes', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('Cost: $110', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('Pledge date: April 10, 2019', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());

        // Profile
        $this->client->request('GET', '/#/profile');
        $this->client->wait()->until(static function (WebDriver $driver) {
            return count($driver->findElements(WebDriverBy::className('js-update-sc-handle'))) > 0;
        });
        $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-update-sc-handle .card-body'))->getText();
        $this->assertContains('Your SC Handle : ionni', $cardBody);
        $this->assertContains('Your SC Number : 123456', $cardBody);
        $this->assertContains('Update my SC handle', $cardBody);

        $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-preferences .card-body'))->getText();
        $this->assertContains('Personal fleet policy', $cardBody);

        $this->assertTrue($this->client->executeScript('return document.querySelector(\'input[name="public-choice"][value="public"]\').checked;'), 'Personal fleet policy is not public.');
    }
}
