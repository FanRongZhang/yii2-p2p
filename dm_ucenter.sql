
use dm_ucenter;

CREATE TABLE `dmu_member` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `account` varchar(45) DEFAULT NULL,
                              `password` varchar(105) DEFAULT NULL,
                              `mobile` varchar(45) DEFAULT NULL,
                              `email` varchar(45) DEFAULT NULL,
                              `status` int DEFAULT NULL,
                              `create_time` int DEFAULT NULL,
                              `source` int DEFAULT NULL,
                              `pay_password` varchar(105) DEFAULT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci