<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126152116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_appointments_date ON appointments (date)');
        $this->addSql('CREATE INDEX idx_appointments_barber_date ON appointments (barber_id, date)');
        $this->addSql('CREATE INDEX idx_appointments_client_date ON appointments (client_id, date)');
        $this->addSql('CREATE INDEX idx_appointments_status ON appointments (status)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_appointments_date ON appointments');
        $this->addSql('DROP INDEX idx_appointments_barber_date ON appointments');
        $this->addSql('DROP INDEX idx_appointments_client_date ON appointments');
        $this->addSql('DROP INDEX idx_appointments_status ON appointments');
    }
}
