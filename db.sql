USE rss_world;

CREATE TABLE IF NOT EXISTS `feed` (
  `feed_id`      BIGINT(20)    NOT NULL AUTO_INCREMENT
  COMMENT '主键',
  `title`        VARCHAR(1024) NOT NULL DEFAULT ''
  COMMENT '站点名称',
  `site_url`     VARCHAR(1024) NOT NULL DEFAULT ''
  COMMENT '站点链接',
  `feed_url`     VARCHAR(1024) NOT NULL DEFAULT ''
  COMMENT 'feed链接',
  `feed_type`    VARCHAR(10)   NOT NULL DEFAULT 'rss'
  COMMENT 'feed类型',
  `feed_updated` VARCHAR(50)   NOT NULL DEFAULT 0
  COMMENT 'feed更新时间',
  PRIMARY KEY (`feed_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
;

CREATE TABLE IF NOT EXISTS `post` (
  `post_id`      BIGINT(20)    NOT NULL AUTO_INCREMENT
  COMMENT '主键',
  `feed_id`      BIGINT(20)    NOT NULL
  COMMENT '文章所属feed',
  `title`        VARCHAR(1024) NOT NULL DEFAULT ''
  COMMENT '文章主题',
  `link`         VARCHAR(1024) NOT NULL DEFAULT ''
  COMMENT '文章链接',
  `publish_date` VARCHAR(50)   NOT NULL DEFAULT 0
  COMMENT '发布时间',
  PRIMARY KEY (`post_id`),
  FOREIGN KEY (`feed_id`) REFERENCES `feed` (`feed_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
;