<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030101418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grocery_list_recipe (grocery_list_id INT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_B3018719D059BDAB (grocery_list_id), INDEX IDX_B301871959D8A214 (recipe_id), PRIMARY KEY(grocery_list_id, recipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE grocery_list_recipe ADD CONSTRAINT FK_B3018719D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_recipe ADD CONSTRAINT FK_B301871959D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list DROP recipes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_recipe DROP FOREIGN KEY FK_B3018719D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_recipe DROP FOREIGN KEY FK_B301871959D8A214');
        $this->addSql('DROP TABLE grocery_list_recipe');
        $this->addSql('ALTER TABLE grocery_list ADD recipes JSON DEFAULT NULL');
    }
}
