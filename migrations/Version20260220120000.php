<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix application_type slug: certificate_of_studing -> certificate_of_studying';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE application_type SET slug = 'certificate_of_studying' WHERE slug = 'certificate_of_studing'");
        $this->addSql("UPDATE application_type SET slug = 'certificate_confirming_studying' WHERE slug = 'certificate_confirming_studing'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE application_type SET slug = 'certificate_of_studing' WHERE slug = 'certificate_of_studying'");
        $this->addSql("UPDATE application_type SET slug = 'certificate_confirming_studing' WHERE slug = 'certificate_confirming_studying'");
    }
}
