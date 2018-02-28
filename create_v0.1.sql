SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `batmake` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `brand` varchar(35) NOT NULL,
  `whratio` double DEFAULT NULL,
  `image` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `battery` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `make_id` int(11) NOT NULL,
  `acquired` date NOT NULL,
  `retired` datetime DEFAULT NULL COMMENT 'if date is null, then the battery is still valid. When retired, the battery is removed from lists'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `battype` (
  `id` int(11) NOT NULL,
  `type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `device` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `shortname` varchar(35) NOT NULL,
  `type_id` int(11) DEFAULT NULL COMMENT 'battery type',
  `nb_batt` int(11) DEFAULT NULL COMMENT 'number of batteries',
  `dev_type` tinyint(4) NOT NULL COMMENT '0=on sometimes, 1=constantly on, 2=charging device, 3=measuring device',
  `image` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `evt` (
  `batt_id` int(11) NOT NULL,
  `mydate` datetime NOT NULL,
  `mah` int(11) DEFAULT NULL,
  `device_id` int(11) NOT NULL,
  `evt_type` tinyint(4) NOT NULL COMMENT '0=charge, 1=measure, 2=load, 3=unload, 4=unload because empty'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(35) NOT NULL,
  `pass` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `batmake`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

ALTER TABLE `battery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`num`,`make_id`),
  ADD KEY `retired` (`retired`);

ALTER TABLE `battype`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `const_on` (`dev_type`);

ALTER TABLE `evt`
  ADD KEY `mydate` (`mydate`),
  ADD KEY `batt_id` (`batt_id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `evt_type` (`evt_type`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);


ALTER TABLE `batmake`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `battery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

ALTER TABLE `battype`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `device`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
