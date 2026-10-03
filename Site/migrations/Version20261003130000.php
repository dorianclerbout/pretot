<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align team_member columns with the configured MySQL schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX id ON team_member');
        $this->addSql('ALTER TABLE team_member CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE bio bio LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_member CHANGE id id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE bio bio TEXT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX id ON team_member (id)');
    }
}