<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add physical dimensions and stacking properties to product table
 */
final class Version20260114191317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add weight_in_grams, length_in_centimeters, width_in_centimeters, height_in_centimeters and max_stack_size to product table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD weight_in_grams INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD length_in_centimeters INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD width_in_centimeters INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD height_in_centimeters INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD max_stack_size SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP weight_in_grams');
        $this->addSql('ALTER TABLE product DROP length_in_centimeters');
        $this->addSql('ALTER TABLE product DROP width_in_centimeters');
        $this->addSql('ALTER TABLE product DROP height_in_centimeters');
        $this->addSql('ALTER TABLE product DROP max_stack_size');
    }
}
