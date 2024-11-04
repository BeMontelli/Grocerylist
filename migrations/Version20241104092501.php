<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104092501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1933FE08C');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD id INT AUTO_INCREMENT NOT NULL, ADD recipe_id INT DEFAULT NULL, ADD activation TINYINT(1) NOT NULL, ADD in_list TINYINT(1) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A159D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id)');
        $this->addSql('CREATE INDEX IDX_D58BC3A159D8A214 ON grocery_list_ingredient (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_ingredient MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A159D8A214');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP FOREIGN KEY FK_D58BC3A1933FE08C');
        $this->addSql('DROP INDEX IDX_D58BC3A159D8A214 ON grocery_list_ingredient');
        $this->addSql('DROP INDEX `PRIMARY` ON grocery_list_ingredient');
        $this->addSql('ALTER TABLE grocery_list_ingredient DROP id, DROP recipe_id, DROP activation, DROP in_list');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD CONSTRAINT FK_D58BC3A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_ingredient ADD PRIMARY KEY (grocery_list_id, ingredient_id)');
    }
}
