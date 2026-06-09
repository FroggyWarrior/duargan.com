-- phpMyAdmin SQL Dump
-- version 5.2.2
-- Example Data for Artist Portfolio CMS
-- Default Admin Credentials: Username: admin | Password: admin (2FA is disabled)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Drop tables in reverse order of dependencies to avoid foreign key errors
-- --------------------------------------------------------
DROP TABLE IF EXISTS `song_platforms`;
DROP TABLE IF EXISTS `song_genres`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `song_types`;
DROP TABLE IF EXISTS `social_media`;
DROP TABLE IF EXISTS `platforms`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `announcement`;
DROP TABLE IF EXISTS `admin_credentials`;

-- --------------------------------------------------------
-- Table structure for table `admin_credentials`
-- --------------------------------------------------------
CREATE TABLE `admin_credentials` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `2fa_enabled` tinyint(1) DEFAULT 0,
  `2fa_secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_credentials` (`id`, `username`, `password`, `updated_at`, `2fa_enabled`, `2fa_secret`) VALUES
(1, 'ndEBGuBI/ZpPBxiAwcppQU9MT2dXZzd2N2t2bUxEODlPNEVyY2c9PQ==', '$2y$10$Az888HN0qgur9peT5VTr4.VAtWtRV/swViwrJgaoUu.HVdW6DWrHa', CURRENT_TIMESTAMP, 0, NULL);

-- --------------------------------------------------------
-- Table structure for table `announcement`
-- --------------------------------------------------------
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `background_color` varchar(7) NOT NULL DEFAULT '#6750a4',
  `text` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `announcement` (`id`, `title`, `background_color`, `text`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'New Single Out Now!', '#6750a4', 'Check out my latest track "Midnight Echoes", available on all major streaming platforms!', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- --------------------------------------------------------
-- Table structure for table `genres`
-- --------------------------------------------------------
CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `genres` (`id`, `name`, `slug`, `is_active`, `created_at`) VALUES
(1, 'Electronic', 'electronic', 1, CURRENT_TIMESTAMP),
(2, 'Lo-fi', 'lo-fi', 1, CURRENT_TIMESTAMP),
(3, 'Synthwave', 'synthwave', 1, CURRENT_TIMESTAMP);

-- --------------------------------------------------------
-- Table structure for table `platforms`
-- Note: Split into individual INSERTs to prevent phpMyAdmin parser timeouts with long SVG strings
-- --------------------------------------------------------
CREATE TABLE `platforms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `base_url` varchar(500) DEFAULT NULL,
  `icon_svg` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `color` varchar(7) DEFAULT '#666666'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `platforms` (`id`, `name`, `slug`, `base_url`, `icon_svg`, `display_order`, `is_active`, `created_at`, `color`) VALUES (1, 'Spotify', 'spotify', 'https://open.spotify.com/track/', '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02z"/></svg>', 1, 1, CURRENT_TIMESTAMP, '#1db954');
INSERT INTO `platforms` (`id`, `name`, `slug`, `base_url`, `icon_svg`, `display_order`, `is_active`, `created_at`, `color`) VALUES (2, 'YouTube', 'youtube', 'https://www.youtube.com/watch?v=', '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>', 2, 1, CURRENT_TIMESTAMP, '#ff0000');

-- --------------------------------------------------------
-- Table structure for table `social_media`
-- --------------------------------------------------------
CREATE TABLE `social_media` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `base_url` varchar(500) DEFAULT NULL,
  `icon_svg` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `social_media` (`id`, `name`, `slug`, `base_url`, `icon_svg`, `display_order`, `is_active`, `created_at`) VALUES (1, 'Instagram', 'instagram', 'https://instagram.com/artist', '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.849.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>', 1, 1, CURRENT_TIMESTAMP);

-- --------------------------------------------------------
-- Table structure for table `songs`
-- --------------------------------------------------------
CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `release_date` date DEFAULT NULL,
  `cover_image_url` varchar(500) DEFAULT NULL,
  `genres` varchar(500) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `songs` (`id`, `title`, `release_date`, `cover_image_url`, `genres`, `type_id`, `created_at`) VALUES
(1, 'Midnight Echoes', '2023-10-15', 'img/covers/example1.webp', NULL, 1, CURRENT_TIMESTAMP),
(2, 'Neon Dreams (Lo-fi Remix)', '2023-11-20', 'img/covers/example2.webp', NULL, 2, CURRENT_TIMESTAMP),
(3, 'Summer Breeze', '2024-01-05', 'img/covers/example3.webp', NULL, 3, CURRENT_TIMESTAMP);

-- --------------------------------------------------------
-- Table structure for table `song_genres`
-- --------------------------------------------------------
CREATE TABLE `song_genres` (
  `song_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `song_genres` (`song_id`, `genre_id`) VALUES
(1, 1), (1, 3), 
(2, 2),         
(3, 1);         

-- --------------------------------------------------------
-- Table structure for table `song_platforms`
-- --------------------------------------------------------
CREATE TABLE `song_platforms` (
  `song_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `track_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `song_platforms` (`song_id`, `platform_id`, `track_url`) VALUES
(1, 1, 'https://open.spotify.com/track/example1'),
(1, 2, 'https://www.youtube.com/watch?v=example1'),
(2, 1, 'https://open.spotify.com/track/example2'),
(3, 2, 'https://www.youtube.com/watch?v=example3');

-- --------------------------------------------------------
-- Table structure for table `song_types`
-- --------------------------------------------------------
CREATE TABLE `song_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `song_types` (`id`, `name`, `slug`, `is_active`, `created_at`) VALUES
(1, 'Official Release', 'official', 1, CURRENT_TIMESTAMP),
(2, 'Remix', 'remix', 1, CURRENT_TIMESTAMP),
(3, 'Free Download', 'free', 1, CURRENT_TIMESTAMP);

-- --------------------------------------------------------
-- Indexes for dumped tables
-- --------------------------------------------------------
ALTER TABLE `admin_credentials` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`);
ALTER TABLE `announcement` ADD PRIMARY KEY (`id`);
ALTER TABLE `genres` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`), ADD UNIQUE KEY `slug` (`slug`);
ALTER TABLE `platforms` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`), ADD UNIQUE KEY `slug` (`slug`);
ALTER TABLE `social_media` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`), ADD UNIQUE KEY `slug` (`slug`);
ALTER TABLE `songs` ADD PRIMARY KEY (`id`);
ALTER TABLE `song_genres` ADD PRIMARY KEY (`song_id`,`genre_id`), ADD KEY `genre_id` (`genre_id`);
ALTER TABLE `song_platforms` ADD PRIMARY KEY (`song_id`,`platform_id`), ADD KEY `platform_id` (`platform_id`);
ALTER TABLE `song_types` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`), ADD UNIQUE KEY `slug` (`slug`);

-- --------------------------------------------------------
-- AUTO_INCREMENT for dumped tables
-- --------------------------------------------------------
ALTER TABLE `admin_credentials` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `announcement` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `genres` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `platforms` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `social_media` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `songs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `song_types` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------
-- Constraints for dumped tables
-- --------------------------------------------------------
ALTER TABLE `song_genres`
  ADD CONSTRAINT `song_genres_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `song_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE;

ALTER TABLE `song_platforms`
  ADD CONSTRAINT `song_platforms_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `song_platforms_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;