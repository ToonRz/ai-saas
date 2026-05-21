-- ai-saas MySQL schema
-- Run: mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS ai_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ai_saas;

-- ---------------------------------------------------------------------------
-- subscriptions: plan catalog (Free, Pro, Premium)
-- PK: id
-- ---------------------------------------------------------------------------
CREATE TABLE subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    daily_limit INT UNSIGNED NOT NULL COMMENT 'Max AI requests per calendar day',
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Monthly price (mock billing)'
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- users: accounts linked to one subscription
-- PK: id | FK: subscription_id -> subscriptions.id
-- ---------------------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    subscription_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_users_email (email),
    CONSTRAINT fk_users_subscription
        FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- usage_logs: per-action tracking for limits and admin audit
-- PK: id | FK: user_id -> users.id (CASCADE)
-- ---------------------------------------------------------------------------
CREATE TABLE usage_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL DEFAULT 'ai_request',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usage_user_date (user_id, created_at),
    CONSTRAINT fk_usage_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- ai_history: saved prompts and responses
-- PK: id | FK: user_id -> users.id (CASCADE)
-- ---------------------------------------------------------------------------
CREATE TABLE ai_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    prompt TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_history_user_created (user_id, created_at),
    CONSTRAINT fk_history_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================================================================
-- Example / seed data
-- ===========================================================================

INSERT INTO subscriptions (id, name, daily_limit, price) VALUES
    (1, 'Free', 20, 0.00),
    (2, 'Pro', 100, 9.99),
    (3, 'Premium', 500, 29.99);

-- password for all demo users below: "password"
-- bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, name, email, password_hash, role, is_banned, subscription_id, created_at) VALUES
    (1, 'Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, 3, '2026-05-01 10:00:00'),
    (2, 'Alice', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, 1, '2026-05-10 14:30:00'),
    (3, 'Bob', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, 2, '2026-05-15 09:15:00'),
    (4, 'Carol', 'carol@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, 1, '2026-05-18 16:45:00');

INSERT INTO usage_logs (id, user_id, action, created_at) VALUES
    (1, 2, 'ai_request', '2026-05-22 08:01:00'),
    (2, 2, 'ai_request', '2026-05-22 08:15:00'),
    (3, 2, 'plan_upgrade', '2026-05-20 12:00:00'),
    (4, 3, 'ai_request', '2026-05-22 09:30:00'),
    (5, 3, 'ai_request', '2026-05-22 10:00:00');

INSERT INTO ai_history (id, user_id, prompt, response, created_at) VALUES
    (1, 2, 'Summarize agile software development in 3 bullet points',
     '1. Deliver work in short iterations.\n2. Collaborate with stakeholders often.\n3. Adapt plans based on feedback.',
     '2026-05-22 08:01:00'),
    (2, 2, 'Write a short welcome email for new SaaS users',
     'Subject: Welcome aboard!\n\nHi there,\n\nThanks for signing up. Explore your dashboard to run your first AI request.',
     '2026-05-22 08:15:00'),
    (3, 3, 'Give me 5 blog post ideas about AI tools',
     '1. How AI assistants save time for founders\n2. Comparing Free vs Pro AI plans\n3. Writing better prompts\n4. AI ethics for startups\n5. Building a SaaS MVP with PHP',
     '2026-05-22 09:30:00');
