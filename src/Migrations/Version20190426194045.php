<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190426194045 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_B6BD307F9AC0396');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, author_id, conversation_id, content FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, conversation_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO message (id, author_id, conversation_id, content) SELECT id, author_id, conversation_id, content FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
        $this->addSql('DROP INDEX IDX_5AECB555A76ED395');
        $this->addSql('DROP INDEX IDX_5AECB5559AC0396');
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation_user AS SELECT conversation_id, user_id FROM conversation_user');
        $this->addSql('DROP TABLE conversation_user');
        $this->addSql('CREATE TABLE conversation_user (conversation_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(conversation_id, user_id), CONSTRAINT FK_5AECB5559AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5AECB555A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO conversation_user (conversation_id, user_id) SELECT conversation_id, user_id FROM __temp__conversation_user');
        $this->addSql('DROP TABLE __temp__conversation_user');
        $this->addSql('CREATE INDEX IDX_5AECB555A76ED395 ON conversation_user (user_id)');
        $this->addSql('CREATE INDEX IDX_5AECB5559AC0396 ON conversation_user (conversation_id)');
        $this->addSql('DROP INDEX IDX_4C62E6387E3C61F9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__contact AS SELECT id, owner_id, contact_id, status FROM contact');
        $this->addSql('DROP TABLE contact');
        $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, status VARCHAR(25) NOT NULL COLLATE BINARY, CONSTRAINT FK_4C62E6387E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4C62E638E7A1254A FOREIGN KEY (contact_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO contact (id, owner_id, contact_id, status) SELECT id, owner_id, contact_id, status FROM __temp__contact');
        $this->addSql('DROP TABLE __temp__contact');
        //$this->addSql('CREATE INDEX IDX_4C62E6387E3C61F9 ON contact (owner_id)');
        //$this->addSql('CREATE UNIQUE INDEX UNIQ_4C62E638E7A1254A ON contact (contact_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        //$this->addSql('DROP INDEX IDX_4C62E6387E3C61F9');
        //$this->addSql('DROP INDEX UNIQ_4C62E638E7A1254A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__contact AS SELECT id, owner_id, contact_id, status FROM contact');
        $this->addSql('DROP TABLE contact');
        $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, status VARCHAR(25) NOT NULL)');
        $this->addSql('INSERT INTO contact (id, owner_id, contact_id, status) SELECT id, owner_id, contact_id, status FROM __temp__contact');
        $this->addSql('DROP TABLE __temp__contact');
        $this->addSql('CREATE INDEX IDX_4C62E6387E3C61F9 ON contact (owner_id)');
        $this->addSql('DROP INDEX IDX_5AECB5559AC0396');
        $this->addSql('DROP INDEX IDX_5AECB555A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation_user AS SELECT conversation_id, user_id FROM conversation_user');
        $this->addSql('DROP TABLE conversation_user');
        $this->addSql('CREATE TABLE conversation_user (conversation_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(conversation_id, user_id))');
        $this->addSql('INSERT INTO conversation_user (conversation_id, user_id) SELECT conversation_id, user_id FROM __temp__conversation_user');
        $this->addSql('DROP TABLE __temp__conversation_user');
        $this->addSql('CREATE INDEX IDX_5AECB5559AC0396 ON conversation_user (conversation_id)');
        $this->addSql('CREATE INDEX IDX_5AECB555A76ED395 ON conversation_user (user_id)');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B');
        $this->addSql('DROP INDEX IDX_B6BD307F9AC0396');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, author_id, conversation_id, content FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, conversation_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO message (id, author_id, conversation_id, content) SELECT id, author_id, conversation_id, content FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
    }
}
