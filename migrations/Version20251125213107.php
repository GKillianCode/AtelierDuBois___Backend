<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125213107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ALTER folder_name TYPE VARCHAR(22)');
        $this->addSql('ALTER TABLE image ALTER image_name TYPE VARCHAR(22)');
        $this->addSql('ALTER TABLE image ALTER format TYPE VARCHAR(4)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image ALTER folder_name TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE image ALTER image_name TYPE VARCHAR(10)');
        $this->addSql('ALTER TABLE image ALTER format TYPE VARCHAR(15)');
    }
}
