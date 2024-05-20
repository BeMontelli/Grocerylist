<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520143344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE grocery_list ADD CONSTRAINT FK_D44D068CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D44D068CA76ED395 ON grocery_list (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list DROP FOREIGN KEY FK_D44D068CA76ED395');
        $this->addSql('DROP INDEX IDX_D44D068CA76ED395 ON grocery_list');
        $this->addSql('ALTER TABLE grocery_list DROP user_id');
    }
}
