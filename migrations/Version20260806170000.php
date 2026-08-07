<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260806170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user and auth_log tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE auth_log (
            id INT AUTO_INCREMENT NOT NULL,
            operation_type VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL,
            user_id INT DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            user_agent LONGTEXT DEFAULT NULL,
            error_message LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_8C06677CA76ED395 (user_id),
            INDEX idx_auth_log_created_at (created_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_8C06677CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE auth_log');
        $this->addSql('DROP TABLE `user`');
    }
}
