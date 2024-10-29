<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029094418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_recipe DROP FOREIGN KEY FK_B8F4F0934584665A');
        $this->addSql('ALTER TABLE product_recipe DROP FOREIGN KEY FK_B8F4F09359D8A214');
        $this->addSql('DROP TABLE product_recipe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_recipe (product_id INT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_B8F4F0934584665A (product_id), INDEX IDX_B8F4F09359D8A214 (recipe_id), PRIMARY KEY(product_id, recipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_recipe ADD CONSTRAINT FK_B8F4F0934584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_recipe ADD CONSTRAINT FK_B8F4F09359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
