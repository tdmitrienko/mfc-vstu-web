<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260218162149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'users.mfc_code, users.documents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD mfc_code VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE users ADD documents JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP mfc_code');
        $this->addSql('ALTER TABLE users DROP documents');
    }
}
