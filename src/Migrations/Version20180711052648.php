<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180711052648 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ticker (id INT AUTO_INCREMENT NOT NULL, project INT DEFAULT NULL, timeline INT DEFAULT NULL, rm_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, category SMALLINT NOT NULL, created_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, last_tick_at DATETIME DEFAULT NULL, usage_count INT NOT NULL, current TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_7EC30896157C774E (rm_id), UNIQUE INDEX UNIQ_7EC308965E237E06 (name), INDEX IDX_7EC308962FB3D0EE (project), INDEX IDX_7EC3089646FEC666 (timeline), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, rm_id INT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2FB3D0EE157C774E (rm_id), UNIQUE INDEX UNIQ_2FB3D0EE5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_line (id INT AUTO_INCREMENT NOT NULL, ticker INT DEFAULT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, duration BIGINT DEFAULT NULL, INDEX IDX_7CA9BDDB7EC30896 (ticker), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticker ADD CONSTRAINT FK_7EC308962FB3D0EE FOREIGN KEY (project) REFERENCES project (id)');
        $this->addSql('ALTER TABLE ticker ADD CONSTRAINT FK_7EC3089646FEC666 FOREIGN KEY (timeline) REFERENCES time_line (id)');
        $this->addSql('ALTER TABLE time_line ADD CONSTRAINT FK_7CA9BDDB7EC30896 FOREIGN KEY (ticker) REFERENCES ticker (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE time_line DROP FOREIGN KEY FK_7CA9BDDB7EC30896');
        $this->addSql('ALTER TABLE ticker DROP FOREIGN KEY FK_7EC308962FB3D0EE');
        $this->addSql('ALTER TABLE ticker DROP FOREIGN KEY FK_7EC3089646FEC666');
        $this->addSql('DROP TABLE ticker');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE time_line');
    }
}
