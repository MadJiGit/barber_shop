<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209141020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add confirmation_token and confirmed_at fields to appointments table for guest booking system';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments ADD confirmation_token VARCHAR(64) DEFAULT NULL, ADD confirmed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A41727AC05FB297 ON appointments (confirmation_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6A41727AC05FB297 ON appointments');
        $this->addSql('ALTER TABLE appointments DROP confirmation_token, DROP confirmed_at');
    }
}
