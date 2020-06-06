<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605233943 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE into_backpack ADD backpack_id INT NOT NULL');
        $this->addSql('ALTER TABLE into_backpack ADD CONSTRAINT FK_ED9A830931009DBE FOREIGN KEY (backpack_id) REFERENCES backpack (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_ED9A830931009DBE ON into_backpack (backpack_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE into_backpack DROP CONSTRAINT FK_ED9A830931009DBE');
        $this->addSql('DROP INDEX IDX_ED9A830931009DBE');
        $this->addSql('ALTER TABLE into_backpack DROP backpack_id');
    }
}
