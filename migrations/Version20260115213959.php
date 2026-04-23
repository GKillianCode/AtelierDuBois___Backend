<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115213959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carrier (id SERIAL NOT NULL, name VARCHAR(80) NOT NULL, tracking_url_template VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN carrier.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN carrier.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE shipment (id SERIAL NOT NULL, delivery_address_id_id INT NOT NULL, billing_address_id_id INT NOT NULL, order_id_id INT NOT NULL, status_id_id INT NOT NULL, carrier_id_id INT DEFAULT NULL, tracking_number VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CB20DC4F13AE97 ON shipment (delivery_address_id_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DC8583B8AF ON shipment (billing_address_id_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DCFCDAEAAA ON shipment (order_id_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DC881ECFA7 ON shipment (status_id_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DCCCAE5A8 ON shipment (carrier_id_id)');
        $this->addSql('COMMENT ON COLUMN shipment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN shipment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE shipment_item (id SERIAL NOT NULL, shipment_id_id INT NOT NULL, order_product_id_id INT NOT NULL, quantity INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C57340F3893CF2 ON shipment_item (shipment_id_id)');
        $this->addSql('CREATE INDEX IDX_1C57340737BADD9 ON shipment_item (order_product_id_id)');
        $this->addSql('COMMENT ON COLUMN shipment_item.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN shipment_item.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DC4F13AE97 FOREIGN KEY (delivery_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DC8583B8AF FOREIGN KEY (billing_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DCFCDAEAAA FOREIGN KEY (order_id_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DC881ECFA7 FOREIGN KEY (status_id_id) REFERENCES order_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DCCCAE5A8 FOREIGN KEY (carrier_id_id) REFERENCES carrier (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment_item ADD CONSTRAINT FK_1C57340F3893CF2 FOREIGN KEY (shipment_id_id) REFERENCES shipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shipment_item ADD CONSTRAINT FK_1C57340737BADD9 FOREIGN KEY (order_product_id_id) REFERENCES order_product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC4F13AE97');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC8583B8AF');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DCFCDAEAAA');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC881ECFA7');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DCCCAE5A8');
        $this->addSql('ALTER TABLE shipment_item DROP CONSTRAINT FK_1C57340F3893CF2');
        $this->addSql('ALTER TABLE shipment_item DROP CONSTRAINT FK_1C57340737BADD9');
        $this->addSql('DROP TABLE carrier');
        $this->addSql('DROP TABLE shipment');
        $this->addSql('DROP TABLE shipment_item');
    }
}
