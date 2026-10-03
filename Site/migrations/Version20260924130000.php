<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create partner table for the contact page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE partner (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                role VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                logo_filename VARCHAR(255) DEFAULT NULL,
                website_url VARCHAR(2048) DEFAULT NULL,
                visible BOOLEAN NOT NULL,
                sort_order INT NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE partner');
    }
}
