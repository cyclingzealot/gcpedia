CREATE TABLE ipblocks (
  ipb_id                INTEGER      NOT NULL  PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY (START WITH 1),
  --DEFAULT nextval('ipblocks_ipb_id_val'),
  ipb_address           VARCHAR(1024),
  ipb_user              BIGINT NOT NULL DEFAULT 0,
  --           REFERENCES user(user_id) ON DELETE SET NULL,
  ipb_by                BIGINT      NOT NULL DEFAULT 0,
  --  REFERENCES user(user_id) ON DELETE CASCADE,
  ipb_by_text           VARCHAR(255)         NOT NULL  DEFAULT '',
  ipb_reason            VARCHAR(1024)         NOT NULL,
  ipb_timestamp         TIMESTAMP(3)  NOT NULL,
  ipb_auto              SMALLINT     NOT NULL  DEFAULT 0,
  ipb_anon_only         SMALLINT     NOT NULL  DEFAULT 0,
  ipb_create_account    SMALLINT     NOT NULL  DEFAULT 1,
  ipb_enable_autoblock  SMALLINT     NOT NULL  DEFAULT 1,
  ipb_expiry            TIMESTAMP(3)  NOT NULL,
  ipb_range_start       VARCHAR(1024),
  ipb_range_end         VARCHAR(1024),
  ipb_deleted           SMALLINT     NOT NULL  DEFAULT 0,
  ipb_block_email       SMALLINT     NOT NULL  DEFAULT 0,
  ipb_allow_usertalk    SMALLINT     NOT NULL  DEFAULT 0

);
