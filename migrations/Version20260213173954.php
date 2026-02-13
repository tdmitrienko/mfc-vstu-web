<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213173954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'mfc_request, mfc_request_file';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mfc_request (id SERIAL NOT NULL, owner_id INT NOT NULL, application_type_id INT DEFAULT NULL, state VARCHAR(16) NOT NULL, document_number VARCHAR(32) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_632D120E7E3C61F9 ON mfc_request (owner_id)');
        $this->addSql('CREATE INDEX IDX_632D120E2EF289A0 ON mfc_request (application_type_id)');
        $this->addSql('COMMENT ON COLUMN mfc_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mfc_request.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mfc_request_file (id SERIAL NOT NULL, request_id INT NOT NULL, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) DEFAULT NULL, size INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EADF8AA9427EB8A5 ON mfc_request_file (request_id)');
        $this->addSql('COMMENT ON COLUMN mfc_request_file.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE mfc_request ADD CONSTRAINT FK_632D120E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mfc_request ADD CONSTRAINT FK_632D120E2EF289A0 FOREIGN KEY (application_type_id) REFERENCES application_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mfc_request_file ADD CONSTRAINT FK_EADF8AA9427EB8A5 FOREIGN KEY (request_id) REFERENCES mfc_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mfc_request DROP CONSTRAINT FK_632D120E7E3C61F9');
        $this->addSql('ALTER TABLE mfc_request DROP CONSTRAINT FK_632D120E2EF289A0');
        $this->addSql('ALTER TABLE mfc_request_file DROP CONSTRAINT FK_EADF8AA9427EB8A5');
        $this->addSql('DROP TABLE mfc_request');
        $this->addSql('DROP TABLE mfc_request_file');
    }
}
