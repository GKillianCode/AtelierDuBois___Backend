<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211191329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT fk_1b3fc062de18e50b');
        $this->addSql('DROP INDEX idx_1b3fc062de18e50b');
        $this->addSql('ALTER TABLE product_review RENAME COLUMN product_id_id TO product_variant_id_id');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC06225231DF8 FOREIGN KEY (product_variant_id_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B3FC06225231DF8 ON product_review (product_variant_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT FK_1B3FC06225231DF8');
        $this->addSql('DROP INDEX IDX_1B3FC06225231DF8');
        $this->addSql('ALTER TABLE product_review RENAME COLUMN product_variant_id_id TO product_id_id');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT fk_1b3fc062de18e50b FOREIGN KEY (product_id_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1b3fc062de18e50b ON product_review (product_id_id)');
    }
}
