<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241224102224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_tag DROP FOREIGN KEY FK_5ACE3AF08D7B4FB4');
        $this->addSql('ALTER TABLE post_tag DROP FOREIGN KEY FK_5ACE3AF0D5E258C5');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE post_tag');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE post_tag (id INT AUTO_INCREMENT NOT NULL, tags_id INT NOT NULL, posts_id INT NOT NULL, INDEX IDX_5ACE3AF08D7B4FB4 (tags_id), INDEX IDX_5ACE3AF0D5E258C5 (posts_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF08D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tag (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF0D5E258C5 FOREIGN KEY (posts_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
