<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728144433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD customer_id INT NOT NULL, ADD number INT DEFAULT NULL, ADD street VARCHAR(255) NOT NULL, DROP line1, DROP line2');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F819395C3F3 ON address (customer_id)');
        $this->addSql('ALTER TABLE brand CHANGE name label VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE content_shopping_cart ADD basket_id INT NOT NULL');
        $this->addSql('ALTER TABLE content_shopping_cart ADD CONSTRAINT FK_2970F2E71BE1FB52 FOREIGN KEY (basket_id) REFERENCES basket (id)');
        $this->addSql('CREATE INDEX IDX_2970F2E71BE1FB52 ON content_shopping_cart (basket_id)');
        $this->addSql('ALTER TABLE image ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C53D045F4584665A ON image (product_id)');
        $this->addSql('ALTER TABLE `order` ADD status_id INT NOT NULL, ADD basket_id INT NOT NULL, ADD mean_of_payment_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993986BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993981BE1FB52 FOREIGN KEY (basket_id) REFERENCES basket (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985F286933 FOREIGN KEY (mean_of_payment_id) REFERENCES mean_of_payment (id)');
        $this->addSql('CREATE INDEX IDX_F52993986BF700BD ON `order` (status_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993981BE1FB52 ON `order` (basket_id)');
        $this->addSql('CREATE INDEX IDX_F52993985F286933 ON `order` (mean_of_payment_id)');
        $this->addSql('ALTER TABLE product ADD brand_id INT NOT NULL, ADD promotion_id INT NOT NULL, ADD category_id INT NOT NULL, ADD content_shopping_cart_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADBC63F13F FOREIGN KEY (content_shopping_cart_id) REFERENCES content_shopping_cart (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD44F5D008 ON product (brand_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD139DF194 ON product (promotion_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADBC63F13F ON product (content_shopping_cart_id)');
        $this->addSql('ALTER TABLE review ADD product_id INT NOT NULL, ADD customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C69395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_794381C64584665A ON review (product_id)');
        $this->addSql('CREATE INDEX IDX_794381C69395C3F3 ON review (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F819395C3F3');
        $this->addSql('DROP INDEX IDX_D4E6F819395C3F3 ON address');
        $this->addSql('ALTER TABLE address ADD line2 VARCHAR(255) NOT NULL, DROP customer_id, DROP number, CHANGE street line1 VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE brand CHANGE label name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE content_shopping_cart DROP FOREIGN KEY FK_2970F2E71BE1FB52');
        $this->addSql('DROP INDEX IDX_2970F2E71BE1FB52 ON content_shopping_cart');
        $this->addSql('ALTER TABLE content_shopping_cart DROP basket_id');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A');
        $this->addSql('DROP INDEX IDX_C53D045F4584665A ON image');
        $this->addSql('ALTER TABLE image DROP product_id');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993986BF700BD');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993981BE1FB52');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993985F286933');
        $this->addSql('DROP INDEX IDX_F52993986BF700BD ON `order`');
        $this->addSql('DROP INDEX UNIQ_F52993981BE1FB52 ON `order`');
        $this->addSql('DROP INDEX IDX_F52993985F286933 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP status_id, DROP basket_id, DROP mean_of_payment_id');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD139DF194');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADBC63F13F');
        $this->addSql('DROP INDEX IDX_D34A04AD44F5D008 ON product');
        $this->addSql('DROP INDEX IDX_D34A04AD139DF194 ON product');
        $this->addSql('DROP INDEX IDX_D34A04AD12469DE2 ON product');
        $this->addSql('DROP INDEX IDX_D34A04ADBC63F13F ON product');
        $this->addSql('ALTER TABLE product DROP brand_id, DROP promotion_id, DROP category_id, DROP content_shopping_cart_id');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64584665A');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C69395C3F3');
        $this->addSql('DROP INDEX IDX_794381C64584665A ON review');
        $this->addSql('DROP INDEX IDX_794381C69395C3F3 ON review');
        $this->addSql('ALTER TABLE review DROP product_id, DROP customer_id');
    }
}
