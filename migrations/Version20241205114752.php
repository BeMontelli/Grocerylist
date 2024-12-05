<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205114752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe ADD thumbnail_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES file (id)');
        $this->addSql('CREATE INDEX IDX_DA88B137FDFF2E92 ON recipe (thumbnail_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137FDFF2E92');
        $this->addSql('DROP INDEX IDX_DA88B137FDFF2E92 ON recipe');
        $this->addSql('ALTER TABLE recipe DROP thumbnail_id');
    }
}
