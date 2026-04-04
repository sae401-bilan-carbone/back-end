<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404104854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'User: add name, surname, locale, profilePicture';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD locale VARCHAR(255) DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD surname VARCHAR(255) DEFAULT NULL, ADD profile_picture LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP locale, DROP name, DROP surname, DROP profile_picture');
    }
}
