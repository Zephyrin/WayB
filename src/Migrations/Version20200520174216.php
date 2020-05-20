<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200520174216 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_path VARCHAR(255) DEFAULT NULL, description VARCHAR(1024) NOT NULL)');
        $this->addSql('DROP INDEX IDX_BCE3F798DE12AB56');
        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, created_by, name, validate, ask_validate FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL, CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BCE3F798DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sub_category (id, category_id, created_by, name, validate, ask_validate) SELECT id, category_id, created_by, name, validate, ask_validate FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F798DE12AB56 ON sub_category (created_by)');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('DROP INDEX IDX_ED9A8309517FE9FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__into_backpack AS SELECT id, equipment_id, count FROM into_backpack');
        $this->addSql('DROP TABLE into_backpack');
        $this->addSql('CREATE TABLE into_backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_ED9A8309517FE9FE FOREIGN KEY (equipment_id) REFERENCES have (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO into_backpack (id, equipment_id, count) SELECT id, equipment_id, count FROM __temp__into_backpack');
        $this->addSql('DROP TABLE __temp__into_backpack');
        $this->addSql('CREATE INDEX IDX_ED9A8309517FE9FE ON into_backpack (equipment_id)');
        $this->addSql('DROP INDEX IDX_522FA950DE12AB56');
        $this->addSql('DROP INDEX IDX_522FA950517FE9FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__characteristic AS SELECT id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate FROM characteristic');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('CREATE TABLE characteristic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, gender VARCHAR(6) NOT NULL COLLATE BINARY, size VARCHAR(25) NOT NULL COLLATE BINARY, price DOUBLE PRECISION NOT NULL, weight INTEGER NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL, CONSTRAINT FK_522FA950517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_522FA950DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO characteristic (id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate) SELECT id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate FROM __temp__characteristic');
        $this->addSql('DROP TABLE __temp__characteristic');
        $this->addSql('CREATE INDEX IDX_522FA950DE12AB56 ON characteristic (created_by)');
        $this->addSql('CREATE INDEX IDX_522FA950517FE9FE ON characteristic (equipment_id)');
        $this->addSql('DROP INDEX IDX_D338D583DE12AB56');
        $this->addSql('DROP INDEX IDX_D338D58344F5D008');
        $this->addSql('DROP INDEX IDX_D338D583F7BFE87C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__equipment AS SELECT id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate FROM equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('CREATE TABLE equipment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, brand_id INTEGER DEFAULT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB NOT NULL COLLATE BINARY, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL, CONSTRAINT FK_D338D583F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D338D58344F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D338D583DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO equipment (id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate) SELECT id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate FROM __temp__equipment');
        $this->addSql('DROP TABLE __temp__equipment');
        $this->addSql('CREATE INDEX IDX_D338D583DE12AB56 ON equipment (created_by)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('DROP INDEX IDX_C358569DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__backpack AS SELECT id, created_by, name FROM backpack');
        $this->addSql('DROP TABLE backpack');
        $this->addSql('CREATE TABLE backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_C358569DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO backpack (id, created_by, name) SELECT id, created_by, name FROM __temp__backpack');
        $this->addSql('DROP TABLE __temp__backpack');
        $this->addSql('CREATE INDEX IDX_C358569DE12AB56 ON backpack (created_by)');
        $this->addSql('DROP INDEX IDX_1C52F958DE12AB56');
        $this->addSql('DROP INDEX UNIQ_1C52F958841CB121');
        $this->addSql('DROP INDEX UNIQ_1C52F9585E237E06');
        $this->addSql('CREATE TEMPORARY TABLE __temp__brand AS SELECT id, created_by, name, uri, validate, ask_validate FROM brand');
        $this->addSql('DROP TABLE brand');
        $this->addSql('CREATE TABLE brand (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, logo_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, uri VARCHAR(255) NOT NULL COLLATE BINARY, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL, CONSTRAINT FK_1C52F958F98F144A FOREIGN KEY (logo_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C52F958DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO brand (id, created_by, name, uri, validate, ask_validate) SELECT id, created_by, name, uri, validate, ask_validate FROM __temp__brand');
        $this->addSql('DROP TABLE __temp__brand');
        $this->addSql('CREATE INDEX IDX_1C52F958DE12AB56 ON brand (created_by)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F958841CB121 ON brand (uri)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F9585E237E06 ON brand (name)');
        $this->addSql('CREATE INDEX IDX_1C52F958F98F144A ON brand (logo_id)');
        $this->addSql('DROP INDEX IDX_64C19C1DE12AB56');
        $this->addSql('DROP INDEX UNIQ_64C19C15E237E06');
        $this->addSql('CREATE TEMPORARY TABLE __temp__category AS SELECT id, created_by, name, validate, ask_validate FROM category');
        $this->addSql('DROP TABLE category');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL, CONSTRAINT FK_64C19C1DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO category (id, created_by, name, validate, ask_validate) SELECT id, created_by, name, validate, ask_validate FROM __temp__category');
        $this->addSql('DROP TABLE __temp__category');
        $this->addSql('CREATE INDEX IDX_64C19C1DE12AB56 ON category (created_by)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('DROP INDEX IDX_D27EEA40DEE9D12B');
        $this->addSql('DROP INDEX IDX_D27EEA40517FE9FE');
        $this->addSql('DROP INDEX IDX_D27EEA40A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__have AS SELECT id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity FROM have');
        $this->addSql('DROP TABLE have');
        $this->addSql('CREATE TABLE have (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, equipment_id INTEGER NOT NULL, characteristic_id INTEGER DEFAULT NULL, own_quantity INTEGER NOT NULL, want_quantity INTEGER NOT NULL, CONSTRAINT FK_D27EEA40A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D27EEA40517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D27EEA40DEE9D12B FOREIGN KEY (characteristic_id) REFERENCES characteristic (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO have (id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity) SELECT id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity FROM __temp__have');
        $this->addSql('DROP TABLE __temp__have');
        $this->addSql('CREATE INDEX IDX_D27EEA40DEE9D12B ON have (characteristic_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40517FE9FE ON have (equipment_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40A76ED395 ON have (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE media_object');
        $this->addSql('DROP INDEX IDX_C358569DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__backpack AS SELECT id, created_by, name FROM backpack');
        $this->addSql('DROP TABLE backpack');
        $this->addSql('CREATE TABLE backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO backpack (id, created_by, name) SELECT id, created_by, name FROM __temp__backpack');
        $this->addSql('DROP TABLE __temp__backpack');
        $this->addSql('CREATE INDEX IDX_C358569DE12AB56 ON backpack (created_by)');
        $this->addSql('DROP INDEX UNIQ_1C52F9585E237E06');
        $this->addSql('DROP INDEX UNIQ_1C52F958841CB121');
        $this->addSql('DROP INDEX IDX_1C52F958F98F144A');
        $this->addSql('DROP INDEX IDX_1C52F958DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__brand AS SELECT id, created_by, name, uri, validate, ask_validate FROM brand');
        $this->addSql('DROP TABLE brand');
        $this->addSql('CREATE TABLE brand (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, uri VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('INSERT INTO brand (id, created_by, name, uri, validate, ask_validate) SELECT id, created_by, name, uri, validate, ask_validate FROM __temp__brand');
        $this->addSql('DROP TABLE __temp__brand');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F9585E237E06 ON brand (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F958841CB121 ON brand (uri)');
        $this->addSql('CREATE INDEX IDX_1C52F958DE12AB56 ON brand (created_by)');
        $this->addSql('DROP INDEX UNIQ_64C19C15E237E06');
        $this->addSql('DROP INDEX IDX_64C19C1DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__category AS SELECT id, created_by, name, validate, ask_validate FROM category');
        $this->addSql('DROP TABLE category');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('INSERT INTO category (id, created_by, name, validate, ask_validate) SELECT id, created_by, name, validate, ask_validate FROM __temp__category');
        $this->addSql('DROP TABLE __temp__category');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE INDEX IDX_64C19C1DE12AB56 ON category (created_by)');
        $this->addSql('DROP INDEX IDX_522FA950517FE9FE');
        $this->addSql('DROP INDEX IDX_522FA950DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__characteristic AS SELECT id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate FROM characteristic');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('CREATE TABLE characteristic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, gender VARCHAR(6) NOT NULL, size VARCHAR(25) NOT NULL, price DOUBLE PRECISION NOT NULL, weight INTEGER NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('INSERT INTO characteristic (id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate) SELECT id, equipment_id, created_by, gender, size, price, weight, validate, ask_validate FROM __temp__characteristic');
        $this->addSql('DROP TABLE __temp__characteristic');
        $this->addSql('CREATE INDEX IDX_522FA950517FE9FE ON characteristic (equipment_id)');
        $this->addSql('CREATE INDEX IDX_522FA950DE12AB56 ON characteristic (created_by)');
        $this->addSql('DROP INDEX IDX_D338D583F7BFE87C');
        $this->addSql('DROP INDEX IDX_D338D58344F5D008');
        $this->addSql('DROP INDEX IDX_D338D583DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__equipment AS SELECT id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate FROM equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('CREATE TABLE equipment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, brand_id INTEGER DEFAULT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('INSERT INTO equipment (id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate) SELECT id, sub_category_id, brand_id, created_by, name, description, validate, ask_validate FROM __temp__equipment');
        $this->addSql('DROP TABLE __temp__equipment');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('CREATE INDEX IDX_D338D583DE12AB56 ON equipment (created_by)');
        $this->addSql('DROP INDEX IDX_D27EEA40A76ED395');
        $this->addSql('DROP INDEX IDX_D27EEA40517FE9FE');
        $this->addSql('DROP INDEX IDX_D27EEA40DEE9D12B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__have AS SELECT id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity FROM have');
        $this->addSql('DROP TABLE have');
        $this->addSql('CREATE TABLE have (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, equipment_id INTEGER NOT NULL, characteristic_id INTEGER DEFAULT NULL, own_quantity INTEGER NOT NULL, want_quantity INTEGER NOT NULL)');
        $this->addSql('INSERT INTO have (id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity) SELECT id, user_id, equipment_id, characteristic_id, own_quantity, want_quantity FROM __temp__have');
        $this->addSql('DROP TABLE __temp__have');
        $this->addSql('CREATE INDEX IDX_D27EEA40A76ED395 ON have (user_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40517FE9FE ON have (equipment_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40DEE9D12B ON have (characteristic_id)');
        $this->addSql('DROP INDEX IDX_ED9A8309517FE9FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__into_backpack AS SELECT id, equipment_id, count FROM into_backpack');
        $this->addSql('DROP TABLE into_backpack');
        $this->addSql('CREATE TABLE into_backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, count INTEGER NOT NULL)');
        $this->addSql('INSERT INTO into_backpack (id, equipment_id, count) SELECT id, equipment_id, count FROM __temp__into_backpack');
        $this->addSql('DROP TABLE __temp__into_backpack');
        $this->addSql('CREATE INDEX IDX_ED9A8309517FE9FE ON into_backpack (equipment_id)');
        $this->addSql('DROP INDEX IDX_BCE3F79812469DE2');
        $this->addSql('DROP INDEX IDX_BCE3F798DE12AB56');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sub_category AS SELECT id, category_id, created_by, name, validate, ask_validate FROM sub_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('INSERT INTO sub_category (id, category_id, created_by, name, validate, ask_validate) SELECT id, category_id, created_by, name, validate, ask_validate FROM __temp__sub_category');
        $this->addSql('DROP TABLE __temp__sub_category');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('CREATE INDEX IDX_BCE3F798DE12AB56 ON sub_category (created_by)');
    }
}
