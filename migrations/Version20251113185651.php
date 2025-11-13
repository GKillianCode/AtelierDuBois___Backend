<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113185651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id SERIAL NOT NULL, product_variant_id_id INT NOT NULL, folder_name VARCHAR(50) NOT NULL, image_name VARCHAR(10) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C53D045F25231DF8 ON image (product_variant_id_id)');
        $this->addSql('COMMENT ON COLUMN image.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN image.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, user_id_id INT NOT NULL, delivery_address_id_id INT NOT NULL, billing_address_id_id INT NOT NULL, status_id_id INT NOT NULL, tracking_number VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F52993989D86650F ON "order" (user_id_id)');
        $this->addSql('CREATE INDEX IDX_F52993984F13AE97 ON "order" (delivery_address_id_id)');
        $this->addSql('CREATE INDEX IDX_F52993988583B8AF ON "order" (billing_address_id_id)');
        $this->addSql('CREATE INDEX IDX_F5299398881ECFA7 ON "order" (status_id_id)');
        $this->addSql('COMMENT ON COLUMN "order".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "order".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE order_product (id SERIAL NOT NULL, order_id_id INT NOT NULL, product_variant_id_id INT NOT NULL, price INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2530ADE6FCDAEAAA ON order_product (order_id_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE625231DF8 ON order_product (product_variant_id_id)');
        $this->addSql('COMMENT ON COLUMN order_product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN order_product.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE order_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN order_status.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN order_status.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product (id SERIAL NOT NULL, name VARCHAR(150) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product_variant (id SERIAL NOT NULL, product_id_id INT NOT NULL, wood_id_id INT NOT NULL, uuid UUID NOT NULL, public_url VARCHAR(100) NOT NULL, is_default BOOLEAN NOT NULL, price INT DEFAULT NULL, stock INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_209AA41DDE18E50B ON product_variant (product_id_id)');
        $this->addSql('CREATE INDEX IDX_209AA41D4649B16B ON product_variant (wood_id_id)');
        $this->addSql('COMMENT ON COLUMN product_variant.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_variant.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product_variant.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE wood (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN wood.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN wood.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F25231DF8 FOREIGN KEY (product_variant_id_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993989D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993984F13AE97 FOREIGN KEY (delivery_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993988583B8AF FOREIGN KEY (billing_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398881ECFA7 FOREIGN KEY (status_id_id) REFERENCES order_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE6FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE625231DF8 FOREIGN KEY (product_variant_id_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41DDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4649B16B FOREIGN KEY (wood_id_id) REFERENCES wood (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP birth_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045F25231DF8');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993989D86650F');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993984F13AE97');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993988583B8AF');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398881ECFA7');
        $this->addSql('ALTER TABLE order_product DROP CONSTRAINT FK_2530ADE6FCDAEAAA');
        $this->addSql('ALTER TABLE order_product DROP CONSTRAINT FK_2530ADE625231DF8');
        $this->addSql('ALTER TABLE product_variant DROP CONSTRAINT FK_209AA41DDE18E50B');
        $this->addSql('ALTER TABLE product_variant DROP CONSTRAINT FK_209AA41D4649B16B');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_product');
        $this->addSql('DROP TABLE order_status');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_variant');
        $this->addSql('DROP TABLE wood');
        $this->addSql('ALTER TABLE "user" ADD birth_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".birth_date IS \'(DC2Type:datetime_immutable)\'');
    }
}
