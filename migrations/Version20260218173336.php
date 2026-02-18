<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218173336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_type ADD document_required BOOLEAN NOT NULL DEFAULT TRUE');
        $this->addSql('ALTER TABLE application_type ADD files_required BOOLEAN NOT NULL DEFAULT TRUE');
        $this->addSql('ALTER TABLE application_type ALTER document_required DROP DEFAULT');
        $this->addSql('ALTER TABLE application_type ALTER files_required DROP DEFAULT');

        // files_required = true только для duplicate_student_id и exam_call, остальным false
        $this->addSql("UPDATE application_type SET files_required = FALSE WHERE slug NOT IN ('duplicate_student_id', 'exam_call')");

        // удалить transfer_from_another_university
        $this->addSql("DELETE FROM application_type WHERE slug = 'transfer_from_another_university'");

        // новая запись: Справка с места работы
        $this->addSql("INSERT INTO application_type (name, slug, roles, document_required, files_required) VALUES ('Справка с места работы', 'certificate_of_employee', '[\"ROLE_EMPLOYEE\"]', FALSE, FALSE)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_type DROP document_required');
        $this->addSql('ALTER TABLE application_type DROP files_required');
    }
}
