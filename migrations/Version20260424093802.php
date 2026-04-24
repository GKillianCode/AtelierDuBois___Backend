<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424093802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD company_name VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER INDEX uniq_carrier_technical_name RENAME TO UNIQ_4739F11C72263045');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE address DROP company_name');
        $this->addSql('ALTER INDEX uniq_4739f11c72263045 RENAME TO uniq_carrier_technical_name');
    }
}
