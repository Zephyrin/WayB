<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190123095919 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, name FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sub_category (id, category_id, name) SELECT id, category_id, name FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('DROP INDEX IDX_5E19D16FF7BFE87C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field_def AS SELECT id, sub_category_id, type, name, is_price, is_weight FROM extra_field_def');
        $this->addSql('DROP TABLE extra_field_def');
        $this->addSql('CREATE TABLE extra_field_def (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, link_to_id INTEGER DEFAULT NULL, type INTEGER NOT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, is_price BOOLEAN NOT NULL, is_weight BOOLEAN NOT NULL, CONSTRAINT FK_5E19D16FF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5E19D16F9F8E16BB FOREIGN KEY (link_to_id) REFERENCES extra_field_def (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO extra_field_def (id, sub_category_id, type, name, is_price, is_weight) SELECT id, sub_category_id, type, name, is_price, is_weight FROM __temp__extra_field_def');
        $this->addSql('DROP TABLE __temp__extra_field_def');
        $this->addSql('CREATE INDEX IDX_5E19D16FF7BFE87C ON extra_field_def (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_5E19D16F9F8E16BB ON extra_field_def (link_to_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_5E19D16FF7BFE87C');
        $this->addSql('DROP INDEX IDX_5E19D16F9F8E16BB');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field_def AS SELECT id, sub_category_id, type, name, is_price, is_weight FROM extra_field_def');
        $this->addSql('DROP TABLE extra_field_def');
        $this->addSql('CREATE TABLE extra_field_def (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, type INTEGER NOT NULL, name VARCHAR(255) NOT NULL, is_price BOOLEAN NOT NULL, is_weight BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO extra_field_def (id, sub_category_id, type, name, is_price, is_weight) SELECT id, sub_category_id, type, name, is_price, is_weight FROM __temp__extra_field_def');
        $this->addSql('DROP TABLE __temp__extra_field_def');
        $this->addSql('CREATE INDEX IDX_5E19D16FF7BFE87C ON extra_field_def (sub_category_id)');
        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, name FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO sub_category (id, category_id, name) SELECT id, category_id, name FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
    }
}
