<?php

namespace App\Tests\Controller\Spa;

use App\Tests\WebTestCase;

class SitemapControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testOrganization(): void
    {
        $this->client->xmlHttpRequest('GET', '/sitemap.xml');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/xml', $this->client->getResponse()->headers->get('Content-Type'));
    }
}
