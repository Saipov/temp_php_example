<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200320145802 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catalog (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, productions_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, is_active TINYINT(1) DEFAULT NULL, is_new TINYINT(1) DEFAULT NULL, is_sale TINYINT(1) DEFAULT NULL, anonce LONGTEXT DEFAULT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_1B2C324712469DE2 (category_id), INDEX IDX_1B2C32472727179C (productions_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C324712469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C32472727179C FOREIGN KEY (productions_id) REFERENCES productions (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE catalog');
    }
}
