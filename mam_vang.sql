-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 06:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mam vang`
--

-- --------------------------------------------------------

--
-- Table structure for table `cai_dat`
--

CREATE TABLE `cai_dat` (
  `id` int(11) NOT NULL,
  `khoa_cai_dat` varchar(100) NOT NULL,
  `gia_tri` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cai_dat`
--

INSERT INTO `cai_dat` (`id`, `khoa_cai_dat`, `gia_tri`) VALUES
(1, 'walletMax', '500000');

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_don_hang`
--

CREATE TABLE `chi_tiet_don_hang` (
  `id` int(11) NOT NULL,
  `don_hang_id` int(11) NOT NULL,
  `mon_an_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `don_gia` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_don_hang`
--

INSERT INTO `chi_tiet_don_hang` (`id`, `don_hang_id`, `mon_an_id`, `so_luong`, `don_gia`) VALUES
(1, 1, 1, 1, 30000.00),
(2, 2, 4, 2, 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `don_hang_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `so_sao` int(11) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `thoi_gian_tao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `danh_gia`
--

INSERT INTO `danh_gia` (`id`, `don_hang_id`, `nguoi_dung_id`, `so_sao`, `noi_dung`, `thoi_gian_tao`) VALUES
(1, 1, 1, 5, 'Món ăn ngon, phục vụ nhanh.', '2026-09-21 10:59:52');

-- --------------------------------------------------------

--
-- Table structure for table `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`) VALUES
(3, 'Ăn vặt'),
(1, 'Món chính'),
(4, 'Tráng miệng'),
(2, 'Đồ uống');

-- --------------------------------------------------------

--
-- Table structure for table `don_hang`
--

CREATE TABLE `don_hang` (
  `id` int(11) NOT NULL,
  `ma_don` varchar(50) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `khung_gio_id` int(11) NOT NULL,
  `tong_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `phuong_thuc_thanh_toan` varchar(30) NOT NULL,
  `trang_thai` varchar(30) NOT NULL DEFAULT 'Mới',
  `thoi_gian_tao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `don_hang`
--

INSERT INTO `don_hang` (`id`, `ma_don`, `nguoi_dung_id`, `khung_gio_id`, `tong_tien`, `phuong_thuc_thanh_toan`, `trang_thai`, `thoi_gian_tao`) VALUES
(1, 'MV1001', 1, 1, 30000.00, 'wallet', 'Đang làm', '2026-09-21 10:58:25'),
(2, 'MV1002', 2, 2, 40000.00, 'cash', 'Mới', '2026-09-21 10:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `khung_gio`
--

CREATE TABLE `khung_gio` (
  `id` int(11) NOT NULL,
  `nhan` varchar(100) NOT NULL,
  `suc_chua` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khung_gio`
--

INSERT INTO `khung_gio` (`id`, `nhan`, `suc_chua`) VALUES
(1, '10:00 - 10:30', 20),
(2, '10:30 - 11:00', 20),
(3, '11:00 - 11:30', 20),
(4, '11:30 - 12:00', 20),
(5, '12:00 - 12:30', 20),
(6, '12:30 - 13:00', 20);

-- --------------------------------------------------------

--
-- Table structure for table `mon_an`
--

CREATE TABLE `mon_an` (
  `id` int(11) NOT NULL,
  `danh_muc_id` int(11) NOT NULL,
  `ten_mon` varchar(150) NOT NULL,
  `gia` decimal(12,2) NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `dang_ban` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mon_an`
--

INSERT INTO `mon_an` (`id`, `danh_muc_id`, `ten_mon`, `gia`, `hinh_anh`, `dang_ban`) VALUES
(1, 1, 'Bún bò Huế', 30000.00, NULL, 1),
(2, 1, 'Cơm gà', 35000.00, NULL, 1),
(3, 1, 'Phở bò', 35000.00, NULL, 1),
(4, 2, 'Trà đào', 20000.00, NULL, 1),
(5, 2, 'Coca Cola', 15000.00, NULL, 1),
(6, 2, 'Nước cam', 20000.00, NULL, 1),
(7, 3, 'Khoai tây chiên', 20000.00, NULL, 1),
(8, 3, 'Xúc xích', 15000.00, NULL, 1),
(9, 3, 'Cá viên chiên', 20000.00, NULL, 1),
(10, 4, 'Chè', 15000.00, NULL, 1),
(11, 4, 'Bánh flan', 15000.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `vai_tro` varchar(20) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `ten_dang_nhap` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `lop` varchar(50) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_du` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `vai_tro`, `ho_ten`, `ten_dang_nhap`, `so_dien_thoai`, `lop`, `mat_khau`, `so_du`) VALUES
(1, 'nguoi_dung', 'Nguyễn Văn An', 'sinhvien01', '0900000001', 'CNTT01', '123456', 100000.00),
(2, 'nguoi_dung', 'Trần Thị Mai', 'sinhvien02', '0900000002', 'CNTT02', '123456', 150000.00),
(3, 'nhan_vien', 'Lê Văn Nam', 'nhanvien01', '0900000003', NULL, '123456', 0.00),
(4, 'admin', 'Quản trị viên', 'admin', '0900000004', NULL, 'admin123', 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cai_dat`
--
ALTER TABLE `cai_dat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `khoa_cai_dat` (`khoa_cai_dat`);

--
-- Indexes for table `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chi_tiet_don_hang` (`don_hang_id`),
  ADD KEY `fk_chi_tiet_mon_an` (`mon_an_id`);

--
-- Indexes for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_danh_gia_don_hang` (`don_hang_id`),
  ADD KEY `fk_danh_gia_nguoi_dung` (`nguoi_dung_id`);

--
-- Indexes for table `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_danh_muc` (`ten_danh_muc`);

--
-- Indexes for table `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_don` (`ma_don`),
  ADD KEY `fk_don_hang_nguoi_dung` (`nguoi_dung_id`),
  ADD KEY `fk_don_hang_khung_gio` (`khung_gio_id`);

--
-- Indexes for table `khung_gio`
--
ALTER TABLE `khung_gio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mon_an`
--
ALTER TABLE `mon_an`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mon_an_danh_muc` (`danh_muc_id`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cai_dat`
--
ALTER TABLE `cai_dat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `khung_gio`
--
ALTER TABLE `khung_gio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mon_an`
--
ALTER TABLE `mon_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `fk_chi_tiet_don_hang` FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chi_tiet_mon_an` FOREIGN KEY (`mon_an_id`) REFERENCES `mon_an` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `fk_danh_gia_don_hang` FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_danh_gia_nguoi_dung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `fk_don_hang_khung_gio` FOREIGN KEY (`khung_gio_id`) REFERENCES `khung_gio` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_don_hang_nguoi_dung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mon_an`
--
ALTER TABLE `mon_an`
  ADD CONSTRAINT `fk_mon_an_danh_muc` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
