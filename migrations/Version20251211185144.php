<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211185144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_review (id SERIAL NOT NULL, user_id_id INT NOT NULL, product_id_id INT NOT NULL, rating SMALLINT NOT NULL, comment VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B3FC0629D86650F ON product_review (user_id_id)');
        $this->addSql('CREATE INDEX IDX_1B3FC062DE18E50B ON product_review (product_id_id)');
        $this->addSql('COMMENT ON COLUMN product_review.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product_review.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC0629D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC062DE18E50B FOREIGN KEY (product_id_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT FK_1B3FC0629D86650F');
        $this->addSql('ALTER TABLE product_review DROP CONSTRAINT FK_1B3FC062DE18E50B');
        $this->addSql('DROP TABLE product_review');
    }
}
