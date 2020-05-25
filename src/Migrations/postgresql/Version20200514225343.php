<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200514225343 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE sub_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fos_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE into_backpack_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE characteristic_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE equipment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE backpack_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE brand_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE have_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sub_category (id INT NOT NULL, category_id INT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'false\' NOT NULL, ask_validate BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BCE3F79812469DE2 ON sub_category (category_id)');
        $this->addSql('CREATE INDEX IDX_BCE3F798DE12AB56 ON sub_category (created_by)');
        $this->addSql('CREATE TABLE fos_user (id INT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles TEXT NOT NULL, gender VARCHAR(6) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A647992FC23A8 ON fos_user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479A0D96FBF ON fos_user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479C05FB297 ON fos_user (confirmation_token)');
        $this->addSql('COMMENT ON COLUMN fos_user.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE into_backpack (id INT NOT NULL, equipment_id INT NOT NULL, count INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED9A8309517FE9FE ON into_backpack (equipment_id)');
        $this->addSql('CREATE TABLE characteristic (id INT NOT NULL, equipment_id INT NOT NULL, created_by INT DEFAULT NULL, gender VARCHAR(6) NOT NULL, size VARCHAR(25) NOT NULL, price DOUBLE PRECISION NOT NULL, weight INT NOT NULL, validate BOOLEAN DEFAULT \'false\' NOT NULL, ask_validate BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_522FA950517FE9FE ON characteristic (equipment_id)');
        $this->addSql('CREATE INDEX IDX_522FA950DE12AB56 ON characteristic (created_by)');
        $this->addSql('CREATE TABLE equipment (id INT NOT NULL, sub_category_id INT NOT NULL, brand_id INT DEFAULT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, validate BOOLEAN DEFAULT \'false\' NOT NULL, ask_validate BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D338D583F7BFE87C ON equipment (sub_category_id)');
        $this->addSql('CREATE INDEX IDX_D338D58344F5D008 ON equipment (brand_id)');
        $this->addSql('CREATE INDEX IDX_D338D583DE12AB56 ON equipment (created_by)');
        $this->addSql('CREATE TABLE backpack (id INT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C358569DE12AB56 ON backpack (created_by)');
        $this->addSql('CREATE TABLE brand (id INT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, uri VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'false\' NOT NULL, ask_validate BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F9585E237E06 ON brand (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F958841CB121 ON brand (uri)');
        $this->addSql('CREATE INDEX IDX_1C52F958DE12AB56 ON brand (created_by)');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, validate BOOLEAN DEFAULT \'false\' NOT NULL, ask_validate BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE INDEX IDX_64C19C1DE12AB56 ON category (created_by)');
        $this->addSql('CREATE TABLE have (id INT NOT NULL, user_id INT NOT NULL, equipment_id INT NOT NULL, characteristic_id INT DEFAULT NULL, own_quantity INT NOT NULL, want_quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D27EEA40A76ED395 ON have (user_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40517FE9FE ON have (equipment_id)');
        $this->addSql('CREATE INDEX IDX_D27EEA40DEE9D12B ON have (characteristic_id)');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F798DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE into_backpack ADD CONSTRAINT FK_ED9A8309517FE9FE FOREIGN KEY (equipment_id) REFERENCES have (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE characteristic ADD CONSTRAINT FK_522FA950DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58344F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE backpack ADD CONSTRAINT FK_C358569DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE have ADD CONSTRAINT FK_D27EEA40A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE have ADD CONSTRAINT FK_D27EEA40517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE have ADD CONSTRAINT FK_D27EEA40DEE9D12B FOREIGN KEY (characteristic_id) REFERENCES characteristic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE equipment DROP CONSTRAINT FK_D338D583F7BFE87C');
        $this->addSql('ALTER TABLE sub_category DROP CONSTRAINT FK_BCE3F798DE12AB56');
        $this->addSql('ALTER TABLE characteristic DROP CONSTRAINT FK_522FA950DE12AB56');
        $this->addSql('ALTER TABLE equipment DROP CONSTRAINT FK_D338D583DE12AB56');
        $this->addSql('ALTER TABLE backpack DROP CONSTRAINT FK_C358569DE12AB56');
        $this->addSql('ALTER TABLE brand DROP CONSTRAINT FK_1C52F958DE12AB56');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1DE12AB56');
        $this->addSql('ALTER TABLE have DROP CONSTRAINT FK_D27EEA40A76ED395');
        $this->addSql('ALTER TABLE have DROP CONSTRAINT FK_D27EEA40DEE9D12B');
        $this->addSql('ALTER TABLE characteristic DROP CONSTRAINT FK_522FA950517FE9FE');
        $this->addSql('ALTER TABLE have DROP CONSTRAINT FK_D27EEA40517FE9FE');
        $this->addSql('ALTER TABLE equipment DROP CONSTRAINT FK_D338D58344F5D008');
        $this->addSql('ALTER TABLE sub_category DROP CONSTRAINT FK_BCE3F79812469DE2');
        $this->addSql('ALTER TABLE into_backpack DROP CONSTRAINT FK_ED9A8309517FE9FE');
        $this->addSql('DROP SEQUENCE sub_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fos_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE into_backpack_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE characteristic_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE equipment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE backpack_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE brand_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE have_id_seq CASCADE');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE into_backpack');
        $this->addSql('DROP TABLE characteristic');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE backpack');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE have');
    }
}