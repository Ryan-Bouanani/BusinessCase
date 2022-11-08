<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221030224957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F819395C3F3');
        $this->addSql('DROP INDEX IDX_D4E6F819395C3F3 ON address');
        $this->addSql('ALTER TABLE address ADD last_name VARCHAR(255) NOT NULL, ADD line1 VARCHAR(255) NOT NULL, ADD line2 VARCHAR(255) DEFAULT NULL, DROP customer_id, DROP number, DROP country, CHANGE street first_name VARCHAR(255) NOT NULL, CHANGE line3 phone_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_81398E09F5B7AF75 ON customer (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD customer_id INT NOT NULL, ADD number INT DEFAULT NULL, ADD street VARCHAR(255) NOT NULL, ADD line3 VARCHAR(255) DEFAULT NULL, ADD country VARCHAR(60) NOT NULL, DROP first_name, DROP last_name, DROP phone_number, DROP line1, DROP line2');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F819395C3F3 ON address (customer_id)');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09F5B7AF75');
        $this->addSql('DROP INDEX IDX_81398E09F5B7AF75 ON customer');
        $this->addSql('ALTER TABLE customer DROP address_id');
    }
}
