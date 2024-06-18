<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617100053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grocery_list_ingredient (grocery_list_id INT NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_D58BC3A1D059BDAB (grocery_list_id), INDEX IDX_D58BC3A1933FE08C (ingredient_id), PRIMARY KEY(grocery_list_id, ingredient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1933FE08C');
        $this->addSql('DROP TABLE grocery_list_ingredient');
    }
}
