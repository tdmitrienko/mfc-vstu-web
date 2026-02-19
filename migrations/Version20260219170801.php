<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219170801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'mfc_request.mfc_code';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mfc_request ADD mfc_code VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_632D120EA4D8D9B6 ON mfc_request (mfc_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_632D120EA4D8D9B6');
        $this->addSql('ALTER TABLE mfc_request DROP mfc_code');
    }
}
