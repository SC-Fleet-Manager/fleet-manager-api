<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class SpaControllerTest extends PantherTestCase
{
    use ReloadDatabaseTrait;

    private Client $client;

    public static function setUpBeforeClass(): void
    {
        @mkdir('var/screenshots');
    }

    public function setUp(): void
    {
        if ($_SERVER['NO_PANTHER'] ?? false) {
            $this->client = Client::createSeleniumClient('http://selenium-hub:4444/wd/hub', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY_W3C, [
                'w3c' => false,
                'binary' => '',
                'args' => ['start-maximized'],
            ],//(new ChromeOptions())->addArguments(['start-maximized'])
            ), 'http://apache-test');
        } else {
            $this->client = Client::createChromeClient(null, null, [], 'http://apache-test');
        }
        static::bootKernel();
    }

    public function tearDown(): void
    {
        $this->client->quit();
    }

    private function pause(int $s = 30): void
    {
        $this->client->wait($s, 100)->until(static function () {
            return false;
        });
    }

    private function login(?string $userId = null): void
    {
        $this->client->request('GET', '/connect/discord'.($userId ? "?userId=$userId" : ''));
    }

    /**
     * @group end2end
     * @group spa
     *
     * @group toto
     */
    public function testGlobalSpa(): void
    {
        try {
            $this->client->request('GET', '/');
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return stripos($driver->findElement(WebDriverBy::cssSelector('h1'))->getText(), 'Fleet Manager') !== false;
            });
            $this->client->refreshCrawler();

            // registration
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Use Now")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::id('modal-registration-login'))->isDisplayed();
            });
            $this->client->findElement(WebDriverBy::xpath('//span[contains(text(), "Sign up")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//button[contains(text(), "Register")]'))->isDisplayed();
            });
            $this->client->findElement(WebDriverBy::cssSelector('input#input-registration-email'))->sendKeys('foobar@example.com');
            $this->client->findElement(WebDriverBy::cssSelector('input#input-registration-password'))->sendKeys('123456');
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Register")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//*[@id="modal-registration-login"]//button[contains(text(), "Log in")]'))->isDisplayed()
                    && $driver->findElement(WebDriverBy::cssSelector('.alert'))->isDisplayed();
            });
            static::assertStringContainsString('Well done! An email has been sent to you to confirm your registration.', $this->client->findElement(WebDriverBy::cssSelector('.alert.alert-success'))->getText());

            // lost password
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//*[@id="modal-registration-login"]//button[contains(text(), "Log in")]'))->isDisplayed();
            });
            $this->client->findElement(WebDriverBy::xpath('//div[contains(text(), "lost your password?")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//button[contains(text(), "Send me a new password")]'))->isDisplayed();
            });
            $this->client->findElement(WebDriverBy::cssSelector('input#input-lost-password-email'))->sendKeys('foobar@example.com');
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Send me a new password")]'))->click();

            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//*[@id="modal-registration-login"]//button[contains(text(), "Log in")]'))->isDisplayed()
                    && $driver->findElement(WebDriverBy::cssSelector('.alert'))->isDisplayed();
            });
            static::assertStringContainsString('If we recognize this email, we will send to you the instructions to create a new password.', $this->client->findElement(WebDriverBy::cssSelector('.alert.alert-success'))->getText());

            // login
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return $driver->findElement(WebDriverBy::xpath('//*[@id="modal-registration-login"]//button[contains(text(), "Log in")]'))->isDisplayed();
            });
            $this->client->findElement(WebDriverBy::cssSelector('input#input-email'))->sendKeys('ioni@example.com');
            $this->client->findElement(WebDriverBy::cssSelector('input#input-password'))->sendKeys('123456');
            $this->client->findElement(WebDriverBy::xpath('//*[@id="modal-registration-login"]//button[contains(text(), "Log in")]'))->click();

            // Profile
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('js-update-sc-handle'))) > 0;
            });
            $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-update-sc-handle .card-body'))->getText();
            static::assertStringContainsString('SC Handle : ionni', $cardBody);
            static::assertStringContainsString('SC Number : 123456', $cardBody);
            static::assertStringContainsString('Update my SC handle', $cardBody);

            $cardBody = $this->client->findElement(WebDriverBy::cssSelector('.js-preferences .card-body'))->getText();
            static::assertStringContainsString('Personal fleet policy', $cardBody);

            static::assertTrue($this->client->executeScript('return document.querySelector(\'input[name="public-choice"][value="public"]\').checked;'), 'Personal fleet policy is not public.');
        } catch (\Exception $e) {
            $this->client->takeScreenshot(sprintf('var/screenshots/error-%s.png', date('Y-m-d_H:i:s')));
            throw $e;
        }
    }

    /**
     * @group end2end
     * @group spa
     */
    public function testProfile(): void
    {
        $this->login();

        try {
            // change password success
            $this->client->request('GET', '/profile');
            $this->client->refreshCrawler();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('js-security'))) > 0;
            });
            $this->client->findElement(WebDriverBy::cssSelector('input#input-change-password-old-password'))->sendKeys('123456');
            $this->client->findElement(WebDriverBy::cssSelector('input#input-change-password-password'))->sendKeys('456789');
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Change my password")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('toast-message'))) > 0;
            });
            static::assertStringContainsString('Your password has been successfully updated!', $this->client->findElement(WebDriverBy::cssSelector('.toast-success'))->getText());

            // change email success
            $this->client->request('GET', '/profile');
            $this->client->refreshCrawler();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('js-security'))) > 0;
            });
            $this->client->findElement(WebDriverBy::cssSelector('input#input-change-email-new-email'))->sendKeys('new-email@example.com');
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Change my email")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('alert'))) > 0;
            });
            static::assertStringContainsString('An email has been sent to you to confirm your new email address.', $this->client->findElement(WebDriverBy::cssSelector('.alert-success'))->getText());

            // Link Discord
            $this->login('77ea3d73-a786-4f0f-83dd-36a37265f952'); // linksocialnetworks-with-citizen@example.com
            $this->client->request('GET', '/connect/service/discord?discordId=123456789002&discordTag=0002&nickname=Ashuvidz'); // click on "Link my Discord"
            $this->client->refreshCrawler();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::cssSelector('.js-security .alert-warning form'))) > 0;
            });
            $labels = $this->client->findElements(WebDriverBy::cssSelector('.js-security label'));
            static::assertStringContainsString('ashuvidz (VyrtualSynthese)', $labels[1]->getText());
            static::assertStringContainsString('link_social_networks (Link social networks)', $labels[0]->getText());
            $this->client->findElement(WebDriverBy::xpath('//label[contains(text(), "ashuvidz (VyrtualSynthese)")]'))->click();
            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Link my Discord account and use the selected Citizen.")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::className('toast-message'))) > 0;
            });
            static::assertStringContainsString('Your Discord account is successfully linked!', $this->client->findElement(WebDriverBy::cssSelector('.toast-success'))->getText());
            static::assertCount(0, $this->client->findElements(WebDriverBy::cssSelector('.js-security .alert-warning')));
        } catch (\Exception $e) {
            $this->client->takeScreenshot(sprintf('var/screenshots/error-%s.png', date('Y-m-d_H:i:s')));
            throw $e;
        }
    }

    /**
     * @group end2end
     * @group spa
     * @group funding
     */
    public function testFunding(): void
    {
        $em = static::$container->get('doctrine')->getManager();
        /** @var UserRepository $repo */
        $repo = $em->getRepository(User::class);
        $user = $repo->find('d92e229e-e743-4583-905a-e02c57eacfe0');
        $user->setLastPatchNoteReadAt(new \DateTimeImmutable('+5 minutes'));
        $em->flush();
        $em->refresh($user);

        $this->login('d92e229e-e743-4583-905a-e02c57eacfe0');

        try {
            $this->client->request('GET', '/my-backings');
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return stripos($driver->findElement(WebDriverBy::id('backings-table'))->getText(), 'You have no backings! 😢 Feel free to support us. 😎') === false;
            });
            $this->client->refreshCrawler();

            static::assertSame("$\n51.33", $this->client->findElement(WebDriverBy::id('total-backed'))->getText());
            static::assertSame('5133', $this->client->findElement(WebDriverBy::id('count-fm-coins'))->getText());

            static::assertSame('Completed', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(1) td:nth-child(2)'))->getText());
            static::assertSame('$51.33', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(1) td:nth-child(3)'))->getText());
            static::assertSame('5133', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(1) td:nth-child(4)'))->getText());
            static::assertSame('5133', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(1) td:nth-child(5)'))->getText());

            static::assertSame('Created', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(2) td:nth-child(2)'))->getText());
            static::assertSame('$1.00', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(2) td:nth-child(3)'))->getText());
            static::assertSame('', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(2) td:nth-child(4)'))->getText());
            static::assertSame('0', $this->client->findElement(WebDriverBy::cssSelector('#backings-table tbody tr:nth-child(2) td:nth-child(5)'))->getText());

            static::assertCount(1, $this->client->findElements(WebDriverBy::cssSelector('.navbar-text .supporter-badge')));

            $this->login('14203774-91fa-4300-b464-bcda42697b10');
            $this->client->request('GET', '/supporters');
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::cssSelector('.spinner'))) === 0
                    && strpos($driver->findElement(WebDriverBy::cssSelector('#progress-amount'))->getText(), '63.12') !== false;
            });
            $this->client->refreshCrawler();

            static::assertSame('$ 63.12 / $ 150.00', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#progress-amount'))->getText()));
            static::assertSame('1. ionni $ 51.33', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(1)'))->getText()));
            static::assertSame('2. Anonymous $ 21.50', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(2)'))->getText())); // fundings-1 /w supporterVisible == false
            static::assertSame('20. 16_fundings $ 1.16', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(20)'))->getText()));
            static::assertSame('26. 10_fundings $ 1.10', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(21)'))->getText()));

            $this->client->findElement(WebDriverBy::xpath('//button[contains(text(), "Support Us")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return count($driver->findElements(WebDriverBy::cssSelector('#input-funding-amount'))) === 1
                    && $driver->findElement(WebDriverBy::cssSelector('#modal-funding button.close'))->isDisplayed();
            });
            $this->client->refreshCrawler();
            $this->client->wait(5, 100)->until(static function (WebDriver $driver) {
                if (count($driver->findElements(WebDriverBy::cssSelector('#modal-funding button.close'))) === 1) {
                    $driver->findElement(WebDriverBy::cssSelector('#modal-funding button.close'))->click();
                }

                return count($driver->findElements(WebDriverBy::cssSelector('#modal-funding'))) === 0;
            });

            $this->client->findElement(WebDriverBy::xpath('//label[contains(text(), "Organizations Tops")]'))->click();
            $this->client->wait(3, 100)->until(static function (WebDriver $driver) {
                return strpos($driver->findElement(WebDriverBy::cssSelector('#ladder-all-time'))->getText(), 'Les Gardiens') !== false;
            });
            static::assertSame('1. Anonymous $ 58.33', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(1)'))->getText())); // FallKrom /w supporterVisible==false
            static::assertSame('2. Les Gardiens $ 0.90', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-all-time .row:nth-child(2)'))->getText()));

            $this->client->findElement(WebDriverBy::xpath('//a[contains(text(), "Monthly Top 20")]'))->click();
            static::assertSame('1. Les Gardiens $ 0.90', preg_replace('~\s+~', ' ', $this->client->findElement(WebDriverBy::cssSelector('#ladder-monthly .row:nth-child(1)'))->getText()));
        } catch (\Exception $e) {
            $this->client->takeScreenshot(sprintf('var/screenshots/error-%s.png', date('Y-m-d_H:i:s')));
            throw $e;
        }
    }
}
