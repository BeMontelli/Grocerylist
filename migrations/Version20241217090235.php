<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217090235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137FDFF2E92');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES file (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE section ADD position INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP position');
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137FDFF2E92');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE section DROP position');
    }
}
