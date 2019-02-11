/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : smdc

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2019-01-29 10:07:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `smdc_category`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_category`;
CREATE TABLE `smdc_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(128) DEFAULT NULL COMMENT '菜品分类名称',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='菜品分类';

-- ----------------------------
-- Records of smdc_category
-- ----------------------------
INSERT INTO `smdc_category` VALUES ('2', '8', '海鲜', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `smdc_category` VALUES ('3', '8', '牛肉', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `smdc_extend_label`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_extend_label`;
CREATE TABLE `smdc_extend_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(128) DEFAULT NULL COMMENT '菜品分类名称',
  `img_url` varchar(1024) DEFAULT '' COMMENT '图片',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='推广标签';

-- ----------------------------
-- Records of smdc_extend_label
-- ----------------------------
INSERT INTO `smdc_extend_label` VALUES ('11', '8', '热门', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `smdc_extend_label` VALUES ('12', '8', '招牌', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `smdc_hot_search`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_hot_search`;
CREATE TABLE `smdc_hot_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(128) DEFAULT NULL COMMENT '菜品分类名称',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='热门搜索';

-- ----------------------------
-- Records of smdc_hot_search
-- ----------------------------

-- ----------------------------
-- Table structure for `smdc_label`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_label`;
CREATE TABLE `smdc_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(128) DEFAULT NULL COMMENT '菜品分类名称',
  `img_url` varchar(1024) DEFAULT '' COMMENT '图片',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='菜品标签';

-- ----------------------------
-- Records of smdc_label
-- ----------------------------
INSERT INTO `smdc_label` VALUES ('11', '8', '热门', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `smdc_label` VALUES ('12', '8', '招牌', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `smdc_order`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_order`;
CREATE TABLE `smdc_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `order_num` varchar(32) DEFAULT '' COMMENT '订单号码',
  `user_num` int(11) DEFAULT '0' COMMENT '就餐人数',
  `table_title` varchar(128) DEFAULT '' COMMENT '桌号名称',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格元',
  `status` tinyint(1) DEFAULT '0' COMMENT '订单状态，0待结算，1已结算',
  `note` varchar(1024) DEFAULT '' COMMENT '备注',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '下单时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_title`),
  KEY `add_time` (`add_time`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='订单';

-- ----------------------------
-- Records of smdc_order
-- ----------------------------
INSERT INTO `smdc_order` VALUES ('51', '8', '20190111093621dCIodHd', '1', 'A4', '36.00', '0', '', '2019-01-11 09:36:21', '2019-01-11 09:59:13');

-- ----------------------------
-- Table structure for `smdc_order_day`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_order_day`;
CREATE TABLE `smdc_order_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` int(11) DEFAULT '0' COMMENT '统计日期',
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `order_count` int(11) DEFAULT '0' COMMENT '订单数量',
  `order_price` decimal(10,2) DEFAULT '0.00' COMMENT '订单营业额元',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day` (`day`,`user_id`),
  KEY `day_2` (`day`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='订单每日数量';

-- ----------------------------
-- Records of smdc_order_day
-- ----------------------------
INSERT INTO `smdc_order_day` VALUES ('4', '20181125', '10', '7', '90.00');
INSERT INTO `smdc_order_day` VALUES ('5', '20181210', '8', '2', '253.00');
INSERT INTO `smdc_order_day` VALUES ('7', '20181212', '8', '19', '1502.00');
INSERT INTO `smdc_order_day` VALUES ('8', '20190110', '8', '1', '10.00');
INSERT INTO `smdc_order_day` VALUES ('12', '20190111', '8', '1', '36.00');

-- ----------------------------
-- Table structure for `smdc_order_detail`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_order_detail`;
CREATE TABLE `smdc_order_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `order_id` int(11) DEFAULT '0' COMMENT '订单ID',
  `product_id` int(11) DEFAULT '0' COMMENT '菜品ID',
  `product_gift_id` int(11) DEFAULT '0' COMMENT '赠品ID',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格元',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `add_time` (`add_time`),
  KEY `product_gift_id` (`product_gift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COMMENT='订单详细';

-- ----------------------------
-- Records of smdc_order_detail
-- ----------------------------
INSERT INTO `smdc_order_detail` VALUES ('68', '8', '51', '7', '0', '3', '30.00', '2019-01-11 09:36:21');
INSERT INTO `smdc_order_detail` VALUES ('69', '8', '51', '0', '1', '3', '6.00', '2019-01-11 09:36:21');

-- ----------------------------
-- Table structure for `smdc_product`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_product`;
CREATE TABLE `smdc_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(255) DEFAULT '' COMMENT '菜品名称',
  `category_id` int(11) DEFAULT '0' COMMENT '分类ID',
  `label_id` int(11) DEFAULT '0' COMMENT '标签ID',
  `extend_label_id` int(11) DEFAULT '0' COMMENT '推广ID',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格元',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `sale_num` int(11) DEFAULT '0' COMMENT '销量',
  `img_url` varchar(1024) DEFAULT '' COMMENT '图片',
  `is_online` tinyint(1) DEFAULT '0' COMMENT '上架，0下架，1上架',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `gift_ids` varchar(1024) DEFAULT '' COMMENT '赠品商品ID',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_online` (`is_online`),
  KEY `category_id` (`category_id`),
  KEY `label_id` (`label_id`),
  KEY `extend_label_id` (`extend_label_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='菜品管理';

-- ----------------------------
-- Records of smdc_product
-- ----------------------------
INSERT INTO `smdc_product` VALUES ('1', '8', '22222', '1', '11', null, '2.00', '78', '28', '1', '1', null, null, '');
INSERT INTO `smdc_product` VALUES ('2', '8', '22222', '222', '122', null, '4.00', '64', '46', '', '1', null, null, '');
INSERT INTO `smdc_product` VALUES ('3', '8', '222', '3', '333', null, '6.00', '90', '10', '', '1', null, null, '');
INSERT INTO `smdc_product` VALUES ('4', '8', '100111', '1', '0', '0', '0.00', '0', '0', '', '1', '2019-01-10 17:01:32', null, '');
INSERT INTO `smdc_product` VALUES ('5', '8', '100111', '1', '0', '0', '0.00', '0', '0', '', '1', '2019-01-10 17:02:07', null, '');
INSERT INTO `smdc_product` VALUES ('6', '8', '100111', '1', '0', '0', '0.00', '0', '0', '', '1', '2019-01-10 17:02:21', null, '1');
INSERT INTO `smdc_product` VALUES ('7', '8', '100111', '1', '0', '0', '10.00', '99991', '9', '', '1', '2019-01-10 17:13:13', null, '1');

-- ----------------------------
-- Table structure for `smdc_product_gift`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_product_gift`;
CREATE TABLE `smdc_product_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(255) DEFAULT '' COMMENT '菜品名称',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格元',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `sale_num` int(11) DEFAULT '0' COMMENT '销量',
  `img_url` varchar(1024) DEFAULT '' COMMENT '图片',
  `is_online` tinyint(1) DEFAULT '0' COMMENT '上架，0下架，1上架',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_online` (`is_online`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='赠品管理';

-- ----------------------------
-- Records of smdc_product_gift
-- ----------------------------
INSERT INTO `smdc_product_gift` VALUES ('1', '8', '100热望奇热网确认', '2.00', '95', '5', '', '1', '2019-01-10 14:15:28', null);

-- ----------------------------
-- Table structure for `smdc_system`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_system`;
CREATE TABLE `smdc_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_use_info` text COMMENT '软件使用说明',
  `contact_us` text COMMENT '联系我们',
  `user_use_info` text COMMENT '用户使用说明',
  `about_us` text COMMENT '关于我们',
  `system_info` text COMMENT '系统说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- Records of smdc_system
-- ----------------------------
INSERT INTO `smdc_system` VALUES ('1', '1', null, null, null, null);

-- ----------------------------
-- Table structure for `smdc_table`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_table`;
CREATE TABLE `smdc_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `title` varchar(128) DEFAULT NULL COMMENT '菜品分类名称',
  `qrcode` varchar(1024) DEFAULT '' COMMENT '二维码',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='桌号管理';

-- ----------------------------
-- Records of smdc_table
-- ----------------------------
INSERT INTO `smdc_table` VALUES ('1', '1', '124312412', null, null, null);
INSERT INTO `smdc_table` VALUES ('7', '1', '9', null, '2018-11-25 10:05:09', '2018-11-25 10:47:47');
INSERT INTO `smdc_table` VALUES ('9', '8', '10', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('10', '1', 'a1111', 'http://www.ec2order.com//upload/2018-12-23/3d0b6d44fd8b67e16afe645e4cb80a1b.png', '2018-12-23 10:12:39', '2018-12-23 23:17:31');
INSERT INTO `smdc_table` VALUES ('11', '1', 'a1', null, '2018-12-23 11:57:14', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('12', '1', 'a3', null, '2018-12-23 12:06:08', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('13', '1', 'a4', null, '2018-12-23 12:06:30', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('14', '1', 'a5', null, '2018-12-23 23:06:06', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('15', '1', 'a6', null, '2018-12-23 23:06:30', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('16', '1', 'a7', 'http://git.localhost.com/smdc/upload/2018-12-23/81961f97e02cfa129017dec16c7f24c6.png', '2018-12-23 23:08:13', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('17', '1', 'a8', 'http://www.ec2order.com//upload/2018-12-23/c30d0b865e506fd7c6e5f29c936bc4bc.png', '2018-12-23 23:14:43', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('18', '1', 'a9', 'http://www.ec2order.com//upload/2018-12-23/d1efda0f28a6b0bf3e03b2708bbb2fd3.png', '2018-12-23 23:14:59', '0000-00-00 00:00:00');
INSERT INTO `smdc_table` VALUES ('19', '1', 'a10', 'http://www.ec2order.com//upload/2018-12-23/cd9867d9d9702ea559b459a09969107b.png', '2018-12-23 23:16:12', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `smdc_user`
-- ----------------------------
DROP TABLE IF EXISTS `smdc_user`;
CREATE TABLE `smdc_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(128) DEFAULT '' COMMENT '账号',
  `show_password` varchar(128) DEFAULT '' COMMENT '明文密码',
  `password` varchar(32) DEFAULT '' COMMENT '密码',
  `salt` varchar(32) DEFAULT '' COMMENT '盐',
  `shop_name` varchar(255) DEFAULT '' COMMENT '店铺名称',
  `shop_address` varchar(255) DEFAULT '' COMMENT '店铺地址',
  `contact_phone` varchar(32) DEFAULT '' COMMENT '联系电话',
  `shop_owner_name` varchar(255) DEFAULT '' COMMENT '店长名称',
  `shop_owner_phone` varchar(32) DEFAULT '' COMMENT '店长电话',
  `business_hours` varchar(64) DEFAULT '' COMMENT '营业时间',
  `shop_img_url` varchar(1024) DEFAULT '' COMMENT '店铺照片',
  `shop_note` varchar(1204) DEFAULT '' COMMENT '店铺备注',
  `other_note` varchar(1024) DEFAULT '' COMMENT '其他说明',
  `role` varchar(16) DEFAULT 'user' COMMENT '角色, admin 超级管理员，user 用户',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除，1已删除，0未删除',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  `special_note` varchar(1024) DEFAULT '' COMMENT '特别介绍',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='用户账号店铺信息';

-- ----------------------------
-- Records of smdc_user
-- ----------------------------
INSERT INTO `smdc_user` VALUES ('1', 'admin', null, 'c6aee6a1087f19d3350ed9608a252ceb', 'zW1TWEZirlQ2Dqzk1d1HNfAp3EkIBHyc', '1111', '', '', '', '', '', '', '', '', 'admin', '0', '0000-00-00 00:00:00', '2018-11-25 10:37:11', null);
INSERT INTO `smdc_user` VALUES ('8', 'test', null, '4032cfca3d96ac950ae9bd3b5de2bda4', 'ovxAnimbbm5EpeKgkjOEhYIWy7Hfs4YL', '111111', '', '', '', '', '', '', '', '', 'user', '0', '0000-00-00 00:00:00', '2018-11-25 10:47:01', null);
INSERT INTO `smdc_user` VALUES ('9', 'test2', null, '70e71aa8644376c29d29b9fb8ea87b83', '8O72Y6HQh2gHn65LrapCg8IcKyiplIC2', '1111', '', '', '', '', '', '', '', '', 'user', '0', '2018-11-25 10:21:54', '2018-11-25 10:37:31', null);
