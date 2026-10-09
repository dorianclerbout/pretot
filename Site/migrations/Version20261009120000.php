<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261009120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create carousel_slide table with the three historical home images';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE carousel_slide (id INT AUTO_INCREMENT NOT NULL, image_filename VARCHAR(255) NOT NULL, caption LONGTEXT DEFAULT NULL, alt_text VARCHAR(255) DEFAULT NULL, visible TINYINT NOT NULL, sort_order INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO carousel_slide (image_filename, caption, alt_text, visible, sort_order) VALUES
            ('hero-dog.jpg', 'Une présence attentive\npour vos compagnons.', 'Golden retriever suivi avec attention par la clinique vétérinaire', 1, 1),
            ('clinic-cat.jpg', 'Une présence attentive\npour vos compagnons.', 'Chat accueilli à la clinique vétérinaire', 1, 2),
            ('advice-cat.jpg', 'Une présence attentive\npour vos compagnons.', 'Chat suivi par l''équipe vétérinaire', 1, 3)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE carousel_slide');
    }
}
