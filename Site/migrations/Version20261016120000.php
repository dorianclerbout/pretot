<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261016120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove product price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP price');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD price VARCHAR(50) DEFAULT NULL');
    }
}