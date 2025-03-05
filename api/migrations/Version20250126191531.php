<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250126191531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api token table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_token (id SERIAL NOT NULL, user_id INT NOT NULL, expires_at TIMESTAMP WITHOUT TIME ZONE NOT NULL, token VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BA2F5EBA76ED395 ON api_token (user_id)');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB5F37A13B ON api_token (token)');
        $this->addSql('COMMENT ON COLUMN api_token.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token DROP CONSTRAINT FK_7BA2F5EBA76ED395');
        $this->addSql('DROP TABLE api_token');
    }
}
