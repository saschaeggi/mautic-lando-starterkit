<?php

namespace Mautic\PageBundle\Tests\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;

class PageControllerTest extends MauticMysqlTestCase
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->prefix = $this->container->getParameter('mautic.db_table_prefix');
    }

    public function testLandingPageTracking()
    {
        $this->connection->insert($this->prefix.'pages', [
            'is_published' => true,
            'date_added'   => (new \DateTime())->format('Y-m-d H:i:s'),
            'title'        => 'Page:Page:LandingPageTracking',
            'alias'        => 'page-page-landingPageTracking',
            'template'     => 'blank',
            'hits'         => 0,
            'unique_hits'  => 0,
            'variant_hits' => 0,
            'revision'     => 0,
            'lang'         => 'en',
        ]);
        $leadsBeforeTest   = $this->connection->fetchAll('SELECT `id` FROM `'.$this->prefix.'leads`;');
        $leadIdsBeforeTest = array_column($leadsBeforeTest, 'id');
        $this->client->request('GET', '/page-page-landingPageTracking');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $sql = 'SELECT `id` FROM `'.$this->prefix.'leads`';
        if (!empty($leadIdsBeforeTest)) {
            $sql .= ' WHERE `id` NOT IN ('.implode(',', $leadIdsBeforeTest).');';
        }
        $newLeads = $this->connection->fetchAll($sql);
        $this->assertCount(1, $newLeads);
        $leadId        = reset($newLeads)['id'];
        $leadEventLogs = $this->connection->fetchAll('
          SELECT `id`, `action`
          FROM `'.$this->prefix.'lead_event_log`
          WHERE `lead_id` = :leadId
          AND `bundle` = "page" AND `object` = "page";', ['leadId' => $leadId]
        );
        $this->assertCount(1, $leadEventLogs);
        $this->assertSame('created_contact', reset($leadEventLogs)['action']);
    }

    /**
     * Skipped for now.
     */
    public function LandingPageTrackingSecondVisit()
    {
        $this->connection->insert($this->prefix.'pages', [
            'is_published' => true,
            'date_added'   => (new \DateTime())->format('Y-m-d H:i:s'),
            'title'        => 'Page:Page:LandingPageTrackingSecondVisit',
            'alias'        => 'page-page-landingPageTrackingSecondVisit',
            'template'     => 'blank',
            'hits'         => 0,
            'unique_hits'  => 0,
            'variant_hits' => 0,
            'revision'     => 0,
            'lang'         => 'en',
        ]);
        $leadsBeforeTest   = $this->connection->fetchAll('SELECT `id` FROM `'.$this->prefix.'leads`;');
        $leadIdsBeforeTest = array_column($leadsBeforeTest, 'id');
        $this->client->request('GET', '/page-page-landingPageTrackingSecondVisit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $sql = 'SELECT `id` FROM `'.$this->prefix.'leads`';
        if (!empty($leadIdsBeforeTest)) {
            $sql .= ' WHERE `id` NOT IN ('.implode(',', $leadIdsBeforeTest).');';
        }
        $newLeadsAfterFirstVisit = $this->connection->fetchAll($sql);
        $this->assertCount(1, $newLeadsAfterFirstVisit);
        $leadId                   = reset($newLeadsAfterFirstVisit)['id'];
        $eventLogsAfterFirstVisit = $this->connection->fetchAll('
          SELECT `id`, `action`
          FROM `'.$this->prefix.'lead_event_log`
          WHERE `lead_id` = :leadId
          AND `bundle` = "page" AND `object` = "page";', ['leadId' => $leadId]
        );
        $this->assertCount(1, $eventLogsAfterFirstVisit);
        $this->assertSame('created_contact', reset($eventLogsAfterFirstVisit)['action']);
        $this->client->request('GET', '/page-page-landingPageTrackingSecondVisit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $eventLogsAfterSecondVisit = $this->connection->fetchAll('
          SELECT `id`, `action`
          FROM `'.$this->prefix.'lead_event_log`
          WHERE `lead_id` = :leadId
          AND `bundle` = "page" AND `object` = "page";', ['leadId' => $leadId]
        );
        $this->assertCount(1, $eventLogsAfterSecondVisit);
        $this->assertSame(reset($eventLogsAfterFirstVisit)['id'], reset($eventLogsAfterSecondVisit)['id']);
    }
}
