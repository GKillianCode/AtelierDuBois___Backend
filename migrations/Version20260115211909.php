<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115211909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT fk_f52993984f13ae97');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT fk_f52993988583b8af');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT fk_f5299398881ecfa7');
        $this->addSql('DROP INDEX idx_f52993984f13ae97');
        $this->addSql('DROP INDEX idx_f52993988583b8af');
        $this->addSql('DROP INDEX idx_f5299398881ecfa7');
        $this->addSql('ALTER TABLE "order" ADD total_price INT NOT NULL');
        $this->addSql('ALTER TABLE "order" DROP delivery_address_id_id');
        $this->addSql('ALTER TABLE "order" DROP billing_address_id_id');
        $this->addSql('ALTER TABLE "order" DROP status_id_id');
        $this->addSql('ALTER TABLE "order" DROP tracking_number');
        $this->addSql('ALTER TABLE "order" DROP order_number');
        $this->addSql('ALTER TABLE product ALTER weight_in_grams SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER length_in_centimeters TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE product ALTER length_in_centimeters SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER width_in_centimeters SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER height_in_centimeters SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER max_stack_size SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product ALTER weight_in_grams DROP NOT NULL');
        $this->addSql('ALTER TABLE product ALTER length_in_centimeters TYPE INT');
        $this->addSql('ALTER TABLE product ALTER length_in_centimeters DROP NOT NULL');
        $this->addSql('ALTER TABLE product ALTER width_in_centimeters DROP NOT NULL');
        $this->addSql('ALTER TABLE product ALTER height_in_centimeters DROP NOT NULL');
        $this->addSql('ALTER TABLE product ALTER max_stack_size DROP NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD billing_address_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD status_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD tracking_number VARCHAR(22) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD order_number VARCHAR(17) NOT NULL');
        $this->addSql('ALTER TABLE "order" RENAME COLUMN total_price TO delivery_address_id_id');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT fk_f52993984f13ae97 FOREIGN KEY (delivery_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT fk_f52993988583b8af FOREIGN KEY (billing_address_id_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT fk_f5299398881ecfa7 FOREIGN KEY (status_id_id) REFERENCES order_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f52993984f13ae97 ON "order" (delivery_address_id_id)');
        $this->addSql('CREATE INDEX idx_f52993988583b8af ON "order" (billing_address_id_id)');
        $this->addSql('CREATE INDEX idx_f5299398881ecfa7 ON "order" (status_id_id)');
    }
}
