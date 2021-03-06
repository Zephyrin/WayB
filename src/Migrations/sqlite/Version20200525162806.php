<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200525162806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE sub_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('CREATE INDEX IDX_BCE3F798DE12AB56 ON sub_category (created_by)');
        $this->addSql('CREATE TABLE wuser (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, gender VARCHAR(6) NOT NULL, email VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2107C297F85E0677 ON wuser (username)');
        $this->addSql('CREATE TABLE into_backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, count INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_ED9A8309517FE9FE ON into_backpack (equipment_id)');
        $this->addSql('CREATE TABLE characteristic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, equipment_id INTEGER NOT NULL, created_by INTEGER DEFAULT NULL, gender VARCHAR(6) NOT NULL, size VARCHAR(25) NOT NULL, price DOUBLE PRECISION NOT NULL, weight INTEGER NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('CREATE INDEX IDX_522FA950517FE9FE ON characteristic (equipment_id)');
        $this->addSql('CREATE INDEX IDX_522FA950DE12AB56 ON characteristic (created_by)');
        $this->addSql('CREATE TABLE equipment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sub_category_id INTEGER NOT NULL, brand_id INTEGER DEFAULT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('CREATE INDEX IDX_D338D583DE12AB56 ON equipment (created_by)');
        $this->addSql('CREATE TABLE backpack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_C358569DE12AB56 ON backpack (created_by)');
        $this->addSql('CREATE TABLE brand (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, logo_id INTEGER DEFAULT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, uri VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F9585E237E06 ON brand (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F958841CB121 ON brand (uri)');
        $this->addSql('CREATE INDEX IDX_1C52F958F98F144A ON brand (logo_id)');
        $this->addSql('CREATE INDEX IDX_1C52F958DE12AB56 ON brand (created_by)');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'0\' NOT NULL, ask_validate BOOLEAN DEFAULT \'0\' NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE INDEX IDX_64C19C1DE12AB56 ON category (created_by)');
        $this->addSql('CREATE TABLE have (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, equipment_id INTEGER NOT NULL, characteristic_id INTEGER DEFAULT NULL, own_quantity INTEGER NOT NULL, want_quantity INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_D27EEA40A76ED395 ON have (user_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40517FE9FE ON have (equipment_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40DEE9D12B ON have (characteristic_id)');
        $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_path VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE wuser');
        $this->addSql('DROP TABLE into_backpack');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE backpack');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE have');
        $this->addSql('DROP TABLE media_object');
    }
}
