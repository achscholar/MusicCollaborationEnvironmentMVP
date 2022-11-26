-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2022 at 10:35 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `songshome`
--

USE `dcd3255_songshome`;

-- --------------------------------------------------------

--
-- Table structure for table `collaborations`
--

DROP TABLE IF EXISTS `collaborations`;
CREATE TABLE `collaborations` (
  `id` bigint(20) NOT NULL,
  `songId` bigint(20) NOT NULL,
  `collaboration_title` varchar(450) COLLATE utf8_bin NOT NULL,
  `created_by_member_id` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `collaboration_key` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `collaborations`
--

INSERT INTO `collaborations` (`id`, `songId`, `collaboration_title`, `created_by_member_id`, `created`, `collaboration_key`) VALUES
(46, 1, 'Melody &amp; Lyrics Brainstorming', 1002, '2022-11-17 06:07:52', '828m72wnkxn4'),
(47, 1, 'Putting It All Together', 1006, '2022-11-17 06:41:50', '1wd8k4254712'),
(49, 25, 'Ideas', 1005, '2022-11-21 23:35:21', '19bq23r7tr8x');

-- --------------------------------------------------------

--
-- Table structure for table `collaboration_participants`
--

DROP TABLE IF EXISTS `collaboration_participants`;
CREATE TABLE `collaboration_participants` (
  `id` bigint(20) NOT NULL,
  `songId` bigint(20) NOT NULL,
  `collaboration_id` bigint(20) NOT NULL,
  `member_id` bigint(20) NOT NULL,
  `joined_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `collaboration_key` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `collaboration_participants`
--

INSERT INTO `collaboration_participants` (`id`, `songId`, `collaboration_id`, `member_id`, `joined_on`, `collaboration_key`) VALUES
(179, 1, 46, 1002, '2022-11-17 06:07:52', '828m72wnkxn4'),
(181, 1, 46, 1005, '2022-11-17 06:11:50', '828m72wnkxn4'),
(182, 1, 46, 1001, '2022-11-17 06:11:56', '828m72wnkxn4'),
(183, 1, 46, 1006, '2022-11-17 06:11:56', '828m72wnkxn4'),
(184, 1, 47, 1006, '2022-11-17 06:41:50', '1wd8k4254712'),
(185, 1, 47, 1001, '2022-11-17 06:41:56', '1wd8k4254712'),
(187, 25, 49, 1005, '2022-11-21 23:35:21', '19bq23r7tr8x');

-- --------------------------------------------------------

--
-- Table structure for table `collaboration_posts`
--

DROP TABLE IF EXISTS `collaboration_posts`;
CREATE TABLE `collaboration_posts` (
  `id` bigint(20) NOT NULL,
  `collaboration_id` bigint(20) NOT NULL,
  `member_id` bigint(20) NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_label` varchar(210) COLLATE utf8_bin NOT NULL,
  `file_key` varchar(12) COLLATE utf8_bin NOT NULL,
  `filename` varchar(210) COLLATE utf8_bin NOT NULL,
  `mbSize` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `link` varchar(5000) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `collaboration_posts`
--

INSERT INTO `collaboration_posts` (`id`, `collaboration_id`, `member_id`, `content`, `uploaded`, `file_label`, `file_key`, `filename`, `mbSize`, `link`) VALUES
(24, 46, 1002, 'Hello everyone, let\'s get started on working on the UNCA Anthem. Please post any melodies that you have in mind.', '2022-11-17 16:10:21', '', '', '', '0', ''),
(27, 46, 1005, 'I have been working on this melody for a while and I think that it would be a good fit. What does everyone think?', '2022-11-17 16:17:08', 'My Melody', '229p778123x8', 'postfile_27_229p778123x8.mp3', '0.04', ''),
(30, 46, 1002, 'I made a few additions to the extended melody Emily posted.', '2022-11-17 16:27:06', 'Updated Melody', '91pg1v1sv24t', 'postfile_30_91pg1v1sv24t.mp3', '0.04', ''),
(32, 46, 1001, 'That is perfect Thomas. It sounds great! We will use this melody for the UNCA Anthem. I will request the public to make their own additions.', '2022-11-17 16:30:43', '', '', '', '0', ''),
(33, 46, 1002, 'Everyone, please start thinking about the lyrics.', '2022-11-17 16:39:44', '', '', '', '0', '');

-- --------------------------------------------------------

--
-- Table structure for table `collaboration_requests`
--

DROP TABLE IF EXISTS `collaboration_requests`;
CREATE TABLE `collaboration_requests` (
  `id` bigint(20) NOT NULL,
  `collaboration_id` bigint(20) NOT NULL,
  `member_id` bigint(20) NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_for` text COLLATE utf8_bin DEFAULT NULL,
  `open` int(1) NOT NULL DEFAULT 1,
  `target_post_id` bigint(20) NOT NULL,
  `target_filename` varchar(210) COLLATE utf8_bin NOT NULL,
  `target_filelabel` varchar(210) COLLATE utf8_bin NOT NULL,
  `target_filekey` varchar(12) COLLATE utf8_bin NOT NULL,
  `target_link` varchar(5000) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `collaboration_requests`
--

INSERT INTO `collaboration_requests` (`id`, `collaboration_id`, `member_id`, `content`, `uploaded`, `request_for`, `open`, `target_post_id`, `target_filename`, `target_filelabel`, `target_filekey`, `target_link`) VALUES
(34, 46, 1001, 'I love it! But could you make it a little longer?', '2022-11-17 16:18:13', '1005', 0, 27, 'postfile_27_229p778123x8.mp3', 'My Melody', '229p778123x8', ''),
(35, 46, 1001, 'Hello everyone, we are excited to announce that there will be a new UNCA Anthem and anyone from our community can contribute. You can download the melody we have created so far and then upload your version to this page. Thank you! We appreciate your contributions.', '2022-11-17 16:33:07', 'PUBLIC', 1, 30, 'postfile_30_91pg1v1sv24t.mp3', 'Updated Melody', '91pg1v1sv24t', '');

-- --------------------------------------------------------

--
-- Table structure for table `collaboration_request_files`
--

DROP TABLE IF EXISTS `collaboration_request_files`;
CREATE TABLE `collaboration_request_files` (
  `id` bigint(20) NOT NULL,
  `collaboration_id` bigint(20) NOT NULL,
  `collaboration_requests_id` bigint(20) NOT NULL,
  `member_id` bigint(20) DEFAULT NULL,
  `filename` varchar(210) COLLATE utf8_bin NOT NULL,
  `file_key` varchar(210) COLLATE utf8_bin NOT NULL,
  `mbSize` varchar(15) COLLATE utf8_bin NOT NULL,
  `link` varchar(1000) COLLATE utf8_bin NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_public_upload` timestamp NULL DEFAULT NULL,
  `visitor_firstname` varchar(50) COLLATE utf8_bin NOT NULL,
  `visitor_lastname` varchar(50) COLLATE utf8_bin NOT NULL,
  `visitor_email` varchar(320) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `visitor_key` varchar(30) COLLATE utf8_bin NOT NULL,
  `verified` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `collaboration_request_files`
--

INSERT INTO `collaboration_request_files` (`id`, `collaboration_id`, `collaboration_requests_id`, `member_id`, `filename`, `file_key`, `mbSize`, `link`, `uploaded`, `last_public_upload`, `visitor_firstname`, `visitor_lastname`, `visitor_email`, `visitor_key`, `verified`) VALUES
(38, 46, 34, 1005, 'postrequest_38_1h78k81pxt24.mp3', '1h78k81pxt24', '0.04', '', '2022-11-17 16:19:10', NULL, '', '', '', '', 1),
(64, 46, 35, NULL, 'postrequest_64_m1qs5r45k44m.mp3', 'm1qs5r45k44m', '0.04', '', '2022-11-25 07:03:42', '2022-11-25 07:03:47', 'John', 'Smith', 'johnsmith@gmail.com', 'f2rp3mrcrqdn1n297k8138g75gt1p8', 1),
(65, 46, 35, NULL, 'postrequest_65_95k3t9jbf472.mp3', '95k3t9jbf472', '0.04', '', '2022-11-25 07:04:24', '2022-11-25 07:04:32', 'Berry', 'Smith', 'berrysmith@gmail.com', '8xq392d81dg591p9p1l81kr91jl873', 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` bigint(20) NOT NULL,
  `groupName` varchar(210) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `groupPicture` varchar(210) COLLATE utf8_bin NOT NULL,
  `groupPictureMBSize` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `dataLimitGB` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `groupKey` varchar(12) COLLATE utf8_bin NOT NULL DEFAULT 'a12b123c1234',
  `relationalGroupId` int(15) DEFAULT NULL,
  `linkAccessOnly` tinyint(1) NOT NULL DEFAULT 0,
  `toDelete` tinyint(1) NOT NULL DEFAULT 0,
  `locationIndex` text COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `groupName`, `created`, `groupPicture`, `groupPictureMBSize`, `dataLimitGB`, `groupKey`, `relationalGroupId`, `linkAccessOnly`, `toDelete`, `locationIndex`) VALUES
(1, 'UNCA Music Department ', '2022-09-13 21:12:28', '', '0', '15', '997vt47xp7z8', NULL, 1, 0, NULL),
(6, 'MUSC 129 - Jazz', '2022-10-14 20:09:32', '', '0', '0', '52v1t2lmfr9f', 1, 0, 0, '1,'),
(7, 'MUSC 101 - Class Piano', '2022-10-14 20:10:45', '', '0', '0', '28gn5pwz93t9', 1, 0, 0, '1,'),
(8, 'MUSC 121 - Wind Ensemble', '2022-10-14 20:11:09', '', '0', '0', 'c9j9msw9k913', 1, 0, 0, '1,'),
(9, 'MUSC 124 - Blue Ridge Orchestra', '2022-10-14 20:11:28', '', '0', '0', 'zvt57qgtd55d', 1, 0, 0, '1,'),
(11, 'User Testing', '2022-10-28 15:12:27', '', '0', '15', '2hj8dqlcgj8c', NULL, 0, 0, NULL),
(19, 'Sub', '2022-11-01 22:18:06', '', '0', '0', '84qz21dxv523', 11, 0, 0, '11,');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `linkId` bigint(20) NOT NULL,
  `songId` bigint(20) NOT NULL,
  `linkType` tinyint(1) NOT NULL,
  `linkData` varchar(5000) COLLATE utf8_bin NOT NULL,
  `linkName` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `linkSort` bigint(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`linkId`, `songId`, `linkType`, `linkData`, `linkName`, `linkSort`, `created`) VALUES
(248, 25, 2, 'PLzG044WlhJC8C2LtDFr266pAjxCMrXLn3', 'UNCA Loves Music', 11, '2022-11-21 19:38:15'),
(249, 25, 1, 'G7yE79zZzjo', 'Vocal Jazz Ensemble', 12, '2022-11-21 19:38:15'),
(250, 1, 0, 'https://music.unca.edu/', 'Release Details', 13, '2022-11-22 16:57:49'),
(251, 1, 0, 'https://www.youtube.com/channel/UCOyx4wVtjMUotU26Mm4jZVA', 'Visit Youtube Page', 14, '2022-11-22 16:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `member_id` bigint(20) NOT NULL,
  `parent_group_id` bigint(20) DEFAULT 0,
  `user_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `pass_word` varchar(255) COLLATE utf8_bin NOT NULL,
  `access_level` tinyint(2) NOT NULL,
  `firstname` varchar(50) COLLATE utf8_bin NOT NULL,
  `lastname` varchar(50) COLLATE utf8_bin NOT NULL,
  `user_email` varchar(320) COLLATE utf8_bin NOT NULL,
  `updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `parent_group_id`, `user_name`, `pass_word`, `access_level`, `firstname`, `lastname`, `user_email`, `updated`) VALUES
(1000, 1, 'a34rwfsafda', 'sadfq3rqwe', 11, 'Sitewide', 'Admin', 'support@unca.edu', '2022-11-01 19:00:48'),
(1001, 1, 'weiuohaskndf', 'ewipjq89uiwe', 5, 'Michael', 'Smith', 'michaelsmith@gmail.com', '2022-11-01 20:43:36'),
(1002, 1, 'qfwqiljefs', 'wef9uielqweiuj', 1, 'Thomas', 'Clark', 'thomasclark@gmail.com', '2022-11-01 20:45:03'),
(1004, 11, 'foreign', 'dasfqwesdfa', 5, 'Foreign', 'User', 'foreign@gmail.com', '2022-11-06 01:15:28'),
(1005, 1, 'sadfasfasfd', 'q3rwefasfadsf', 1, 'Emily', 'Davis', 'emilydavis@gmail.com', '2022-11-10 00:27:23'),
(1006, 1, 'sfdsafsafd', 'asdfsafdsaf', 5, 'Jennifer', 'Miller', 'jennifermiller@gmail.com', '2022-11-16 14:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `music`
--

DROP TABLE IF EXISTS `music`;
CREATE TABLE `music` (
  `musicId` bigint(20) NOT NULL,
  `songId` bigint(20) NOT NULL,
  `musicName` varchar(210) COLLATE utf8_bin NOT NULL,
  `musicKey` varchar(12) COLLATE utf8_bin NOT NULL DEFAULT 'a12b123c1234',
  `musicFileName` varchar(210) COLLATE utf8_bin NOT NULL,
  `mbSize` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `musicSort` bigint(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `inlcludeInGroupPlayer` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `music`
--

INSERT INTO `music` (`musicId`, `songId`, `musicName`, `musicKey`, `musicFileName`, `mbSize`, `musicSort`, `created`, `inlcludeInGroupPlayer`) VALUES
(14, 1, 'UNCA Melody', '4fw5j2h2g32r', 'audio_14_4fw5j2h2g32r.mp3', '0.04', 0, '2022-09-28 20:09:58', 1),
(21, 1, 'UNCA Anthem', '88j1m4r9qm34', 'audio_21_88j1m4r9qm34.mp3', '0.04', 1, '2022-11-17 00:44:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

DROP TABLE IF EXISTS `pictures`;
CREATE TABLE `pictures` (
  `pictureId` bigint(20) NOT NULL,
  `songId` bigint(20) NOT NULL,
  `pictureKey` varchar(12) COLLATE utf8_bin NOT NULL DEFAULT 'a12b123c1234',
  `pictureFileName` varchar(210) COLLATE utf8_bin NOT NULL,
  `mbSize` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `pictureSort` bigint(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `pictures`
--

INSERT INTO `pictures` (`pictureId`, `songId`, `pictureKey`, `pictureFileName`, `mbSize`, `pictureSort`, `created`) VALUES
(48, 1, 'mgl38r4tx8q5', 'image_48_mgl38r4tx8q5.jpg', '0.43', 0, '2022-10-18 16:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

DROP TABLE IF EXISTS `songs`;
CREATE TABLE `songs` (
  `id` bigint(20) NOT NULL,
  `groupId` bigint(20) NOT NULL,
  `title` varchar(450) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `songNumber` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `lyrics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `songKey` varchar(12) COLLATE utf8_bin NOT NULL DEFAULT 'a12b123c1234',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `linkAccessOnly` tinyint(1) NOT NULL DEFAULT 0,
  `toDelete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `groupId`, `title`, `songNumber`, `lyrics`, `songKey`, `created`, `linkAccessOnly`, `toDelete`) VALUES
(1, 1, 'UNCA Anthem', '1', '<p>Lyrics</p>', '8z5n2n744lnh', '2022-11-22 21:57:49', 0, 0),
(18, 11, 'Test Security Release', '', '<p>norm</p>', '8kg2j1lzdmqd', '2022-11-02 17:13:53', 0, 0),
(23, 11, 'Test', '', '', 'rbrb912h9p81', '2022-11-04 18:54:49', 0, 0),
(25, 1, 'UNCA Music Department Music', '', '<p>New Releases Coming Soon!</p>', 'xk29323pq75h', '2022-11-22 00:38:15', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collaborations`
--
ALTER TABLE `collaborations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `songcollaborationsToSongsForeignRelation` (`songId`),
  ADD KEY `collaborationKey` (`collaboration_key`);

--
-- Indexes for table `collaboration_participants`
--
ALTER TABLE `collaboration_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collaboration_id` (`collaboration_id`,`member_id`),
  ADD KEY `collaborationToCollaborationsForeignRelation` (`collaboration_id`),
  ADD KEY `memberidToMembersForeignRelation` (`member_id`),
  ADD KEY `collaborationkeyToCollaborationsForeignRelation` (`collaboration_key`),
  ADD KEY `songidToCollaborationsForeignRelation` (`songId`);

--
-- Indexes for table `collaboration_posts`
--
ALTER TABLE `collaboration_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collaborationfeedbackToCollaborationsForeignRelation` (`collaboration_id`);

--
-- Indexes for table `collaboration_requests`
--
ALTER TABLE `collaboration_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collabidToCollabForeignRelation` (`collaboration_id`);

--
-- Indexes for table `collaboration_request_files`
--
ALTER TABLE `collaboration_request_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collabidrequestfilesToCollabForeignRelation` (`collaboration_id`),
  ADD KEY `collabrequestsidrequestfilesToCollabRequestsForeignRelation` (`collaboration_requests_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupKey` (`groupKey`),
  ADD KEY `relationalGroupId` (`relationalGroupId`),
  ADD KEY `locationIndex` (`locationIndex`(1024));

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`linkId`),
  ADD KEY `songId` (`songId`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `user_name` (`user_name`) USING BTREE,
  ADD UNIQUE KEY `user_email` (`member_id`);

--
-- Indexes for table `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`musicId`),
  ADD KEY `musicKey` (`musicKey`),
  ADD KEY `songId` (`songId`);

--
-- Indexes for table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`pictureId`),
  ADD KEY `songId` (`songId`),
  ADD KEY `pictureKey` (`pictureKey`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupId` (`groupId`,`songNumber`,`songKey`),
  ADD KEY `title` (`title`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `collaborations`
--
ALTER TABLE `collaborations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `collaboration_participants`
--
ALTER TABLE `collaboration_participants`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `collaboration_posts`
--
ALTER TABLE `collaboration_posts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `collaboration_requests`
--
ALTER TABLE `collaboration_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `collaboration_request_files`
--
ALTER TABLE `collaboration_request_files`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `linkId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1007;

--
-- AUTO_INCREMENT for table `music`
--
ALTER TABLE `music`
  MODIFY `musicId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `pictureId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collaborations`
--
ALTER TABLE `collaborations`
  ADD CONSTRAINT `songcollaborationsToSongsForeignRelation` FOREIGN KEY (`songId`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collaboration_participants`
--
ALTER TABLE `collaboration_participants`
  ADD CONSTRAINT `collaborationToCollaborationsForeignRelation` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collaborationkeyToCollaborationsForeignRelation` FOREIGN KEY (`collaboration_key`) REFERENCES `collaborations` (`collaboration_key`) ON DELETE CASCADE,
  ADD CONSTRAINT `memberidToMembersForeignRelation` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `songidToCollaborationsForeignRelation` FOREIGN KEY (`songId`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collaboration_posts`
--
ALTER TABLE `collaboration_posts`
  ADD CONSTRAINT `collaborationfeedbackToCollaborationsForeignRelation` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collaboration_requests`
--
ALTER TABLE `collaboration_requests`
  ADD CONSTRAINT `collabidToCollabForeignRelation` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collaboration_request_files`
--
ALTER TABLE `collaboration_request_files`
  ADD CONSTRAINT `collabidrequestfilesToCollabForeignRelation` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collabrequestsidrequestfilesToCollabRequestsForeignRelation` FOREIGN KEY (`collaboration_requests_id`) REFERENCES `collaboration_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `songlinksToSongsForeignRelation` FOREIGN KEY (`songId`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `music`
--
ALTER TABLE `music`
  ADD CONSTRAINT `songmusicToSongsForeignRelation` FOREIGN KEY (`songId`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pictures`
--
ALTER TABLE `pictures`
  ADD CONSTRAINT `songpicturesToSongsForeignRelation` FOREIGN KEY (`songId`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songsToGroupsForeignRelation` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
