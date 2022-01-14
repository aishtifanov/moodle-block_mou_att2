
CREATE TABLE  `mdl_monit_att_remark` (
  `id` int(10) NOT NULL auto_increment,
  `staffid` int(10) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(500) NOT NULL default '',
  `answer` varchar(500) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `staffid_idx` (`staffid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mdl_monit_att_attestation` (
  `id` int(10) NOT NULL auto_increment,
  `yearid` int(10) unsigned NOT NULL default '4',
  `staffid` int(10) unsigned NOT NULL default '0',
  `stafftypeid` int(10) unsigned NOT NULL default '0',  
  `criteriaid` int(10) unsigned NOT NULL default '0',
  `mark` int(11) NOT NULL default '0',
  `status` tinyint(3) NOT NULL default '2',
  `namefield` varchar(200) default '',
  `fielddata` text,
  PRIMARY KEY  (`id`),
  KEY `idx_staffid` (`staffid`),
  KEY `idx_stafftypeid` (`stafftypeid`),
  KEY `idx_criteriaid` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `mdl_monit_att_criteria` (
  `id` int(10) NOT NULL auto_increment,
  `yearid` int(10) unsigned NOT NULL default '4',
  `edutypeid` int(10) unsigned NOT NULL default '0',  
  `stafftypeid` int(10) unsigned NOT NULL default '0',
  `name` text,
  `num` varchar(10) NOT NULL default '0',
  `description` varchar(255) default NULL,
  `is_loadfile` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_edutypeid` (`edutypeid`),
  KEY `idx_stafftypeid` (`stafftypeid`)    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mdl_monit_att_estimates` (
  `id` int(10) NOT NULL auto_increment,
  `criteriaid` int(10) unsigned NOT NULL default '0',
  `name` varchar(500) NOT NULL default '',
  `mark` int(11) NOT NULL default '0',
  `maxmark` int(11) default '0',
  `typefield` varchar(10) default NULL,
  `namefield` varchar(50) default NULL,
  `printname` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_criteriaid` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `mdl_monit_att_meeting_ak` (
  `id` int(10) NOT NULL auto_increment,
  `edutypeid` int(10) unsigned NOT NULL default '0',  
  `name` varchar(50) default '',
  `date_ak` date default NULL,
  `level_ak` tinyint(3) unsigned default '0',
  `type_ou` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


TRUNCATE TABLE mdl_monit_school_type;

ALTER TABLE `mdl_monit_school_type` ADD COLUMN `is_att_type` TINYINT DEFAULT 0 AFTER `cod`;
ALTER TABLE `mdl_monit_school_type` ADD COLUMN `tblname` varchar(255) default 'monit_school';
 
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (1, 'Общеобразовательные учреждения', '03', 1, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (2, 'Вечерние (сменные) общеобразовательные учреждения', '04', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (3, 'Общеобразовательное учреждение интернатного типа', '05', 1, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (4, 'Кадетская школа', '06', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (5, 'Школа-интернат с первоначальной летной подготовкой', '07', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (6, 'ГОУ начального профессионального образования', '08', 1, 'monit_college');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (7, 'ГОУ среднего профессионального образования', '09', 1, 'monit_college');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (8, 'ГОУ высшего профессионального образования', '10', 0, 'monit_college');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (9, 'Учреждения для детей-сирот и детей, оставшихся без попечения родителей', '11', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (10, 'Специальные (коррекционные) образовательные учреждения для обучающихся, воспитанников с отклонениями в развитии', '12', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (11, 'Учреждения для детей и подростков с девиантным поведением', '13', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (12, 'Учреждения для детей, нуждающихся в длительном лечении', '14', 0, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (13, 'ГОУ СПО музыкального профиля', '15', 1, 'monit_college');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (14, 'ГОУ СПО медицинского профиля', '16', 1, 'monit_college');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (15, 'Учреждение ДОД спортивного профиля', '17', 1, 'monit_udod');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (16, 'Учреждение ДОД музыкального  и художественного профиля', '18', 1, 'monit_udod');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (17, 'Учреждение ДОД общего профиля', '19', 1,  'monit_udod');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (18, 'Дошкольное ОУ', '20',  1, 'monit_education');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (19, 'Детский дом', '21', 1, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (20, 'Социально-реабилитационный центр', '22', 1, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (21, 'Коррекционное общеобразовательное учреждение', '23', 1, 'monit_school');
INSERT INTO `mdl_monit_school_type` (id, name, cod, is_att_type, tblname) VALUES  (22, 'Межшкольный учебный комбинат', '24', 1,  'monit_school');

CREATE TABLE  `mdl_monit_att_stafftype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `att_result` text,
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('учитель школы', '<p><strong><em>Для учителей русского языка, математики, истории, биологии, географии, физики, обществознания, информатики, иностранного языка:</em><br /></strong><em>- 75</em> баллов и более - высшая квалификационная категория;<br />- от 55 до 74 - первая квалификационная категория;<br />- ниже 55 баллов - нет оснований для аттестации на квалификационную категорию.<br /><br /><strong><em>Для учителей, являющихся руководителями образовательных учреждений:</em><br /></strong>- 60 баллов и более - высшая квалификационная категория;<br />- от 50 до 59 - первая квалификационная категория;<br />- ниже 50 баллов - нет оснований для аттестации на квалификационную категорию.<br /><br /><strong><em>Для учителей начальных классов:</em><br /></strong>- 65 баллов и более - высшая квалификационная категория;<br />- от 50 до 64 - первая квалификационная категория;<br />- ниже 50 баллов - нет оснований для аттестации на квалификационную категорию.<br /><br /><strong><em>Для учителей музыки, изобразительного искусства, физической культуры, технологии, православной культуры:</em><br /></strong>- 60 баллов и более - высшая квалификационная категория;<br />- от 45 до 59 - первая квалификационная категория;<br />- ниже 45 баллов - нет оснований для аттестации на квалификационную категорию.</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('воспитатель ГПД школы', '<p><strong>Если педагогический работник набирает</strong></p> <p>- 50 баллов и более &#150; высшая квалификационная категория;</p> <p>- от 40 до 49 &#150; первая квалификационная категория;</p> <p>- ниже 40 баллов &#150; нет оснований для аттестации на квалификационную категорию;</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('педагог-психолог школы', '<p><strong>Если педагогический работник набирает</strong></p><p>- от 60 баллов и более &#150; высшая квалификационная категория;</p><p>- от 50 до 59 &#150; первая квалификационная категория;</p><p>- ниже 50 баллов &#150; нет оснований для аттестации на квалификационную категорию</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('социальный педагог школы', '<p><strong>Если педагогический работник набирает</strong></p><p>- от 60 баллов и более &#150; высшая квалификационная категория;</p><p>- от 50 до 59 &#150; первая квалификационная категория;</p><p>- ниже 50 баллов &#150; нет оснований для аттестации на квалификационную категорию</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('старший вожатый школы', '<p><strong>Если педагогический работник набирает</strong></p> <p>- 50 баллов и более &#150; высшая квалификационная категория;</p> <p>- от 40 до 49 &#150; первая квалификационная категория;</p> <p>- ниже 40 баллов &#150; нет оснований для аттестации на квалификационную категорию;</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('учитель-логопед школы', '<p><strong>Если педагогический работник набирает</strong></p> <p>- 55 баллов и более - высшая квалификационная категория;<br />- от 45 до 54 - первая квалификационная категория;<br />- ниже 45 баллов - нет оснований для аттестации на квалификационную категорию.</p>');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('преподаватель СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('мастер производственного обучения СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('воспитатель СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('методист СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('руководитель физвоспитания СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('преподаватель музыкального колледжа', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('концертмейстер музыкального колледжа', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('воспитатель ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('музыкальный руководитель ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('педагог дополнительного образования ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('инструктор по физвоспитанию ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('учитель-логопед ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('педагог-психолог ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('преподаватель ДШИ, ДХИ, ДМШ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('тренер-преподаватель ДОД');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('педагог дополнительного образования ДОД', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('методист ДОД', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('руководитель школы', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('руководитель СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('зав. отделением СПО НПО', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('концертмейстер НПО СПО (устаревш)', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('концертмейстер УДОД (устаревш)', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('руководитель УДОД', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('воспитатель школы-интерната', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('старший воспитатель ДОУ', '');
INSERT INTO `mdl_monit_att_stafftype` (name, att_result) VALUES ('старший мастер НПО СПО', '');


CREATE TABLE `mdl_monit_att_staff` (
  `id` int(10) NOT NULL auto_increment,
  `rayonid` int(10) unsigned NOT NULL default '0',
  `schoolid` int(10) unsigned NOT NULL default '0',
  `collegeid` int(10) unsigned NOT NULL default '0',
  `udodid` int(10) unsigned NOT NULL default '0',
  `douid` int(10) unsigned NOT NULL default '0',  
  `edutypeid` int(10) unsigned NOT NULL default '0',  
  `userid` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(1) default '0',
  `listegeids` varchar(255) default '',
  `listmiids` varchar(255) default NULL,
  `pswtxt` varchar(20) default NULL,
  `graduate` varchar(20) NOT NULL default '',
  `birthday` date default NULL,
  `whatgraduated` varchar(255) NOT NULL default '',
  `speciality` varchar(255) default NULL,
  `totalstanding` float NOT NULL default '0',
  `yeargraduate` int(10) unsigned NOT NULL default '0',
  `gos_awards` varchar(255) default '',
  `reg_awards` varchar(255) default '',
  `thanks` varchar(255) default '',
  `brevet` varchar(255) default '',
  `timemodified` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_rayonid` (`rayonid`),
  KEY `idx_schoolid` (`schoolid`),
  KEY `idx_edutypeid` (`edutypeid`),  
  KEY `idx_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
  
  
CREATE TABLE `mdl_monit_att_appointment` (  
  `id` int(10) NOT NULL auto_increment,
  `staffid` int(10) unsigned NOT NULL default '0',
  `stafftypeid` int(10) unsigned NOT NULL default '0',
  `meetingid` int(10) unsigned NOT NULL default '0',  
  `appointment` varchar(255) NOT NULL default '',
  `pedagog_time` float NOT NULL default '0',
  `standing` float NOT NULL default '0',
  `standing_this` float NOT NULL default '0',
  `qualify` varchar(45) default '',
  `qualify_date` date default NULL,
  `qualifynow` varchar(45) default '',  
  `place_advan_train` varchar(255) default '',
  `date_advan_train` date default NULL,
  `total_mark` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_staffid` (`staffid`),
  KEY `idx_stafftypeid` (`stafftypeid`),
  KEY `idx_meetingid` (`meetingid`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mdl_monit_att_staffshared` (
  `id` int(10) NOT NULL auto_increment,
  `staffid` int(10) unsigned NOT NULL default '0',
  `schoolid` int(10) unsigned NOT NULL default '0',
  `collegeid` int(10) unsigned NOT NULL default '0',
  `udodid` int(10) unsigned NOT NULL default '0',
  `douid` int(10) unsigned NOT NULL default '0',  
  `edutypeid` int(10) unsigned NOT NULL default '0',  
  PRIMARY KEY  (`id`),
  KEY `idx_staffid` (`staffid`),
  KEY `idx_edutypeid` (`edutypeid`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `mdl_monit_att_stst` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `edutypeid` int(10) unsigned NOT NULL default '0',
  `stafftypeid` int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
