-- Setup database for Moodler
CREATE TABLE `mood` (
  `mood` VARCHAR(20) NOT NULL,
  `org` VARCHAR(50) NOT NULL DEFAULT 'demo',
  `count` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`mood`, `org`)
);

INSERT INTO `mood` VALUES
  ('superhappy', 'demo', 0),
  ('happy', 'demo', 0),
  ('normal', 'demo', 0),
  ('sad', 'demo', 0),
  ('crying', 'demo', 0);