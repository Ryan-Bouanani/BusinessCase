<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728192953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_shopping_cart ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE content_shopping_cart ADD CONSTRAINT FK_2970F2E74584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_2970F2E74584665A ON content_shopping_cart (product_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADBC63F13F');
        $this->addSql('DROP INDEX IDX_D34A04ADBC63F13F ON product');
        $this->addSql('ALTER TABLE product DROP content_shopping_cart_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_shopping_cart DROP FOREIGN KEY FK_2970F2E74584665A');
        $this->addSql('DROP INDEX IDX_2970F2E74584665A ON content_shopping_cart');
        $this->addSql('ALTER TABLE content_shopping_cart DROP product_id');
        $this->addSql('ALTER TABLE product ADD content_shopping_cart_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADBC63F13F FOREIGN KEY (content_shopping_cart_id) REFERENCES content_shopping_cart (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADBC63F13F ON product (content_shopping_cart_id)');
    }
}
