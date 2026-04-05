<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404105234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE carrier ADD technical_name VARCHAR(50) DEFAULT NULL');

        $this->addSql('ALTER TABLE carrier ALTER technical_name SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CARRIER_TECHNICAL_NAME ON carrier (technical_name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_CARRIER_TECHNICAL_NAME');
        $this->addSql('ALTER TABLE carrier DROP technical_name');
    }
}
