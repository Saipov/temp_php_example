<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200323101637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE technologies ADD image_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE categories ADD image_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE works ADD image_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE catalog ADD image_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE catalog DROP image_name');
        $this->addSql('ALTER TABLE categories DROP image_name');
        $this->addSql('ALTER TABLE technologies DROP image_name');
        $this->addSql('ALTER TABLE works DROP image_name');
    }
}
