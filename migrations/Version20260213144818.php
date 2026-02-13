<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213144818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'some application_type rows added';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Справка об оплате\', \'certificates_of_payments\', \'["ROLE_STUDENT"]\')');
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Справка, подтверждающая обучение\', \'certificate_confirming_studing\', \'["ROLE_STUDENT"]\')');
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Студенческий билет\', \'certificate_of_studing\', \'["ROLE_STUDENT"]\')');
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Дубликат студенческого билета\', \'duplicate_student_id\', \'["ROLE_STUDENT"]\')');
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Справка вызов\', \'exam_call\', \'["ROLE_STUDENT"]\')');
        $this->addSql('INSERT INTO application_type (name, slug, roles) VALUES (\'Перевод из другого университета\', \'transfer_from_another_university\', \'["ROLE_STUDENT"]\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM application_type WHERE slug in (
            \'certificates_of_payments\',
            \'certificate_confirming_studing\',
            \'certificate_of_studing\',
            \'duplicate_student_id\',
            \'exam_call\',
            \'transfer_from_another_university\'
        )');
    }
}
