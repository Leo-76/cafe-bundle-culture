<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale — création des tables du CafeCultureBundle.
 */
final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables : cafe_category, cafe_tag, cafe_article, cafe_article_tag, cafe_comment, cafe_rating';
    }

    public function up(Schema $schema): void
    {
        // cafe_category
        $this->addSql('CREATE TABLE cafe_category (
            id          INT AUTO_INCREMENT NOT NULL,
            name        VARCHAR(100)  NOT NULL,
            slug        VARCHAR(100)  NOT NULL,
            description LONGTEXT      DEFAULT NULL,
            color       VARCHAR(7)    DEFAULT NULL,
            icon        VARCHAR(50)   DEFAULT NULL,
            position    INT           NOT NULL DEFAULT 0,
            UNIQUE INDEX UNIQ_CAFE_CATEGORY_SLUG (slug),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // cafe_tag
        $this->addSql('CREATE TABLE cafe_tag (
            id   INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(80) NOT NULL,
            slug VARCHAR(80) NOT NULL,
            UNIQUE INDEX UNIQ_CAFE_TAG_SLUG (slug),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // cafe_article
        $this->addSql('CREATE TABLE cafe_article (
            id                   INT AUTO_INCREMENT NOT NULL,
            category_id          INT          NOT NULL,
            title                VARCHAR(255) NOT NULL,
            slug                 VARCHAR(255) NOT NULL,
            content              LONGTEXT     NOT NULL,
            excerpt              LONGTEXT     DEFAULT NULL,
            cover_image          VARCHAR(255) DEFAULT NULL,
            status               VARCHAR(50)  NOT NULL DEFAULT \'draft\',
            featured             TINYINT(1)   NOT NULL DEFAULT 0,
            views                INT          NOT NULL DEFAULT 0,
            reading_time_minutes INT          NOT NULL DEFAULT 3,
            coffee_origin        VARCHAR(100) DEFAULT NULL,
            aroma_profile        JSON         DEFAULT NULL,
            author_name          VARCHAR(100) DEFAULT NULL,
            meta_title           VARCHAR(255) DEFAULT NULL,
            meta_description     VARCHAR(500) DEFAULT NULL,
            created_at           DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at           DATETIME     DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            published_at         DATETIME     DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_CAFE_ARTICLE_SLUG (slug),
            INDEX IDX_CAFE_ARTICLE_CATEGORY (category_id),
            INDEX IDX_CAFE_ARTICLE_STATUS (status),
            INDEX IDX_CAFE_ARTICLE_FEATURED (featured),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // cafe_article_tag (pivot)
        $this->addSql('CREATE TABLE cafe_article_tag (
            article_id INT NOT NULL,
            tag_id     INT NOT NULL,
            PRIMARY KEY (article_id, tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // cafe_comment
        $this->addSql('CREATE TABLE cafe_comment (
            id           INT AUTO_INCREMENT NOT NULL,
            article_id   INT          NOT NULL,
            author_name  VARCHAR(100) NOT NULL,
            author_email VARCHAR(255) NOT NULL,
            content      LONGTEXT     NOT NULL,
            approved     TINYINT(1)   NOT NULL DEFAULT 0,
            created_at   DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_CAFE_COMMENT_ARTICLE (article_id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // cafe_rating
        $this->addSql('CREATE TABLE cafe_rating (
            id         INT AUTO_INCREMENT NOT NULL,
            article_id INT      NOT NULL,
            score      SMALLINT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at DATETIME    NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_CAFE_RATING_ARTICLE_IP (article_id, ip_address),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign keys
        $this->addSql('ALTER TABLE cafe_article
            ADD CONSTRAINT FK_CAFE_ARTICLE_CATEGORY FOREIGN KEY (category_id) REFERENCES cafe_category (id)');
        $this->addSql('ALTER TABLE cafe_article_tag
            ADD CONSTRAINT FK_CAFE_ART_TAG_ARTICLE FOREIGN KEY (article_id) REFERENCES cafe_article (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_CAFE_ART_TAG_TAG     FOREIGN KEY (tag_id)     REFERENCES cafe_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cafe_comment
            ADD CONSTRAINT FK_CAFE_COMMENT_ARTICLE FOREIGN KEY (article_id) REFERENCES cafe_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cafe_rating
            ADD CONSTRAINT FK_CAFE_RATING_ARTICLE FOREIGN KEY (article_id) REFERENCES cafe_article (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cafe_article     DROP FOREIGN KEY FK_CAFE_ARTICLE_CATEGORY');
        $this->addSql('ALTER TABLE cafe_article_tag DROP FOREIGN KEY FK_CAFE_ART_TAG_ARTICLE');
        $this->addSql('ALTER TABLE cafe_article_tag DROP FOREIGN KEY FK_CAFE_ART_TAG_TAG');
        $this->addSql('ALTER TABLE cafe_comment     DROP FOREIGN KEY FK_CAFE_COMMENT_ARTICLE');
        $this->addSql('ALTER TABLE cafe_rating      DROP FOREIGN KEY FK_CAFE_RATING_ARTICLE');

        $this->addSql('DROP TABLE cafe_rating');
        $this->addSql('DROP TABLE cafe_comment');
        $this->addSql('DROP TABLE cafe_article_tag');
        $this->addSql('DROP TABLE cafe_article');
        $this->addSql('DROP TABLE cafe_tag');
        $this->addSql('DROP TABLE cafe_category');
    }
}
