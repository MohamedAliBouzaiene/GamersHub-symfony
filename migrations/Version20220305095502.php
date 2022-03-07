<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305095502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE spam_user (spam_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C4870B4BFD07508 (spam_id), INDEX IDX_C4870B4A76ED395 (user_id), PRIMARY KEY(spam_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spam_user ADD CONSTRAINT FK_C4870B4BFD07508 FOREIGN KEY (spam_id) REFERENCES spam (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spam_user ADD CONSTRAINT FK_C4870B4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE spam_user');
    }
}