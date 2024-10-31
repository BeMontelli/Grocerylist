<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031111041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD current_grocery_list_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64997A567A9 FOREIGN KEY (current_grocery_list_id) REFERENCES grocery_list (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64997A567A9 ON user (current_grocery_list_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64997A567A9');
        $this->addSql('DROP INDEX IDX_8D93D64997A567A9 ON user');
        $this->addSql('ALTER TABLE user DROP current_grocery_list_id');
    }
}
