<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502132413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_review ADD shipment_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC062F3893CF2 FOREIGN KEY (shipment_id_id) REFERENCES shipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B3FC062F3893CF2 ON product_review (shipment_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT FK_1B3FC062F3893CF2');
        $this->addSql('DROP INDEX IDX_1B3FC062F3893CF2');
        $this->addSql('ALTER TABLE product_review DROP shipment_id_id');
    }
}
