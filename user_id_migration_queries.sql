-- =====================================================
-- SQL Queries for Adding user_id Column to All Tables
-- =====================================================
-- This file contains raw SQL queries for:
-- 1. Adding user_id column to all tables (from migrations)
-- 2. Setting user_id = 1 for all existing records
-- =====================================================

-- =====================================================
-- PART 1: MIGRATION QUERIES (Adding user_id columns)
-- =====================================================

-- 1. Add user_id to forms table
ALTER TABLE `forms` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `forms_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 2. Add user_id to fields table
ALTER TABLE `fields` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `fields_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 3. Add user_id to formulas table
ALTER TABLE `formulas` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `formulas_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 4. Add user_id to bar_bending_locations table
ALTER TABLE `bar_bending_locations` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `bar_bending_locations_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 5. Add user_id to bar_bending_item_details table
ALTER TABLE `bar_bending_item_details` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `bar_bending_item_details_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 6. Add user_id to bar_bending_form_items table
ALTER TABLE `bar_bending_form_items` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `bar_bending_form_items_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- 7. Add user_id to bar_bending_form_locations table
ALTER TABLE `bar_bending_form_locations` 
ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `bar_bending_form_locations_user_id_foreign` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE;

-- =====================================================
-- PART 2: USER SEEDER QUERIES (Insert/Update Users)
-- =====================================================
-- Note: Passwords are bcrypt hashed using Laravel's Hash::make()
-- These queries match the UserSeeder functionality using INSERT ... ON DUPLICATE KEY UPDATE

-- Insert or Update User 1: mia59@gmail.com
-- Password: mia_password
-- Bcrypt Hash: $2y$12$4HYIOdxebFZG4PoWxjQjN.Mf66sUDN7W5TE95EfpFecOVJy6GOcHC
INSERT INTO `users` (`name`, `email`, `password`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`)
VALUES ('MIA User', 'mia59@gmail.com', '$2y$12$4HYIOdxebFZG4PoWxjQjN.Mf66sUDN7W5TE95EfpFecOVJy6GOcHC', NOW(), NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `name` = 'MIA User',
    `password` = '$2y$12$4HYIOdxebFZG4PoWxjQjN.Mf66sUDN7W5TE95EfpFecOVJy6GOcHC',
    `email_verified_at` = NOW(),
    `updated_at` = NOW();

-- Insert or Update User 2: aden@gmail.com
-- Password: aden_password
-- Bcrypt Hash: $2y$12$R8ntS4WtlrNG40P/avi9E.gYnwLpof6NFo4PLbFuSbjFMy/g0H4e.
INSERT INTO `users` (`name`, `email`, `password`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`)
VALUES ('Aden User', 'aden@gmail.com', '$2y$12$R8ntS4WtlrNG40P/avi9E.gYnwLpof6NFo4PLbFuSbjFMy/g0H4e.', NOW(), NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `name` = 'Aden User',
    `password` = '$2y$12$R8ntS4WtlrNG40P/avi9E.gYnwLpof6NFo4PLbFuSbjFMy/g0H4e.',
    `email_verified_at` = NOW(),
    `updated_at` = NOW();

-- =====================================================
-- PART 3: UPDATE QUERIES (Setting user_id = 1 for existing records)
-- =====================================================
-- Note: Make sure user with id = 1 exists before running these queries

-- Update forms table
UPDATE `forms` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update fields table
UPDATE `fields` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update formulas table
UPDATE `formulas` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update bar_bending_locations table
UPDATE `bar_bending_locations` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update bar_bending_item_details table
UPDATE `bar_bending_item_details` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update bar_bending_form_items table
UPDATE `bar_bending_form_items` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- Update bar_bending_form_locations table
UPDATE `bar_bending_form_locations` 
SET `user_id` = 1 
WHERE `user_id` IS NULL;

-- =====================================================
-- PART 4: ROLLBACK QUERIES (Optional - for reference)
-- =====================================================
-- These queries can be used to remove the user_id columns if needed

-- Remove user_id from bar_bending_form_locations table
-- ALTER TABLE `bar_bending_form_locations` 
-- DROP FOREIGN KEY `bar_bending_form_locations_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from bar_bending_form_items table
-- ALTER TABLE `bar_bending_form_items` 
-- DROP FOREIGN KEY `bar_bending_form_items_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from bar_bending_item_details table
-- ALTER TABLE `bar_bending_item_details` 
-- DROP FOREIGN KEY `bar_bending_item_details_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from bar_bending_locations table
-- ALTER TABLE `bar_bending_locations` 
-- DROP FOREIGN KEY `bar_bending_locations_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from formulas table
-- ALTER TABLE `formulas` 
-- DROP FOREIGN KEY `formulas_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from fields table
-- ALTER TABLE `fields` 
-- DROP FOREIGN KEY `fields_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- Remove user_id from forms table
-- ALTER TABLE `forms` 
-- DROP FOREIGN KEY `forms_user_id_foreign`,
-- DROP COLUMN `user_id`;

-- =====================================================
-- END OF FILE
-- =====================================================

