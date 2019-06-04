<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class SpaControllerTest extends PantherTestCase
{
    use ReloadDatabaseTrait;

    private $client;

    public function setUp(): void
    {
        $this->client = Client::createSeleniumClient('http://selenium-hub:4444/wd/hub', null, 'http://apache-test');
    }

    private function pause(int $s = 30): void
    {
        $this->client->wait($s, 100)->until(static function () {
            return false;
        });
    }

    /**
     * @group end2end
     * @group spa
     */
    public function testIndexSuccessResponse(): void
    {
        $this->client->request('GET', '/');
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return stripos($driver->findElement(WebDriverBy::cssSelector('h1'))->getText(), 'Fleet Manager') !== false;
        });
        $this->client->refreshCrawler();

        // connect with default user
        $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Start using Fleet Manager now")]'))->click();
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return $driver->findElement(WebDriverBy::id('modal-login___BV_modal_body_'))->isDisplayed();
        });
        $this->client->clickLink('Login with Discord');

        // Profile
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return count($driver->findElements(WebDriverBy::className('js-update-sc-handle'))) > 0;
        });
        $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-update-sc-handle .card-body'))->getText();
        $this->assertContains('SC Handle : ionni', $cardBody);
        $this->assertContains('SC Number : 123456', $cardBody);
        $this->assertContains('Update my SC handle', $cardBody);

        $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-preferences .card-body'))->getText();
        $this->assertContains('Personal fleet policy', $cardBody);

        $this->assertTrue($this->client->executeScript('return document.querySelector(\'input[name="public-choice"][value="public"]\').checked;'), 'Personal fleet policy is not public.');

        // My Fleet
        $this->client->clickLink('My Fleet');
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return count($driver->findElements(WebDriverBy::className('js-card-ship'))) > 0;
        });
        $this->assertSame('Cutlass Black', $this->client->findElement(WebDriverBy::cssSelector('.card-title'))->getText());
        $this->assertContains('Manufacturer: Drake', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('LTI: Yes', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('Cost: $110', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertContains('Pledge date: April 10, 2019', $this->client->findElement(WebDriverBy::cssSelector('.card-body'))->getText());
        $this->assertStringEndsWith('/api/create-citizen-fleet-file', $this->client->findElement(WebDriverBy::linkText('Export my fleet (.json)'))->getAttribute('href'));

        $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Update my fleet")]'))->click();
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return $driver->findElement(WebDriverBy::id('modal-upload-fleet___BV_modal_body_'))->isDisplayed();
        });
        $this->client->findElement(WebDriverBy::cssSelector('#modal-upload-fleet .close'))->click();
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return count($driver->findElements(WebDriverBy::id('modal-upload-fleet'))) === 0;
        });

        // Organizations' fleets
        $this->client->clickLink("Organizations' fleets");
        $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
            return (int) $driver->executeScript('return document.querySelectorAll(".card-ship").length;') > 0;
        });
        $this->client->refreshCrawler();
        $this->assertSame('Select an organization', $this->client->findElement(WebDriverBy::cssSelector('label[for="select-orga"]'))->getText());
        $this->assertSame('flk', $this->client->executeScript('return document.querySelector(\'#select-orga\').value;'));
        $this->assertContains('DRAK - Cutlass', $this->client->findElement(WebDriverBy::cssSelector('.card-ship .card-title'))->getText());

        // TODO : click on chassis
    }
}
