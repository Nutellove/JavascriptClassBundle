<?php

namespace Sensio\HelloBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();

        # Trying to load an inexistant Entity should 404
        $crawler = $client->request('GET', '/jsclass/JavascriptClassBundle/NotFoundAnt/load/1');
        $this->assertTrue($client->getResponse()->isNotFound());
    }
}