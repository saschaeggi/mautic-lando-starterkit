<?php

/*
 * @package     Mautic
 * @copyright   2019 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191017140848 extends AbstractMauticMigration
{
    /**
     * @throws SkipMigration
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema): void
    {
        $smsStatsTable = $schema->getTable(MAUTIC_TABLE_PREFIX.'sms_message_stats');
        if ($smsStatsTable->hasColumn('is_failed') && $smsStatsTable->hasColumn('details')) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $smsStatsTable = $schema->getTable(MAUTIC_TABLE_PREFIX.'sms_message_stats');
        if (!$smsStatsTable->hasColumn('is_failed')) {
            $this->addSql('ALTER TABLE '.$this->prefix.'sms_message_stats ADD is_failed TINYINT(1) DEFAULT NULL');
            $this->addSql("UPDATE {$this->prefix}sms_message_stats SET is_failed = '0'");
            $this->addSql("CREATE INDEX {$this->prefix}stat_sms_failed_search ON {$this->prefix}sms_message_stats (is_failed)");
        }

        if (!$smsStatsTable->hasColumn('details')) {
            $this->addSql("ALTER TABLE {$this->prefix}sms_message_stats ADD details LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)';");
        }
    }
}
