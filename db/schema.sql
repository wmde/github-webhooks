CREATE TABLE `releases` (
  `refid`	TEXT NOT NULL,
  `branch`	TEXT,
  `ts_added`	TEXT,
  `ts_started`	TEXT,
  `ts_ended`	TEXT,
  `num_tries` INTEGER DEFAULT 0,
  PRIMARY KEY(refid)
)