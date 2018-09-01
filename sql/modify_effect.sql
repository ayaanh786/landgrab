--
-- Dumping data for table `modify_effect`
--

INSERT INTO `modify_effect` (`id`, `name`, `sort_order`, `is_land_upgrade`, `is_embassy`, `is_sanctions`, `population`, `culture`, `gdp`, `treasury`, `defense`, `military`, `support`, `created`) VALUES
(1, 'unclaimed', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2016-11-09 06:26:05'),
(2, 'village', 1, 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '2016-11-09 06:26:51'),
(3, 'town', 2, 1, 0, 0, 100, 0, 10, 0, 2, 0, 0, '2016-11-09 06:27:27'),
(4, 'city', 3, 1, 0, 0, 1000, 3, 100, 0, 3, 0, 0, '2016-11-09 06:27:58'),
(5, 'metropolis', 4, 1, 0, 0, 10000, 10, 1000, 0, 4, 0, 0, '2016-11-09 06:28:34'),
(6, 'fortification', 2, 1, 0, 0, 10, 0, 1, -100, 10, 25, 0, '2016-11-25 02:24:53'),
(10, 'capitol', 5, 1, 0, 0, 1000, 50, 1000, 0, 5, 100, 100, '2016-11-09 06:29:47'),
(11, 'skyscraper', 11, 0, 0, 0, 5000, 1, 200, -250, 0, 0, 0, '2016-11-09 06:45:22'),
(12, 'factory', 10, 0, 0, 0, 0, -1, 80, -100, 0, 120, 0, '2016-11-09 06:45:12'),
(14, 'hospital', 9, 0, 0, 0, 3000, 0, 0, -50, 0, 0, 3, '2016-11-09 06:33:05'),
(15, 'military_base', 8, 0, 0, 0, 0, 0, 0, -300, 0, 600, 5, '2016-11-09 06:34:10'),
(16, 'school', 6, 0, 0, 0, 0, 5, 70, -100, 0, 0, 5, '2016-11-09 06:35:48'),
(17, 'embassy', 50, 0, 1, 0, 1000, 100, 100, 0, 0, 300, 25, '2017-04-29 15:20:18'),
(18, 'stadium', 12, 0, 0, 0, 0, 3, 200, -500, 0, 0, 15, '2017-05-04 07:20:32'),
(19, 'courthouse', 7, 0, 0, 0, 0, 0, 0, -50, 0, 30, 5, '2017-05-04 07:27:41'),
(20, 'park', 15, 0, 0, 0, 0, 10, 0, -50, 0, 0, 2, '2017-05-05 10:46:05'),
(21, 'sanctions', 55, 0, 0, 1, 0, 0, -300, 0, 0, 0, -25, '2018-08-06 22:30:07');