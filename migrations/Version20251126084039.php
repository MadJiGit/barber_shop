<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126084039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business_hours (id INT AUTO_INCREMENT NOT NULL, day_of_week SMALLINT NOT NULL, open_time TIME NOT NULL, close_time TIME NOT NULL, is_closed TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE business_hours_exception (id INT AUTO_INCREMENT NOT NULL, barber_id INT DEFAULT NULL, created_by INT DEFAULT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', reason VARCHAR(255) NOT NULL, is_closed TINYINT(1) DEFAULT 1 NOT NULL, open_time TIME DEFAULT NULL, close_time TIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8B1FA04BBFF2FEF2 (barber_id), INDEX IDX_8B1FA04BDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BBFF2FEF2 FOREIGN KEY (barber_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business_hours_exception DROP FOREIGN KEY FK_8B1FA04BBFF2FEF2');
        $this->addSql('ALTER TABLE business_hours_exception DROP FOREIGN KEY FK_8B1FA04BDE12AB56');
        $this->addSql('DROP TABLE business_hours');
        $this->addSql('DROP TABLE business_hours_exception');
    }
}
