<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200523092429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');
        $this->addSql('DELETE FROM backpack WHERE created_by=1');
        $this->addSql('DELETE FROM have WHERE user_id=1');
        $this->addSql('DELETE FROM characteristic WHERE created_by=1');
        $this->addSql('DELETE FROM sub_category WHERE created_by=1');
        $this->addSql('DELETE FROM brand WHERE created_by=1');
        $this->addSql('DELETE FROM category WHERE created_by=1');
        $this->addSql('DELETE FROM equipment WHERE created_by=1');
        $this->addSql('DELETE FROM fos_user WHERE id=1');
        $this->addSql('INSERT INTO fos_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, roles, gender) VALUES (1, \'zephyrin\', \'zephyrin\', \'damortien@gmail.com\', \'damortien@gmail.com\', 1, \'lFkVmP5pk1wLFqfH9ZavQakVt8H.ru12gQ7Rwgjynkc\', \'$argon2id$v=19$m=65536,t=4,p=1$f1lcT+jwPm9QgQTsjHv55w$i2t9MG4+FR0zgfKy5GbQSR2R9S8LIVLD7l2QN+oFAnw\', \'a:1:{i:0;s:10:"ROLE_ADMIN";}\', \'MALE\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');
        $this->addSql('DELETE FROM backpack WHERE created_by=1');
        $this->addSql('DELETE FROM have WHERE user_id=1');
        $this->addSql('DELETE FROM characteristic WHERE created_by=1');
        $this->addSql('DELETE FROM sub_category WHERE created_by=1');
        $this->addSql('DELETE FROM brand WHERE created_by=1');
        $this->addSql('DELETE FROM category WHERE created_by=1');
        $this->addSql('DELETE FROM equipment WHERE created_by=1');
        $this->addSql('DELETE FROM fos_user WHERE id=1');
    }
}
