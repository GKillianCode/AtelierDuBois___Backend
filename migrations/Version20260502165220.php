<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502165220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT fk_1b3fc062f3893cf2');
        $this->addSql('DROP INDEX idx_1b3fc062f3893cf2');
        $this->addSql('ALTER TABLE product_review RENAME COLUMN shipment_id_id TO order_id_id');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC062FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1B3FC062FCDAEAAA ON product_review (order_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT FK_1B3FC062FCDAEAAA');
        $this->addSql('DROP INDEX IDX_1B3FC062FCDAEAAA');
        $this->addSql('ALTER TABLE product_review RENAME COLUMN order_id_id TO shipment_id_id');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT fk_1b3fc062f3893cf2 FOREIGN KEY (shipment_id_id) REFERENCES shipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1b3fc062f3893cf2 ON product_review (shipment_id_id)');
    }
}
