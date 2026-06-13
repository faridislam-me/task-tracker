-- ===========================================================================
-- Task Tracker - schema + seed data for SHARED HOSTING (e.g. InfinityFree)
-- ---------------------------------------------------------------------------
-- Free shared hosts do NOT allow CREATE DATABASE. They give you a database
-- that is already created (e.g. epiz_12345678_task_tracker). So:
--   1) In the host control panel, create the MySQL database first.
--   2) Open phpMyAdmin, SELECT that database in the left sidebar.
--   3) Use the Import tab and upload THIS file (no CREATE DATABASE / USE here).
-- ===========================================================================

-- Clean slate (safe to re-run). Drop child table first (FK).
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `users`;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)  NOT NULL,
    `email`         VARCHAR(190)  NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)  NOT NULL,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- tasks
-- ---------------------------------------------------------------------------
CREATE TABLE `tasks` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT          NOT NULL,
    `title`       VARCHAR(200) NOT NULL,
    `description` TEXT         NULL,
    `priority`    ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `status`      ENUM('pending','done')      NOT NULL DEFAULT 'pending',
    `due_date`    DATE         NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tasks_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_tasks_user` (`user_id`),
    INDEX `idx_tasks_status` (`status`),
    INDEX `idx_tasks_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================================
-- Seed data  (demo login -> email: demo@example.com  password: Demo@1234)
-- ===========================================================================
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`) VALUES
    (1, 'Demo User', 'demo@example.com',
     '$2y$12$ngsB4wY78JNJ77KzBvOps.FmDSlxqgd94sh6NdkYWvSVqkfnaHJui');

INSERT INTO `tasks` (`user_id`, `title`, `description`, `priority`, `status`, `due_date`) VALUES
    (1, 'Finish CSE 471 project proposal', 'Write the 2-page proposal and submit on the portal.', 'high',   'pending', '2026-06-20'),
    (1, 'Read database normalization notes', 'Cover 1NF through BCNF with examples.',                'medium', 'done',    '2026-06-15'),
    (1, 'Buy groceries',                     'Milk, eggs, rice, vegetables.',                        'low',    'pending', '2026-06-14'),
    (1, 'Prepare slides for presentation',   'Make 10 slides covering features and demo.',           'high',   'pending', '2026-06-22'),
    (1, 'Pay internet bill',                 'Due this week - pay online.',                          'medium', 'pending', '2026-06-18');
