<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029092027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6DC044C5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_group_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D34A04ADA76ED395 (user_id), INDEX IDX_D34A04AD35E4B3D0 (product_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_recipe (product_id INT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_B8F4F0934584665A (product_id), INDEX IDX_B8F4F09359D8A214 (recipe_id), PRIMARY KEY(product_id, recipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_grocery_list (product_id INT NOT NULL, grocery_list_id INT NOT NULL, INDEX IDX_59E924E74584665A (product_id), INDEX IDX_59E924E7D059BDAB (grocery_list_id), PRIMARY KEY(product_id, grocery_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD35E4B3D0 FOREIGN KEY (product_group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE product_recipe ADD CONSTRAINT FK_B8F4F0934584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_recipe ADD CONSTRAINT FK_B8F4F09359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_grocery_list ADD CONSTRAINT FK_59E924E74584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_grocery_list ADD CONSTRAINT FK_59E924E7D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5A76ED395');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA76ED395');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD35E4B3D0');
        $this->addSql('ALTER TABLE product_recipe DROP FOREIGN KEY FK_B8F4F0934584665A');
        $this->addSql('ALTER TABLE product_recipe DROP FOREIGN KEY FK_B8F4F09359D8A214');
        $this->addSql('ALTER TABLE product_grocery_list DROP FOREIGN KEY FK_59E924E74584665A');
        $this->addSql('ALTER TABLE product_grocery_list DROP FOREIGN KEY FK_59E924E7D059BDAB');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_recipe');
        $this->addSql('DROP TABLE product_grocery_list');
    }
}
