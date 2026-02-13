<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213144724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'application_type table added';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE application_type (id SERIAL NOT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B323FA1989D9B62 ON application_type (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE application_type');
    }
}
