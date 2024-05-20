<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520135317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list DROP user_id');
        $this->addSql('ALTER TABLE ingredient ADD section_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('CREATE INDEX IDX_6BAF7870D823E37A ON ingredient (section_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870D823E37A');
        $this->addSql('DROP INDEX IDX_6BAF7870D823E37A ON ingredient');
        $this->addSql('ALTER TABLE ingredient DROP section_id');
    }
}
