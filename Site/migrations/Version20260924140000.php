<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create advice_article table for the advice page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE advice_article (
                id SERIAL NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                excerpt TEXT NOT NULL,
                content TEXT NOT NULL,
                image_filename VARCHAR(255) DEFAULT NULL,
                visible BOOLEAN NOT NULL,
                sort_order INT NOT NULL,
                PRIMARY KEY(id),
                UNIQUE (slug)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE advice_article');
    }
}
