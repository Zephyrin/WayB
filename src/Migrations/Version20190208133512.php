<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190208133512 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE have (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, equipment_id INTEGER NOT NULL, own_quantity INTEGER NOT NULL, want_quantity INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_D27EEA40A76ED395 ON have (user_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40517FE9FE ON have (equipment_id)');
        $this->addSql('DROP INDEX IDX_D338D583F7BFE87C');
        $this->addSql('DROP INDEX IDX_D338D58344F5D008');
        $this->addSql('CREATE TEMPORARY TABLE __temp__equipment AS SELECT id, sub_category_id, brand_id, name, description FROM equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('CREATE TABLE equipment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, brand_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB NOT NULL COLLATE BINARY, CONSTRAINT FK_D338D583F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D338D58344F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO equipment (id, sub_category_id, brand_id, name, description) SELECT id, sub_category_id, brand_id, name, description FROM __temp__equipment');
        $this->addSql('DROP TABLE __temp__equipment');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, name FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sub_category (id, category_id, name) SELECT id, category_id, name FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('DROP INDEX IDX_5E19D16FF7BFE87C');
        $this->addSql('DROP INDEX IDX_5E19D16F9F8E16BB');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field_def AS SELECT id, sub_category_id, link_to_id, name, is_price, is_weight, type FROM extra_field_def');
        $this->addSql('DROP TABLE extra_field_def');
        $this->addSql('CREATE TABLE extra_field_def (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, link_to_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, is_price BOOLEAN NOT NULL, is_weight BOOLEAN NOT NULL, type VARCHAR(20) NOT NULL COLLATE BINARY, CONSTRAINT FK_5E19D16FF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5E19D16F9F8E16BB FOREIGN KEY (link_to_id) REFERENCES extra_field_def (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO extra_field_def (id, sub_category_id, link_to_id, name, is_price, is_weight, type) SELECT id, sub_category_id, link_to_id, name, is_price, is_weight, type FROM __temp__extra_field_def');
        $this->addSql('DROP TABLE __temp__extra_field_def');
        $this->addSql('CREATE INDEX IDX_5E19D16FF7BFE87C ON extra_field_def (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_5E19D16F9F8E16BB ON extra_field_def (link_to_id)');
        $this->addSql('DROP INDEX IDX_A16E05F8517FE9FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field AS SELECT id, equipment_id, type, value, name, is_weight, is_price FROM extra_field');
        $this->addSql('DROP TABLE extra_field');
        $this->addSql('CREATE TABLE extra_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, type VARCHAR(6) NOT NULL COLLATE BINARY, value CLOB NOT NULL COLLATE BINARY --(DC2Type:object)
        , name VARCHAR(255) NOT NULL COLLATE BINARY, is_weight BOOLEAN NOT NULL, is_price BOOLEAN NOT NULL, CONSTRAINT FK_A16E05F8517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO extra_field (id, equipment_id, type, value, name, is_weight, is_price) SELECT id, equipment_id, type, value, name, is_weight, is_price FROM __temp__extra_field');
        $this->addSql('DROP TABLE __temp__extra_field');
        $this->addSql('CREATE INDEX IDX_A16E05F8517FE9FE ON extra_field (equipment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE have');
        $this->addSql('DROP INDEX IDX_D338D583F7BFE87C');
        $this->addSql('DROP INDEX IDX_D338D58344F5D008');
        $this->addSql('CREATE TEMPORARY TABLE __temp__equipment AS SELECT id, sub_category_id, brand_id, name, description FROM equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('CREATE TABLE equipment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, brand_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL)');
        $this->addSql('INSERT INTO equipment (id, sub_category_id, brand_id, name, description) SELECT id, sub_category_id, brand_id, name, description FROM __temp__equipment');
        $this->addSql('DROP TABLE __temp__equipment');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('DROP INDEX IDX_A16E05F8517FE9FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field AS SELECT id, equipment_id, type, value, name, is_weight, is_price FROM extra_field');
        $this->addSql('DROP TABLE extra_field');
        $this->addSql('CREATE TABLE extra_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, type VARCHAR(6) NOT NULL, value CLOB NOT NULL --(DC2Type:object)
        , name VARCHAR(255) NOT NULL, is_weight BOOLEAN NOT NULL, is_price BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO extra_field (id, equipment_id, type, value, name, is_weight, is_price) SELECT id, equipment_id, type, value, name, is_weight, is_price FROM __temp__extra_field');
        $this->addSql('DROP TABLE __temp__extra_field');
        $this->addSql('CREATE INDEX IDX_A16E05F8517FE9FE ON extra_field (equipment_id)');
        $this->addSql('DROP INDEX IDX_5E19D16FF7BFE87C');
        $this->addSql('DROP INDEX IDX_5E19D16F9F8E16BB');
        $this->addSql('CREATE TEMPORARY TABLE __temp__extra_field_def AS SELECT id, sub_category_id, link_to_id, type, name, is_price, is_weight FROM extra_field_def');
        $this->addSql('DROP TABLE extra_field_def');
        $this->addSql('CREATE TABLE extra_field_def (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, link_to_id INTEGER DEFAULT NULL, type VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, is_price BOOLEAN NOT NULL, is_weight BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO extra_field_def (id, sub_category_id, link_to_id, type, name, is_price, is_weight) SELECT id, sub_category_id, link_to_id, type, name, is_price, is_weight FROM __temp__extra_field_def');
        $this->addSql('DROP TABLE __temp__extra_field_def');
        $this->addSql('CREATE INDEX IDX_5E19D16FF7BFE87C ON extra_field_def (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_5E19D16F9F8E16BB ON extra_field_def (link_to_id)');
        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, name FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO sub_category (id, category_id, name) SELECT id, category_id, name FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
    }
}
