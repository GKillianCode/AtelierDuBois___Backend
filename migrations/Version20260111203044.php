<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout de la colonne 'code' à la table order_status
 */
final class Version20260111203044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add code column to order_status table';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne code (nullable temporairement)
        $this->addSql('ALTER TABLE order_status ADD code VARCHAR(50) DEFAULT NULL');

        // Rendre la colonne NOT NULL et unique
        $this->addSql('ALTER TABLE order_status ALTER COLUMN code SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B88F75C977153098 ON order_status (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_B88F75C977153098');
        $this->addSql('ALTER TABLE order_status DROP code');
    }
}
