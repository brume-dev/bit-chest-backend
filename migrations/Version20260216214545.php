<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216214545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, abbreviation VARCHAR(10) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE price (id INT AUTO_INCREMENT NOT NULL, value NUMERIC(20, 8) NOT NULL, date DATETIME NOT NULL, crypto_id INT NOT NULL, INDEX IDX_CAC822D9E9571A63 (crypto_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, amount NUMERIC(20, 8) NOT NULL, date DATETIME NOT NULL, user_id INT NOT NULL, crypto_id INT NOT NULL, price_id INT NOT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D1E9571A63 (crypto_id), INDEX IDX_723705D1D614C7E7 (price_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, balance NUMERIC(10, 2) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1D614C7E7 FOREIGN KEY (price_id) REFERENCES price (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D9E9571A63');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1E9571A63');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1D614C7E7');
        $this->addSql('DROP TABLE crypto');
        $this->addSql('DROP TABLE price');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE user');
    }
}
