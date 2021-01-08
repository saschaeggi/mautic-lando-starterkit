<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Unit\EventListener;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Mautic\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumn;
use Mautic\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumns;
use Mautic\CoreBundle\Doctrine\Provider\GeneratedColumnsProviderInterface;
use Mautic\CoreBundle\EventListener\DoctrineGeneratedColumnsListener;
use Psr\Log\LoggerInterface;

class DoctrineGeneratedColumnsListenerTest extends \PHPUnit\Framework\TestCase
{
    private $generatedColumnsProvider;
    private $logger;
    private $event;
    private $schema;
    private $table;

    /**
     * @var DoctrineGeneratedColumnsListener
     */
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();

        defined('MAUTIC_TABLE_PREFIX') || define('MAUTIC_TABLE_PREFIX', getenv('MAUTIC_DB_PREFIX') ?: '');

        $this->generatedColumnsProvider = $this->createMock(GeneratedColumnsProviderInterface::class);
        $this->logger                   = $this->createMock(LoggerInterface::class);
        $this->event                    = $this->createMock(GenerateSchemaEventArgs::class);
        $this->schema                   = $this->createMock(Schema::class);
        $this->table                    = $this->createMock(Table::class);
        $this->listener                 = new DoctrineGeneratedColumnsListener($this->generatedColumnsProvider, $this->logger);

        $generatedColumn  = new GeneratedColumn('page_hits', 'generated_hit_date', 'DATE', 'not important');
        $generatedColumns = new GeneratedColumns();

        $generatedColumns->add($generatedColumn);

        $this->generatedColumnsProvider->method('getGeneratedColumns')->willReturn($generatedColumns);
        $this->event->method('getSchema')->willReturn($this->schema);
    }

    public function testPostGenerateSchemaWhenTableDoesNotExist()
    {
        $this->schema->expects($this->once())
            ->method('hasTable')
            ->with('page_hits')
            ->willReturn(false);

        $this->schema->expects($this->never())
            ->method('getTable');

        $this->listener->postGenerateSchema($this->event);
    }

    public function testPostGenerateSchemaWhenColumnExists()
    {
        $this->schema->expects($this->once())
            ->method('hasTable')
            ->with('page_hits')
            ->willReturn(true);

        $this->schema->expects($this->once())
            ->method('getTable')
            ->with('page_hits')
            ->willReturn($this->table);

        $this->table->expects($this->once())
            ->method('hasColumn')
            ->with('generated_hit_date')
            ->willReturn(true);

        $this->table->expects($this->never())
            ->method('addColumn');

        $this->listener->postGenerateSchema($this->event);
    }

    public function testPostGenerateSchemaWhenColumnDoesNotExist()
    {
        $this->schema->expects($this->once())
            ->method('hasTable')
            ->with('page_hits')
            ->willReturn(true);

        $this->schema->expects($this->once())
            ->method('getTable')
            ->with('page_hits')
            ->willReturn($this->table);

        $this->table->expects($this->once())
            ->method('hasColumn')
            ->with('generated_hit_date')
            ->willReturn(false);

        $this->table->expects($this->once())
            ->method('addColumn')
            ->with('generated_hit_date');

        $this->table->expects($this->once())
            ->method('addIndex')
            ->with(['generated_hit_date'], MAUTIC_TABLE_PREFIX.'generated_hit_date');

        $this->listener->postGenerateSchema($this->event);
    }
}
